<?php
// order view page (replace your existing file content with this)
session_start();
include('connection.php');

if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$orderId = (int)($_GET['order_id'] ?? 0);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupee($n){ return number_format((float)$n, 2); }

$order = null;
$items = [];
$address = null;

if ($userId > 0 && $orderId > 0) {
    // Order meta
    $sql = "SELECT id, user_id, address_id, total_amount, status, payment_method, payment_id, created_at
            FROM orders WHERE id = ? AND user_id = ? LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $order = $res->fetch_assoc() ?: null;
    $stmt->close();

    if ($order) {
        // Items
        $sqlI = "SELECT oi.product_id, oi.product_name, oi.price, oi.quantity, p.product_image
                 FROM order_items oi
                 LEFT JOIN product p ON p.id = oi.product_id
                 WHERE oi.order_id = ?
                 ORDER BY oi.id ASC";
        $stmtI = $con->prepare($sqlI);
        $stmtI->bind_param("i", $orderId);
        $stmtI->execute();
        $resI = $stmtI->get_result();
        while ($r = $resI->fetch_assoc()) { $items[] = $r; }
        $stmtI->close();

        // Address (if saved with order)
        if (!empty($order['address_id'])) {
            $aid = (int)$order['address_id'];
            $sqlA = "SELECT id, address_label, door_no, street_address, city, state, pincode, receiver_name, receiver_number
                     FROM address_details
                     WHERE id = ? AND user_id = ? LIMIT 1";
            $stmtA = $con->prepare($sqlA);
            $stmtA->bind_param("ii", $aid, $userId);
            $stmtA->execute();
            $resA = $stmtA->get_result();
            $address = $resA->fetch_assoc() ?: null;
            $stmtA->close();
        }
    }
}

