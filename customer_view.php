<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/sidebar.php';
ini_set('display_errors', 1);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($customerId <= 0) {
  http_response_code(400);
  echo '<div style="padding:16px;color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;">Invalid customer reference.</div>';
  exit;
}

/* Fetch customer info for header */
$customer = null;
if ($stmt = $con->prepare('SELECT id, name, mobile_number, email, address, door_no, pincode, create_at, user_country_code FROM users WHERE id = ? LIMIT 1')) {
  $stmt->bind_param('i', $customerId);
  $stmt->execute();
  $res = $stmt->get_result();
  $customer = $res ? $res->fetch_assoc() : null;
  $stmt->close();
}
if (!$customer) {
  http_response_code(404);
  echo '<div style="padding:16px;color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;">Customer not found.</div>';
  exit;
}

/* Filters */
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

$from    = trim($_GET['from'] ?? ''); // YYYY-MM-DD
$to      = trim($_GET['to'] ?? '');   // YYYY-MM-DD
$status  = trim($_GET['status'] ?? ''); // '', pending, paid, processing, shipped, out_for_delivery, delivered, cancelled

$allowedStatuses = ['pending','paid','processing','shipped','out_for_delivery','delivered','cancelled'];

$whereSql = 'WHERE o.user_id = ?';
$params   = [$customerId];
$types    = 'i';

if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
  $whereSql .= ' AND DATE(o.created_at) >= ?';
  $params[] = $from;
  $types   .= 's';
}
if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
  $whereSql .= ' AND DATE(o.created_at) <= ?';
  $params[] = $to;
  $types   .= 's';
}
if ($status !== '' && in_array(strtolower($status), $allowedStatuses, true)) {
  $whereSql .= ' AND o.status = ?';
  $params[] = strtolower($status);
  $types   .= 's';
}

/* Count total orders */
$sqlCount = "SELECT COUNT(*) AS c FROM orders o $whereSql";
$stmt = $con->prepare($sqlCount);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$totalRows = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
$stmt->close();

$totalPages = max(1, (int)ceil($totalRows / $perPage));

/* Fetch orders page */
$sqlList = "
  SELECT
    o.id,
    o.total_amount,
    o.payment_method,
    o.created_at,
    o.delivered_at,
    o.status,
    o.address_id
  FROM orders o
  $whereSql
  ORDER BY o.id DESC
  LIMIT ?, ?
";
$paramsList = array_merge($params, [$offset, $perPage]);
$typesList  = $types . 'ii';

$stmt = $con->prepare($sqlList);
$stmt->bind_param($typesList, ...$paramsList);
$stmt->execute();
$res = $stmt->get_result();
$orders = [];
while ($r = $res->fetch_assoc()) { $orders[] = $r; }
$stmt->close();

/* Fetch items for these orders */
$itemsByOrder = [];
if ($orders) {
  $ids = array_column($orders, 'id');
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $typesItems = str_repeat('i', count($ids));
  $sqlItems = "
    SELECT oi.order_id, oi.product_id, oi.product_name, oi.price, oi.quantity, p.product_image
    FROM order_items oi
    LEFT JOIN product p ON p.id = oi.product_id
    WHERE oi.order_id IN ($in)
    ORDER BY oi.order_id DESC, oi.id ASC
  ";
  $stmt = $con->prepare($sqlItems);
  $stmt->bind_param($typesItems, ...$ids);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $oid = (int)$row['order_id'];
    if (!isset($itemsByOrder[$oid])) $itemsByOrder[$oid] = [];
    $itemsByOrder[$oid][] = $row;
  }
  $stmt->close();
}

/* Fetch addresses for these orders */
$addressById = [];
$addressIds = array_values(array_unique(array_filter(array_map(function($o){ return (int)($o['address_id'] ?? 0); }, $orders))));
if ($addressIds) {
  $in  = implode(',', array_fill(0, count($addressIds), '?'));
  $typesA = str_repeat('i', count($addressIds));
  $sqlA = "
    SELECT id, address_label, door_no, street_address, city, state, pincode, receiver_name, receiver_number
    FROM address_details
    WHERE id IN ($in)
  ";
  $stmt = $con->prepare($sqlA);
  $stmt->bind_param($typesA, ...$addressIds);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $addressById[(int)$row['id']] = $row;
  }
  $stmt->close();
}

