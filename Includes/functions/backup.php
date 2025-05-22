<?php
class DatabaseBackup {
    private $db;
    private $backupDir;
    private $maxBackups;

    public function __construct($db, $maxBackups = 5) {
        $this->db = $db;
        $this->backupDir = __DIR__ . '/../../backups';
        $this->maxBackups = $maxBackups;
        
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function createBackup() {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $filepath = $this->backupDir . '/' . $filename;

        try {
            // Get all tables
            $tables = [];
            $result = $this->db->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            // Create backup file
            $output = "-- Database Backup\n";
            $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

            foreach ($tables as $table) {
                // Get table structure
                $result = $this->db->query("SHOW CREATE TABLE `$table`");
                $row = $result->fetch(PDO::FETCH_NUM);
                $output .= "\n\n" . $row[1] . ";\n\n";

                // Get table data
                $result = $this->db->query("SELECT * FROM `$table`");
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $output .= "INSERT INTO `$table` VALUES (";
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }
                    $output .= implode(', ', $values) . ");\n";
                }
            }

            // Save backup file
            file_put_contents($filepath, $output);

            // Clean up old backups
            $this->cleanupOldBackups();

            return true;
        } catch (Exception $e) {
            error_log("Backup failed: " . $e->getMessage());
            return false;
        }
    }

    private function cleanupOldBackups() {
        $files = glob($this->backupDir . '/backup_*.sql');
        if (count($files) > $this->maxBackups) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            $filesToDelete = array_slice($files, 0, count($files) - $this->maxBackups);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }
}
?> 