<?php
session_start();
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];
    
    if (!$email) {
        $_SESSION['error'] = "Sesi telah berakhir. Silakan coba lagi.";
        header("Location: forgot_password.php");
        exit();
    }
    
    // Validasi password
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password minimal 8 karakter.";
        header("Location: reset_password.php");
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password tidak cocok.";
        header("Location: reset_password.php");
        exit();
    }
    
    // Hash password baru
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password dan hapus token reset
    $stmt = $con->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?");
    $stmt->execute([$hashed_password, $email]);
    
    // Hapus session
    unset($_SESSION['reset_email']);
    
    $_SESSION['success'] = "Password berhasil direset. Silakan login dengan password baru Anda.";
    header("Location: login.php");
    exit();
}
?> 