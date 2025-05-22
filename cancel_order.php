<?php
// Start session
session_start();

// Include database connection
include "connect.php";
include 'Includes/functions/functions.php';

// Validate request
if (!isset($_GET['order_id']) || !isset($_GET['token'])) {
    header("Location: index.php?error=invalid_request");
    exit();
}

$order_id = intval($_GET['order_id']);
$token = $_GET['token'];

// Validate token
$expected_token = md5($order_id . 'cancel_token');
if ($token !== $expected_token) {
    header("Location: index.php?error=invalid_token");
    exit();
}

try {
    // Get order details
    $stmt = $con->prepare("SELECT * FROM placed_orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception("Pesanan tidak ditemukan");
    }
    
    // Check if order can be cancelled
    if (isset($order['order_status']) && $order['order_status'] === 'dibatalkan') {
        $_SESSION['success_message'] = "Pesanan #" . $order_id . " sudah dibatalkan sebelumnya.";
        header("Location: index.php?msg=already_cancelled");
        exit();
    }
    
    // Ensure required columns exist
    $con->beginTransaction();
    
    // Check if order_status column exists
    $checkColumn = $con->prepare("SHOW COLUMNS FROM placed_orders LIKE 'order_status'");
    $checkColumn->execute();
    
    if ($checkColumn->rowCount() == 0) {
        // Add order_status column if it doesn't exist
        $alterTable = $con->prepare("ALTER TABLE placed_orders ADD COLUMN order_status VARCHAR(50) DEFAULT 'pending'");
        $alterTable->execute();
    }
    
    // Check if cancellation_reason column exists
    $checkColumn = $con->prepare("SHOW COLUMNS FROM placed_orders LIKE 'cancellation_reason'");
    $checkColumn->execute();
    
    if ($checkColumn->rowCount() == 0) {
        // Add cancellation_reason column if it doesn't exist
        $alterTable = $con->prepare("ALTER TABLE placed_orders ADD COLUMN cancellation_reason TEXT");
        $alterTable->execute();
    }
    
    // Update order status
    $stmt = $con->prepare("UPDATE placed_orders SET order_status = 'dibatalkan', cancellation_reason = ? WHERE order_id = ?");
    $stmt->execute(['Dibatalkan oleh pelanggan karena ongkir tidak sesuai', $order_id]);
    
    $con->commit();
    
    $_SESSION['success_message'] = "Pesanan #" . $order_id . " berhasil dibatalkan.";
    header("Location: index.php?msg=order_cancelled");
    exit();
    
} catch (Exception $e) {
    if ($con->inTransaction()) {
        $con->rollBack();
    }
    
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header("Location: waiting_shipping.php?order_id=" . $order_id);
    exit();
}
?> 