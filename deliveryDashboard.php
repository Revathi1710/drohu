<?php
session_start();
require_once __DIR__ . '/connection.php';

if (empty($_SESSION['deliveryperson_id'])) {
  header('Location: deliveryLogin.php');
  exit;
}
ini_set('display_errors', 1);
$deliverypersonId   = (int)$_SESSION['deliveryperson_id'];
$deliverypersonName = $_SESSION['deliveryperson_name'] ?? 'Partner';
$servicePincode     = $_SESSION['deliveryperson_pincode'] ?? '-';

$flashSuccess = '';
$flashError = '';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function generate_otp(): string {
  return (string)random_int(100000, 999999);
}

// Optional: implement to actually notify customer via SMS/Email
function send_otp_to_customer(mysqli $con, int $orderId, string $otp): void {
  // Example (pseudo): fetch customer's phone/email from your users table using orders.user_id and send OTP
  // $sql = "SELECT u.email, u.number FROM users u JOIN orders o ON o.user_id = u.id WHERE o.id = ?";
  // Then integrate SMS/email provider here.
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action  = $_POST['action'] ?? '';
  $orderId = isset($_POST['order_id']) && ctype_digit($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

  if ($orderId > 0) {
    // Ensure the order belongs to this delivery partner and is not already delivered
    $stmt = $con->prepare("SELECT id, status, delivery_otp, otp_verified FROM orders WHERE id = ? AND delivery_person_id = ? LIMIT 1");
    $stmt->bind_param('ii', $orderId, $deliverypersonId);
    $stmt->execute();
    $orderRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$orderRow) {
      $flashError = 'Order not found or not assigned to you.';
    } else {
      if ($action === 'start_delivery') {
        if ((string)$orderRow['status'] === 'delivered') {
          $flashError = 'Order already delivered.';
        } else {
          // Generate and set OTP; move to out_for_delivery if not already
          $otp = generate_otp();
          $newStatus = ((string)$orderRow['status'] === 'out_for_delivery') ? 'out_for_delivery' : 'out_for_delivery';

          $stmt = $con->prepare("UPDATE orders SET delivery_otp = ?, otp_verified = 0, status = ? WHERE id = ? LIMIT 1");
          $stmt->bind_param('ssi', $otp, $newStatus, $orderId);
          if ($stmt->execute()) {
            send_otp_to_customer($con, $orderId, $otp);
            $flashSuccess = 'OTP generated and order set to Out for delivery.';
          } else {
            $flashError = 'Failed to generate OTP.';
          }
          $stmt->close();
        }
      } elseif ($action === 'verify_otp') {
        $inputOtp = trim($_POST['otp'] ?? '');
        if (!preg_match('/^\d{6}$/', $inputOtp)) {
          $flashError = 'Please enter a valid 6-digit OTP.';
        } elseif ((string)$orderRow['status'] === 'delivered') {
          $flashError = 'Order already delivered.';
        } else {
          // Re-fetch OTP to compare
          $stmt = $con->prepare("SELECT delivery_otp FROM orders WHERE id = ? AND delivery_person_id = ? LIMIT 1");
          $stmt->bind_param('ii', $orderId, $deliverypersonId);
          $stmt->execute();
          $row = $stmt->get_result()->fetch_assoc();
          $stmt->close();

          $storedOtp = (string)($row['delivery_otp'] ?? '');
          if ($storedOtp !== '' && hash_equals($storedOtp, $inputOtp)) {
            $status = 'delivered';
            $now = date('Y-m-d H:i:s');
            $stmt = $con->prepare("UPDATE orders SET status = ?, otp_verified = 1, delivered_at = ?, delivery_otp = NULL WHERE id = ? LIMIT 1");
            $stmt->bind_param('ssi', $status, $now, $orderId);
            if ($stmt->execute()) {
              $flashSuccess = 'Order marked as Delivered.';
            } else {
              $flashError = 'Failed to update order status.';
            }
            $stmt->close();
          } else {
            $flashError = 'Invalid OTP. Please try again.';
          }
        }
      }
    }
  } else {
    $flashError = 'Invalid order reference.';
  }
}

