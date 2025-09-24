<?php
// process_order.php
session_start();
include('connection.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}

$paymentMethod = $_GET['method'] ?? 'cod';
$razorpayPaymentId = $_GET['payment_id'] ?? null;
$selectedAddressId = (int)($_SESSION['selected_address_id'] ?? 0);
$userId = (int)($_SESSION['user_id'] ?? 0);

try {
    // 1. Validate mandatory data
    if ($userId === 0) {
        throw new Exception("User not logged in.");
    }

    $stmt_address = $con->prepare("SELECT id FROM address_details WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt_address->bind_param("ii", $selectedAddressId, $userId);
    $stmt_address->execute();
    $res_address = $stmt_address->get_result();
    if ($res_address->num_rows === 0) {
        throw new Exception("Missing or invalid delivery address.");
    }
    $addressIdForOrder = $selectedAddressId;
    $stmt_address->close();

    $stmt_cart = $con->prepare("SELECT quantity, prod_id, price FROM addcart WHERE user_id = ?");
    $stmt_cart->bind_param("i", $userId);
    $stmt_cart->execute();
    $res_cart = $stmt_cart->get_result();
    $cart_items_db = $res_cart->fetch_all(MYSQLI_ASSOC);
    $stmt_cart->close();
    
    if (empty($cart_items_db)) {
        throw new Exception("Empty cart.");
    }
    
    $total_amount = 0.0;
    foreach ($cart_items_db as $item) {
        $total_amount += (float)$item['price'] * (int)$item['quantity'];
    }

    // 2. Start Transaction & Insert Order
    $con->begin_transaction();
    $orderStatus = ($paymentMethod === 'cod') ? 'pending' : 'paid';

    $sql_insert_order = "INSERT INTO `orders` (user_id, address_id, total_amount, payment_method, payment_id, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_order = $con->prepare($sql_insert_order);
    $stmt_order->bind_param("iidsss", $userId, $addressIdForOrder, $total_amount, $paymentMethod, $razorpayPaymentId, $orderStatus);
    $stmt_order->execute();
    $orderId = $con->insert_id;
    $stmt_order->close();

    // 3. Insert Order Items
    $sql_insert_item = "INSERT INTO `order_items` (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)";
    $stmt_item = $con->prepare($sql_insert_item);
    foreach ($cart_items_db as $item) {
        $productId = (int)$item['prod_id'];
        $productName = 'Product Name Placeholder'; // Fetch from products table if needed
        $price = (float)$item['price'];
        $qty = (int)$item['quantity'];
        $stmt_item->bind_param("iisdi", $orderId, $productId, $productName, $price, $qty);
        $stmt_item->execute();
    }
    $stmt_item->close();

    // 4. Commit and Redirect to Success
    $con->commit();

    $stmt_del = $con->prepare("DELETE FROM addcart WHERE user_id = ?");
    $stmt_del->bind_param("i", $userId);
    $stmt_del->execute();
    $stmt_del->close();

    header("Location: order_success.php?order_id=" . $orderId);
    exit();

} catch (Exception $e) {
    // 5. Rollback and Redirect to Failed Page
    $con->rollback();
    error_log("Order processing failed for user {$userId}: " . $e->getMessage());
    $orderId = $orderId ?? 0;
    $reason = urlencode($e->getMessage());
    header("Location: order_failed.php?order_id={$orderId}&reason={$reason}");
    exit();
} finally {
    if (isset($con)) {
        $con->close();
    }
}
?>