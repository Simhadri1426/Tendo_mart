-- Create database
CREATE DATABASE IF NOT EXISTS simhadri;
USE simhadri;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Products table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Orders table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order items table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Cart table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Create view for order details
CREATE VIEW order_details AS
SELECT 
    o.order_id,
    u.username,
    o.order_date,
    o.total_amount,
    o.status,
    oi.product_id,
    p.product_name,
    oi.quantity,
    oi.price_per_unit
FROM orders o
JOIN users u ON o.user_id = u.user_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id;

-- Insert sample categories
INSERT INTO categories (category_name, description) VALUES
('Fashion', 'Clothing and accessories'),
('Home Appliances', 'Household appliances'),
('Electronics', 'Electronic devices and gadgets'),
('Beauty Products', 'Cosmetics and personal care');

-- Insert sample products
INSERT INTO products (category_id, product_name, description, price, image_url, stock_quantity) VALUES
-- Fashion products
(1, 'Men\'s T-Shirt', 'Comfortable cotton t-shirt', 29.99, 'images/fashion/tshirt.jpg', 50),
(1, 'Women\'s Dress', 'Elegant summer dress', 49.99, 'images/fashion/dress.jpg', 30),
(1, 'Jeans', 'Classic blue jeans', 59.99, 'images/fashion/jeans.jpg', 40),
(1, 'Sneakers', 'Casual sports shoes', 79.99, 'images/fashion/sneakers.jpg', 25),
(1, 'Jacket', 'Warm winter jacket', 89.99, 'images/fashion/jacket.jpg', 20),
(1, 'Sweater', 'Woolen sweater', 69.99, 'images/fashion/sweater.jpg', 35),
(1, 'Skirt', 'A-line skirt', 39.99, 'images/fashion/skirt.jpg', 45),
(1, 'Hat', 'Stylish cap', 19.99, 'images/fashion/hat.jpg', 60),
(1, 'Scarf', 'Winter scarf', 24.99, 'images/fashion/scarf.jpg', 55),
(1, 'Belt', 'Leather belt', 29.99, 'images/fashion/belt.jpg', 70),
(1, 'Socks', 'Cotton socks pack', 14.99, 'images/fashion/socks.jpg', 100),
(1, 'Gloves', 'Winter gloves', 34.99, 'images/fashion/gloves.jpg', 40),

-- Home Appliances
(2, 'Refrigerator', 'Double door refrigerator', 899.99, 'images/appliances/fridge.jpg', 10),
(2, 'Washing Machine', 'Front load washing machine', 699.99, 'images/appliances/washer.jpg', 15),
(2, 'Microwave', 'Countertop microwave', 199.99, 'images/appliances/microwave.jpg', 20),
(2, 'Toaster', '2-slice toaster', 49.99, 'images/appliances/toaster.jpg', 30),
(2, 'Blender', 'High-speed blender', 79.99, 'images/appliances/blender.jpg', 25),
(2, 'Coffee Maker', 'Automatic coffee maker', 129.99, 'images/appliances/coffeemaker.jpg', 18),
(2, 'Vacuum Cleaner', 'Bagless vacuum cleaner', 159.99, 'images/appliances/vacuum.jpg', 22),
(2, 'Air Conditioner', 'Split AC unit', 799.99, 'images/appliances/ac.jpg', 12),
(2, 'Electric Kettle', 'Quick-boil kettle', 39.99, 'images/appliances/kettle.jpg', 35),
(2, 'Food Processor', 'Multi-function food processor', 149.99, 'images/appliances/processor.jpg', 15),
(2, 'Iron', 'Steam iron', 45.99, 'images/appliances/iron.jpg', 40),
(2, 'Fan', 'Table fan', 29.99, 'images/appliances/fan.jpg', 50),

-- Electronics
(3, 'Smartphone', 'Latest model smartphone', 699.99, 'images/electronics/phone.jpg', 25),
(3, 'Laptop', '15-inch laptop', 999.99, 'images/electronics/laptop.jpg', 15),
(3, 'Headphones', 'Wireless headphones', 129.99, 'images/electronics/headphones.jpg', 30),
(3, 'Tablet', '10-inch tablet', 399.99, 'images/electronics/tablet.jpg', 20),
(3, 'Smart Watch', 'Fitness tracker watch', 199.99, 'images/electronics/watch.jpg', 25),
(3, 'Camera', 'Digital camera', 499.99, 'images/electronics/camera.jpg', 12),
(3, 'Speaker', 'Bluetooth speaker', 79.99, 'images/electronics/speaker.jpg', 35),
(3, 'Power Bank', '20000mAh power bank', 49.99, 'images/electronics/powerbank.jpg', 40),
(3, 'Keyboard', 'Mechanical keyboard', 89.99, 'images/electronics/keyboard.jpg', 30),
(3, 'Mouse', 'Wireless mouse', 39.99, 'images/electronics/mouse.jpg', 45),
(3, 'Monitor', '24-inch monitor', 199.99, 'images/electronics/monitor.jpg', 18),
(3, 'Printer', 'All-in-one printer', 299.99, 'images/electronics/printer.jpg', 15),

-- Beauty Products
(4, 'Face Cream', 'Moisturizing face cream', 29.99, 'images/beauty/facecream.jpg', 40),
(4, 'Lipstick', 'Matte lipstick', 19.99, 'images/beauty/lipstick.jpg', 50),
(4, 'Shampoo', 'Natural hair shampoo', 24.99, 'images/beauty/shampoo.jpg', 45),
(4, 'Perfume', 'Floral perfume', 59.99, 'images/beauty/perfume.jpg', 30),
(4, 'Makeup Kit', 'Professional makeup kit', 89.99, 'images/beauty/makeupkit.jpg', 25),
(4, 'Face Mask', 'Clay face mask', 14.99, 'images/beauty/facemask.jpg', 60),
(4, 'Hair Dryer', 'Professional hair dryer', 79.99, 'images/beauty/hairdryer.jpg', 20),
(4, 'Nail Polish', 'Long-lasting nail polish', 9.99, 'images/beauty/nailpolish.jpg', 70),
(4, 'Eye Shadow', 'Eye shadow palette', 34.99, 'images/beauty/eyeshadow.jpg', 35),
(4, 'Foundation', 'Liquid foundation', 39.99, 'images/beauty/foundation.jpg', 40),
(4, 'Mascara', 'Volumizing mascara', 24.99, 'images/beauty/mascara.jpg', 45),
(4, 'Serum', 'Anti-aging serum', 49.99, 'images/beauty/serum.jpg', 30);

-- Create trigger to update stock after order
DELIMITER //
CREATE TRIGGER after_order_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE products 
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE product_id = NEW.product_id;
END//
DELIMITER ; 