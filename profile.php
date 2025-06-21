<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_sql);

if (!$user_result) {
    $error_message = "Error fetching user information: " . mysqli_error($conn);
    $user = null;
} else {
    $user = mysqli_fetch_assoc($user_result);
}

// Fetch user's order history
$orders_sql = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_sql);

if (!$orders_result) {
    $orders_error = "Error fetching order history: " . mysqli_error($conn);
    $orders = [];
} else {
    $orders = mysqli_fetch_all($orders_result, MYSQLI_ASSOC);
}

// Process profile update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Check if email is already taken by another user
    $check_email_sql = "SELECT user_id FROM users WHERE email = '$email' AND user_id != '$user_id'";
    $check_email_result = mysqli_query($conn, $check_email_sql);
    
    if (mysqli_num_rows($check_email_result) > 0) {
        $update_error = "Email is already taken by another user.";
    } else {
        // Update user information
        $update_sql = "UPDATE users SET username = '$username', email = '$email', phone = '$phone', address = '$address' WHERE user_id = '$user_id'";
        $update_result = mysqli_query($conn, $update_sql);
        
        if ($update_result) {
            $update_success = "Profile updated successfully!";
            // Refresh user data
            $user_result = mysqli_query($conn, $user_sql);
            $user = mysqli_fetch_assoc($user_result);
        } else {
            $update_error = "Error updating profile: " . mysqli_error($conn);
        }
    }
}

// Process password change if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $verify_sql = "SELECT password FROM users WHERE user_id = '$user_id'";
    $verify_result = mysqli_query($conn, $verify_sql);
    $user_data = mysqli_fetch_assoc($verify_result);
    
    if (!password_verify($current_password, $user_data['password'])) {
        $password_error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $password_error = "Password must be at least 6 characters long.";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $password_sql = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
        $password_result = mysqli_query($conn, $password_sql);
        
        if ($password_result) {
            $password_success = "Password changed successfully!";
        } else {
            $password_error = "Error changing password: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Simhadri</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 30px 0;
        }
        
        .profile-sidebar {
            flex: 1;
            min-width: 300px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .profile-main {
            flex: 2;
            min-width: 300px;
        }
        
        .profile-section {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 40px;
            color: #666;
        }
        
        .profile-info {
            margin-bottom: 20px;
        }
        
        .profile-info p {
            margin: 5px 0;
        }
        
        .profile-info strong {
            display: inline-block;
            width: 100px;
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
        
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-danger {
            background-color: #dc3545;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 3px;
            margin-bottom: 15px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .order-item {
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .order-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-details {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .order-total {
            font-weight: bold;
        }
        
        .view-order {
            color: #007bff;
            text-decoration: none;
        }
        
        .view-order:hover {
            text-decoration: underline;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        
        .tab.active {
            border-bottom-color: #007bff;
            color: #007bff;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1>My Profile</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($update_success)): ?>
            <div class="alert alert-success"><?php echo $update_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($update_error)): ?>
            <div class="alert alert-danger"><?php echo $update_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($password_success)): ?>
            <div class="alert alert-success"><?php echo $password_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($password_error)): ?>
            <div class="alert alert-danger"><?php echo $password_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($orders_error)): ?>
            <div class="alert alert-danger"><?php echo $orders_error; ?></div>
        <?php endif; ?>
        
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="profile-info">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></p>
                    <p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
                
                <div class="profile-section">
                    <h3>Change Password</h3>
                    <form method="POST" action="profile.php">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn">Change Password</button>
                    </form>
                </div>
            </div>
            
            <div class="profile-main">
                <div class="tabs">
                    <div class="tab active" data-tab="profile">Profile Information</div>
                    <div class="tab" data-tab="orders">Order History</div>
                </div>
                
                <div class="tab-content active" id="profile-tab">
                    <div class="profile-section">
                        <h3>Edit Profile</h3>
                        <form method="POST" action="profile.php">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn">Update Profile</button>
                        </form>
                    </div>
                </div>
                
                <div class="tab-content" id="orders-tab">
                    <div class="profile-section">
                        <h3>Order History</h3>
                        <?php if (empty($orders)): ?>
                            <p>You haven't placed any orders yet.</p>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <div>
                                            <strong>Order #<?php echo $order['order_id']; ?></strong>
                                            <span class="order-status status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span>
                                        </div>
                                        <div><?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                                    </div>
                                    <div class="order-details">
                                        <div class="order-total">₹<?php echo number_format($order['total_amount'], 2); ?></div>
                                        <a href="order_confirmation.php?order_id=<?php echo $order['order_id']; ?>" class="view-order">View Order</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding content
                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
        });
    </script>
</body>
</html> 