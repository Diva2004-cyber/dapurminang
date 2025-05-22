<?php
include "connect.php";

try {
    // Add payment_method column
    $sql1 = "ALTER TABLE placed_orders 
             ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery' 
             AFTER delivery_address";
    $con->exec($sql1);
    echo "Column 'payment_method' added successfully!<br>";
    
    // Add order_notes column
    $sql2 = "ALTER TABLE placed_orders 
             ADD COLUMN IF NOT EXISTS order_notes TEXT 
             AFTER payment_method";
    $con->exec($sql2);
    echo "Column 'order_notes' added successfully!<br>";
    
    // Tambahkan kolom client_city
    $sql = "ALTER TABLE placed_orders ADD COLUMN client_city VARCHAR(50)";
    $con->exec($sql);
    echo "Column 'client_city' added successfully!<br>";
    
    // Tampilkan struktur tabel untuk verifikasi
    echo "<h3>Table Structure:</h3>";
    $result = $con->query("DESCRIBE placed_orders");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>".$row['Field']."</td>";
        echo "<td>".$row['Type']."</td>";
        echo "<td>".$row['Null']."</td>";
        echo "<td>".$row['Key']."</td>";
        echo "<td>".$row['Default']."</td>";
        echo "<td>".$row['Extra']."</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 