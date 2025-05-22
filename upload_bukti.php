<?php
// Start output buffering to prevent header issues
ob_start();

// Include connection file
include "connect.php";
include 'Includes/functions/functions.php';

// Periksa apakah user sudah login
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_message = '';
$success_message = '';

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
        $error_message = 'ID pesanan tidak valid!';
    } else if (!isset($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] != 0) {
        $error_message = 'Terjadi kesalahan saat upload file!';
    } else {
        $order_id = $_POST['order_id'];
        $user_id = $_SESSION['user_id'];
        
        // Periksa apakah pesanan milik user yang login
        $stmt = $con->prepare("
            SELECT po.* 
            FROM placed_orders po
            JOIN clients c ON po.client_id = c.client_id
            WHERE po.order_id = ? AND c.client_email = (
                SELECT email FROM users WHERE user_id = ?
            )
        ");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            $error_message = 'Pesanan tidak ditemukan!';
        } else {
            // Periksa apakah bukti sudah pernah diupload
            if (!empty($order['payment_proof'])) {
                $error_message = 'Bukti pembayaran untuk pesanan ini sudah pernah diupload.';
            } else {
                // Validasi file
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                $file = $_FILES['bukti_transfer'];
                
                if (!in_array($file['type'], $allowed_types)) {
                    $error_message = 'Format file tidak didukung! Gunakan JPG, PNG, atau PDF.';
                } else if ($file['size'] > $max_size) {
                    $error_message = 'Ukuran file terlalu besar! Maksimal 2MB.';
                } else {
                    // Generate nama file unik
                    $filename = 'bukti_' . $order_id . '_' . time() . '_' . basename($file['name']);
                    $upload_dir = 'uploads/payment_proof/';
                    $target_file = $upload_dir . $filename;
                    
                    // Upload file
                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        // Update database
                        $stmt = $con->prepare("
                            UPDATE placed_orders 
                            SET payment_proof = ?, payment_status = 'menunggu-verifikasi' 
                            WHERE order_id = ?
                        ");
                        $stmt->execute([$target_file, $order_id]);
                        
                        // Tambahkan notifikasi untuk admin
                        $stmt = $con->prepare("
                            INSERT INTO notifications (user_id, order_id, message, status)
                            VALUES (?, ?, ?, 'unread')
                        ");
                        $message = "Bukti pembayaran untuk pesanan #$order_id telah diupload dan menunggu verifikasi.";
                        $stmt->execute([1, $order_id, $message]); // Admin ID = 1
                        
                        $success_message = 'Bukti pembayaran berhasil diupload! Admin akan memverifikasi pembayaran Anda.';
                    } else {
                        $error_message = 'Gagal mengupload file! Silakan coba lagi.';
                    }
                }
            }
        }
    }
}

// Redirect jika sukses
if (!empty($success_message)) {
    header("Location: my_orders.php?success=" . urlencode($success_message));
    exit();
}

// Jika ada error, redirect ke halaman sebelumnya dengan pesan error
if (!empty($error_message)) {
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "&error=" . urlencode($error_message));
    } else {
        header("Location: my_orders.php?error=" . urlencode($error_message));
    }
    exit();
}

// Jika tidak ada POST, redirect ke halaman utama
header("Location: index.php");
exit();

ob_end_flush();
?> 