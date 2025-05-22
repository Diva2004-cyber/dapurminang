<?php
include 'connect.php'; // Pastikan file ini sudah ada dan terhubung ke database
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header("Location: register.php");
        exit();
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid!";
        header("Location: register.php");
        exit();
    }

    // Validasi panjang username
    if (strlen($username) < 3) {
        $_SESSION['error'] = "Username harus minimal 3 karakter!";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password dan konfirmasi password tidak cocok!";
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password harus minimal 8 karakter!";
        header("Location: register.php");
        exit();
    }

    // Validasi password harus mengandung huruf dan angka
    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $password)) {
        $_SESSION['error'] = "Password harus mengandung huruf dan angka!";
        header("Location: register.php");
        exit();
    }

    try {
        // Cek apakah username sudah digunakan
        $stmt = $con->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Username '" . $username . "' sudah digunakan. Silakan gunakan username lain.";
            header("Location: register.php");
            exit();
        }

        // Cek apakah email sudah digunakan
        $stmt = $con->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email '" . $email . "' sudah terdaftar. Silakan gunakan email lain atau lakukan login.";
            header("Location: register.php");
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan data pengguna ke database
            $stmt = $con->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            $_SESSION['success'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
                header("Location: login.php");
                exit();
            } else {
            $_SESSION['error'] = "Terjadi kesalahan saat registrasi. Silakan coba lagi.";
            header("Location: register.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        header("Location: register.php");
        exit();
    }
}
?>
