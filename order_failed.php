<?php
session_start();
include('connection.php');

if (!isset($_SESSION['mobile_number'])) {
header("Location: login.php");
 exit();
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupee($n){ return number_format((float)$n, 2); }

$userId = (int)($_SESSION['user_id'] ?? 0);
$orderId = (int)($_GET['order_id'] ?? 0);

// In a real application, you would query the database to get the failed order details.
// For this example, we'll assume the details are passed in the URL or retrieved from a failed transaction log.
$order = null;
if ($userId > 0 && $orderId > 0) {
 $stmt = $con->prepare("SELECT id, total_amount, status, payment_method, created_at FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
 $stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$res = $stmt->get_result();
 $order = $res->fetch_assoc() ?: null;
 $stmt->close();
}

$total = $order ? (float)$order['total_amount'] : 0.00;
$method = $order ? strtoupper((string)$order['payment_method']) : 'ONLINE';
$placedAt = $order && $order['created_at'] ? date('d M Y, h:i A', strtotime($order['created_at'])) : date('d M Y, h:i A');

// Get the reason for failure, e.g., from a URL parameter.
$reason = h($_GET['reason'] ?? 'Payment failed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order Failed</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;900&display=swap" rel="stylesheet">
<style>
 :root{
 --z-primary:#7a1fa2; --z-primary-2:#b42acb;
--fail:#ef4444; --bg:#f6f7fb; --card:#ffffff; --text:#0b1020; --muted:#6b7280; --border:#eef2f7;
 }
 *{box-sizing:border-box}
 body{margin:0;background:var(--bg);font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif;color:var(--text);}

 /* Header (Zepto-like) */
 .z-header{position:sticky;top:0;z-index:10;color:#fff;background:linear-gradient(135deg,var(--z-primary) 0%,var(--z-primary-2) 100%);border-radius:0 0 18px 18px;box-shadow:0 6px 18px rgba(0,0,0,.15)}
 .z-head{display:flex;align-items:center;justify-content:space-between;padding:16px}
.z-title{font-weight:900}

 /* Failure hero */
 .wrap{max-width:720px;margin:14px auto;padding:0 14px 90px 14px;position:relative}
 .card{background:var(--card);border:1px solid var(--border);border-radius:16px}
 .hero{position:relative;overflow:hidden;text-align:center;padding:28px 18px}
 .ring{position:relative;display:inline-flex;align-items:center;justify-content:center;width:112px;height:112px;border-radius:999px;background:rgba(239,68,68,.1);box-shadow:0 8px 24px rgba(239,68,68,.15) inset}
 .ring::before,.ring::after{content:"";position:absolute;border-radius:999px;inset:-6px;border:3px solid rgba(239,68,68,.25);animation:pulse 1.8s ease-out infinite}
 .ring::after{inset:-14px;border-color:rgba(239,68,68,.15);animation-delay:.6s}
 @keyframes pulse{0%{transform:scale(.85);opacity:.9}100%{transform:scale(1.25);opacity:0}}
 .xmark{width:58px;height:58px;color:#fff}
.xmark circle{fill:var(--fail)}
 .xmark path{stroke:#fff;stroke-width:4;fill:none;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:100;stroke-dashoffset:100;animation:draw .8s .2s ease forwards}
 @keyframes draw{to{stroke-dashoffset:0}}

 .title{font-weight:900;font-size:22px;margin:14px 0 6px}
 .subtitle{color:var(--muted);font-size:14px}

 /* Summary chips */
.chips{display:flex;gap:10px;justify-content:center;margin-top:14px;flex-wrap:wrap}
 .chip{background:#fef2f2;border:1px solid #fee2e2;color:#991b1b;border-radius:999px;padding:6px 10px;font-weight:800;font-size:12px}
 .chip-alt{background:#eff6ff;border-color:#dbeafe;color:#1d4ed8}

 /* Progress bar (prep ETA) */
 .progress{margin:18px auto 0 auto;width:min(520px,90%);height:8px;background:#f1f5f9;border-radius:999px;overflow:hidden;border:1px solid #e2e8f0}
 .bar{height:100%;width:0;background:linear-gradient(90deg,#dc2626,#ef4444);animation:load 2.8s ease forwards}
 @keyframes load{to{width:100%}}

 /* Order meta card */
 .meta{margin-top:16px;padding:12px}
 .line{display:flex;align-items:center;justify-content:space-between;padding:8px 0}
 .muted{color:var(--muted);font-size:13px}
 .val-strong{font-weight:900}

 /* CTA footer (sticky) */
 .sticky{position:fixed;left:0;right:0;bottom:0;background:#fff;border-top:1px solid var(--border)}
 .foot{max-width:720px;margin:0 auto;padding:12px 14px;display:flex;gap:10px}
 .btn{border:none;border-radius:12px;padding:12px 14px;font-weight:900;cursor:pointer}
 .btn-outline{background:#fff;border:1px solid #dfe4ea;color:#0b1020}
 .btn-fail{background:var(--fail);color:#fff}

 /* No confetti */
 canvas#confetti{display: none;}
</style>
</head>
<body>

<canvas id="confetti"></canvas>

<header class="z-header">
<div class="z-head">
<div class="z-title">Order Failed</div>
 <a href="orders.php" style="color:#fff;text-decoration:none"><i class="fa-solid fa-xmark"></i></a>
 </div>
</header>

<div class="wrap">
 <section class="card hero">
 <div class="ring" aria-hidden="true">
 <svg class="xmark" viewBox="0 0 64 64">
<circle cx="32" cy="32" r="32"/>
 <path d="M22 22 42 42M22 42 42 22"/>
 </svg>
 </div>
<div class="title">Oops! Your order has failed</div>
 <div class="subtitle">There was an issue processing your payment. Please try again.</div>

 <div class="chips">
 <div class="chip">Order #<?= (int)$orderId ?></div>
 <div class="chip chip-alt"><?= h($method) ?></div>
</div>

<div class="progress" style="display:none;"></div>

 <div class="meta">
<div class="line"><span class="muted">Reason</span><span class="val-strong"><?= h($reason) ?></span></div>
 <div class="line"><span class="muted">Amount</span><span class="val-strong">₹<?= rupee($total) ?></span></div>
 </div>
 </section>

 <section class="card" style="margin-top:14px;padding:12px">
 <div class="line" style="padding-bottom:0">
 <div>
<div class="val-strong">What can you do?</div>
<div class="muted" style="margin-top:4px">You can retry the payment or choose a different payment method.</div>
</div>
<i class="fa-solid fa-redo" style="color:#ef4444"></i>
</div>
</section>
</div>

<div class="sticky">
 <div class="foot">
<a href="cart.php?order_id=<?= (int)$orderId ?>" style="text-decoration:none"><button class="btn btn-fail" type="button">Retry Payment</button></a>
 <a href="products.php" style="text-decoration:none;margin-left:auto"><button class="btn btn-outline" type="button">Continue shopping</button></a>
 </div>
</div>

<script>
// No confetti script for failed page
</script>

</body>
</html>