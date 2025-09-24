<?php
// admin_order_details.php
session_start();
include('connection.php');
include('sidebar.php');

if (!isset($_GET['order_id'])) {
    header('Location: admin_orders.php'); exit();
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupee($n){ return number_format((float)$n, 2); }

$orderId = (int)$_GET['order_id'];
$order = null; $items = []; $address = null;

/* Order + customer + address */
$sql = "SELECT o.id,o.user_id,o.address_id,o.total_amount,o.status,o.payment_method,o.payment_id,o.created_at,
               u.name AS user_name,u.mobile_number AS user_mobile,
               a.address_label,a.door_no,a.street_address,a.city,a.state,a.pincode,a.receiver_name,a.receiver_number
        FROM orders o
        LEFT JOIN users u ON u.id=o.user_id
        LEFT JOIN address_details a ON a.id=o.address_id
        WHERE o.id=? LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->bind_param('i',$orderId);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc() ?: null;
$stmt->close();

if (!$order) { header('Location: admin_orders.php'); exit(); }

/* Items */
$stmtI = $con->prepare("SELECT oi.product_id,oi.product_name,oi.price,oi.quantity,p.product_image
                        FROM order_items oi
                        LEFT JOIN product p ON p.id=oi.product_id
                        WHERE oi.order_id=? ORDER BY oi.id ASC");
$stmtI->bind_param('i',$orderId);
$stmtI->execute();
$resI = $stmtI->get_result();
while ($r=$resI->fetch_assoc()) { $items[] = $r; }
$stmtI->close();

$items_total = 0; $qty_total = 0;
foreach ($items as $it){ $items_total += (float)$it['price']*(int)$it['quantity']; $qty_total += (int)$it['quantity']; }

$badgeMap = [
  'pending'   => ['Pending','st-pending'],
  'paid'      => ['Paid','st-paid'],
  'processing'=> ['Processing','st-processing'],
  'shipped'   => ['Shipped','st-shipped'],
  'delivered' => ['Delivered','st-delivered'],
  'cancelled' => ['Cancelled','st-cancelled'],
];
$stKey = strtolower($order['status'] ?? 'paid');
$badge  = $badgeMap[$stKey] ?? ['Paid','st-paid'];
$placed = $order['created_at'] ? date('d M Y, h:i A', strtotime($order['created_at'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order #<?= (int)$orderId ?> · Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<style>
  :root{ --card:#fff; --border:#e2e8f0; --muted:#6b7280; --ok:#1a9c46; }
  body{ background:linear-gradient(135deg,#f8fafc,#e2e8f0); font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif; }
  .main-container{ padding:16px; margin-left:var(--sidebar-width); }
  .main-content.expanded + .main-container{ margin-left:var(--sidebar-collapsed-width); }
  .cardx{ background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; }
  .hd{ padding:12px 14px; border-bottom:1px solid var(--border); background:#f8fafc; display:flex; justify-content:space-between; align-items:center; }
  .section{ padding:12px 14px; }
  .badge-status{ font-weight:800; border-radius:999px; padding:6px 10px; font-size:11px; }
  .st-pending{ background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; }
  .st-paid{ background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
  .st-processing{ background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; }
  .st-shipped{ background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe; }
  .st-delivered{ background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
  .st-cancelled{ background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
  .stat-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; }
  .muted{ color:var(--muted); }
  .itm{ display:flex; gap:12px; align-items:center; padding:10px 0; border-bottom:1px solid #f1f5f9; }
  .itm:last-child{ border-bottom:none; }
  .thumb{ width:56px; height:56px; border-radius:12px; border:1px solid var(--border); background:#f9fafb; overflow:hidden; }
  .thumb img{ width:100%; height:100%; object-fit:cover; }
  .actions .btn{ border-radius:10px; padding:8px 12px; }
</style>
</head>
<body>
<div class="main-container">
  <!-- Header -->
  <div class="cardx mb-3">
    <div class="hd">
      <div>
        <div class="fw-bold">Order #<?= (int)$orderId ?></div>
        <div class="small text-muted">Placed on <?= h($placed) ?></div>
      </div>
      <div id="statusBadge" class="badge-status <?= $badge[1] ?>"><?= h($badge[0]) ?></div>
    </div>
    <div class="section stat-grid">
      <div>
        <div class="muted small">Customer</div>
        <div class="fw-semibold"><?= h($order['user_name'] ?: 'Customer') ?></div>
        <div class="small text-muted"><?= h($order['user_mobile'] ?: '-') ?></div>
      </div>
      <div>
        <div class="muted small">Payment</div>
        <div class="fw-semibold"><?= h(strtoupper($order['payment_method'])) ?></div>
        <div class="small text-muted"><?= $order['payment_id'] ? ('Txn: '.h($order['payment_id'])) : '' ?></div>
      </div>
      <div>
        <div class="muted small">Items</div>
        <div class="fw-semibold"><?= (int)$qty_total ?> pcs</div>
        <div class="small text-muted"><?= count($items) ?> lines</div>
      </div>
      <div>
        <div class="muted small">Amount</div>
        <div class="fw-bold fs-5">₹<?= rupee($order['total_amount']) ?></div>
      </div>
    </div>
  </div>

  <!-- Address -->
  <div class="cardx mb-3">
    <div class="hd"><div class="fw-semibold">Delivery Address</div></div>
    <div class="section">
      <?php if ($order['address_id']): ?>
        <div class="fw-semibold"><?= h($order['address_label'] ?: 'Home') ?></div>
        <div class="small text-muted">
          <?= h(trim(($order['door_no'] ?: '').', '.($order['street_address'] ?: ''),', ')) ?><br>
          <?= h(trim(($order['city'] ?: '').', '.($order['state'] ?: '').' - '.($order['pincode'] ?: ''),', - ')) ?><br>
          <?= h($order['receiver_name'] ?: '') ?> • <?= h($order['receiver_number'] ?: '') ?>
        </div>
      <?php else: ?>
        <div class="text-muted">No address stored with this order.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Items -->
  <div class="cardx mb-3">
    <div class="hd"><div class="fw-semibold">Items (<?= (int)$qty_total ?>)</div></div>
    <div class="section">
      <?php foreach ($items as $it): $line=(float)$it['price']*(int)$it['quantity']; ?>
        <div class="itm">
          <div class="thumb">
            <img src="./<?= h($it['product_image']) ?>" alt=""
                 onerror="this.src='https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=120&h=120&fit=crop&crop=center'">
          </div>
          <div class="flex-grow-1">
            <div class="fw-semibold"><?= h($it['product_name']) ?></div>
            <div class="small text-muted">₹<?= rupee($it['price']) ?> × <?= (int)$it['quantity'] ?></div>
          </div>
          <div class="fw-bold">₹<?= rupee($line) ?></div>
        </div>
      <?php endforeach; ?>
      <div class="d-flex justify-content-end pt-2">
        <div class="text-end">
          <div class="small text-muted">Item total</div>
          <div class="fw-bold fs-5">₹<?= rupee($items_total) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Status controls -->
  <div class="cardx">
    <div class="hd"><div class="fw-semibold">Update Status</div></div>
    <div class="section">
      <div class="row g-2 align-items-end">
        <div class="col-sm-6 col-md-4">
          <label class="form-label small text-muted">Set status</label>
          <select class="form-select" id="statusSelect">
            <?php
              $opts = ['pending','paid','processing','shipped','delivered','cancelled'];
              foreach ($opts as $s) {
                $sel = (strtolower($order['status'])===$s)?'selected':'';
                echo "<option value=\"".h($s)."\" $sel>".ucfirst($s)."</option>";
              }
            ?>
          </select>
        </div>
        <div class="col-sm-6 col-md-8 d-flex gap-2">
          <button class="btn btn-primary" id="btnSaveStatus"><i class="fa-solid fa-floppy-disk me-1"></i>Save status</button>
          <button class="btn btn-success" id="btnDelivered"><i class="fa-solid fa-check me-1"></i>Mark Delivered</button>
          <button class="btn btn-danger" id="btnCancel"><i class="fa-solid fa-xmark me-1"></i>Cancel Order</button>
          <a class="btn btn-outline-secondary ms-auto" href="allorder.php"><i class="fa-regular fa-circle-left me-1"></i>Back</a>
        </div>
      </div>
      <div class="small text-muted mt-2">Quick actions call the same update endpoint used on the orders list.</div>
    </div>
  </div>
</div>

<script>
const orderId = <?= (int)$orderId ?>;
const badge = document.getElementById('statusBadge');
const selectEl = document.getElementById('statusSelect');
const map = {
  pending:{label:'Pending',cls:'st-pending'},
  paid:{label:'Paid',cls:'st-paid'},
  processing:{label:'Processing',cls:'st-processing'},
  shipped:{label:'Shipped',cls:'st-shipped'},
  delivered:{label:'Delivered',cls:'st-delivered'},
  cancelled:{label:'Cancelled',cls:'st-cancelled'}
};

function applyBadge(status){
  const key = (status||'').toLowerCase();
  const m = map[key] || map.paid;
  badge.textContent = m.label;
  badge.className = 'badge-status ' + m.cls;
  selectEl.value = key;
}

async function postStatus(payload){
  const res = await fetch('update_order_status.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  return res.json();
}

document.getElementById('btnSaveStatus').addEventListener('click', async ()=>{
  const status = selectEl.value;
  // Prefer generic "set" if your endpoint supports it; map delivered/cancelled to quick actions otherwise
  const payload = (status==='delivered' || status==='cancelled')
      ? { order_id: orderId, action: (status==='delivered'?'complete':'cancel') }
      : { order_id: orderId, action: 'set', status };
  try{
    const data = await postStatus(payload);
    if (data.success) applyBadge(data.status || status);
    else alert(data.message || 'Failed to update.');
  }catch(e){ alert('Network error.'); }
});

document.getElementById('btnDelivered').addEventListener('click', async ()=>{
  if (!confirm('Mark this order as Delivered?')) return;
  try{
    const data = await postStatus({ order_id: orderId, action:'complete' });
    if (data.success) applyBadge('delivered'); else alert(data.message||'Failed');
  }catch(e){ alert('Network error.'); }
});

document.getElementById('btnCancel').addEventListener('click', async ()=>{
  if (!confirm('Cancel this order?')) return;
  try{
    const data = await postStatus({ order_id: orderId, action:'cancel' });
    if (data.success) applyBadge('cancelled'); else alert(data.message||'Failed');
  }catch(e){ alert('Network error.'); }
});
</script>
</body>
</html>