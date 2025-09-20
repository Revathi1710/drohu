<?php 
session_start();
header('Content-Type: application/json');
include('connection.php');
ini_set('display_errors', 1);
$input = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($input['order_id'] ?? 0);
$action  = strtolower(trim($input['action'] ?? ''));
$status  = strtolower(trim($input['status'] ?? ''));

// Validate order
$stmt = $con->prepare("SELECT status FROM orders WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
  echo json_encode(['success'=>false,'message'=>'Order not found']); exit;
}

$current = strtolower($row['status']);

// Block updates if already Delivered/Cancelled
if (in_array($current, ['delivered','cancelled'])) {
  echo json_encode(['success'=>false,'message'=>'Order already in terminal state']); exit;
}

// Decide new status
$newStatus = '';
if ($action === 'complete') {
  $newStatus = 'delivered';
} elseif ($action === 'cancel') {
  $newStatus = 'cancelled';
} elseif ($action === 'set' && $status) {
  $allowed = ['pending','paid','processing','shipped']; // allowed non-terminal statuses
  if (!in_array($status, $allowed)) {
    echo json_encode(['success'=>false,'message'=>'Invalid status']); exit;
  }
  $newStatus = $status;
} else {
  echo json_encode(['success'=>false,'message'=>'Invalid action']); exit;
}

// Update DB
$up = $con->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
$up->bind_param("si", $newStatus, $orderId);
$ok = $up->execute();
$up->close();

if (!$ok) { echo json_encode(['success'=>false,'message'=>'Update failed']); exit; }

// Status map for UI
$map = [
  'pending'   => ['label'=>'Pending','class'=>'st-pending'],
  'paid'      => ['label'=>'Paid','class'=>'st-paid'],
  'processing'=> ['label'=>'Processing','class'=>'st-processing'],
  'shipped'   => ['label'=>'Shipped','class'=>'st-shipped'],
  'delivered' => ['label'=>'Delivered','class'=>'st-delivered'],
  'cancelled' => ['label'=>'Cancelled','class'=>'st-cancelled'],
];

echo json_encode([
  'success' => true,
  'status'  => $newStatus,
  'status_label' => $map[$newStatus]['label'],
  'status_class' => $map[$newStatus]['class'],
]);
