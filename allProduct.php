<?php
include('connection.php');
session_start();

include('sidebar.php');
ini_set('display_errors', 1);

$filter_query = "";

// Check employee's designation

function getTotalDriversCount($filter_query = "") {
    global $con;
    $base_query = "SELECT COUNT(*) AS total FROM product";
    if (!empty($filter_query)) {
        $base_query .= " WHERE $filter_query";
    }
    $result = mysqli_query($con, $base_query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    } else {
        echo "Error getting total candidate count: " . mysqli_error($con);
        return 0;
    }
}

function getDriversPaginated($offset, $driversPerPage, $filter_query = "", $order_by = "") {
    global $con;
    $base_query = "SELECT * FROM product";
    if (!empty($filter_query)) {
        $base_query .= " WHERE $filter_query";
    }
    if (!empty($order_by)) {
        $base_query .= " $order_by";
    } else {
        $base_query .= " ORDER BY id ASC";
    }
    $base_query .= " LIMIT $offset, $driversPerPage";
    $result = mysqli_query($con, $base_query);
    if ($result) {
        return $result;
    } else {
        echo "Error executing paginated candidate query: " . mysqli_error($con);
        return null;
    }
}

$driversPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $driversPerPage;

$search_value = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : '';
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;

$from_date = isset($_GET['fromdate']) ? $_GET['fromdate'] : '';
$to_date = isset($_GET['todate']) ? $_GET['todate'] : '';

function convertDateFormat($date) {
    $dateArray = explode('/', $date);
    if (count($dateArray) == 3) {
        return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
    }
    return null;
}

$from_date_db = convertDateFormat($from_date);
$to_date_db = convertDateFormat($to_date);

$order_by = "";
if (!empty($sort)) {
    if ($sort === 'ascending') {
        $order_by = "ORDER BY product_name ASC";
    } elseif ($sort === 'descending') {
        $order_by = "ORDER BY product_name DESC";
    } elseif ($sort === 'recently_added') {
        $order_by = "ORDER BY id DESC";
    }
}

if (!empty($categorization)) {
    $filter_query = "(category = '$categorization')";
}

if ($search_value) {
    if (empty($filter_query)) {
        $filter_query = "(product_name LIKE '%$search_value%')";
    } else {
        $filter_query .= " AND (product_name LIKE '%$search_value%')";
    }
}

if (!empty($from_date) && !empty($to_date)) {
    if (empty($filter_query)) {
        $filter_query = "(created_at BETWEEN '$from_date_db' AND '$to_date_db')";
    } else {
        $filter_query .= " AND (created_at BETWEEN '$from_date_db' AND '$to_date_db')";
    }
}

$total = getTotalDriversCount($filter_query);
$employee = getDriversPaginated($offset, $driversPerPage, $filter_query, $order_by);

