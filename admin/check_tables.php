<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    include "connect.php";
    
    try {
        // Cek struktur tabel
        $stmt = $con->query("DESCRIBE tables");
        echo "<h2>Struktur Tabel 'tables':</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
        
        // Cek data tabel
        $stmt = $con->query("SELECT * FROM tables LIMIT 5");
        echo "<h2>Data Tabel 'tables':</h2>";
        echo "<table border='1'>";
        echo "<tr><th>table_id</th><th>table_number</th><th>capacity</th><th>location</th><th>status</th></tr>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>".$row['table_id']."</td>";
            echo "<td>".$row['table_number']."</td>";
            echo "<td>".$row['capacity']."</td>";
            echo "<td>".$row['location']."</td>";
            echo "<td>".$row['status']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage() . "<br>";
        echo "Error Code: " . $e->getCode() . "<br>";
        echo "File: " . $e->getFile() . "<br>";
        echo "Line: " . $e->getLine() . "<br>";
        echo "Trace: " . $e->getTraceAsString() . "<br>";
    }
?> 