<?php
// Include database connection
include "connect.php";

echo "<h2>Database Update Script</h2>";

try {
    // Check if columns exist
    $stmt = $con->prepare("
        SELECT COUNT(*) as count
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'placed_orders' 
        AND COLUMN_NAME IN ('payment_method', 'order_notes', 'order_status')
    ");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['count'] < 3) {
        // Add the columns
        $sql = "ALTER TABLE placed_orders
                ADD COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery' AFTER delivery_address,
                ADD COLUMN order_notes TEXT AFTER payment_method,
                ADD COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'Pending' AFTER order_notes";
        $con->exec($sql);
        echo "<p style='color:green'>Columns 'payment_method', 'order_notes', and 'order_status' added successfully to table 'placed_orders'.</p>";
    } else {
        echo "<p>Columns 'payment_method', 'order_notes', and 'order_status' already exist in table 'placed_orders'.</p>";
    }
} catch(PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Back to Homepage</a></p>";
?> 