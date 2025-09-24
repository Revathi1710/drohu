<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    echo json_encode(['ok' => false, 'error' => 'not_logged_in']);
    exit;
}

// Get all broadcast IDs
$broadcasts = $con->query("SELECT id FROM broadcasts");
if (!$broadcasts) {
    echo json_encode(['ok' => false, 'error' => 'db_error']);
    exit;
}
$broadcastIds = [];
while ($row = $broadcasts->fetch_assoc()) {
    $broadcastIds[] = (int)$row['id'];
}

if (empty($broadcastIds)) {
    echo json_encode(['ok' => true, 'message' => 'no_broadcasts']);
    exit;
}

// Prepare a dynamic query to insert read status for all broadcasts, explicitly marking them as read (1)
$values = [];
$params = [];
$types = '';
$isRead = 1;

foreach ($broadcastIds as $bId) {
    $values[] = "(?, ?, ?)";
    $params[] = $userId;
    $params[] = $bId;
    $params[] = $isRead;
    $types .= 'iii';
}

$sql = "INSERT INTO user_broadcasts_status (user_id, broadcast_id, is_read) VALUES " . implode(',', $values) . " ON DUPLICATE KEY UPDATE is_read = VALUES(is_read)";
$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true]);