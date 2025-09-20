<?php
session_start();
header('Content-Type: application/json');
include('connection.php');

if (!isset($_SESSION['mobile_number'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$count = 0;

if ($userId > 0) {
    $stmt = $con->prepare("SELECT COALESCE(SUM(quantity), 0) AS cnt FROM addcart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($cnt);
    if ($stmt->fetch()) {
        $count = (int)$cnt;
    }
    $stmt->close();
}

echo json_encode(['count' => $count]);
