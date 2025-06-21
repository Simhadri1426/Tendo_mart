<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$sql = "SELECT c.*, p.product_name, p.price, p.image_url 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

// Check if query was successful
if (!$result) {
    // Handle the error
    $error_message = mysqli_error($conn);
    $cart_items = [];
} else {
    $cart_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Process checkout if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if cart is empty
    if (empty($cart_items)) {
        $error_message = "Your cart is empty. Please add items before checkout.";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Create order
            $order_sql = "INSERT INTO orders (user_id, total_amount, status) VALUES ('$user_id', '$total', 'pending')";
            $order_result = mysqli_query($conn, $order_sql);
            
            if (!$order_result) {
                throw new Exception("Error creating order: " . mysqli_error($conn));
            }
            
            $order_id = mysqli_insert_id($conn);
            
            // Add order items
            foreach ($cart_items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                
                $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price_per_unit) 
                                   VALUES ('$order_id', '$product_id', '$quantity', '$price')";
                $order_item_result = mysqli_query($conn, $order_item_sql);
                
                if (!$order_item_result) {
                    throw new Exception("Error adding order items: " . mysqli_error($conn));
                }
            }
            
            // Clear cart
            $clear_cart_sql = "DELETE FROM cart WHERE user_id = '$user_id'";
            $clear_cart_result = mysqli_query($conn, $clear_cart_sql);
            
            if (!$clear_cart_result) {
                throw new Exception("Error clearing cart: " . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Redirect to order confirmation page
            header("Location: order_confirmation.php?order_id=$order_id");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error_message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Simhadri</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .checkout-form {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .order-total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
            margin-top: 15px;
        }
        
        .submit-btn {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .submit-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1>Checkout</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                Error: <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty. <a href="home.php">Continue shopping</a></p>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <form method="POST" action="checkout.php" class="checkout-form">
                        <h2>Shipping Information</h2>
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                        <div class="form-group">
                            <label for="zip">ZIP Code</label>
                            <input type="text" id="zip" name="zip" required>
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" required>
                        </div>
                        
                        <h2>Payment Information</h2>
                        <div class="form-group">
                            <label for="card_name">Name on Card</label>
                            <input type="text" id="card_name" name="card_name" required>
                        </div>
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" required>
                        </div>
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" required>
                        </div>
                        
                        <button type="submit" class="submit-btn">Place Order</button>
                    </form>
                </div>
                
                <div class="col-md-4">
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div>
                                    ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="order-total">
                            Total: ₹<?php echo number_format($total, 2); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 