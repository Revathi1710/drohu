<?php
session_start();
include('connection.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Require login
if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}


$paymentMethod = $_GET['method'] ?? 'cod';
$razorpayPaymentId = $_GET['payment_id'] ?? null;

// Selected address id saved during cart selection
$selectedAddressId = (int)($_SESSION['selected_address_id'] ?? 0);

// Sanitize simple strings
$paymentMethod = $con->real_escape_string($paymentMethod);
$razorpayPaymentId = $razorpayPaymentId !== null ? $con->real_escape_string($razorpayPaymentId) : null;

// Verify selected address belongs to the user; otherwise null it
$userId = (int)($_SESSION['user_id'] ?? 0);
$addressIdForOrder = null;
if ($selectedAddressId > 0 && $userId > 0) {
    $stmt = $con->prepare("SELECT id FROM address_details WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $selectedAddressId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $addressIdForOrder = $selectedAddressId;
    }
    $stmt->close();
}

// Calculate total from session cart
$total_amount = 0.0;
foreach ($_SESSION['cart'] as $item) {
    $qty = (int)$item['quantity'];
    $price = (float)$item['price'];
    $total_amount += $price * $qty;
}

$orderStatus = ($paymentMethod === 'cod') ? 'pending' : 'paid';

try {
    $con->begin_transaction();

    // Insert into orders with address_id
    $sql_insert_order = "INSERT INTO `orders`
        (user_id, address_id, total_amount, payment_method, payment_id, status)
        VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_order = $con->prepare($sql_insert_order);
    // user_id (i), address_id (i or null), total_amount (d), payment_method (s), payment_id (s or null), status (s)
    $stmt_order->bind_param(
        "iidsss",
        $userId,
        $addressIdForOrder,
        $total_amount,
        $paymentMethod,
        $razorpayPaymentId,
        $orderStatus
    );
    $stmt_order->execute();
    $orderId = $con->insert_id;
    $stmt_order->close();

    // Insert order items
    $sql_insert_item = "INSERT INTO `order_items`
        (order_id, product_id, product_name, price, quantity)
        VALUES (?, ?, ?, ?, ?)";
    $stmt_item = $con->prepare($sql_insert_item);

    foreach ($_SESSION['cart'] as $item) {
        $productId = (int)$item['id'];
        $productName = $item['name'];
        $price = (float)$item['price'];
        $qty = (int)$item['quantity'];
        $stmt_item->bind_param("iisdi", $orderId, $productId, $productName, $price, $qty);
        $stmt_item->execute();
    }
    $stmt_item->close();

    $con->commit();

    // Clear session cart
    unset($_SESSION['cart']);

    // Also clear DB cart for this user (table name: addcart)
    if ($userId > 0) {
        $stmt_del = $con->prepare("DELETE FROM addcart WHERE user_id = ?");
        $stmt_del->bind_param("i", $userId);
        $stmt_del->execute();
        $stmt_del->close();
    }

    header("Location: order_success.php?order_id=" . $orderId);
    exit();
} catch (Exception $e) {
    $con->rollback();
    error_log("Order processing failed: " . $e->getMessage());
    header("Location: order_failed.php");
    exit();
} finally {
    $con->close();
}