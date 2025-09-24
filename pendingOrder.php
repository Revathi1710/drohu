<?php
include('connection.php');
session_start();
// Include the sidebar with updated styles
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
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$orderIdQ = trim($_GET['order_id'] ?? '');
$statusQ = strtolower(trim($_GET['status'] ?? 'pending')); // Default to 'pending'
$fromQ = $_GET['fromdate'] ?? '';
$toQ = $_GET['todate'] ?? '';
$fromDb = toDbDate($fromQ);
$toDb = toDbDate($toQ);

$where = [];
$params = [];
$types = '';

if ($orderIdQ !== '') { $where[] = 'o.id = ?'; $params[] = (int)$orderIdQ; $types .= 'i'; }
if ($statusQ !== '') { $where[] = 'LOWER(o.status) = ?'; $params[] = $statusQ; $types .= 's'; }
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
    'status' => $statusQ,
    'fromdate' => $fromQ,
    'todate' => $toQ,
    'per_page' => $perPage
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
       <style>
        /* Modern Zoho-like color palette */
        :root {
            --card-bg: #FFFFFF;
            --border-color: #E2E8F0;
            --text-muted: #6B7280;
            --primary-blue: #2563EB;
            --primary-blue-light: #EFF6FF;
            --primary-green: #10B981;
            --primary-green-light: #ECFDF5;
            --primary-yellow: #F59E0B;
            --primary-yellow-light: #FFF7ED;
            --primary-red: #DC2626;
            --primary-red-light: #FEF2F2;
            --primary-purple: #8B5CF6;
            --primary-purple-light: #EDE9FE;
        }
:root {
    --primary-color: #4A90E2;
    --primary-hover: #3A7BC8;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color
#ffc107
: #ffc107;
    --light-bg: #F0F4F8;
    --white: #ffffff;
    --border-color: #E0E6ED;
    --text-primary: #212529;
    --text-secondary: #6C757D;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 2px 4px 0 rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 4px 8px 0 rgba(0, 0, 0, 0.12);
    --radius-sm: 0.25rem;
    --radius-md: 0.35rem;
    --radius-lg: 0.5rem;
}.counter {
    background: var(--primary-color);
    color: var(--white);
    border-radius: 999px;
    padding: 0.25rem 0.75rem;
    font-weight: 600;
    font-size: 0.8rem;
}
        body {
            background-color: #F8F9FA;
            font-family: 'Inter', sans-serif;
            color: #333;
        }

        .main-container {
            padding: 2rem;
        }
        
        /* New Zoho-like table card */
        .data-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* Hide scrollbar for a cleaner look */
        .table-responsive::-webkit-scrollbar {
            display: none;
        }
        .table-responsive {
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }
        
        .table {
            --bs-table-hover-bg: #F8FAFC;
        }
        
        .table thead th {
            background-color: #F8FAFC;
            color: #6B7280;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }

        .table tbody td {
            font-size: 0.875rem;
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Status Badges */
        .badge-status {
            font-weight: 700;
            border-radius: 999px;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            display: inline-block;
        }

        .st-pending { background-color: var(--primary-yellow-light); color: var(--primary-yellow); }
        .st-paid, .st-processing { background-color: var(--primary-blue-light); color: var(--primary-blue); }
        .st-shipped { background-color: var(--primary-purple-light); color: var(--primary-purple); }
        .st-delivered { background-color: var(--primary-green-light); color: var(--primary-green); }
        .st-cancelled { background-color: var(--primary-red-light); color: var(--primary-red); }

        .form-control, .form-select {
            border-radius: 8px;
            border-color: var(--border-color);
        }

        .btn {
            border-radius: 8px;
        }
        .btn-primary { background-color: var(--primary-blue); border-color: var(--primary-blue); }
        .btn-primary:hover { background-color: #1D4ED8; border-color: #1D4ED8; }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }.page-header {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
}.card-elevated {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-color);
}
    </style>
</head>
<body>

    <div class="main-container">

        <div class="d-flex justify-content-between align-items-center mb-4 page-header">
            <h4 class="mb-0 fw-bold">Pending Orders</h4>
            <span class="badge counter rounded-pill fw-bold py-2 px-3"><?= (int)$totalOrders ?> total orders</span>
        </div>

        <div class="card p-3 mb-4 card-elevated ">
            <form method="get">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label text-muted small">Order ID</label>
                        <input type="number" class="form-control" name="order_id" value="<?= h($orderIdQ) ?>" placeholder="e.g. 1024">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label text-muted small">Status</label>
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
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label text-muted small">Date Range</label>
                        <input type="text" id="daterange" class="form-control" value="<?= ($fromQ && $toQ)?(h($fromQ.' - '.$toQ)):'' ?>" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label text-muted small">Per page</label>
                        <select class="form-select" name="per_page">
                            <?php foreach ([10,20,50,100,200] as $pp): ?>
                                <option value="<?= $pp ?>" <?= $pp==$perPage?'selected':'' ?>><?= $pp ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 mt-3">
                        <button class="btn btn-primary me-2"><i class="fa-solid fa-filter me-1"></i>Apply Filters</button>
                        <a class="btn btn-outline-secondary" href="<?= strtok($_SERVER['REQUEST_URI'],'?') ?>"><i class="fa-solid fa-rotate-left me-1"></i>Reset</a>
                    </div>
                </div>
                <input type="hidden" name="fromdate" id="fromdate" value="<?= h($fromQ) ?>">
                <input type="hidden" name="todate" id="todate" value="<?= h($toQ) ?>">
            </form>
        </div>

        <div class="data-card card-elevated ">
            <div class="table-responsive">
                <table class="table table-hover m-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <!--<th>Items</th>-->
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
                                <td colspan="9" class="text-center text-muted py-5">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php
                                $serialNumber = $offset + 1;
                                foreach ($orders as $row):
                                    $status = strtolower($row['status'] ?? 'paid');
                                    $badgeClass = 'st-' . $status;
                                    $canComplete = !in_array($status, ['delivered','cancelled']);
                                    $canCancel = !in_array($status, ['delivered','cancelled']);
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
                                   <!-- <td>
                                        <div class="fw-semibold"><?= (int)$row['qty_total'] ?> qty</div>
                                       
                                    </td>-->
                                    <td class="fw-bold"><?= (int)$row['qty_total'] ?> qty <br>₹<?= rupee($row['total_amount']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary text-white"><?= h(strtoupper($row['payment_method'])) ?></span>
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
                                    <td class="text-end actions" style="width:200px;">
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
                <nav class="p-3 d-flex justify-content-center">
                    <ul class="pagination m-0">
                        <?php
                            $qsBase = $filtersQS; unset($qsBase['page']);
                            $makeLink = fn($p) => '?'.http_build_query(array_merge($qsBase, ['page' => $p]));
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
            document.getElementById('todate').value = picker.endDate.format('DD/MM/YYYY');
        });
        $dr.on('cancel.daterangepicker', function() {
            this.value = '';
            document.getElementById('fromdate').value = '';
            document.getElementById('todate').value = '';
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