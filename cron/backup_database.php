<?php
require_once __DIR__ . '/../Includes/config.php';
require_once __DIR__ . '/../Includes/functions/backup.php';

try {
    $backup = new DatabaseBackup($db);
    if ($backup->createBackup()) {
        error_log("Database backup completed successfully");
    } else {
        error_log("Database backup failed");
    }
} catch (Exception $e) {
    error_log("Backup error: " . $e->getMessage());
}
?> 