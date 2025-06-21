<?php
require_once 'config.php';

// Get all tables in the database
$sql = "SHOW TABLES";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "Error getting tables: " . mysqli_error($conn);
    exit();
}

echo "<h2>Tables in the database:</h2>";
echo "<ul>";
while ($row = mysqli_fetch_row($result)) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// Check if all required tables exist
$required_tables = ['users', 'categories', 'products', 'orders', 'order_items', 'cart'];
$missing_tables = [];

foreach ($required_tables as $table) {
    $check_sql = "SHOW TABLES LIKE '$table'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 0) {
        $missing_tables[] = $table;
    }
}

if (count($missing_tables) > 0) {
    echo "<h2>Missing tables:</h2>";
    echo "<ul>";
    foreach ($missing_tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
} else {
    echo "<p>All required tables exist in the database.</p>";
}

// Close the connection
mysqli_close($conn);
?> 