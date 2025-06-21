# E-Commerce Website

A simple e-commerce website built with PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap.

## Features

- User registration and login
- Product browsing by categories
- Shopping cart functionality
- Order placement and confirmation
- Responsive design

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/XAMPP)
- Modern web browser

## Installation

1. Clone or download this repository to your web server's root directory (e.g., `htdocs` for XAMPP).

2. Create a new MySQL database named `simhadri`.

3. Import the `simhadri.sql` file into your database:
   - Open phpMyAdmin
   - Select the `simhadri` database
   - Go to the "Import" tab
   - Choose the `simhadri.sql` file
   - Click "Go" to import

4. Configure the database connection:
   - Open `config.php`
   - Update the database credentials if needed:
     ```php
     $host = 'localhost';
     $username = 'root';
     $password = '';
     $database = 'simhadri';
     ```

5. Create an `images` directory in the root folder with the following subdirectories:
   - `fashion`
   - `appliances`
   - `electronics`
   - `beauty`

6. Add product images to their respective directories according to the image paths in the database.

## Usage

1. Start your web server (e.g., XAMPP).

2. Open your web browser and navigate to:
   ```
   http://localhost/simhadri
   ```

3. Register a new account or login with existing credentials.

4. Browse products by category, add items to cart, and place orders.

## Database Structure

The database includes the following tables:
- `users`: User account information
- `categories`: Product categories
- `products`: Product details
- `orders`: Order information
- `order_items`: Individual items in each order
- `cart`: Shopping cart items

## Security Features

- Password hashing
- SQL injection prevention
- Session management
- Input validation
- XSS prevention

## Contributing

Feel free to submit issues and enhancement requests.

## License

This project is licensed under the MIT License. 