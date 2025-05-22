<?php
    session_start();
    include "connect.php";
    include 'Includes/functions/functions.php';

    // Cek apakah user sudah login
    if(!isset($_SESSION['username_restaurant_qRewacvAqzA']) && !isset($_SESSION['password_restaurant_qRewacvAqzA']))
    {
        header('Location: index.php');
        exit();
    }

    // Cek apakah parameter yang diperlukan ada
    if(isset($_GET['id']) && isset($_GET['status'])) {
        $reservation_id = $_GET['id'];
        $status = $_GET['status'];

        // Validasi status
        $valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if(!in_array($status, $valid_statuses)) {
            $_SESSION['error'] = "Status tidak valid";
            header('Location: reservations.php');
            exit();
        }

        try {
            // Update status reservasi
            $stmt = $con->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
            $stmt->execute([$status, $reservation_id]);

            $_SESSION['success'] = "Status reservasi berhasil diperbarui";
        } catch(PDOException $e) {
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Parameter tidak lengkap";
    }

    // Redirect kembali ke halaman reservations
    header('Location: reservations.php');
    exit();
?> 