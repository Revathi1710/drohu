<?php
include('connection.php');
session_start();
include('sidebar.php');
ini_set('display_errors', 1);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

$nameQ   = trim($_GET['name'] ?? '');
$mobileQ = trim($_GET['mobile'] ?? '');

$where = [];
$params = [];
$types  = '';

if ($nameQ !== '') { $where[] = 'name LIKE ?'; $params[] = "%{$nameQ}%"; $types .= 's'; }
if ($mobileQ !== '') { $where[] = 'mobile_number LIKE ?'; $params[] = "%{$mobileQ}%"; $types .= 's'; }

$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

$sqlCount = "SELECT COUNT(*) AS c FROM users $whereSql";
$stmt = $con->prepare($sqlCount);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalRows = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
$stmt->close();

$sql = "
    SELECT id, name, mobile_number, email, COALESCE(role,'customer') AS role
    FROM users
    $whereSql
    ORDER BY id DESC
    LIMIT ?, ?
";
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
$filtersQS = ['name'=>$nameQ,'mobile'=>$mobileQ,'per_page'=>$perPage];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
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

        .actions .btn { padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header d-flex align-items-center justify-content-between">
            <div>
                <h1 class="page-title"><i class="fas fa-users"></i>All Customers</h1>
                <p class="page-subtitle">View and manage all customer accounts</p>
            </div>
            <span class="counter"><?= (int)$totalRows ?> total</span>
        </div>

        <div class="card card-elevated mb-4">
            <div class="card-body">
                <form method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?= h($nameQ) ?>" placeholder="Search by name">
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control" name="mobile" value="<?= h($mobileQ) ?>" placeholder="Search by mobile">
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
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th style="width:160px;">Role</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No customers found.</td></tr>
                        <?php else: $serial=$offset+1; foreach ($rows as $row): ?>
                            <tr id="cust-<?= (int)$row['id'] ?>">
                                <td><?= $serial ?></td>
                                <td class="fw-semibold" data-col="name"><?= h($row['name']) ?></td>
                                <td data-col="mobile"><?= h($row['mobile_number']) ?></td>
                                <td data-col="email"><?= h($row['email']) ?></td>
                                <td>
                                    <select class="form-select form-select-sm role-select" data-id="<?= (int)$row['id'] ?>" data-name="<?= h($row['name']) ?>" data-mobile="<?= h($row['mobile_number']) ?>" data-email="<?= h($row['email']) ?>">
                                        <option value="customer" <?= strtolower($row['role'])==='customer'?'selected':'' ?>>Customer</option>
                                        <option value="delivery" <?= strtolower($row['role'])==='delivery'?'selected':'' ?>>Delivery</option>
                                    </select>
                                </td>
                                <td class="text-end actions">
                                    <a class="btn btn-sm btn-outline-secondary" href="customer_view.php?id=<?= (int)$row['id'] ?>"><i class="fa-regular fa-eye"></i></a>
                                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="<?= (int)$row['id'] ?>">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <a class="btn btn-sm btn-danger" href="customer_delete.php?id=<?= (int)$row['id'] ?>" onclick="return confirm('Delete this customer?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php $serial++; endforeach; endif; ?>
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
                    <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= $makeLink(max(1,$page-1)) ?>">&laquo;</a></li>
                    <?php for($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?>
                        <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="<?= $makeLink($i) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?= $makeLink(min($totalPages,$page+1)) ?>">&raquo;</a></li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="convertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <form id="convertForm">
                <div class="modal-header">
                    <h5 class="modal-title">Convert to Delivery Person</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="convUserId">
                    <div class="mb-2"><label class="form-label">Username</label><input type="text" class="form-control" id="convUsername" placeholder="4-32 chars (letters, numbers, . _ -)" required></div>
                    <div class="mb-2"><label class="form-label">Password</label><input type="password" class="form-control" id="convPassword" placeholder="min 6 characters" required></div>
                    <div class="mb-2"><label class="form-label">Service Pincode</label><input type="text" class="form-control" id="convPincode" placeholder="6-digit pincode" required></div>
                    <div id="convErr" class="text-danger small d-none"></div>
                    <div id="convOk" class="text-success small d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="convSaveBtn">
                        <span class="save-text">Save</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editCustomerForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="custId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="custName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control" name="mobile_number" id="custMobile" inputmode="numeric" pattern="\d{10}" maxlength="10" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="custEmail" required>
                        </div>
                        <div class="text-danger small mt-2 d-none" id="editErr"></div>
                        <div class="text-success small mt-2 d-none" id="editOk"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <span class="save-text">Save changes</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const convertModal = new bootstrap.Modal(document.getElementById('convertModal'));
        const convUserId = document.getElementById('convUserId');
        const convUsername = document.getElementById('convUsername');
        const convPassword = document.getElementById('convPassword');
        const convPincode = document.getElementById('convPincode');
        const convErr = document.getElementById('convErr');
        const convOk = document.getElementById('convOk');
        const convForm = document.getElementById('convertForm');
        const convSaveBtn = document.getElementById('convSaveBtn');

        function showErr(el, msg) { el.textContent = msg; el.classList.remove('d-none'); }
        function showOk(el, msg) { el.textContent = msg; el.classList.remove('d-none'); setTimeout(() => el.classList.add('d-none'), 1200); }

        document.addEventListener('change', (e) => {
            const sel = e.target.closest('.role-select');
            if (!sel) return;
            const id = Number(sel.dataset.id);
            const role = sel.value;
            if (role === 'delivery') {
                convUserId.value = String(id);
                convUsername.value = (sel.dataset.name || 'user').toLowerCase().replace(/\s+/g, '').slice(0, 20) + id;
                convPassword.value = '';
                convPincode.value = '';
                convErr.classList.add('d-none');
                convOk.classList.add('d-none');
                convertModal.show();
            } else {
                fetch('users_role_update.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, role })
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (!d.ok) {
                            alert(d.error || 'Role update failed');
                            sel.value = d.prev || 'customer';
                        }
                    });
            }
        });

        convForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            convErr.classList.add('d-none');
            convOk.classList.add('d-none');
            const id = Number(convUserId.value);
            const username = convUsername.value.trim();
            const password = convPassword.value;
            const pincode = convPincode.value.trim();
            if (!/^[A-Za-z0-9._-]{4,32}$/.test(username)) {
                showErr(convErr, 'Invalid username');
                return;
            }
            if (password.length < 6) {
                showErr(convErr, 'Password min 6 chars');
                return;
            }
            if (!/^\d{6}$/.test(pincode)) {
                showErr(convErr, 'Invalid pincode');
                return;
            }
            convSaveBtn.disabled = true;
            convSaveBtn.querySelector('.spinner-border').classList.remove('d-none');
            convSaveBtn.querySelector('.save-text').classList.add('d-none');
            try {
                const res = await fetch('users_role_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, role: 'delivery', username, password, pincode })
                });
                const data = await res.json();
                if (data.ok) {
                    showOk(convOk, 'Saved');
                    setTimeout(() => convertModal.hide(), 600);
                } else {
                    showErr(convErr, data.error || 'Save failed');
                }
            } catch (_) {
                showErr(convErr, 'Network error');
            } finally {
                convSaveBtn.disabled = false;
                convSaveBtn.querySelector('.spinner-border').classList.add('d-none');
                convSaveBtn.querySelector('.save-text').classList.remove('d-none');
            }
        });

        const modalEl = document.getElementById('editCustomerModal');
        const editModal = new bootstrap.Modal(modalEl);
        const editForm = document.getElementById('editCustomerForm');
        const errBox = document.getElementById('editErr');
        const okBox = document.getElementById('editOk');
        const saveBtn = document.getElementById('saveBtn');
        const spinner = saveBtn.querySelector('.spinner-border');
        const saveTxt = saveBtn.querySelector('.save-text');

        function showErr(msg) {
            errBox.textContent = msg;
            errBox.classList.remove('d-none');
            okBox.classList.add('d-none');
        }

        function showOk(msg) {
            okBox.textContent = msg;
            okBox.classList.remove('d-none');
            errBox.classList.add('d-none');
        }

        function clearMsgs() {
            errBox.classList.add('d-none');
            okBox.classList.add('d-none');
        }

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-edit');
            if (!btn) return;
            const id = Number(btn.dataset.id);
            clearMsgs();
            try {
                const res = await fetch('customer_get.php?id=' + id, {
                    cache: 'no-store'
                });
                const data = await res.json();
                if (!data.ok) {
                    showErr(data.error || 'Failed to load');
                    return;
                }
                document.getElementById('custId').value = data.user.id;
                document.getElementById('custName').value = data.user.name || '';
                document.getElementById('custMobile').value = data.user.mobile_number || '';
                document.getElementById('custEmail').value = data.user.email || '';
                editModal.show();
            } catch (_) {
                showErr('Network error');
            }
        });

        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearMsgs();
            saveBtn.disabled = true;
            spinner.classList.remove('d-none');
            saveTxt.classList.add('d-none');
            const payload = {
                id: Number(document.getElementById('custId').value),
                name: document.getElementById('custName').value.trim(),
                mobile_number: document.getElementById('custMobile').value.trim(),
                email: document.getElementById('custEmail').value.trim()
            };
            try {
                const res = await fetch('customer_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.ok) {
                    showOk('Saved');
                    const row = document.getElementById('cust-' + payload.id);
                    if (row) {
                        const nameCell = row.querySelector('[data-col="name"]');
                        const mobCell = row.querySelector('[data-col="mobile"]');
                        const emCell = row.querySelector('[data-col="email"]');
                        if (nameCell) nameCell.textContent = data.user.name;
                        if (mobCell) mobCell.textContent = data.user.mobile_number;
                        if (emCell) emCell.textContent = data.user.email;
                    }
                    setTimeout(() => editModal.hide(), 600);
                } else {
                    showErr(data.error || 'Save failed');
                }
            } catch (_) {
                showErr('Network error');
            } finally {
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
                saveTxt.classList.remove('d-none');
            }
        });
    </script>
</body>
</html>