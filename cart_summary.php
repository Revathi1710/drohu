<?php
session_start();
include('connection.php');

header('Content-Type: application/json');

$response = ['total_items' => 0, 'total_price' => 0.00];
$userId = (int)($_SESSION['user_id'] ?? 0);

if ($userId > 0 && isset($con)) {
    $stmt = $con->prepare("SELECT SUM(quantity) as total_items, SUM(quantity * price) as total_price FROM addcart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $response['total_items'] = (int)($data['total_items'] ?? 0);
    $response['total_price'] = (float)($data['total_price'] ?? 0.00);

    $stmt->close();
}

echo json_encode($response);
exit();
?>