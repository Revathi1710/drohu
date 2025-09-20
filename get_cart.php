<?php
// get_cart.php
session_start();
header('Content-Type: application/json');

// Check if the cart session variable is set and not empty.
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    echo json_encode(['success' => true, 'cart_items' => $_SESSION['cart']]);
} else {
    // Return an empty cart.
    echo json_encode(['success' => true, 'cart_items' => []]);
}
?>