<?php
include('connection.php');
session_start();
include('sidebar.php');
ini_set('display_errors', 1);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupee($n){ return number_format((float)$n, 2); }
function toDbDate($d){
  if (!$d) return null;
  $parts = explode('/', $d); // DD/MM/YYYY
  return count($parts)===3 ? ($parts[2].'-'.$parts[1].'-'.$parts[0]) : null;
}

$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

$orderIdQ = trim($_GET['order_id'] ?? '');
$statusQ  = strtolower(trim($_GET['status'] ?? ''));
$fromQ    = $_GET['fromdate'] ?? '';
$toQ      = $_GET['todate'] ?? '';
$fromDb   = toDbDate($fromQ);
$toDb     = toDbDate($toQ);

$where = [];
$params = [];
$types  = '';

if ($orderIdQ !== '') { $where[] = 'o.id = ?'; $params[] = (int)$orderIdQ; $types .= 'i'; }
if ($statusQ !== '')  { $where[] = 'LOWER(o.status) = ?'; $params[] = $statusQ; $types .= 's'; }
if ($fromDb && $toDb) { $where[] = 'DATE(o.created_at) BETWEEN ? AND ?'; $params[] = $fromDb; $params[] = $toDb; $types .= 'ss'; }

$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

/* Count total orders (distinct orders) */
$sqlCount = "SELECT COUNT(*) AS c FROM orders o $whereSql";
$stmt = $con->prepare($sqlCount);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalOrders = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
$stmt->close();

/* Fetch paginated orders with summary */
$sql = "
  SELECT
    o.id,
    o.user_id,
    o.total_amount,
    o.status,
    o.delivery_person_id,
    o.payment_method,
    o.created_at,
    u.name AS user_name,
    u.mobile_number AS user_mobile,
    COUNT(oi.id) AS line_count,
    COALESCE(SUM(oi.quantity),0) AS qty_total
  FROM orders o
  LEFT JOIN users u ON u.id = o.user_id
  LEFT JOIN order_items oi ON oi.order_id = o.id
  $whereSql
  GROUP BY o.id
  ORDER BY o.id DESC
  LIMIT ?, ?";
$stmt = $con->prepare($sql);
if ($types) {
  $bindTypes = $types.'ii';
  $params[] = $offset;
  $params[] = $perPage;
  $stmt->bind_param($bindTypes, ...$params);
} else {
  $stmt->bind_param('ii', $offset, $perPage);
}
$stmt->execute();
$res = $stmt->get_result();
$orders = [];
while ($r = $res->fetch_assoc()) { $orders[] = $r; }
$stmt->close();

