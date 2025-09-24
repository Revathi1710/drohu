<?php
include('connection.php');
session_start();

// Check if the product ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: allProduct.php');
    exit;
}

$product_id = $_GET['id'];

// First, get the product image path to delete it from the server
$image_query = "SELECT product_image FROM product WHERE id = $product_id";
$image_result = mysqli_query($con, $image_query);
$row = mysqli_fetch_assoc($image_result);

if ($row && !empty($row['product_image']) && file_exists($row['product_image'])) {
    unlink($row['product_image']);
}

// Now, delete the product record from the database
$delete_query = "DELETE FROM product WHERE id = $product_id";

if (mysqli_query($con, $delete_query)) {
    header('Location: allProduct.php?status=deleted');
    exit;
} else {
    // Handle error, maybe redirect with an error status
    header('Location: allProduct.php?status=error');
    exit;
}
?>