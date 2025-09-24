<?php
include('connection.php');
session_start();
include('sidebar.php');
ini_set('display_errors', 1);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

$nameQ   = trim($_GET['deliveryperson_name'] ?? '');
$pinQ    = trim($_GET['pincode'] ?? '');

$where = [];
$params = [];
$types  = '';

if ($nameQ !== '') {
    $where[] = 'deliveryperson_name LIKE ?';
    $params[] = "%{$nameQ}%";
    $types   .= 's';
}
if ($pinQ !== '') {
    $where[] = 'pincode LIKE ?';
    $params[] = "%{$pinQ}%";
    $types   .= 's';
}

$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

/* Count total delivery persons */
$sqlCount = "SELECT COUNT(*) AS c FROM deliveryPerson $whereSql";
$stmt = $con->prepare($sqlCount);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$totalRows = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
$stmt->close();

/* Fetch paginated delivery persons */
$sql = "
    SELECT id, deliveryperson_name, number, email, username, pincode
    FROM deliveryPerson
    $whereSql
    ORDER BY id DESC
    LIMIT ?, ?";
$stmt = $con->prepare($sql);
if ($types) {
    $bindTypes = $types.'ii';
    $paramsWithPaging = array_merge($params, [$offset, $perPage]);
    $stmt->bind_param($bindTypes, ...$paramsWithPaging);
} else {
    $stmt->bind_param('ii', $offset, $perPage);
}
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }
$stmt->close();

$totalPages = max(1, (int)ceil($totalRows / $perPage));

$filtersQS = [
    'deliveryperson_name' => $nameQ,
    'pincode'             => $pinQ,
    'per_page'            => $perPage
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Delivery Persons</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2;
            --primary-hover: #3A7BC8;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
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
        }
        * { box-sizing: border-box; }
        body { background: var(--light-bg); font-family: 'Inter', system-ui, sans-serif; }
        .main-container { min-height: 100vh; padding: 1.5rem; margin-left: var(--sidebar-width); }
        .page-header { background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid var(--border-color); }
        .page-title { color: var(--text-primary); font-weight: 600; font-size: 1.5rem; margin: 0; display: flex; align-items: center; gap: 0.75rem; }
        .page-title i { color: var(--primary-color); font-size: 1.75rem; }
        .page-subtitle { color: var(--text-secondary); margin: 0.5rem 0 0 0; font-size: 0.9rem; font-weight: 400; }
        
        .card-elevated { background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); }
        .card-header { background: transparent; border-bottom: 1px solid var(--border-color); padding: 1.25rem 1.5rem; }
        .card-header h5 { margin: 0; font-weight: 600; font-size: 1.125rem; }
        .card-body { padding: 1.5rem; }
        .form-label { font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem; font-size: 0.875rem; }
        .form-control, .form-select { border-radius: var(--radius-md); border-color: var(--border-color); padding: 0.75rem 1rem; }
        .btn-primary { background: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }
        .btn-outline-secondary { color: var(--text-secondary); border-color: var(--text-secondary); }
        .btn-outline-secondary:hover { color: var(--white); background: var(--secondary-color); border-color: var(--secondary-color); }

        .table-container { overflow-x: auto; }
        .table { --bs-table-bg: transparent; --bs-table-hover-bg: #f8fafc; }
        .table thead th { 
            background: var(--light-bg); 
            font-size: 0.8rem; 
            color: var(--text-secondary);
            font-weight: 600; 
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }
        .table tbody td {
            padding: 1rem 1.5rem;
            color: var(--text-primary);
            vertical-align: middle;
            border-top: 1px solid var(--divider-color);
            font-size: 0.9rem;
        }
        .table tbody tr:first-child td { border-top: none; }
        .table-hover tbody tr:hover { background-color: var(--bs-table-hover-bg); }

        .counter { background: var(--primary-color); color: var(--white); border-radius: 999px; padding: 0.25rem 0.75rem; font-weight: 600; font-size: 0.8rem; }
        .pagination .page-link { border-radius: var(--radius-sm); margin: 0 4px; border-color: var(--border-color); color: var(--text-secondary); }
        .pagination .page-item.active .page-link { background: var(--primary-color); border-color: var(--primary-color); color: var(--white); }
        .pagination .page-item.disabled .page-link { background: #f1f5f9; border-color: var(--border-color); color: var(--text-secondary); }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header d-flex align-items-center justify-content-between">
            <div>
                <h1 class="page-title"><i class="fas fa-people-group"></i>All Delivery Persons</h1>
                <p class="page-subtitle">View and manage all delivery accounts</p>
            </div>
            <span class="counter"><?= (int)$totalRows ?> total</span>
        </div>

        <div class="card card-elevated mb-4">
            <div class="card-body">
                <form method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Delivery Person Name</label>
                            <input type="text" class="form-control" name="deliveryperson_name" value="<?= h($nameQ) ?>" placeholder="Search by name">
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="pincode" value="<?= h($pinQ) ?>" placeholder="Search by pincode">
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <label class="form-label">Per page</label>
                            <select class="form-select" name="per_page" onchange="this.form.submit()">
                                <?php foreach ([10,20,50,100,200] as $pp): ?>
                                    <option value="<?= $pp ?>" <?= $pp==$perPage?'selected':'' ?>><?= $pp ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-4 text-end">
                            <button type="submit" class="btn btn-primary me-2"><i class="fa-solid fa-filter me-1"></i>Apply Filters</button>
                            <a class="btn btn-outline-secondary" href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>"><i class="fa-solid fa-rotate-left me-1"></i>Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-elevated">
            <div class="table-container">
                <table class="table table-hover m-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Person Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Pincode</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No delivery persons found.</td></tr>
                        <?php else: ?>
                            <?php $serial = $offset + 1; foreach ($rows as $row): ?>
                                <tr id="dp-<?= (int)$row['id'] ?>">
                                    <td><?= $serial ?></td>
                                    <td class="fw-semibold"><?= h($row['deliveryperson_name']) ?></td>
                                    <td><?= h($row['number']) ?></td>
                                    <td><?= h($row['email']) ?></td>
                                    <td><?= h($row['username']) ?></td>
                                    <td><?= h($row['pincode']) ?></td>
                                    <td class="text-end actions">
                                        <a class="btn btn-sm btn-outline-secondary" href="deliveryperson_view.php?id=<?= (int)$row['id'] ?>"><i class="fa-regular fa-eye"></i></a>
                                        <a class="btn btn-sm btn-outline-primary" href="deliveryperson_edit.php?id=<?= (int)$row['id'] ?>"><i class="fa-regular fa-pen-to-square"></i></a>
                                        <a class="btn btn-sm btn-danger" href="deliveryperson_delete.php?id=<?= (int)$row['id'] ?>" onclick="return confirm('Delete this delivery person?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php $serial++; endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="p-3">
                <ul class="pagination justify-content-center m-0">
                    <?php
                        $qsBase = $filtersQS; unset($qsBase['page']);
                        $makeLink = function($p) use ($qsBase){ $qsBase['page']=$p; return '?'.http_build_query($qsBase); };
                    ?>
                    <li class="page-item <?= $page<=1?'disabled':'' ?>">
                        <a class="page-link" href="<?= $makeLink(max(1,$page-1)) ?>">&laquo;</a>
                    </li>
                    <?php
                        $start = max(1, $page - 2);
                        $end   = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?= $i==$page?'active':'' ?>">
                            <a class="page-link" href="<?= $makeLink($i) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
                        <a class="page-link" href="<?= $makeLink(min($totalPages,$page+1)) ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>