<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'simhadri';

$conn = mysqli_connect($host, $username, $password, $database,3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?> 