function status_badge_class(string $s): string {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer · <?php echo h($customer['name']); ?></title>
  <meta name="theme-color" content="#7b2ff7">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
    .hero{
      background:#fff; padding: 18px;
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
      margin-bottom: 12px;
    }
    .container-narrow{ max-width: 1100px; width:100%; margin:0 auto; }
    .brand-title{ font-weight:800; letter-spacing:.3px; margin:0; font-size:clamp(18px,4.8vw,22px); }
    .sub{ color:var(--muted); }
    .card-elevated{ border:0; border-radius: var(--radius-lg); background: var(--surface); box-shadow: var(--shadow-md); }
    .filter-card{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:12px; margin-bottom:16px; }
    .list-thumb{ width:44px; height:44px; border-radius:10px; object-fit:cover; background:#f3f4f6; }
    .order-badge{ font-size:12px; border-radius:999px; padding:4px 10px; font-weight:700; }
    .addr{ font-size:13px; color:var(--muted); }
    .pagination .page-link{ border-radius:8px; }
  </style>
</head>
<body>
  <main class="main-container">
    <div class="container-narrow">
      <header class="hero">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h1 class="brand-title mb-1"><?php echo h($customer['name']); ?></h1>
            <div class="sub">
              <?php echo h(($customer['user_country_code'] ?: '+') . ' ' . $customer['mobile_number']); ?>
              · <?php echo h($customer['email'] ?: '-'); ?>
              · Pincode: <strong><?php echo h($customer['pincode'] ?: '-'); ?></strong>
            </div>
          </div>
          <div>
            <a href="customers.php" class="btn btn-light btn-sm">Back</a>
          </div>
        </div>
      </header>

      <form method="get" class="filter-card">
        <input type="hidden" name="id" value="<?php echo (int)$customerId; ?>">
        <div class="row g-2">
          <div class="col-sm-3">
            <label class="form-label">From</label>
            <input type="date" class="form-control" name="from" value="<?php echo h($from); ?>">
          </div>
          <div class="col-sm-3">
            <label class="form-label">To</label>
            <input type="date" class="form-control" name="to" value="<?php echo h($to); ?>">
          </div>
          <div class="col-sm-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="">All</option>
              <?php foreach ($allowedStatuses as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo strtolower($status)===$s?'selected':''; ?>>
                  <?php echo ucwords(str_replace('_',' ', $s)); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-2">
            <label class="form-label">Per page</label>
            <select class="form-select" name="per_page" onchange="this.form.submit()">
              <?php foreach ([10,20,50,100,200] as $pp): ?>
                <option value="<?php echo $pp; ?>" <?php echo $pp==$perPage?'selected':''; ?>><?php echo $pp; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-1 d-flex align-items-end">
            <button class="btn btn-primary w-100">Apply</button>
          </div>
        </div>
      </form>

      <?php if (!$orders): ?>
        <div class="card card-elevated p-4">
          <div class="text-muted">No orders found for the selected filters.</div>
        </div>
      <?php else: ?>
        <?php foreach ($orders as $o): ?>
          <?php
            $oid = (int)$o['id'];
            $addr = null;
            if (!empty($o['address_id'])) {
              $addr = $addressById[(int)$o['address_id']] ?? null;
            }
            $badgeClass = status_badge_class((string)$o['status']);
          ?>
          <div class="card card-elevated mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="mb-1">
                    <span class="order-badge <?php echo $badgeClass; ?>">#<?php echo h($oid); ?> · <?php echo h(ucwords(str_replace('_',' ', (string)$o['status']))); ?></span>
                  </div>
                  <div class="fw-bold">₹<?php echo h(number_format((float)$o['total_amount'], 2)); ?> · <?php echo h(strtoupper((string)$o['payment_method'])); ?></div>
                  <div class="text-muted small">
                    Placed: <?php echo h($o['created_at'] ? date('d M Y, h:i A', strtotime($o['created_at'])) : '-'); ?>
                    <?php if (!empty($o['delivered_at'])): ?>
                      · Delivered: <strong><?php echo h(date('d M Y, h:i A', strtotime($o['delivered_at']))); ?></strong>
                    <?php endif; ?>
                  </div>
                  <?php if ($addr): ?>
                    <div class="addr mt-2">
                      <?php echo h($addr['receiver_name'] ?? ''); ?> · <?php echo h($addr['receiver_number'] ?? ''); ?><br>
                      <?php
                        $line1 = trim(($addr['door_no'] ?? '') . ', ' . ($addr['street_address'] ?? ''), ', ');
                        $line2 = trim(($addr['city'] ?? '') . ', ' . ($addr['state'] ?? '') . ' - ' . ($addr['pincode'] ?? ''), ', - ');
                        echo h($line1); echo $line1 && $line2 ? '<br>' : ''; echo h($line2);
                      ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div>
                  <a class="btn btn-outline-secondary btn-sm" href="order_view.php?order_id=<?php echo (int)$oid; ?>">
                    <i class="bi bi-box-seam"></i> View
                  </a>
                </div>
              </div>

              <?php if (!empty($itemsByOrder[$oid])): ?>
                <div class="mt-3">
                  <?php foreach ($itemsByOrder[$oid] as $it): ?>
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

        <?php if ($totalPages > 1): ?>
          <nav class="mt-3">
            <ul class="pagination justify-content-center m-0">
              <?php
                $qsBase = [
                  'id' => $customerId,
                  'from' => $from,
                  'to' => $to,
                  'status' => $status,
                  'per_page' => $perPage
                ];
                $makeLink = function($p) use ($qsBase){ $qsBase['page']=$p; return '?'.http_build_query($qsBase); };
              ?>
              <li class="page-item <?php echo $page<=1?'disabled':''; ?>">
                <a class="page-link" href="<?php echo $makeLink(max(1,$page-1)); ?>">&laquo;</a>
              </li>
              <?php
                $start = max(1, $page - 2);
                $end   = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
              ?>
                <li class="page-item <?php echo $i==$page?'active':''; ?>">
                  <a class="page-link" href="<?php echo $makeLink($i); ?>"><?php echo $i; ?></a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>">
                <a class="page-link" href="<?php echo $makeLink(min($totalPages,$page+1)); ?>">&raquo;</a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>