// Derived totals
$items_total = 0.0;
$total_qty = 0;
foreach ($items as $it) {
    $items_total += ((float)$it['price']) * (int)$it['quantity'];
    $total_qty += (int)$it['quantity'];
}
$delivery_fee = 0.0;
if ($order) {
    $delivery_fee = max(0, round(((float)$order['total_amount']) - $items_total, 2));
}
$statusToBadge = [
    'pending' => ['label' => 'Pending', 'class' => 'bdg-pending'],
    'paid' => ['label' => 'Paid', 'class' => 'bdg-paid'],
    'processing' => ['label' => 'Processing', 'class' => 'bdg-processing'],
    'shipped' => ['label' => 'Shipped', 'class' => 'bdg-shipped'],
    'out_for_delivery' => ['label' => 'Out for Delivery', 'class' => 'bdg-shipped'],
    'delivered' => ['label' => 'Delivered', 'class' => 'bdg-delivered'],
    'cancelled' => ['label' => 'Cancelled', 'class' => 'bdg-cancelled'],
];
$badge = $statusToBadge[strtolower($order['status'] ?? '')] ?? ['label'=>ucfirst($order['status'] ?? 'Paid'),'class'=>'bdg-paid'];
$dateStr = $order && $order['created_at'] ? date('d M Y, h:i A', strtotime($order['created_at'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order #<?= (int)$orderId ?></title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
<style>
    :root{
        --z-primary:#7a1fa2; --z-primary-2:#b42acb;
        --bg:#f6f7fb; --card:#ffffff; --text:#0b1020; --muted:#6b7280; --border:#eef2f7;
        --ok:#1a9c46; --warn:#ffb020; --bad:#ff4d4f; --info:#1b74e4;
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif;color:var(--text)}

    /* Header */
    .z-header{position:sticky;top:0;z-index:1000;color:#fff;
        background:linear-gradient(135deg,#1a9cfa 0%,#0d6efd 100%);
        border-radius:0 0 18px 18px;box-shadow:0 6px 18px rgba(0,0,0,.15)}
    .z-head-bar{display:flex;align-items:center;justify-content:space-between;padding:16px}
    .z-title{font-weight:800}
    .z-right a{color:#fff;text-decoration:none}

    .wrap{max-width:680px;margin:14px auto;padding:0 14px 70px 14px}

    /* Summary card */
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:14px}
    .card-head{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid var(--border);background:#fafbff}
    .bdg{display:inline-block;font-size:11px;font-weight:800;padding:4px 10px;border-radius:999px}
    .bdg-paid{background:#e8fff0;color:#0f7a37;border:1px solid #c9f3d8}
    .bdg-pending{background:#fff7e8;color:#8a5b0b;border:1px solid #ffe2b8}
    .bdg-processing{background:#eaf5ff;color:#0b67d3;border:1px solid #cfe6ff}
    .bdg-shipped{background:#f0f7ff;color:#1456a5;border:1px solid #d6e8ff}
    .bdg-delivered{background:#e8fff0;color:#0f7a37;border:1px solid #c9f3d8}
    .bdg-cancelled{background:#fff0f0;color:#b42318;border:1px solid #ffd7d9}

    .sum-row{display:flex;align-items:center;justify-content:space-between;padding:12px 14px}
    .muted{color:var(--muted);font-size:12px}
    .total{font-weight:900}

    /* Timeline (simple) */
    .timeline{padding:12px 14px;display:grid;grid-template-columns:auto 1fr;gap:10px 12px}
    .dot{width:10px;height:10px;border-radius:50%;background:#cbd5e1;margin-top:2px}
    .dot.active{background:#22c55e}
    .t-text{font-size:12px;color:var(--muted)}
    .t-strong{font-weight:800;color:var(--text)}

    /* Address */
    .addr{padding:12px 14px;display:flex;gap:12px;align-items:flex-start}
    .addr-ico{width:36px;height:36px;border-radius:10px;background:#f4f6ff;color:#375dfb;display:flex;align-items:center;justify-content:center}
    .addr-title{font-weight:800}
    .addr-sub{font-size:12px;color:var(--muted)}

    /* Items */
    .item{display:flex;gap:12px;align-items:center;padding:12px 14px}
    .item + .item{border-top:1px solid var(--border)}
    .thumb{width:56px;height:56px;border-radius:12px;border:1px solid var(--border);background:#f5f7fa;overflow:hidden;flex:0 0 56px}
    .thumb img{width:100%;height:100%;object-fit:cover}
    .i-title{font-weight:800;margin-bottom:2px}
    .i-sub{font-size:12px;color:var(--muted)}
    .i-amt{margin-left:auto;text-align:right;font-weight:900}
    .i-qty{font-size:12px;color:var(--muted)}

    /* Bill */
    .bill{padding:12px 14px}
    .bill-line{display:flex;align-items:center;justify-content:space-between;padding:6px 0}
    .bill-total{border-top:1px solid var(--border);margin-top:6px;padding-top:10px;font-weight:900}

    /* Footer actions */
    .foot-actions{position:sticky;bottom:0;background:#fff;border-top:1px solid var(--border)}
    .foot-inner{max-width:680px;margin:0 auto;padding:12px 14px;display:flex;gap:10px}
    .btn{border:none;border-radius:10px;padding:12px 14px;font-weight:800;cursor:pointer}
    .btn-outline{background:#fff;border:1px solid #dfe4ea;color:#0b1020}
    .btn-primary{background:#1a9c46;color:#fff}

    /* Delivery OTP */
    .otp-card-wait{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:13px}
    .otp-spin{width:14px;height:14px;border-radius:50%;border:2px solid #cbd5e1;border-top-color:#1b74e4;animation:spin 1s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .otp-wrap{padding:12px 14px}
    .otp-boxes{display:flex;gap:8px;margin-top:6px}
    .otp-box{
      width:40px;height:46px;border:1px solid var(--border);border-radius:10px;
      display:flex;align-items:center;justify-content:center;font-weight:900;font-size:18px;background:#fff
    }
    .otp-actions{margin-top:10px;display:flex;gap:8px;align-items:center}
</style>
</head>
<body>

<header class="z-header">
    <div class="z-head-bar">
        <div class="z-title">Order #<?= (int)$orderId ?></div>
        <div class="z-right"><a href="orders.php"><i class="fa-solid fa-xmark"></i></a></div>
    </div>
</header>

<div class="wrap">
    <?php if (!$order): ?>
        <div class="card" style="padding:18px;text-align:center;color:var(--muted)">
            Order not found.
            <div style="margin-top:10px"><a href="orders.php"><button class="btn btn-outline">Back to Orders</button></a></div>
        </div>
    <?php else: ?>

        <!-- Summary -->
        <section class="card">
            <div class="card-head">
                <div><strong>Status</strong></div>
                <div class="bdg <?= $badge['class'] ?>"><?= h($badge['label']) ?></div>
            </div>
            <div class="sum-row">
                <div>
                    <div class="muted">Placed on</div>
                    <div><strong><?= h($dateStr) ?></strong></div>
                </div>
                <div class="total">₹<?= rupee((float)$order['total_amount']) ?></div>
            </div>
            <div class="timeline">
                <div class="dot <?= in_array(strtolower($order['status']), ['pending','paid','processing','shipped','out_for_delivery','delivered'])?'active':'' ?>"></div>
                <div><div class="t-strong">Order Placed</div><div class="t-text"><?= h($dateStr) ?></div></div>

                <div class="dot <?= in_array(strtolower($order['status']), ['paid','processing','shipped','out_for_delivery','delivered'])?'active':'' ?>"></div>
                <div><div class="t-strong">Payment <?= strtolower($order['status'])==='pending'?'Pending':'Confirmed' ?></div>
                    <div class="t-text"><?= strtoupper($order['payment_method']) ?><?= $order['payment_id'] ? ' • '.h($order['payment_id']) : '' ?></div>
                </div>

                <div class="dot <?= in_array(strtolower($order['status']), ['processing','shipped','out_for_delivery','delivered'])?'active':'' ?>"></div>
                <div><div class="t-strong">Processing</div><div class="t-text">We’re preparing your order</div></div>

                <div class="dot <?= in_array(strtolower($order['status']), ['shipped','out_for_delivery','delivered'])?'active':'' ?>"></div>
                <div><div class="t-strong">Out for Delivery</div><div class="t-text">Your order will arrive soon</div></div>

                <div class="dot <?= in_array(strtolower($order['status']), ['delivered'])?'active':'' ?>"></div>
                <div><div class="t-strong">Delivered</div><div class="t-text">Thanks for ordering with us</div></div>
            </div>
        </section>

        <!-- Delivery Address -->
        <section class="card">
            <div class="card-head"><strong>Delivery Address</strong></div>
            <div class="addr">
                <div class="addr-ico"><i class="fa-solid fa-location-dot"></i></div>
                <div>
                    <?php if ($address): ?>
                        <div class="addr-title"><?= h($address['address_label'] ?: 'Home') ?></div>
                        <div class="addr-sub">
                            <?= h(trim(($address['door_no'] ?? '').', '.($address['street_address'] ?? ''), ', ')) ?><br>
                            <?= h(trim(($address['city'] ?? '').', '.($address['state'] ?? '').' - '.($address['pincode'] ?? ''), ', - ')) ?><br>
                            <?= h($address['receiver_name'] ?? '') ?> • <?= h($address['receiver_number'] ?? '') ?>
                        </div>
                    <?php else: ?>
                        <div class="addr-title">Address not available</div>
                        <div class="addr-sub">This order doesn’t have a saved address.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Items -->
        <section class="card">
            <div class="card-head"><strong>Items (<?= (int)$total_qty ?>)</strong></div>
            <?php foreach ($items as $it): ?>
                <?php $line = ((float)$it['price']) * (int)$it['quantity']; ?>
                <div class="item">
                    <div class="thumb">
                        <img src="./<?= h($it['product_image']) ?>" alt=""
                             onerror="this.src='https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=120&h=120&fit=crop&crop=center'">
                    </div>
                    <div>
                        <div class="i-title"><?= h($it['product_name']) ?></div>
                        <div class="i-sub">₹<?= rupee((float)$it['price']) ?> <span class="i-qty">× <?= (int)$it['quantity'] ?></span></div>
                    </div>
                    <div class="i-amt">₹<?= rupee($line) ?></div>
                </div>
            <?php endforeach; ?>
            <div class="bill">
                <div class="bill-line"><span>Item total</span><span>₹<?= rupee($items_total) ?></span></div>
                <div class="bill-line"><span>Delivery fee</span><span><?= $delivery_fee>0 ? '₹'.rupee($delivery_fee) : '—' ?></span></div>
                <div class="bill-line bill-total"><span>Grand total</span><span>₹<?= rupee((float)$order['total_amount']) ?></span></div>
            </div>
        </section>

        <!-- Payment -->
        <section class="card">
            <div class="card-head"><strong>Payment</strong></div>
            <div class="sum-row">
                <div>
                    <div class="muted">Method</div>
                    <div><strong><?= h(strtoupper($order['payment_method'])) ?></strong></div>
                </div>
                <div class="muted"><?= $order['payment_id'] ? 'Txn: '.h($order['payment_id']) : '' ?></div>
            </div>
        </section>

        <!-- Delivery OTP (live, no refresh) -->
        <section class="card" id="otpCard" style="display:none">
            <div class="card-head"><strong>Delivery OTP</strong></div>
            <div class="otp-wrap">
                <div id="otpWaiting" class="otp-card-wait">
                    <div class="otp-spin"></div>
                    <div>Waiting for OTP to be generated…</div>
                </div>

                <div id="otpContent" style="display:none">
                    <div class="muted">Share this code with the delivery partner on arrival</div>
                    <div class="otp-boxes" id="otpBoxes">
                        <div class="otp-box" data-otp-box="0">•</div>
                        <div class="otp-box" data-otp-box="1">•</div>
                        <div class="otp-box" data-otp-box="2">•</div>
                        <div class="otp-box" data-otp-box="3">•</div>
                        <div class="otp-box" data-otp-box="4">•</div>
                        <div class="otp-box" data-otp-box="5">•</div>
                    </div>
                    <div class="otp-actions">
                        <button type="button" class="btn btn-primary" id="copyOtpBtn">Copy OTP</button>
                        <span class="muted" id="otpCopiedMsg" style="display:none">Copied</span>
                    </div>
                </div>

                <div id="otpDelivered" class="otp-card-wait" style="display:none">
                    <i class="fa-solid fa-circle-check" style="color:#1a9c46"></i>
                    <div>Order delivered. OTP no longer needed.</div>
                </div>
            </div>
        </section>

        <!-- Footer actions -->
        <div class="foot-actions">
            <div class="foot-inner">
                <a href="orders.php" style="text-decoration:none"><button class="btn btn-outline" type="button">Back to Orders</button></a>
                <form action="reorder.php" method="post" style="margin:0;margin-left:auto">
                    <input type="hidden" name="order_id" value="<?= (int)$orderId ?>">
                    <button class="btn btn-primary" type="submit">Reorder</button>
                </form>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
(function(){
  const orderId = <?= (int)$orderId ?>;
  const card = document.getElementById('otpCard');
  const waiting = document.getElementById('otpWaiting');
  const content = document.getElementById('otpContent');
  const delivered = document.getElementById('otpDelivered');
  const boxes = document.querySelectorAll('[data-otp-box]');
  const copyBtn = document.getElementById('copyOtpBtn');
  const copiedMsg = document.getElementById('otpCopiedMsg');

  let lastOtp = '';
  let lastStatus = '';

  function show(el, yes){ if(!el) return; el.style.display = yes ? '' : 'none'; }
  function renderOtp(otp){
    for (let i = 0; i < boxes.length; i++) {
      boxes[i].textContent = otp && otp[i] ? otp[i] : '•';
    }
  }

  async function tick(){
    try{
      const res = await fetch('order-otp.php?order_id=' + orderId, {cache:'no-store'});
      if(!res.ok) return;
      const data = await res.json();
      if(!data.ok) return;

      const status = String(data.status || '').toLowerCase();
      const otp = String(data.otp || '');

      show(card, true);

      if (status === 'delivered' || Number(data.otp_verified) === 1) {
        show(waiting, false);
        show(content, false);
        show(delivered, true);
        lastStatus = status;
        lastOtp = '';
        return;
      }

      if (otp && otp.length === 6) {
        if (otp !== lastOtp) {
          renderOtp(otp);
          lastOtp = otp;
        }
        show(waiting, false);
        show(content, true);
        show(delivered, false);
      } else {
        show(waiting, true);
        show(content, false);
        show(delivered, false);
        renderOtp('');
        lastOtp = '';
      }

      lastStatus = status;
    }catch(e){
      // ignore transient errors
    }
  }

  copyBtn?.addEventListener('click', async () => {
    if (!lastOtp) return;
    try {
      await navigator.clipboard.writeText(lastOtp);
      show(copiedMsg, true);
      setTimeout(() => show(copiedMsg, false), 1200);
    } catch (_) {}
  });

  tick();
  const intervalId = setInterval(tick, 3000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) tick(); });
})();
</script>
</body>
</html>