// Fetch active orders
$activeOrders = [];
$activeItemsByOrder = [];
{
  $stmt = $con->prepare("
    SELECT id, total_amount, status, payment_method, COALESCE(created_at, id) AS created_at, delivery_otp, otp_verified
    FROM orders
    WHERE delivery_person_id = ? and status !='delivered'
    ORDER BY id DESC
    LIMIT 200
  ");
  $stmt->bind_param('i', $deliverypersonId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) { $activeOrders[] = $r; }
  $stmt->close();

  if ($activeOrders) {
    $ids = array_column($activeOrders, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $sql = "
      SELECT oi.order_id, oi.product_id, oi.product_name, oi.price, oi.quantity, p.product_image
      FROM order_items oi
      LEFT JOIN product p ON p.id = oi.product_id
      WHERE oi.order_id IN ($in)
      ORDER BY oi.order_id DESC, oi.id ASC
    ";
    $stmt = $con->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      $oid = (int)$row['order_id'];
      if (!isset($activeItemsByOrder[$oid])) $activeItemsByOrder[$oid] = [];
      $activeItemsByOrder[$oid][] = $row;
    }
    $stmt->close();
  }
}

// Fetch delivered history
$deliveredOrders = [];
$deliveredItemsByOrder = [];
{
  $stmt = $con->prepare("
    SELECT id, total_amount, status, payment_method, COALESCE(created_at, id) AS created_at, delivered_at
    FROM orders
    WHERE delivery_person_id = ? AND status = 'delivered'
    ORDER BY delivered_at DESC, id DESC
    LIMIT 100
  ");
  $stmt->bind_param('i', $deliverypersonId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) { $deliveredOrders[] = $r; }
  $stmt->close();

  if ($deliveredOrders) {
    $ids = array_column($deliveredOrders, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $sql = "
      SELECT oi.order_id, oi.product_id, oi.product_name, oi.price, oi.quantity, p.product_image
      FROM order_items oi
      LEFT JOIN product p ON p.id = oi.product_id
      WHERE oi.order_id IN ($in)
      ORDER BY oi.order_id DESC, oi.id ASC
    ";
    $stmt = $con->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      $oid = (int)$row['order_id'];
      if (!isset($deliveredItemsByOrder[$oid])) $deliveredItemsByOrder[$oid] = [];
      $deliveredItemsByOrder[$oid][] = $row;
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Delivery Dashboard</title>
  <meta name="theme-color" content="#7b2ff7">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js"></script>

<script>
  const firebaseConfig = {
  apiKey: "AIzaSyAHChNxtWWB4v28t8UQglk3OxCl13LBr-E",
  authDomain: "deliveryapp-8b3ec.firebaseapp.com",
  projectId: "deliveryapp-8b3ec",
  storageBucket: "deliveryapp-8b3ec.firebasestorage.app",
  messagingSenderId: "401322501798",
  appId: "1:401322501798:web:443c04a655b131b0417667",
  measurementId: "G-HJ91GC32GE"
};

  firebase.initializeApp(firebaseConfig);
  const messaging = firebase.messaging();

  // Request permission to receive notifications
  Notification.requestPermission().then(permission => {
    if (permission === 'granted') {
      console.log('Notification permission granted.');
      messaging.getToken({ vapidKey: 'YOUR_VAPID_KEY' }).then((token) => {
        if (token) {
          console.log('FCM Token:', token);
          // Send token to backend to save against delivery person
        } else {
          console.log('No token available.');
        }
      }).catch(console.error);
    } else {
      console.log('Notification permission denied.');
    }
  });
</script>
  <style>
    :root{
      --brand-start:#7b2ff7; --brand-end:#f107a3;
      --text:#1f2937; --muted:#6b7280; --surface:#ffffff; --bg:#fafafa;
      --radius-xl:22px; --radius-lg:18px; --radius-md:12px;
      --shadow-lg:0 16px 44px rgba(0,0,0,.18); --shadow-md:0 10px 32px rgba(0,0,0,.12);
    }
    *{box-sizing:border-box}
    body{ background:var(--bg); color:var(--text); font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
    .hero{
      background:linear-gradient(135deg,var(--brand-start),var(--brand-end));
      color:#fff; padding: clamp(16px,5vw,28px) 18px calc(22px + env(safe-area-inset-top));
      border-bottom-left-radius: var(--radius-xl);
      border-bottom-right-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
      margin-bottom: 12px;
    }
    .container-narrow{ max-width: 1100px; width:100%; margin:0 auto; padding: 12px 16px 28px; }
    .brand-title{ font-weight:800; letter-spacing:.3px; margin:0; font-size:clamp(18px,4.8vw,22px); }
    .sub{ opacity:.92; }
    .card-elevated{ border:0; border-radius: var(--radius-lg); background:var(--surface); box-shadow: var(--shadow-md); }
    .order-badge{
      font-size:12px; border-radius:999px; padding:4px 10px; font-weight:700;
    }
    .status-assigned{ background:#fff2d6; color:#a15a00; }
    .status-packed{ background:#e6f3ff; color:#0a5fa5; }
    .status-ofd{ background:#eae4ff; color:#5b36f0; }
    .status-delivered{ background:#e8f5e9; color:#2e7d32; }
    .list-thumb{
      width:44px; height:44px; border-radius:10px; object-fit:cover; background:#f3f4f6;
    }
    .btn-primary{
      border:none; border-radius:14px; padding:10px 14px; font-weight:700; letter-spacing:.2px;
      background:linear-gradient(135deg,var(--brand-start),var(--brand-end));
      box-shadow:0 8px 24px rgba(241,7,163,.24);
    }
    .btn-outline{
      background:#fff; border:2px solid #efe7ff; color:#5b36f0; border-radius:12px; font-weight:700;
    }
    .input-group-text{ background:#f3f4f6; border:none; }
    .form-control{ border-radius: var(--radius-md); }
  </style>
</head>
<body>
  <header class="hero">
    <div class="container-narrow">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h1 class="brand-title mb-1">Welcome, <?php echo h($deliverypersonName); ?></h1>
          <div class="sub">Service pincode: <strong><?php echo h($servicePincode); ?></strong></div>
        </div>
        <div>
          <a href="deliveryLogout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <main class="container-narrow">
    <?php if ($flashSuccess): ?>
      <div class="alert alert-success"><?php echo h($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="alert alert-danger"><?php echo h($flashError); ?></div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-3" id="dashTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">Active Orders</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="delivered-tab" data-bs-toggle="tab" data-bs-target="#delivered" type="button" role="tab">Delivered</button>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
        <?php if (!$activeOrders): ?>
          <div class="card card-elevated p-4">
            <div class="text-muted">No active orders.</div>
          </div>
        <?php else: ?>
          <?php foreach ($activeOrders as $o): ?>
            <?php
              $oid    = (int)$o['id'];
              $status = (string)$o['status'];
              $badgeClass = $status === 'assigned' ? 'status-assigned' : ($status === 'packed' ? 'status-packed' : 'status-ofd');
            ?>
            <div class="card card-elevated mb-3">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="mb-1">
                      <span class="order-badge <?php echo $badgeClass; ?>">#<?php echo h($oid); ?> · <?php echo h(ucwords(str_replace('_',' ', $status))); ?></span>
                    </div>
                    <div class="fw-bold">₹<?php echo h(number_format((float)$o['total_amount'], 2)); ?> · <?php echo h(strtoupper((string)$o['payment_method'])); ?></div>
                    <div class="text-muted small">Placed: <?php echo h((string)$o['created_at']); ?></div>
                  </div>
                  <div>
                    <?php if (!empty($o['delivery_otp']) && (int)$o['otp_verified'] === 0): ?>
                      <span class="badge rounded-pill text-bg-warning">OTP pending</span>
                    <?php elseif ((string)$o['status'] === 'out_for_delivery' && empty($o['delivery_otp'])): ?>
                      <span class="badge rounded-pill text-bg-info">On the way</span>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if (!empty($activeItemsByOrder[$oid])): ?>
                  <div class="mt-3">
                    <?php foreach ($activeItemsByOrder[$oid] as $it): ?>
                      <div class="d-flex align-items-center py-2 border-bottom">
                        <?php if (!empty($it['product_image'])): ?>
                          <img class="list-thumb me-2" src="<?php echo h($it['product_image']); ?>" alt="">
                        <?php else: ?>
                          <div class="list-thumb me-2 d-flex align-items-center justify-content-center text-muted">—</div>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                          <div class="fw-semibold"><?php echo h($it['product_name']); ?></div>
                          <div class="text-muted small">Qty: <?php echo h($it['quantity']); ?> · ₹<?php echo h(number_format((float)$it['price'], 2)); ?></div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="mt-3 d-flex flex-wrap gap-2">
                  <?php if (empty($o['delivery_otp'])): ?>
                    <form method="post" action="" class="me-2">
                      <input type="hidden" name="action" value="start_delivery">
                      <input type="hidden" name="order_id" value="<?php echo h($oid); ?>">
                      <button type="submit" class="btn btn-outline">Start Delivery (Generate OTP)</button>
                    </form>
                  <?php endif; ?>

                  <?php if (!empty($o['delivery_otp']) && (int)$o['otp_verified'] === 0): ?>
                    <form method="post" action="" class="">
                      <input type="hidden" name="action" value="verify_otp">
                      <input type="hidden" name="order_id" value="<?php echo h($oid); ?>">
                      <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="text" name="otp" class="form-control" placeholder="Enter 6-digit OTP" inputmode="numeric" pattern="\d{6}" maxlength="6" required>
                      </div>
                      <button type="submit" class="btn btn-primary mt-2">Complete Delivery</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="tab-pane fade" id="delivered" role="tabpanel" aria-labelledby="delivered-tab">
        <?php if (!$deliveredOrders): ?>
          <div class="card card-elevated p-4">
            <div class="text-muted">No delivered orders yet.</div>
          </div>
        <?php else: ?>
          <?php foreach ($deliveredOrders as $o): ?>
            <?php $oid = (int)$o['id']; ?>
            <div class="card card-elevated mb-3">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="mb-1">
                      <span class="order-badge status-delivered">#<?php echo h($oid); ?> · Delivered</span>
                    </div>
                    <div class="fw-bold">₹<?php echo h(number_format((float)$o['total_amount'], 2)); ?> · <?php echo h(strtoupper((string)$o['payment_method'])); ?></div>
                    <div class="text-muted small">Delivered at: <?php echo h((string)$o['delivered_at'] ?: '—'); ?></div>
                  </div>
                </div>

                <?php if (!empty($deliveredItemsByOrder[$oid])): ?>
                  <div class="mt-3">
                    <?php foreach ($deliveredItemsByOrder[$oid] as $it): ?>
                      <div class="d-flex align-items-center py-2 border-bottom">
                        <?php if (!empty($it['product_image'])): ?>
                          <img class="list-thumb me-2" src="<?php echo h($it['product_image']); ?>" alt="">
                        <?php else: ?>
                          <div class="list-thumb me-2 d-flex align-items-center justify-content-center text-muted">—</div>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                          <div class="fw-semibold"><?php echo h($it['product_name']); ?></div>
                          <div class="text-muted small">Qty: <?php echo h($it['quantity']); ?> · ₹<?php echo h(number_format((float)$it['price'], 2)); ?></div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>