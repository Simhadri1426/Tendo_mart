<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch cart items
$user_id = $_SESSION['user_id'];
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Simhadri</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1>Shopping Cart</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                Error: <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty. <a href="home.php">Continue shopping</a></p>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <p class="price">₹<?php echo number_format($item['price'], 2); ?></p>
                            <div class="quantity-controls">
                                <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 'decrease')">-</button>
                                <span><?php echo $item['quantity']; ?></span>
                                <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 'increase')">+</button>
                            </div>
                            <button class="remove-item" onclick="removeItem(<?php echo $item['cart_id']; ?>)">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <p>Total Items: <?php echo count($cart_items); ?></p>
                <p>Total Amount: ₹<?php echo number_format($total, 2); ?></p>
                <button onclick="window.location.href='checkout.php'" class="checkout-btn">Proceed to Checkout</button>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    function updateQuantity(cartId, action) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the cart');
        });
    }

    function removeItem(cartId) {
        if (confirm('Are you sure you want to remove this item?')) {
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing the item');
            });
        }
    }
    </script>
</body>
</html> 