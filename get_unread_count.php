<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');

$userId = (int)($_SESSION['user_id'] ?? 0);

if ($userId === 0) {
    echo json_encode(['unread_count' => 0]);
    exit;
}

$unreadCount = 0;
// Count broadcasts that do not have a corresponding "read" entry for the user
$q = "SELECT COUNT(b.id) AS unread_count FROM broadcasts b
      LEFT JOIN user_broadcasts_status s ON b.id = s.broadcast_id AND s.user_id = ?
      WHERE s.broadcast_id IS NULL";
$stmt = $con->prepare($q);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $unreadCount = (int)$row['unread_count'];
}
$stmt->close();

echo json_encode(['unread_count' => $unreadCount]);