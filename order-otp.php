<?php
// order-otp.php
session_start();
require_once __DIR__ . '/connection.php';

header('Content-Type: application/json');

$userId  = (int)($_SESSION['user_id'] ?? 0);
$orderId = (int)($_GET['order_id'] ?? 0);

if ($userId <= 0) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'unauthorized']);
  exit;
}
if ($orderId <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'invalid_order']);
  exit;
}

$sql = "SELECT id, user_id, status, delivery_otp, otp_verified
        FROM orders
        WHERE id = ? AND user_id = ?
        LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
  http_response_code(404);
  echo json_encode(['ok' => false, 'error' => 'not_found']);
  exit;
}

echo json_encode([
  'ok'           => true,
  'order_id'     => (int)$row['id'],
  'status'       => (string)$row['status'],
  'otp'          => isset($row['delivery_otp']) ? (string)$row['delivery_otp'] : '',
  'otp_verified' => (int)($row['otp_verified'] ?? 0),
]);