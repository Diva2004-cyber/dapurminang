<?php
require_once '../connect.php';
require_once '../auth_check.php';

// Konfigurasi Midtrans
require_once '../vendor/midtrans/midtrans-php/Midtrans.php';

\Midtrans\Config::$serverKey = 'YOUR_SERVER_KEY';
\Midtrans\Config::$isProduction = false; // Set true untuk production
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $payment_method = $_POST['payment_method'];
    
    // Ambil detail order
    $sql = "SELECT o.*, u.email, u.name FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    // Parameter transaksi
    $transaction_details = array(
        'order_id' => $order_id,
        'gross_amount' => $order['total_amount']
    );
    
    // Data customer
    $customer_details = array(
        'first_name' => $order['name'],
        'email' => $order['email']
    );
    
    // Parameter tambahan sesuai metode pembayaran
    $custom_expiry = array(
        'start_time' => date("Y-m-d H:i:s O", time()),
        'unit' => 'hour',
        'duration' => 24
    );
    
    $transaction_data = array(
        'transaction_details' => $transaction_details,
        'customer_details' => $customer_details,
        'expiry' => $custom_expiry
    );
    
    try {
        // Generate Snap Token
        $snapToken = \Midtrans\Snap::getSnapToken($transaction_data);
        
        // Update order dengan payment token
        $update_sql = "UPDATE orders SET payment_token = ?, payment_method = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssi", $snapToken, $payment_method, $order_id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'token' => $snapToken
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

require 'vendor/autoload.php'; // Pastikan path ini sesuai dengan lokasi autoload.php

$options = array(
    'cluster' => 'YOUR_CLUSTER', // Ganti dengan cluster Anda
    'useTLS' => true
);

$pusher = new Pusher\Pusher(
    'YOUR_APP_KEY', // Ganti dengan app key Anda
    'YOUR_APP_SECRET', // Ganti dengan app secret Anda
    'YOUR_APP_ID', // Ganti dengan app ID Anda
    $options
);

$data['message'] = 'Pesanan Anda telah diterima.';
$pusher->trigger('my-channel', 'my-event', $data);
?> 