$filtersQS = [
  'order_id' => $orderIdQ,
  'status'   => $statusQ,
  'fromdate' => $fromQ,
  'todate'   => $toQ,
  'per_page' => $perPage
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>All Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
  <style>
    :root{ --card:#fff; --border:#e2e8f0; --muted:#6b7280; --ok:#1a9c46; --warn:#f59e0b; --bad:#dc2626; --info:#2563eb; }
    body{ background:linear-gradient(135deg,#f8fafc,#e2e8f0); font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif; }

    .main-container{ padding:16px; margin-left:var(--sidebar-width); }
    .main-content.expanded + .main-container{ margin-left:var(--sidebar-collapsed-width); }
    .page-header{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:12px 16px; margin-bottom:16px; }
    .page-title{ display:flex; align-items:center; justify-content:space-between; }
    .counter{ background:#111827; color:#fff; border-radius:999px; padding:6px 10px; font-weight:800; font-size:12px; }

    .filter-card{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:12px; margin-bottom:16px; }
    .grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; }
    .form-control{ height:42px; }

    .data-card{ background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .table thead th{ background:#f8fafc; border-bottom:1px solid var(--border); font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
    .table tbody td{ vertical-align:middle; }
    .badge-status{ font-weight:800; border-radius:999px; padding:6px 10px; font-size:11px; }
    .st-pending{ background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; }
    .st-paid,.st-processing{ background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .st-shipped{ background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; }
    .st-delivered{ background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .st-cancelled{ background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    .actions .btn{ padding:6px 10px; border-radius:8px; }
    .pagination .page-link{ border-radius:8px; }
  </style>
</head>
<body>
  <div class="main-container">
    <div class="page-header">
      <div class="page-title">
        <h5 class="m-0">All Orders</h5>
        <span class="counter"><?= (int)$totalOrders ?> total</span>
      </div>
    </div>

    <form class="filter-card" method="get">
      <div class="grid">
        <div>
          <label class="form-label">Order ID</label>
          <input type="number" class="form-control" name="order_id" value="<?= h($orderIdQ) ?>" placeholder="e.g. 1024">
        </div>
        <div>
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <?php
              $opts = ['' => 'All', 'pending'=>'Pending','paid'=>'Paid','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'];
              foreach ($opts as $k=>$v) {
                $sel = ($k===$statusQ)?'selected':'';
                echo "<option value=\"".h($k)."\" $sel>".h($v)."</option>";
              }
            ?>
          </select>
        </div>
        <div>
          <label class="form-label">Date Range</label>
          <input type="text" id="daterange" class="form-control" value="<?= ($fromQ && $toQ)?(h($fromQ.' - '.$toQ)):'' ?>" placeholder="DD/MM/YYYY - DD/MM/YYYY">
        </div>
        <div>
          <label class="form-label">Per page</label>
          <select class="form-select" name="per_page" onchange="this.form.submit()">
            <?php foreach ([10,20,50,100,200] as $pp): ?>
              <option value="<?= $pp ?>" <?= $pp==$perPage?'selected':'' ?>><?= $pp ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary"><i class="fa-solid fa-filter me-1"></i>Apply</button>
        <a class="btn btn-outline-secondary" href="<?= strtok($_SERVER['REQUEST_URI'],'?') ?>"><i class="fa-solid fa-rotate-left me-1"></i>Reset</a>
      </div>
      <input type="hidden" name="fromdate" id="fromdate" value="<?= h($fromQ) ?>">
      <input type="hidden" name="todate" id="todate" value="<?= h($toQ) ?>">
    </form>

    <div class="data-card">
      <div class="table-responsive">
      <table class="table table-hover m-0"> 
  <thead>
    <tr>
      <th>#</th>
      <th>Order</th>
      <th>Customer</th>
      <th>Items</th>
      <th>Amount</th>
      <th>Payment</th>
      <th>Delivery Person</th>
      <th>Status</th>
      <th class="text-end">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!$orders): ?>
      <tr>
        <td colspan="8" class="text-center text-muted py-4">No orders found</td>
      </tr>
    <?php else: ?>
      <?php  
        $serialNumber = $offset + 1; // Initialize outside loop
        foreach ($orders as $row):  
          $status = strtolower($row['status'] ?? 'paid');
          $badgeClass = 'st-' . $status;
          $canComplete = !in_array($status, ['delivered','cancelled']);
          $canCancel   = !in_array($status, ['delivered','cancelled']);
      ?>
        <tr id="order-row-<?= (int)$row['id'] ?>">
          <td><?= $serialNumber ?></td>
          <td>
            <div class="fw-semibold">#<?= (int)$row['id'] ?></div>
            <div class="text-muted small"><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></div>
          </td>
          <td>
            <div class="fw-semibold"><?= h($row['user_name'] ?: 'Customer') ?></div>
            <div class="text-muted small"><?= h($row['user_mobile'] ?: '-') ?></div>
          </td>
          <td>
            <div class="fw-semibold"><?= (int)$row['qty_total'] ?> qty</div>
            <div class="text-muted small"><?= (int)$row['line_count'] ?> lines</div>
          </td>
          <td class="fw-bold">₹<?= rupee($row['total_amount']) ?></td>
          <td>
            <span class="badge bg-light text-dark"><?= h(strtoupper($row['payment_method'])) ?></span>
          </td>
<td>
  <select class="form-select form-select-sm delivery-select" data-order-id="<?= (int)$row['id'] ?>">
    <option value="">Select Delivery Person</option>
    <?php 
      $dpRes = $con->query("SELECT id, deliveryperson_name FROM deliveryPerson ORDER BY deliveryperson_name ASC");
      if ($dpRes && $dpRes->num_rows > 0) {
        while ($dp = $dpRes->fetch_assoc()) {
          $sel = ($row['delivery_person_id'] == $dp['id']) ? 'selected' : '';
          echo '<option value="'.(int)$dp['id'].'" '.$sel.'>'.h($dp['deliveryperson_name']).'</option>';
        }
      } else {
        echo '<option disabled>No delivery persons found</option>';
      }
    ?>
  </select>
</td>



          <td>
            <span class="badge-status <?= $badgeClass ?>" id="status-<?= (int)$row['id'] ?>">
              <?= h(ucfirst($row['status'])) ?>
            </span>
          </td>
          <td class="text-end actions">
            <a class="btn btn-sm btn-outline-secondary" href="order_details_admin.php?order_id=<?= (int)$row['id'] ?>">
              <i class="fa-regular fa-eye"></i>
            </a>
            <button class="btn btn-sm btn-success" <?= $canComplete ? '' : 'disabled' ?> data-action="complete" data-id="<?= (int)$row['id'] ?>">
              <i class="fa-solid fa-check"></i>
            </button>
            <button class="btn btn-sm btn-danger" <?= $canCancel ? '' : 'disabled' ?> data-action="cancel" data-id="<?= (int)$row['id'] ?>">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </td>
        </tr>
      <?php $serialNumber++; endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

      </div>

      <?php
        $totalPages = max(1, (int)ceil($totalOrders / $perPage));
        if ($totalPages > 1):
      ?>
      <nav class="p-3">
        <ul class="pagination justify-content-center m-0">
          <?php
            $qsBase = $filtersQS; unset($qsBase['page']);
            $makeLink = function($p) use ($qsBase){ $qsBase['page']=$p; return '?'.http_build_query($qsBase); };
          ?>
          <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= $makeLink(max(1,$page-1)) ?>">&laquo;</a></li>
          <?php for($i=max(1,$page-1); $i<=min($totalPages,$page+1); $i++): ?>
            <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="<?= $makeLink($i) ?>"><?= $i ?></a></li>
          <?php endfor; ?>
          <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?= $makeLink(min($totalPages,$page+1)) ?>">&raquo;</a></li>
        </ul>
      </nav>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/min/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
 <script>
$(document).on('change', '.delivery-select', function() {
  const $select = $(this);
  const orderId = $select.data('order-id');
  const personId = $select.val();

  $.ajax({
    url: 'update_delivery_person.php',
    method: 'POST',
    data: { order_id: orderId, delivery_person_id: personId },
    success: function(res) {
      let data;
      try {
        data = (typeof res === "string") ? JSON.parse(res) : res;
      } catch(e) {
        alert("Unexpected server response:\n" + res);
        return;
      }

      if (data.success) {
        if (data.delivery_person_name) {
          alert("Assigned to: " + data.delivery_person_name);
        } else {
          alert("Delivery person cleared for this order.");
        }
      } else {
        alert(data.message || 'Failed to update.');
      }
    },
    error: function() {
      alert('Network error, please try again.');
    }
  });
});
</script>


  <script>
    // Date range picker -> fills hidden inputs and submits
    $(function(){
      const $dr = $('#daterange');
      $dr.daterangepicker({
        autoUpdateInput: false,
        locale: { format: 'DD/MM/YYYY', cancelLabel: 'Clear' }
      });
      $dr.on('apply.daterangepicker', function(ev, picker) {
        this.value = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
        document.getElementById('fromdate').value = picker.startDate.format('DD/MM/YYYY');
        document.getElementById('todate').value   = picker.endDate.format('DD/MM/YYYY');
      });
      $dr.on('cancel.daterangepicker', function() {
        this.value = '';
        document.getElementById('fromdate').value = '';
        document.getElementById('todate').value   = '';
      });
    });

    // Complete / Cancel actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;
      const id = Number(btn.dataset.id);
      const action = btn.dataset.action; // 'complete' or 'cancel'
      if (action === 'cancel' && !confirm('Cancel this order?')) return;
      if (action === 'complete' && !confirm('Mark this order as Delivered?')) return;

      btn.disabled = true;
      try {
        const res = await fetch('update_order_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ order_id: id, action })
        });
        const data = await res.json();
        if (data.success) {
          const st = document.getElementById('status-'+id);
          if (st) {
            st.textContent = data.status_label;
            st.className = 'badge-status ' + data.status_class;
          }
          // disable both buttons after terminal state
          if (['delivered','cancelled'].includes(data.status)) {
            document.querySelectorAll('button[data-id="'+id+'"]').forEach(b => b.disabled = true);
          }
        } else {
          alert(data.message || 'Failed to update order.');
          btn.disabled = false;
        }
      } catch(err) {
        alert('Network error.');
        btn.disabled = false;
      }
    });
  </script>
</body>
</html>