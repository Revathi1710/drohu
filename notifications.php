<?php
session_start();
require_once __DIR__ . '/connection.php';
// Include header if it exists and defines a header. Otherwise, provide a fallback.
if (file_exists(__DIR__ . '/header.php')) {
    include __DIR__ . '/header.php';
} else {
    echo '<style>.app-header { display: none; }</style>';
}
ini_set('display_errors', 1);

$userId = (int)($_SESSION['user_id'] ?? 0);
$notifications = [];

if ($userId > 0) {
    // This query fetches all broadcasts and checks if the current user has read them
    $q = "SELECT b.id, b.message, b.timestamp, s.is_read FROM broadcasts b
          LEFT JOIN user_broadcasts_status s ON b.id = s.broadcast_id AND s.user_id = ?
          ORDER BY b.timestamp DESC";
    
    $stmt = $con->prepare($q);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'message' => htmlspecialchars($row['message']),
            'timestamp' => new DateTime($row['timestamp']),
            'is_read' => $row['is_read'] === 1
        ];
    }
    $stmt->close();
}

// Function to format time for a human-readable display
function time_ago($datetime) {
    $now = new DateTime();
    $diff = $now->getTimestamp() - $datetime->getTimestamp();
    
    // Fix: prevent negative diffs (DB clock ahead of PHP clock)
    if ($diff < 0) {
        $diff = 0;
    }

    $day_diff = floor($diff / (60 * 60 * 24));

    if ($day_diff === 0) {
        if ($diff < 60) return "Just now";
        if ($diff < 3600) return floor($diff / 60) . " min ago";
        return floor($diff / 3600) . " hr ago";
    }
    if ($day_diff === 1) return "Yesterday";
    if ($day_diff < 7) return $day_diff . " days ago";
    return $datetime->format('d M');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg-light: #F8F9FA;
            --card-bg: #FFFFFF;
            --unread-bg: #F0F9FF; /* Light blue for unread notifications */
            --border-color: #E2E8F0;
            --text-primary: #1A202C;
            --text-secondary: #6B7280;
            --blue: #1E90FF;
            --radius-md: 14px;
        }  :root {
        --primary-blue: #1E90FF;
        --secondary-blue: #E8F4FF;
        --light-grey: #eef2f7;
        --text-color: #0b1020;
        --muted-text: #6b7280;
        --border-color: #eef2f7;
        --success-green: #28a745;
        --danger-red: #dc3545;
    }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
        }
        .app-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background:linear-gradient(135deg, var(--primary-blue, #1E90FF), #0056b3);
            box-shadow: var(--header-shadow);
            color: var(--text-color-light);
            padding: 16px 0;
        }
        .main-container {
            padding: 1.5rem;
            max-width: 800px;
            margin: auto;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .page-header h4 {
            font-weight: 700;
            margin: 0;
            font-size: 1.5rem;
        }
        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .notification-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }
        .notification-card.unread {
            background-color: var(--unread-bg);
            border-left: 4px solid var(--blue);
        }
        .unread-dot {
            width: 10px;
            height: 10px;
            background-color: var(--blue);
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 4px;
        }
        .notification-content {
            flex-grow: 1;
        }
        .notification-content h6 {
            margin: 0;
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary);
        }
        .notification-card.unread h6 {
            font-weight: 700;
        }
        .notification-content p {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin: 0.5rem 0 0;
        }
        .notification-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }
        .no-notifications {
            text-align: center;
            color: var(--text-secondary);
            margin-top: 2rem;
            font-size: 1rem;
        } .bottom-nav { 
        position: fixed; 
        left: 0; 
        right: 0; 
        bottom: 0; 
        background: #fff; 
        border-top: 1px solid var(--border-color); 
        display: grid; 
        grid-template-columns: repeat(4, 1fr); 
        padding: 8px 0; 
        z-index: 50; 
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
    }
    .bottom-nav button { 
        border: none; 
        background: transparent; 
        color: var(--muted-text); 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        gap: 4px; 
        font-size: 11px; 
        transition: color 0.2s ease;
    }
    .bottom-nav .active { 
        color: var(--primary-blue); 
    }
.notification-badge {
    position: absolute;
    top: 1px;
    right: 32px;
    background-color: var(--danger-red);
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 4px;
    border-radius: 50%;
    line-height: 1;
    display: none;
}
    </style>
</head>
<body>

    <div class="main-container">
        <div class="page-header">
            <h4>Notifications</h4>
            <div class="text-muted small">
                <?= count($notifications) ?> messages
            </div>
        </div>

        <div class="notification-list">
            <?php if (empty($notifications)): ?>
                <div class="no-notifications">
                    <p>No new notifications to display.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="notification-card <?= $n['is_read'] ? '' : 'unread' ?>">
                        <?php if (!$n['is_read']): ?>
                            <div class="unread-dot"></div>
                        <?php endif; ?>
                        <div class="notification-content">
                            <h6>New Broadcast</h6>
                            <p><?= $n['message'] ?></p>
                            <div class="notification-meta">
                                <?= time_ago($n['timestamp']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<nav class="bottom-nav">
    <button onclick="location.href='products.php'"><i class="fa-solid fa-house"></i><span>Home</span></button>
    <button onclick="location.href='orders.php'"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></button>
    <button onclick="location.href='profile.php'"><i class="fa-solid fa-user"></i><span>Profile</span></button>  <button  class="active"id="bellButton"><i class="fa-solid fa-bell"></i><span>Nodification</span><span class="notification-badge" id="unreadCountBadge">0</span></button>
</nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    async function markAllAsRead() {
        try {
            const res = await fetch('mark_all_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'read_all' })
            });
            const data = await res.json();
            if (data.ok) {
                console.log('Notifications marked as read.');
            }
        } catch (e) {
            console.error('Failed to mark notifications as read:', e);
        }
    }
    window.addEventListener('load', markAllAsRead);
    </script>
</body>
</html>