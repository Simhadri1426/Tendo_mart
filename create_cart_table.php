<?php
require_once 'config.php';

// SQL to create cart table
$sql = "CREATE TABLE IF NOT EXISTS cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
)";

// Execute the query
if (mysqli_query($conn, $sql)) {
    echo "Cart table created successfully";
} else {
    echo "Error creating cart table: " . mysqli_error($conn);
}

// Close the connection
mysqli_close($conn);
?> 