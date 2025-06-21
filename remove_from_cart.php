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

$cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
$user_id = $_SESSION['user_id'];

// Verify cart item belongs to user
$check_sql = "SELECT * FROM cart WHERE cart_id = '$cart_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit();
}

// Remove item
$delete_sql = "DELETE FROM cart WHERE cart_id = '$cart_id'";
mysqli_query($conn, $delete_sql);

echo json_encode(['success' => true]);
?> 