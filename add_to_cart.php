<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if required parameters are present
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
$quantity = (int)$_POST['quantity'];
$user_id = $_SESSION['user_id'];

// Validate quantity
if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
    exit();
}

// Check if product exists and has enough stock
$check_sql = "SELECT stock_quantity FROM products WHERE product_id = '$product_id'";
$result = mysqli_query($conn, $check_sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit();
}

// Check if product already in cart
$check_cart_sql = "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
$cart_result = mysqli_query($conn, $check_cart_sql);

if (!$cart_result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

if (mysqli_num_rows($cart_result) > 0) {
    // Update quantity
    $cart_item = mysqli_fetch_assoc($cart_result);
    $new_quantity = $cart_item['quantity'] + $quantity;
    
    if ($new_quantity > $product['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit();
    }
    
    $update_sql = "UPDATE cart SET quantity = '$new_quantity' WHERE cart_id = '" . $cart_item['cart_id'] . "'";
    $update_result = mysqli_query($conn, $update_sql);
    
    if (!$update_result) {
        echo json_encode(['success' => false, 'message' => 'Error updating cart: ' . mysqli_error($conn)]);
        exit();
    }
} else {
    // Insert new cart item
    $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', '$quantity')";
    $insert_result = mysqli_query($conn, $insert_sql);
    
    if (!$insert_result) {
        echo json_encode(['success' => false, 'message' => 'Error adding to cart: ' . mysqli_error($conn)]);
        exit();
    }
}

echo json_encode(['success' => true]);
?> 