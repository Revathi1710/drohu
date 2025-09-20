<?php
session_start();
include('connection.php');

header('Content-Type: application/json');

// Prevent extra output
ob_clean();

$orderId = (int)($_POST['order_id'] ?? 0);
$deliveryPersonId = ($_POST['delivery_person_id'] !== '' ? (int)$_POST['delivery_person_id'] : null);

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$stmt = $con->prepare("UPDATE orders SET delivery_person_id = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB prepare failed']);
    exit;
}

$stmt->bind_param("ii", $deliveryPersonId, $orderId);

if ($stmt->execute()) {
    // Fetch delivery person name for confirmation
    $dpName = '';
    if ($deliveryPersonId) {
        $dpStmt = $con->prepare("SELECT deliveryperson_name FROM deliveryPerson WHERE id = ?");
        $dpStmt->bind_param("i", $deliveryPersonId);
        $dpStmt->execute();
        $dpStmt->bind_result($dpName);
        $dpStmt->fetch();
        $dpStmt->close();
    }

    echo json_encode([
        'success' => true,
        'delivery_person_id' => $deliveryPersonId,
        'delivery_person_name' => $dpName
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}

$stmt->close();
exit;
