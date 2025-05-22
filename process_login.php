<?php 
session_start();
require("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Validasi input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username/Email dan password harus diisi!";
        header("Location: login.php");
        exit();
    }

    try {
        // Cari user berdasarkan username atau email
        $stmt = $con->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verifikasi password
            if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
                $_SESSION["success"] = "Login berhasil! Selamat datang, " . $user["username"] . "!";
                header("Location: index.php");
                exit();
            } else {
                // Cek apakah input adalah username atau email
                $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
                if ($isEmail) {
                    $_SESSION['error'] = "Password yang Anda masukkan salah untuk email " . $username;
                } else {
                    $_SESSION['error'] = "Password yang Anda masukkan salah untuk username " . $username;
                }
                header("Location: login.php");
            exit();
            }
        } else {
            // Cek apakah input adalah username atau email
            $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
            if ($isEmail) {
                $_SESSION['error'] = "Email " . $username . " tidak terdaftar. Silakan daftar terlebih dahulu.";
            } else {
                $_SESSION['error'] = "Username " . $username . " tidak terdaftar. Silakan daftar terlebih dahulu.";
            }
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        header("Location: login.php");
        exit();
    }
}
?>
