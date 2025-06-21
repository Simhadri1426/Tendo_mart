<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to update cart']);
    exit();
}

// Check if required parameters are present
if (!isset($_POST['cart_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$cart_id = $_POST['cart_id'];
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];

// Verify cart item belongs to user
$sql = "SELECT c.*, p.stock_quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.cart_id = '$cart_id' AND c.user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit();
}

$cart_item = mysqli_fetch_assoc($result);
$current_quantity = $cart_item['quantity'];
$stock_quantity = $cart_item['stock_quantity'];

// Update quantity based on action
if ($action === 'increase') {
    if ($current_quantity >= $stock_quantity) {
        echo json_encode(['success' => false, 'message' => 'Cannot add more items. Stock limit reached']);
        exit();
    }
    $new_quantity = $current_quantity + 1;
} else if ($action === 'decrease') {
    if ($current_quantity <= 1) {
        echo json_encode(['success' => false, 'message' => 'Quantity cannot be less than 1']);
        exit();
    }
    $new_quantity = $current_quantity - 1;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Update cart quantity
$sql = "UPDATE cart SET quantity = '$new_quantity' WHERE cart_id = '$cart_id' AND user_id = '$user_id'";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating cart']);
}
?> 