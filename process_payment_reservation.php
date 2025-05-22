<?php
    include "connect.php";
    include 'Includes/functions/functions.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reservation_id = $_POST['reservation_id'];
        $payment_method = $_POST['payment_method'];
        $payment_amount = $_POST['payment_amount'];

        // Cek apakah reservasi ada
        $stmt = $con->prepare("SELECT * FROM reservations WHERE reservation_id = ?");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            header("Location: payment_reservation.php");
            exit();
        }

        // Proses upload bukti transfer jika ada
        $payment_proof = null;
        if ($payment_method === 'bank_transfer' && isset($_FILES['payment_proof'])) {
            $target_dir = "uploads/payment_proofs/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES["payment_proof"]["name"], PATHINFO_EXTENSION));
            $new_filename = "payment_" . $reservation_id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $target_file)) {
                $payment_proof = $target_file;
            }
        }

        // Update status pembayaran
        $stmt = $con->prepare("UPDATE reservations 
                             SET payment_status = 'paid',
                                 payment_method = ?,
                                 payment_amount = ?,
                                 payment_proof = ?
                             WHERE reservation_id = ?");
        
        $stmt->execute([
            $payment_method,
            $payment_amount,
            $payment_proof,
            $reservation_id
        ]);

        // Redirect ke halaman sukses
        header("Location: payment_success.php?reservation_id=" . $reservation_id);
        exit();
    } else {
        header("Location: table-reservation.php");
        exit();
    }
?> 