$filters = [
    'fromdate' => isset($_GET['fromdate']) ? $_GET['fromdate'] : '',
    'todate' => isset($_GET['todate']) ? $_GET['todate'] : ''
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        :root {
            --primary-color: #4A90E2;
            --primary-hover: #3A7BC8;
            --secondary-color: #6C757D;
            --success-color: #28A745;
            --warning-color: #FBBF24;
            --danger-color: #DC3545;
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

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
            font-size: 0.9375rem;
        }
        
        .main-container {
            padding: 1.5rem;
        }

        .page-header {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .page-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .title-left h1 {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .title-left h1 i {
            color: var(--primary-color);
        }

        .product-count {
            background: #e2f0ff;
            color: var(--primary-color);
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .title-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .btn-primary-custom, .btn-reset {
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
        }
        .btn-primary-custom:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .btn-reset {
            background: var(--white);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .btn-reset:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            background: var(--light-bg);
            color: var(--text-primary);
        }

        .filter-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group { position: relative; }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.875rem;
        }
        .form-control-modern {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: white;
            color: var(--text-primary);
        }
        .form-control-modern:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
        }

        .search-input-wrapper { position: relative; }
        .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); z-index: 2; }
        .search-input { padding-left: 2.5rem !important; }

        .data-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--white);
        }
        .table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .table-modern {
            margin: 0;
            width: 100%;
            border-collapse: collapse;
        }
        .table-modern thead th {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }
        .table-modern tbody td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        .table-modern tbody tr:hover {
            background: var(--light-bg);
            transition: all 0.2s ease;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: var(--radius-sm);
        }
        
        .price-info s {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-right: 0.5rem;
        }

        .action-dropdown {
            position: relative;
            display: inline-block;
        }
        .action-btn {
            background: none;
            border: none;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .action-btn:hover {
            background: rgba(74, 144, 226, 0.1);
            color: var(--primary-color);
        }

        .dropdown-menu-custom {
            position: absolute;
            top: 100%;
            right: 0;
            min-width: 180px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            padding: 0.5rem 0;
            z-index: 1000;
            display: none;
        }
        .dropdown-menu-custom.show { display: block; }

        .dropdown-item-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            background: none;
            border: none;
            text-align: left;
            transition: all 0.2s ease;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .dropdown-item-custom:hover {
            background: rgba(74, 144, 226, 0.1);
            color: var(--primary-color);
        }
        .dropdown-item-custom i {
            width: 16px;
        }
        .dropdown-item-custom.danger:hover {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            background: #f8fafc;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .entries-info { color: var(--text-secondary); font-size: 0.9rem; }
        .per-page-selector { display: flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); font-size: 0.9rem; }
        .per-page-select { border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 0.25rem 0.5rem; background: white; color: var(--text-primary); font-size: 0.9rem; }
        
        .pagination-modern {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }
        .page-link-modern {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            background: white;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
        }
        .page-link-modern:hover { background: var(--primary-color); color: white; transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .page-link-modern.active { background: var(--primary-color); color: white; box-shadow: var(--shadow-md); }

        .fade-in { animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        @media (max-width: 768px) {
            .main-container { padding: 1rem; }
            .filter-row { grid-template-columns: 1fr; gap: 1rem; }
            .page-title { flex-direction: column; align-items: flex-start; }
            .title-actions { margin-top: 0.5rem; }
            .table-modern { font-size: 0.85rem; }
            .table-modern thead th, .table-modern tbody td { padding: 0.75rem 0.75rem; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header fade-in">
            <div class="page-title">
                <div class="title-left">
                    <h1>
                        <i class="fas fa-box"></i>
                        Products
                        <span class="product-count"><?= $total ?> Products</span>
                    </h1>
                </div>
                <div class="title-actions">
                    <button class="btn-reset reset-btn">
                        <i class="fas fa-redo-alt"></i>Reset Filters
                    </button>
                    <a href="addProduct.php" class="btn-primary-custom">
                        <i class="fas fa-plus"></i>Add Product
                    </a>
                </div>
            </div>
        </div>

        <div class="filter-card fade-in">
            <div class="filter-row">
                <div class="form-group">
                    <label class="form-label">Search Product</label>
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text"
                               class="form-control-modern search-input"
                               placeholder="Search by name"
                               name="search"
                               value="<?= $search_value ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Range</label>
                    <input type="text" id="daterange" class="form-control-modern" placeholder="Select date range" />
                </div>
            </div>
        </div>

        <div class="data-card fade-in">
            <div class="table-header">
                <h3 class="table-title">Product List</h3>
            </div>

            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>SI.NO</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($employee) > 0) {
                            $serialNumber = $offset + 1;
                            while ($item = mysqli_fetch_assoc($employee)) {
                                ?>
                                <tr>
                                    <td><?= $serialNumber ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($item['product_image']); ?>" class="product-image" alt="Product Image">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['product_name']); ?></strong><br>
                                        <small class="text-secondary"><?= htmlspecialchars($item['capacity']); ?></small>
                                    </td>
                                    <td class="price-info">
                                        <s class="d-block"><?= htmlspecialchars($item['original_price']); ?></s>
                                        <span><?= htmlspecialchars($item['selling_price']); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button class="action-btn" type="button" onclick="toggleDropdown(<?= $item['id'] ?>)">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div id="dropdown-<?= $item['id'] ?>" class="dropdown-menu-custom">
                                                <a class="dropdown-item-custom" href="editProduct.php?id=<?= $item['id'] ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a class="dropdown-item-custom danger" href="deleteProduct.php?id=<?= $item['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $serialNumber++;
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted py-4'>
                                    <i class='fas fa-box-open fa-2x mb-3 d-block'></i>
                                    <h5>No products found.</h5>
                                    <p>Try adjusting your search or filters.</p>
                                </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="per-page-selector">
                    <span>Show</span>
                    <select name="per_page" class="per-page-select selectpage">
                        <option value="10" <?= $driversPerPage == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= $driversPerPage == 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $driversPerPage == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $driversPerPage == 100 ? 'selected' : '' ?>>100</option>
                        <option value="200" <?= $driversPerPage == 200 ? 'selected' : '' ?>>200</option>
                    </select>
                    <span>entries</span>
                </div>

                <?php if ($total > 0) { ?>
                <div class="pagination-modern">
                    <?php
                    $totalPages = ceil($total / $driversPerPage);
                    $range = 1;
                    $start = max(1, $page - $range);
                    $end = min($totalPages, $page + $range);

                    if ($page > 1) { ?>
                        <a class="page-link-modern" href="allProduct.php?page=<?= $page - 1; ?>&per_page=<?=$driversPerPage?>&<?= http_build_query($filters); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php } ?>

                    <?php for ($i = $start; $i <= $end; $i++) { ?>
                        <a class="page-link-modern <?= ($i == $page) ? 'active' : ''; ?>"
                           href="allProduct.php?page=<?= $i; ?>&per_page=<?=$driversPerPage?>&<?= http_build_query($filters); ?>">
                            <?= $i; ?>
                        </a>
                    <?php } ?>

                    <?php if ($page < $totalPages) { ?>
                        <a class="page-link-modern" href="allProduct.php?page=<?= $page + 1; ?>&per_page=<?=$driversPerPage?>&<?= http_build_query($filters); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        function toggleDropdown(id) {
            document.querySelectorAll('.dropdown-menu-custom').forEach(dropdown => {
                if (dropdown.id !== `dropdown-${id}`) {
                    dropdown.classList.remove('show');
                }
            });
            const dropdown = document.getElementById(`dropdown-${id}`);
            dropdown.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.action-dropdown')) {
                document.querySelectorAll('.dropdown-menu-custom').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });

        document.querySelectorAll('.dropdown-item-custom[data-bs-toggle="modal"]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-menu-custom').forEach(d => d.classList.remove('show'));
            });
        });

        $(function() {
            $('#daterange').daterangepicker({
                opens: 'left',
                locale: { format: 'DD/MM/YYYY' },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month')
            }, function(start, end) {
                const params = new URLSearchParams(window.location.search);
                params.set('fromdate', start.format('DD/MM/YYYY'));
                params.set('todate', end.format('DD/MM/YYYY'));
                window.location.search = params.toString();
            });
        });

        document.querySelector('.reset-btn').addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });

        function setupAutoSubmit(selector, paramName) {
            document.querySelectorAll(selector).forEach(item => {
                item.addEventListener('change', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set(paramName, this.value);
                    window.location.search = urlParams.toString();
                });
            });
        }

        setupAutoSubmit('.search-input', 'search');
        setupAutoSubmit('.selectpage', 'per_page');

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.filter-card, .data-card, .page-header')
                .forEach(card => card.classList.add('fade-in'));
        });
    </script>
</body>
</html>