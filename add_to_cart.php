<?php 
session_start();
header('Content-Type: application/json');
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['name']) || !isset($data['price']) || !isset($data['image'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product data.']);
    exit;
}

$productId    = (int) $data['id'];
$productName  = $data['name'];
$productPrice = (float) $data['price'];
$productImage = $data['image'];
$userId       = $_SESSION['user_id'] ?? 0;


if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId]['quantity']++;
    $message = 'Product quantity updated in cart.';

    // Update DB cart quantity
    $sql = "UPDATE addcart 
            SET price = ?, quantity = quantity + 1
            WHERE prod_id = ? AND user_id = ?";
    $stmt = $con->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("dii", $productPrice, $productId, $userId);
        $stmt->execute();
    }
} else {
    $_SESSION['cart'][$productId] = [
        'id'       => $productId,
        'name'     => $productName,
        'price'    => $productPrice,
        'image'    => $productImage,
        'quantity' => 1
    ];
    $message = 'Product added to cart.';

    // Insert into DB
    $sql = "INSERT INTO addcart (prod_id, price, user_id, quantity) VALUES (?, ?, ?, 1)";
    $stmt = $con->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("idi", $productId, $productPrice, $userId);
        $stmt->execute();
    }
}

// Calculate total quantity
$total_quantity = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_quantity += $item['quantity'];
}

$response = [
    'success'    => true,
    'message'    => $message,
    'cart_count' => $total_quantity,
    'cart_items' => $_SESSION['cart']
];

echo json_encode($response);
exit;
