<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: home.php');
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Fetch order details
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = '$order_id' AND o.user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

// Check if query was successful
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: home.php');
    exit();
}

$order = mysqli_fetch_assoc($result);

// Fetch order items
$items_sql = "SELECT oi.*, p.product_name, p.image_url 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_sql);
$order_items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Simhadri</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .confirmation-container {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }
        
        .confirmation-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .order-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .order-items {
            margin-top: 20px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-price {
            font-weight: bold;
            color: #28a745;
        }
        
        .order-total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
            margin-top: 15px;
        }
        
        .continue-shopping {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 3px;
            text-decoration: none;
            margin-top: 20px;
        }
        
        .continue-shopping:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="confirmation-container">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Thank You for Your Order!</h1>
            <p>Your order has been successfully placed. We'll send you an email with the order details.</p>
            <p>Order ID: #<?php echo $order_id; ?></p>
        </div>
        
        <div class="order-details">
            <h2>Order Details</h2>
            <div class="order-info">
                <div>
                    <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                    <p><strong>Order Status:</strong> <span class="badge badge-success"><?php echo ucfirst($order['status']); ?></span></p>
                </div>
                <div>
                    <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                </div>
            </div>
            
            <h3>Order Items</h3>
            <div class="order-items">
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p class="item-price">₹<?php echo number_format($item['price_per_unit'], 2); ?> each</p>
                        </div>
                        <div>
                            <p class="item-price">₹<?php echo number_format($item['price_per_unit'] * $item['quantity'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="order-total">
                    Total: ₹<?php echo number_format($order['total_amount'], 2); ?>
                </div>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="home.php" class="continue-shopping">Continue Shopping</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 