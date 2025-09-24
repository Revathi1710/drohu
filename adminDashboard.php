<?php
include('connection.php');
ini_set('display_errors', 1);

// Fetch stats
$totalUsers = $con->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalOrders = $con->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$completedDeliveries = $con->query("SELECT COUNT(*) as total FROM orders WHERE status='Delivered'")->fetch_assoc()['total'];
$pendingOrders = $con->query("SELECT COUNT(*) as total FROM orders WHERE status='Pending'")->fetch_assoc()['total'];

// Recent orders
$recentOrders = $con->query("
    SELECT o.id, u.name, o.total_amount, o.status, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Orders per day (for chart)
$orderChartData = $con->query("
    SELECT DATE(created_at) as order_date, COUNT(*) as total_orders
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY order_date ASC
")->fetch_all(MYSQLI_ASSOC);

$weeklyOrders = [];
$orderCounts = [];
// Get the last 7 days including today, even if there are no orders
$startDate = new DateTime('-7 days');
for ($i = 0; $i < 7; $i++) {
    $date = $startDate->format('Y-m-d');
    $weeklyOrders[] = $startDate->format('D');
    $orderCounts[] = 0;
    $startDate->modify('+1 day');
}
foreach($orderChartData as $row) {
    $dayIndex = date('N', strtotime($row['order_date'])) - 1;
    $orderCounts[$dayIndex] = $row['total_orders'];
}

// Revenue by area (for chart)
$revenueData = $con->query("
    SELECT ad.area, SUM(o.total_amount) AS revenue
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN address_details ad ON o.address_id = ad.id
    GROUP BY ad.area
    ORDER BY revenue DESC
")->fetch_all(MYSQLI_ASSOC);

$areas = [];
$revenues = [];
foreach($revenueData as $row){
    $areas[] = htmlspecialchars($row['area']);
    $revenues[] = $row['revenue'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f8;
            color: #333;
        }
        .main-layout {
            display: flex;
            min-height: 100vh;
        }
       
        .main-content {
            flex-grow: 1;
            padding: 2rem;
        }
        .card {
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .chart-container {
            height: 320px;
        }
    </style>
</head>
<body>

<div class="main-layout">
    <?php include('sidebar.php'); ?>

    <main class="main-content">

        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
          
        </header>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card p-6 flex items-center space-x-4">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m0 0l-4-4m4 4l4-4M12 18v-3m0-3V9m0-3V6m0 0a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                    </svg>
                </div>
             <div style="margin:0px; margin-top:10px" onclick="window.location.href='allCustomer.php'" style="cursor:pointer;">
    <h3 class="text-gray-500 text-sm font-medium">Total Users</h3>
    <p class="text-3xl font-semibold text-gray-900 text-center"><?= $totalUsers ?></p>
</div>

            </div>

            <div class="card p-6 flex items-center space-x-4">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
               <div style="margin:0px; margin-top:10px" onclick="window.location.href='allOrder.php'">
                    <h3 class="text-gray-500 text-sm font-medium">Total Orders</h3>
                    <p class="text-3xl font-semibold text-gray-900 text-center"><?= $totalOrders ?></p>
                </div>
            </div>

            <div class="card p-6 flex items-center space-x-4">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div style="margin:0px; margin-top:10px"  onclick="window.location.href='deliveredOrder.php'">
                    <h3 class="text-gray-500 text-sm font-medium">Delivered Order</h3>
                    <p class="text-3xl font-semibold text-gray-900 text-center"><?= $completedDeliveries ?></p>
                </div>
            </div>

            <div class="card p-6 flex items-center space-x-4">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div style="margin:0px; margin-top:10px" onclick="window.location.href='pendingOrder.php'">
                    <h3 class="text-gray-500 text-sm font-medium">Pending Orders</h3>
                    <p class="text-3xl font-semibold text-gray-900 text-center"><?= $pendingOrders ?></p>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="card p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Weekly Order Trends</h2>
                <div class="chart-container">
                    <canvas id="weeklyOrdersChart"></canvas>
                </div>
            </div>
            <div class="card p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Revenue by Area</h2>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Orders</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="px-5 py-3 border-b-2 border-gray-200">Order ID</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200">Customer</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200">Amount</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200">Status</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200">Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentOrders as $order): ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 text-sm">#OD<?= $order['id'] ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 text-sm"><?= htmlspecialchars($order['name']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 text-sm">$<?= number_format($order['total_amount'], 2) ?></td>
                            <?php
                            $statusColor = match($order['status']) {
                                'Delivered' => 'green',
                                'Pending' => 'yellow',
                                'Processing' => 'blue',
                                default => 'gray'
                            };
                            ?>
                            <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                <span class="relative inline-block px-3 py-1 font-semibold text-<?= $statusColor ?>-900 leading-tight">
                                    <span aria-hidden class="absolute inset-0 bg-<?= $statusColor ?>-200 opacity-50 rounded-full"></span>
                                    <span class="relative"><?= $order['status'] ?></span>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 text-sm"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<script>
    // Zoho-like color palette for charts
    const zohoPalette = {
        primary: '#4F46E5', // Indigo
        secondary: '#6366F1', // Light Indigo
        success: '#10B981', // Green
        warning: '#F59E0B', // Yellow
        info: '#3B82F6', // Blue
    };

    const weeklyOrdersChart = new Chart(document.getElementById('weeklyOrdersChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($weeklyOrders) ?>,
            datasets: [{
                label: 'Orders',
                data: <?= json_encode($orderCounts) ?>,
                borderColor: zohoPalette.primary,
                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(200, 200, 200, 0.2)'
                    },
                    ticks: { color: '#666' }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: { color: '#666' }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#fff',
                    borderWidth: 1,
                }
            }
        }
    });

    const revenueChart = new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($areas) ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?= json_encode($revenues) ?>,
                backgroundColor: zohoPalette.primary,
                hoverBackgroundColor: zohoPalette.secondary,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(200, 200, 200, 0.2)'
                    },
                    ticks: { color: '#666' }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: { color: '#666' }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#fff',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>