<?php
session_start();
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];
    $email = $_SESSION['reset_email'];
    
    if (!$email) {
        $_SESSION['error'] = "Sesi telah berakhir. Silakan coba lagi.";
        header("Location: forgot_password.php");
        exit();
    }
    
    // Cek OTP dan waktu kadaluarsa
    $stmt = $con->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error'] = "Kode OTP tidak valid atau telah kadaluarsa.";
        header("Location: verify_otp.php");
        exit();
    }
    
    // OTP valid, arahkan ke halaman reset password
    $_SESSION['reset_email'] = $email;
    header("Location: reset_password.php");
    exit();
}
?> 