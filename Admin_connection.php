<?php
$servername = "localhost";
$username = "root";
$password = "";
$db = "taskhouse_users";

try {
    // Create PDO connection
    $dsn = "mysql:host=$servername;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Set the PDO error mode to exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Set the default fetch mode to associative
        PDO::ATTR_EMULATE_PREPARES => false, // Disable emulation mode for "real" prepared statements
    ];

    $pdo = new PDO($dsn, $username, $password, $options); // Changed $conn to $pdo
    
    // Check connection (optional)
    // echo "Connected successfully"; // You can comment this out or remove it for production

} catch (PDOException $e) {
    // Handle connection errors
    die("Connection failed: " . $e->getMessage());
}
?>
