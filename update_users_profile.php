<?php
// Include the database connection
include "connect.php";

echo "<h2>DAPOER MINANG - Update Database Structure</h2>";

try {
    // Check if fields already exist
    $stmt = $con->prepare("SHOW COLUMNS FROM users LIKE 'alamat'");
    $stmt->execute();
    $alamatExists = $stmt->rowCount() > 0;
    
    // If alamat doesn't exist, then we assume the other fields don't exist either
    if (!$alamatExists) {
        // Add profile fields to users table
        $sql = "ALTER TABLE users 
                ADD COLUMN alamat TEXT DEFAULT NULL,
                ADD COLUMN kota VARCHAR(50) DEFAULT NULL,
                ADD COLUMN kode_pos VARCHAR(10) DEFAULT NULL";
        
        $con->exec($sql);
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
        echo "<h3>✅ Berhasil! Profil pengguna telah diperbarui.</h3>";
        echo "<p>Kolom alamat, kota, dan kode pos telah ditambahkan ke database.</p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
        echo "<h3>ℹ️ Informasi</h3>";
        echo "<p>Kolom profil sudah ada di database. Tidak ada perubahan yang dilakukan.</p>";
        echo "</div>";
    }
    
    // Check if created_at exists
    $stmt = $con->prepare("SHOW COLUMNS FROM users LIKE 'created_at'");
    $stmt->execute();
    $createdAtExists = $stmt->rowCount() > 0;
    
    if (!$createdAtExists) {
        $sql = "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $con->exec($sql);
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<p>Kolom created_at telah ditambahkan ke tabel users.</p>";
        echo "</div>";
    }
    
    // Check if phone_number exists
    $stmt = $con->prepare("SHOW COLUMNS FROM users LIKE 'phone_number'");
    $stmt->execute();
    $phoneNumberExists = $stmt->rowCount() > 0;
    
    if (!$phoneNumberExists) {
        // Add phone_number field to users table
        $sql = "ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) DEFAULT NULL";
        
        $con->exec($sql);
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
        echo "<h3>✅ Berhasil! Profil pengguna telah diperbarui.</h3>";
        echo "<p>Kolom nomor telepon telah ditambahkan ke database.</p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
        echo "<h3>ℹ️ Informasi</h3>";
        echo "<p>Kolom nomor telepon sudah ada di database. Tidak ada perubahan yang dilakukan.</p>";
        echo "</div>";
    }
    
    // Check if profile_photo exists
    $stmt = $con->prepare("SHOW COLUMNS FROM users LIKE 'profile_photo'");
    $stmt->execute();
    $profilePhotoExists = $stmt->rowCount() > 0;
    
    if (!$profilePhotoExists) {
        // Add profile_photo field to users table
        $sql = "ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL";
        
        $con->exec($sql);
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
        echo "<h3>✅ Berhasil! Profil pengguna telah diperbarui.</h3>";
        echo "<p>Kolom foto profil telah ditambahkan ke database.</p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
        echo "<h3>ℹ️ Informasi</h3>";
        echo "<p>Kolom foto profil sudah ada di database. Tidak ada perubahan yang dilakukan.</p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>Gagal memperbarui database: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// Show link to return to profile
echo "<div style='margin-top: 30px;'>";
echo "<a href='profile.php' style='display: inline-block; background-color: #d4a017; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Halaman Profil</a>";
echo "</div>";
?> 