<?php
// Include the database connection
include "connect.php";

echo "<h2>Database Update Utility</h2>";

try {
    // Simple direct approach to add the columns
    $alterQueries = [
        "ALTER TABLE users ADD COLUMN email VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN alamat TEXT DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN kota VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN kode_pos VARCHAR(10) DEFAULT NULL"
    ];
    
    $successCount = 0;
    
    foreach ($alterQueries as $query) {
        try {
            $con->exec($query);
            echo "<p style='color:green'>✓ Successfully executed: " . htmlspecialchars($query) . "</p>";
            $successCount++;
        } catch (PDOException $innerEx) {
            if (strpos($innerEx->getMessage(), 'Duplicate column name') !== false) {
                echo "<p style='color:blue'>ℹ Column already exists: " . htmlspecialchars($query) . "</p>";
                $successCount++;
            } else {
                echo "<p style='color:orange'>⚠ Warning: " . htmlspecialchars($innerEx->getMessage()) . " for query: " . htmlspecialchars($query) . "</p>";
            }
        }
    }
    
    if ($successCount == count($alterQueries)) {
        echo "<h3 style='color:green'>✅ All database updates completed successfully!</h3>";
    } else {
        echo "<h3 style='color:orange'>⚠ Some updates may not have completed. Please check the messages above.</h3>";
    }
    
    echo "<p><a href='profile.php' style='display:inline-block; padding:10px 20px; background-color:#4CAF50; color:white; text-decoration:none; border-radius:5px;'>Return to Profile Page</a></p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color:red'>❌ Error updating database: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p><a href='index.php' style='display:inline-block; padding:10px 20px; background-color:#f44336; color:white; text-decoration:none; border-radius:5px;'>Return to Home Page</a></p>";
}
?> 