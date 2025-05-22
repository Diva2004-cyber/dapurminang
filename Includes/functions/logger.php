<?php
class Logger {
    private $logFile;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->logFile = __DIR__ . '/../../logs/activity.log';
        
        // Create logs directory if it doesn't exist
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    public function logActivity($userId, $action, $details = '') {
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        // Log to file
        $logMessage = "[$timestamp] User ID: $userId | Action: $action | Details: $details | IP: $ipAddress | User Agent: $userAgent\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);

        // Log to database
        try {
            $stmt = $this->db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $details, $ipAddress, $userAgent, $timestamp]);
        } catch (PDOException $e) {
            error_log("Failed to log activity to database: " . $e->getMessage());
        }
    }

    public function logError($error, $context = '') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] ERROR: $error | Context: $context\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
?> 