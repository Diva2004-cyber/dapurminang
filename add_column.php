<?php
include "connect.php";

try {
    $sql = "ALTER TABLE placed_orders 
            ADD COLUMN IF NOT EXISTS order_status VARCHAR(20) NOT NULL DEFAULT 'Pending' 
            AFTER delivery_address";
    
    $con->exec($sql);
    echo "Column 'order_status' added successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 