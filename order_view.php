<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/sidebar.php';
ini_set('display_errors', 1);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money_i($n){ return number_format((float)$n, 2); }
function badge_cls(string $s): string {
  $s = strtolower($s);
  return match ($s) {
    'pending'          => 'bg-warning-subtle text-warning-emphasis',
    'paid'             => 'bg-info-subtle text-info-emphasis',
    'processing'       => 'bg-primary-subtle text-primary-emphasis',
    'shipped'          => 'bg-secondary-subtle text-secondary-emphasis',
    'out_for_delivery' => 'bg-purple-subtle text-purple',
    'delivered'        => 'bg-success-subtle text-success-emphasis',
    'cancelled'        => 'bg-danger-subtle text-danger-emphasis',
    default            => 'bg-light text-dark'
  };
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($orderId <= 0) {
  http_response_code(400);
  echo '<div style="padding:16px;color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;">Invalid order reference.</div>';
  exit;
}

/* Order */
$order = null;
if ($stmt = $con->prepare("
  SELECT id, user_id, delivery_person_id, total_amount, status, payment_method, payment_id,
         created_at, delivered_at, delivery_otp, otp_verified, address_id
  FROM orders
  WHERE id = ? LIMIT 1
")) {
  $stmt->bind_param('i', $orderId);
  $stmt->execute();
  $res = $stmt->get_result();
  $order = $res ? $res->fetch_assoc() : null;
  $stmt->close();
}
if (!$order) {
  http_response_code(404);
  echo '<div style="padding:16px;color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;">Order not found.</div>';
  exit;
}

/* Customer */
$customer = null;
if (!empty($order['user_id'])) {
  if ($stmt = $con->prepare("SELECT id, name, mobile_number, email, user_country_code, address, door_no, pincode FROM users WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i', $order['user_id']);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
  }
}

/* Delivery person */
$dp = null;
if (!empty($order['delivery_person_id'])) {
  if ($stmt = $con->prepare("SELECT id, deliveryperson_name, number, email, pincode FROM deliveryPerson WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i', $order['delivery_person_id']);
    $stmt->execute();
    $dp = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
  }
}

/* Address */
$addr = null;
if (!empty($order['address_id'])) {
  if ($stmt = $con->prepare("
    SELECT id, address_label, door_no, street_address, city, state, pincode, receiver_name, receiver_number
    FROM address_details WHERE id = ? LIMIT 1
  ")) {
    $stmt->bind_param('i', $order['address_id']);
    $stmt->execute();
    $addr = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
  }
}

/* Items */
$items = [];
if ($stmt = $con->prepare("
  SELECT oi.product_id, oi.product_name, oi.price, oi.quantity, p.product_image
  FROM order_items oi
  LEFT JOIN product p ON p.id = oi.product_id
  WHERE oi.order_id = ?
  ORDER BY oi.id ASC
")) {
  $stmt->bind_param('i', $orderId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) { $items[] = $r; }
  $stmt->close();
}

/* Totals */
$itemsTotal = 0.0; $qtyTotal = 0;
foreach ($items as $it) {
  $itemsTotal += ((float)$it['price']) * (int)$it['quantity'];
  $qtyTotal   += (int)$it['quantity'];
}
$deliveryFee = max(0, round(((float)$order['total_amount']) - $itemsTotal, 2));
$badgeClass  = badge_cls((string)$order['status']);
$createdStr  = $order['created_at'] ? date('d M Y, h:i A', strtotime($order['created_at'])) : '-';
$delivStr    = $order['delivered_at'] ? date('d M Y, h:i A', strtotime($order['delivered_at'])) : '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order #<?php echo (int)$orderId; ?></title>
  <meta name="theme-color" content="#7b2ff7">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --brand-start:#7b2ff7; --brand-end:#f107a3;
      --text:#1f2937; --muted:#6b7280; --surface:#ffffff; --bg:#fafafa;
      --radius-xl:22px; --radius-lg:18px; --radius-md:12px;
      --shadow-lg:0 16px 44px rgba(0,0,0,.18); --shadow-md:0 10px 32px rgba(0,0,0,.12);
      --border:#e5e7eb;
    }
    *{box-sizing:border-box}
    body{ background:var(--bg); color:var(--text); font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
    .main-container{ padding:16px; margin-left:var(--sidebar-width); }
    .header-card{ background:#fff; border:1px solid var(--border); border-radius:16px; padding:16px; margin-bottom:12px; box-shadow: var(--shadow-md); }
    .grid-2{ display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:12px; }
    .card-elevated{ background:#fff; border:1px solid var(--border); border-radius:14px; box-shadow: var(--shadow-md); }
    .card-head{ padding:12px 14px; border-bottom:1px solid var(--border); font-weight:700; }
    .card-body{ padding:12px 14px; }
    .list-thumb{ width:48px; height:48px; border-radius:10px; object-fit:cover; background:#f3f4f6; border:1px solid var(--border); }
    .muted{ color:var(--muted); }
    .bill-line{ display:flex; align-items:center; justify-content:space-between; padding:6px 0; }
    .bill-total{ border-top:1px solid var(--border); margin-top:6px; padding-top:10px; font-weight:900; }
  </style>
</head>
<body>
  <div class="main-container">
    <div class="header-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <h5 class="m-0">Order #<?php echo (int)$orderId; ?></h5>
          <div class="mt-1">
            <span class="badge <?php echo $badgeClass; ?>">
              <?php echo h(ucwords(str_replace('_',' ', (string)$order['status']))); ?>
            </span>
          </div>
          <div class="small text-muted mt-2">
            Placed: <?php echo h($createdStr); ?>
            <?php if (!empty($order['delivered_at'])): ?>
              · Delivered: <strong><?php echo h($delivStr); ?></strong>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
      </div>
    </div>

    <div class="grid-2">
      <div class="card-elevated">
        <div class="card-head">Payment</div>
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <div class="muted">Method</div>
              <div class="fw-semibold"><?php echo h(strtoupper((string)$order['payment_method'])); ?></div>
            </div>
            <div class="text-end">
              <div class="muted">Amount</div>
              <div class="fw-bold">₹<?php echo h(money_i((float)$order['total_amount'])); ?></div>
            </div>
          </div>
          <div class="mt-2 small text-muted">
            <?php echo $order['payment_id'] ? 'Txn: '.h($order['payment_id']) : ''; ?>
          </div>
          <?php if (!empty($order['delivery_otp'])): ?>
            <div class="mt-3">
              <span class="badge bg-light text-dark">OTP: <?php echo h($order['delivery_otp']); ?></span>
              <?php if ((int)$order['otp_verified'] === 1): ?>
                <span class="badge bg-success-subtle text-success-emphasis ms-1">Verified</span>
              <?php else: ?>
                <span class="badge bg-warning-subtle text-warning-emphasis ms-1">Pending</span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-elevated">
        <div class="card-head">Customer</div>
        <div class="card-body">
          <?php if ($customer): ?>
            <div class="fw-semibold"><?php echo h($customer['name']); ?></div>
            <div class="small text-muted">
              <?php echo h(($customer['user_country_code'] ?: '+') . ' ' . $customer['mobile_number']); ?>
              · <?php echo h($customer['email'] ?: '-'); ?>
            </div>
            <div class="mt-2 small">
              <?php echo h($customer['door_no'] ?: ''); ?>
              <?php echo ($customer['door_no'] && $customer['address']) ? ', ' : ''; ?>
              <?php echo h($customer['address'] ?: ''); ?>
              <?php if ($customer['pincode']): ?> · <?php echo h($customer['pincode']); ?><?php endif; ?>
            </div>
            <div class="mt-2">
              <a class="btn btn-outline-secondary btn-sm" href="customer_view.php?id=<?php echo (int)$customer['id']; ?>">View customer</a>
            </div>
          <?php else: ?>
            <div class="text-muted">No customer details.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-elevated">
        <div class="card-head">Delivery Address</div>
        <div class="card-body">
          <?php if ($addr): ?>
            <div class="fw-semibold"><?php echo h($addr['address_label'] ?: 'Address'); ?></div>
            <div class="small">
              <?php
                $line1 = trim(($addr['door_no'] ?? '') . ', ' . ($addr['street_address'] ?? ''), ', ');
                $line2 = trim(($addr['city'] ?? '') . ', ' . ($addr['state'] ?? '') . ' - ' . ($addr['pincode'] ?? ''), ', - ');
                echo h($line1); echo $line1 && $line2 ? '<br>' : ''; echo h($line2);
              ?>
            </div>
            <div class="small text-muted mt-1">
              <?php echo h($addr['receiver_name'] ?? ''); ?> · <?php echo h($addr['receiver_number'] ?? ''); ?>
            </div>
          <?php else: ?>
            <div class="text-muted">No saved address with this order.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-elevated">
        <div class="card-head">Delivery Partner</div>
        <div class="card-body">
          <?php if ($dp): ?>
            <div class="fw-semibold"><?php echo h($dp['deliveryperson_name']); ?></div>
            <div class="small text-muted">
              <?php echo h($dp['number'] ?: '-'); ?> · <?php echo h($dp['email'] ?: '-'); ?>
              <?php if (!empty($dp['pincode'])): ?> · Pincode: <?php echo h($dp['pincode']); ?><?php endif; ?>
            </div>
          <?php else: ?>
            <div class="text-muted">Not assigned.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card-elevated mt-3">
      <div class="card-head">Items (<?php echo (int)$qtyTotal; ?>)</div>
      <div class="card-body">
        <?php if (!$items): ?>
          <div class="text-muted">No line items.</div>
        <?php else: ?>
          <?php foreach ($items as $it): ?>
            <?php $line = ((float)$it['price']) * (int)$it['quantity']; ?>
            <div class="d-flex align-items-center py-2 border-bottom">
              <?php if (!empty($it['product_image'])): ?>
                <img class="list-thumb me-2" src="<?php echo h($it['product_image']); ?>" alt="">
              <?php else: ?>
                <div class="list-thumb me-2 d-flex align-items-center justify-content-center text-muted">—</div>
              <?php endif; ?>
              <div class="flex-grow-1">
                <div class="fw-semibold"><?php echo h($it['product_name']); ?></div>
                <div class="muted small">₹<?php echo h(money_i((float)$it['price'])); ?> × <?php echo (int)$it['quantity']; ?></div>
              </div>
              <div class="fw-bold">₹<?php echo h(money_i($line)); ?></div>
            </div>
          <?php endforeach; ?>

          <div class="mt-3">
            <div class="bill-line"><span class="muted">Items total</span><span>₹<?php echo h(money_i($itemsTotal)); ?></span></div>
            <div class="bill-line"><span class="muted">Delivery fee</span><span><?php echo $deliveryFee>0 ? '₹'.h(money_i($deliveryFee)) : '—'; ?></span></div>
            <div class="bill-line bill-total"><span>Grand total</span><span>₹<?php echo h(money_i((float)$order['total_amount'])); ?></span></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>