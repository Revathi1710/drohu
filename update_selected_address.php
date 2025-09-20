<?php
// update_selected_address.php
session_start();
header('Content-Type: application/json');
require 'connection.php';

try {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    $addressId = (int)($json['address_id'] ?? 0);
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($userId <= 0 || $addressId <= 0) {
        echo json_encode(['success' => false]); exit;
    }

    $stmt = $con->prepare("SELECT id, address_label, door_no, street_address, city, state, pincode FROM address_details WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $addressId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$row = $res->fetch_assoc()) {
        echo json_encode(['success' => false]); exit;
    }

    $_SESSION['selected_address_id'] = (int)$row['id'];

    $parts = array_filter([
        $row['door_no'] ?? '',
        $row['street_address'] ?? '',
        $row['city'] ?? '',
        $row['state'] ?? '',
        $row['pincode'] ?? '',
    ]);
    $short = implode(', ', $parts);

    // Optional pricing echo back (for button label)
    $total_price = 0.0;
    $delivery_fee = 40.0;
    if ($userId > 0) {
        $sql = "SELECT ac.quantity, p.selling_price
                FROM addcart ac
                JOIN product p ON ac.prod_id = p.id
                WHERE ac.user_id = ?";
        $s = $con->prepare($sql);
        $s->bind_param("i", $userId);
        $s->execute();
        $r = $s->get_result();
        while ($it = $r->fetch_assoc()) {
            $total_price += ((float)$it['selling_price']) * ((int)$it['quantity']);
        }
    }
    $to_pay = $total_price + ($total_price > 0 ? $delivery_fee : 0);

    echo json_encode([
        'success' => true,
        'selected' => true,
        'address_label' => $row['address_label'] ?: 'Home',
        'short_address' => $short,
        'has_items' => $total_price > 0,
        'to_pay' => number_format($to_pay, 2),
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false]);
}