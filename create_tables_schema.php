<?php
include "connect.php";

try {
    // Cek apakah tabel tables sudah ada
    $tableExists = false;
    $stmt = $con->prepare("SHOW TABLES LIKE 'tables'");
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        $tableExists = true;
    }
    
    // Jika tabel belum ada, buat tabel tables
    if(!$tableExists) {
        $sql = "CREATE TABLE `tables` (
            `table_id` int(11) NOT NULL AUTO_INCREMENT,
            `table_number` int(11) NOT NULL,
            `capacity` int(11) NOT NULL DEFAULT 4,
            `location` enum('Indoor','Outdoor','VIP Room','Smoking Area','Non-Smoking Area') NOT NULL DEFAULT 'Indoor',
            `status` enum('available','occupied','maintenance','inactive') NOT NULL DEFAULT 'available',
            PRIMARY KEY (`table_id`),
            UNIQUE KEY `table_number` (`table_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $con->exec($sql);
        echo "Tabel 'tables' berhasil dibuat!<br>";
        
        // Tambahkan beberapa meja default
        $stmt = $con->prepare("INSERT INTO tables (table_number, capacity, location, status) VALUES 
            (1, 2, 'Indoor', 'available'),
            (2, 2, 'Indoor', 'available'),
            (3, 4, 'Indoor', 'available'),
            (4, 4, 'Indoor', 'available'),
            (5, 6, 'Indoor', 'available'),
            (6, 6, 'Outdoor', 'available'),
            (7, 8, 'VIP Room', 'available'),
            (8, 10, 'VIP Room', 'available'),
            (9, 4, 'Smoking Area', 'available'),
            (10, 4, 'Non-Smoking Area', 'available')
        ");
        $stmt->execute();
        echo "Meja default berhasil ditambahkan!<br>";
    } else {
        echo "Tabel 'tables' sudah ada.<br>";
    }
    
    // Cek kolom pada tabel reservations
    $stmt = $con->prepare("SHOW COLUMNS FROM reservations LIKE 'table_id'");
    $stmt->execute();
    if($stmt->rowCount() == 0) {
        // Tambahkan kolom table_id ke tabel reservations jika belum ada
        $sql = "ALTER TABLE reservations ADD COLUMN table_id INT NULL AFTER selected_time, 
                ADD COLUMN end_time DATETIME NULL AFTER selected_time";
        $con->exec($sql);
        echo "Kolom table_id dan end_time berhasil ditambahkan ke tabel reservations!<br>";
    } else {
        echo "Kolom table_id sudah ada di tabel reservations.<br>";
    }
    
    // Cek kolom status pada tabel reservations
    $stmt = $con->prepare("SHOW COLUMNS FROM reservations LIKE 'status'");
    $stmt->execute();
    if($stmt->rowCount() == 0) {
        // Tambahkan kolom status ke tabel reservations jika belum ada
        $sql = "ALTER TABLE reservations ADD COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending'";
        $con->exec($sql);
        echo "Kolom status berhasil ditambahkan ke tabel reservations!<br>";
    } else {
        echo "Kolom status sudah ada di tabel reservations.<br>";
    }
    
    echo "<br>Semua struktur tabel berhasil dibuat/diperbaharui.";
    echo "<br><a href='index.php'>Kembali ke Halaman Utama</a>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 