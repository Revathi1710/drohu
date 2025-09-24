<?php
session_start();
require_once __DIR__ . '/connection.php';

if (empty($_SESSION['deliveryperson_id'])) {
  echo "0";
  exit;
}

$deliverypersonId = (int)$_SESSION['deliveryperson_id'];

// Fetch one new order not notified yet
$stmt = $con->prepare("
  SELECT id FROM orders 
  WHERE delivery_person_id = ? 
    AND is_notified = 0 
  ORDER BY id DESC LIMIT 1
");
$stmt->bind_param("i", $deliverypersonId);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
  $orderId = $row['id'];

  // Mark as notified
  $up = $con->prepare("UPDATE orders SET is_notified=1 WHERE id=?");
  $up->bind_param("i", $orderId);
  $up->execute();
  $up->close();

  echo $orderId; // return order ID
} else {
  echo "0";
}
$stmt->close();
?>
