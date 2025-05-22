<?php
// Database connection details
$host = "localhost";
$dbname = "restaurant_website";
$username = "root";
$password = "";

try {
    // Connect to database
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to database successfully<br>";
    
    // Check if order_status column exists
    $result = $conn->query("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'placed_orders' 
        AND COLUMN_NAME = 'order_status'
    ");
    
    if ($result->num_rows == 0) {
        // Column doesn't exist, add it
        if ($conn->query("
            ALTER TABLE placed_orders 
            ADD COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'Pending' 
            AFTER delivery_address
        ")) {
            echo "Column 'order_status' added successfully<br>";
        } else {
            echo "Error adding column: " . $conn->error . "<br>";
        }
    } else {
        echo "Column 'order_status' already exists<br>";
    }
    
    echo "Database update completed successfully";
    $conn->close();
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 