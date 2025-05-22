<?php
// Include database connection file
include "connect.php";

// Function to check if a column exists in a table
function columnExists($con, $table, $column) {
    $stmt = $con->prepare("
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = ? 
        AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return $stmt->fetchColumn() > 0;
}

echo "<h1>Database Update Script</h1>";

try {
    // Check if order_status column exists in placed_orders table
    if (!columnExists($con, 'placed_orders', 'order_status')) {
        // Add the order_status column
        $con->exec("
            ALTER TABLE placed_orders 
            ADD COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'Pending' 
            AFTER delivery_address
        ");
        echo "<p style='color:green'>Successfully added 'order_status' column to 'placed_orders' table.</p>";
    } else {
        echo "<p>The 'order_status' column already exists in the 'placed_orders' table.</p>";
    }
    
    echo "<p><strong>Database update completed.</strong></p>";
    echo "<p><a href='index.php'>Return to Homepage</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
}
?> 