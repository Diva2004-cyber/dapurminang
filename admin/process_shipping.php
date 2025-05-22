<?php
//Start session
session_start();

//PHP INCLUDES
include 'connect.php';
include 'Includes/functions/functions.php';

//TEST IF THE SESSION HAS BEEN CREATED BEFORE
if(isset($_SESSION['username_restaurant_qRewacvAqzA']) && isset($_SESSION['password_restaurant_qRewacvAqzA']))
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $order_id = $_POST['order_id'];
        $shipping_cost = $_POST['shipping_cost']; // Akan selalu 15000 dari form
        $shipping_notes = $_POST['shipping_notes'] ?? '';
        $user_email = $_POST['user_email'];

        try {
            // Update the order with shipping cost
            $stmt = $con->prepare("UPDATE placed_orders 
                                 SET shipping_cost = ?, 
                                     shipping_notes = ?, 
                                     shipping_status = 'calculated',
                                     total_amount = total_amount + ?
                                 WHERE order_id = ?");
            $stmt->execute(array($shipping_cost, $shipping_notes, $shipping_cost, $order_id));
            
            if ($stmt->rowCount() > 0) {
                // Send email notification to customer
                $subject = "Biaya Ongkir Telah Dihitung untuk Pesanan #" . $order_id;
                $message = "Halo Pelanggan,\n\n";
                $message .= "Biaya ongkir untuk pesanan #" . $order_id . " telah dihitung.\n";
                $message .= "Biaya Ongkir: Rp " . number_format($shipping_cost, 0, ',', '.') . "\n";
                if (!empty($shipping_notes)) {
                    $message .= "Catatan: " . $shipping_notes . "\n";
                }
                $message .= "\nSilakan lanjutkan untuk menyelesaikan pembayaran Anda.\n\n";
                $message .= "Terima kasih telah berbelanja di toko kami!";

                // Send email using your existing email function
                // sendEmail($user_email, $subject, $message);

                $_SESSION['success_message'] = "Biaya ongkir telah dihitung dan pelanggan telah diberitahu.";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui biaya ongkir. Silakan coba lagi.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
        }

        header('Location: calculate_shipping.php');
        exit;
    }
}
else
{
    header("Location: index.php");
    exit();
}
?> 