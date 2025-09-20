<?php
session_start();
include('connection.php');

if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupee($n){ return number_format((float)$n, 2); }

$orders = []; // [order_id => ['meta'=>..., 'items'=>[...]]]

if ($userId > 0 && isset($con)) {
    $sql = "
        SELECT 
            o.id AS order_id,
            o.total_amount,
            o.status,
            o.payment_method,
            COALESCE(o.created_at, o.id) AS created_at,
            oi.product_id,
            oi.product_name,
            oi.price,
            oi.quantity,
            p.product_image
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        LEFT JOIN product p ON p.id = oi.product_id
        WHERE o.user_id = ?
        ORDER BY o.id DESC, oi.id ASC
        LIMIT 500
    ";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $oid = (int)$r['order_id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'meta' => [
                    'order_id' => $oid,
                    'total_amount' => (float)$r['total_amount'],
                    'status' => (string)$r['status'],
                    'payment_method' => (string)$r['payment_method'],
                    'created_at' => (string)$r['created_at'],
                ],
                'items' => []
            ];
        }
        $orders[$oid]['items'][] = [
            'product_id' => (int)$r['product_id'],
            'product_name' => $r['product_name'],
            'price' => (float)$r['price'],
            'quantity' => (int)$r['quantity'],
            'product_image' => $r['product_image'] ?: ''
        ];
    }
    $stmt->close();
}

