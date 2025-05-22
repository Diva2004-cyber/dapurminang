<?php
    include "connect.php";
    
    try {
        // Cek koneksi
        echo "<h2>Koneksi Database:</h2>";
        echo "Koneksi berhasil!<br><br>";
        
        // Cek database yang digunakan
        $stmt = $con->query("SELECT DATABASE()");
        $db = $stmt->fetchColumn();
        echo "Database yang digunakan: " . $db . "<br><br>";
        
        // Cek tabel yang ada
        $stmt = $con->query("SHOW TABLES");
        echo "<h2>Tabel yang ada:</h2>";
        echo "<ul>";
        while($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
        
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
?> 