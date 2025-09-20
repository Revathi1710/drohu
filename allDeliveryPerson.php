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
    :root{ --card:#fff; --border:#e2e8f0; --muted:#6b7280; }
    body{ background:linear-gradient(135deg,#f8fafc,#e2e8f0); font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif; }

    .main-container{ padding:16px; margin-left:var(--sidebar-width); }
    .main-content.expanded + .main-container{ margin-left:var(--sidebar-collapsed-width); }

    .page-header{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:12px 16px; margin-bottom:16px; }
    .page-title{ display:flex; align-items:center; justify-content:space-between; }
    .counter{ background:#111827; color:#fff; border-radius:999px; padding:6px 10px; font-weight:800; font-size:12px; }

    .filter-card{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:12px; margin-bottom:16px; }
    .grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; }
    .form-control{ height:42px; }

    .data-card{ background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .table thead th{ background:#f8fafc; border-bottom:1px solid var(--border); font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
    .table tbody td{ vertical-align:middle; }
    .actions .btn{ padding:6px 10px; border-radius:8px; }
    .pagination .page-link{ border-radius:8px; }
  </style>
</head>
<body>
  <div class="main-container">
    <div class="page-header">
      <div class="page-title">
        <h5 class="m-0">All Delivery Persons</h5>
        <span class="counter"><?= (int)$totalRows ?> total</span>
      </div>
    </div>

    <form class="filter-card" method="get">
      <div class="grid">
        <div>
          <label class="form-label">Delivery Person Name</label>
          <input type="text" class="form-control" name="deliveryperson_name" value="<?= h($nameQ) ?>" placeholder="e.g. Rohan">
        </div>
        <div>
          <label class="form-label">Pincode</label>
          <input type="text" class="form-control" name="pincode" value="<?= h($pinQ) ?>" placeholder="e.g. 560001">
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
    </form>

    <div class="data-card">
      <div class="table-responsive">
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
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No delivery persons found</td>
              </tr>
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
                    <a class="btn btn-sm btn-outline-secondary" href="deliveryperson_view.php?id=<?= (int)$row['id'] ?>">
                      <i class="fa-regular fa-eye"></i>
                    </a>
                    <a class="btn btn-sm btn-outline-primary" href="deliveryperson_edit.php?id=<?= (int)$row['id'] ?>">
                      <i class="fa-regular fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-sm btn-danger" href="deliveryperson_delete.php?id=<?= (int)$row['id'] ?>" onclick="return confirm('Delete this delivery person?');">
                      <i class="fa-solid fa-trash"></i>
                    </a>
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