$statusToBadge = [
    'pending' => ['label' => 'Pending', 'class' => 'bdg-pending'],
    'paid' => ['label' => 'Paid', 'class' => 'bdg-paid'],
    'processing' => ['label' => 'Processing', 'class' => 'bdg-processing'],
    'shipped' => ['label' => 'Shipped', 'class' => 'bdg-shipped'],
    'delivered' => ['label' => 'Delivered', 'class' => 'bdg-delivered'],
    'cancelled' => ['label' => 'Cancelled', 'class' => 'bdg-cancelled'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Orders</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">

<style>
    :root{
        --z-primary:#7a1fa2;
        --z-primary-2:#b42acb;
        --bg:#f6f7fb;
        --card:#ffffff;
        --text:#0b1020;
        --muted:#6b7280;
        --border:#eef2f7;
        --ok:#1a9c46;
        --warn:#ffb020;
        --bad:#ff4d4f;
        --info:#1b74e4;
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif;color:var(--text)}

    /* Header (Zepto-like) */
    .z-header{position:sticky;top:0;z-index:1000;color:#fff;background:linear-gradient(135deg,#1a9cfa 0%,#0d6efd 100%);border-radius:0 0 18px 18px;box-shadow:0 6px 18px rgba(0,0,0,.15)}
    .z-head-bar{display:flex;align-items:center;justify-content:space-between;padding:16px}
    .z-title{font-weight:800}
    .z-right a{color:#fff;text-decoration:none}
    .z-search{padding:0 16px 14px 16px}
    .z-search-wrap{position:relative}
    .z-search-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#7c8a9b}
    .z-input{width:100%;height:44px;border-radius:12px;border:1px solid rgba(255,255,255,.55);background:rgba(255,255,255,.96);padding:0 12px 0 42px;color:#0b1020}

    /* Container */
    .wrap{max-width:680px;margin:14px auto;padding:0 14px 80px 14px}

    /* Order card */
    .order-card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:14px}
    .oc-head{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid var(--border);background:#fafbff}
    .oc-id{font-weight:800}
    .bdg{display:inline-block;font-size:11px;font-weight:800;padding:4px 10px;border-radius:999px}
    .bdg-paid{background:#e8fff0;color:#0f7a37;border:1px solid #c9f3d8}
    .bdg-pending{background:#fff7e8;color:#8a5b0b;border:1px solid #ffe2b8}
    .bdg-processing{background:#eaf5ff;color:#0b67d3;border:1px solid #cfe6ff}
    .bdg-shipped{background:#f0f7ff;color:#1456a5;border:1px solid #d6e8ff}
    .bdg-delivered{background:#e8fff0;color:#0f7a37;border:1px solid #c9f3d8}
    .bdg-cancelled{background:#fff0f0;color:#b42318;border:1px solid #ffd7d9}

    .oc-body{padding:12px 14px}
    .thumbs{display:flex;gap:8px;overflow:auto;padding-bottom:2px}
    .thumb{width:54px;height:54px;border-radius:10px;border:1px solid var(--border);background:#f5f7fa;overflow:hidden;flex:0 0 54px}
    .thumb img{width:100%;height:100%;object-fit:cover}
    .sum{display:flex;align-items:center;justify-content:space-between;margin-top:10px}
    .sum-left{color:var(--muted);font-size:12px}
    .sum-right{font-weight:900}
    .oc-foot{display:flex;gap:10px;padding:12px 14px;border-top:1px solid var(--border);background:#fff}
    .btn{border:none;border-radius:10px;padding:10px 12px;font-weight:800;cursor:pointer}
    .btn-outline{background:#fff;border:1px solid #dfe4ea;color:#0b1020}
    .btn-primary{background:#1a9c46;color:#fff}
    .meta-line{color:var(--muted);font-size:12px;margin-top:6px}

    /* Empty state */
    .empty{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:24px;text-align:center;color:var(--muted)}
</style>
</head>
<body>

<header class="z-header">
    <div class="z-head-bar">
        <div class="z-title">My Orders</div>
        <div class="z-right"><a href="profile.php"><i class="fa-solid fa-xmark"></i></a></div>
    </div>
    <div class="z-search">
        <div class="z-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input class="z-input" id="orderSearch" type="search" placeholder="Search by order ID or item name…">
        </div>
    </div>
</header>

<div class="wrap" id="ordersWrap">
    <?php if (!$orders): ?>
        <div class="empty">
            <div style="font-weight:800;margin-bottom:6px;">No orders yet</div>
            <div>Start shopping and your order history will appear here.</div>
            <div style="margin-top:12px;">
                <a href="index.php"><button class="btn btn-primary">Shop now</button></a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $oid => $data): ?>
            <?php
                $m = $data['meta'];
                $items = $data['items'];
                $count = 0;
                $computed = 0.0;
                foreach ($items as $it) { $count += (int)$it['quantity']; $computed += ((float)$it['price']) * (int)$it['quantity']; }
                $badge = $statusToBadge[strtolower($m['status'] ?? '')] ?? ['label'=>ucfirst($m['status'] ?? 'Paid'),'class'=>'bdg-paid'];
                $dateStr = $m['created_at'] ? date('d M Y, h:i A', strtotime($m['created_at'])) : '';
            ?>
            <article class="order-card" data-order-id="<?= (int)$oid ?>">
                <div class="oc-head">
                    <div class="oc-id">#<?= (int)$oid ?></div>
                    <div class="bdg <?= $badge['class'] ?>"><?= h($badge['label']) ?></div>
                </div>
                <div class="oc-body">
                    <div class="thumbs">
                        <?php foreach ($items as $it): ?>
                            <div class="thumb" title="<?= h($it['product_name']) ?>">
                                <img src="./<?= h($it['product_image']) ?>" alt=""
                                     onerror="this.src='https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=100&h=100&fit=crop&crop=center'">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="sum">
                        <div class="sum-left"><?= (int)$count ?> item<?= $count>1?'s':'' ?> • <?= h(ucfirst($m['payment_method'])) ?></div>
                        <div class="sum-right">₹<?= rupee($m['total_amount'] > 0 ? $m['total_amount'] : $computed) ?></div>
                    </div>
                    <div class="meta-line"><i class="fa-regular fa-clock"></i> <?= h($dateStr) ?></div>
                </div>
                <div class="oc-foot">
                    <a href="order_details.php?order_id=<?= (int)$oid ?>"><button class="btn btn-outline">View details</button></a>
                    <form action="reorder.php" method="post" style="margin:0">
                        <input type="hidden" name="order_id" value="<?= (int)$oid ?>">
                        <button class="btn btn-primary" type="submit">Reorder</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
(function(){
    const search = document.getElementById('orderSearch');
    const wrap = document.getElementById('ordersWrap');
    if (!search || !wrap) return;

    function norm(s){ return (s||'').toLowerCase(); }

    search.addEventListener('input', () => {
        const q = norm(search.value);
        const cards = wrap.querySelectorAll('.order-card');
        cards.forEach(card => {
            const id = card.getAttribute('data-order-id') || '';
            const text = card.textContent || '';
            const hit = norm('#'+id).includes(q) || norm(text).includes(q);
            card.style.display = hit ? '' : 'none';
        });
    });
})();
</script>

</body>
</html>