<?php
require_once '../connect.php';
require_once '../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOrderNotification($user_email, $order_id, $status) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mybingai2@gmail.com';
        $mail->Password = 'bambangpamungkas123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'DapoerMinang');
        $mail->addAddress($user_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Status Pesanan #' . $order_id;
        
        $status_messages = [
            'received' => 'Pesanan Anda telah diterima',
            'cooking' => 'Pesanan Anda sedang dimasak',
            'delivering' => 'Pesanan Anda sedang dalam pengiriman',
            'completed' => 'Pesanan Anda telah selesai'
        ];
        
        $mail->Body = '
            <h2>Status Pesanan #' . $order_id . '</h2>
            <p>' . $status_messages[$status] . '</p>
            <p>Terima kasih telah memesan di DapoerMinang!</p>
        ';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email not sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}
?> 