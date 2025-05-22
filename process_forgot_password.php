<?php
session_start();
require 'connect.php';
require 'vendor/autoload.php'; // Pastikan PHPMailer sudah diinstall

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // Cek apakah email ada di database
    $stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error'] = "Email tidak ditemukan dalam sistem.";
        header("Location: forgot_password.php");
        exit();
    }
    
    // Generate OTP 6 digit
    $otp = rand(100000, 999999);
    
    // Simpan OTP ke database dengan waktu kadaluarsa (15 menit)
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    $stmt = $con->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
    $stmt->execute([$otp, $expiry, $email]);
    
    // Konfigurasi email
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dapoerminang@gmail.com'; // Ganti dengan email Gmail Anda
        $mail->Password = 'your-app-password'; // Ganti dengan App Password yang dihasilkan
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('dapoerminang@gmail.com', 'DAPOER MINANG');
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password - DAPOER MINANG';
        $mail->Body = "
            <h2>Reset Password</h2>
            <p>Halo,</p>
            <p>Anda telah meminta untuk mereset password akun DAPOER MINANG Anda.</p>
            <p>Kode OTP Anda adalah: <strong>{$otp}</strong></p>
            <p>Kode ini akan kadaluarsa dalam 15 menit.</p>
            <p>Jika Anda tidak meminta reset password, silakan abaikan email ini.</p>
            <p>Salam,<br>Tim DAPOER MINANG</p>
        ";
        
        $mail->send();
        $_SESSION['success'] = "Kode OTP telah dikirim ke email Anda.";
        $_SESSION['reset_email'] = $email;
        header("Location: verify_otp.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal mengirim email. Silakan coba lagi nanti.";
        header("Location: forgot_password.php");
        exit();
    }
}
?> 