<?php
require 'connect.php';

if (!isset($_GET['token'])) {
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

try {
    // Check if token exists and is not expired
    $stmt = $con->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reset) {
        header("Location: login.php?error=invalid_token");
        exit();
    }
} catch (PDOException $e) {
    header("Location: login.php?error=db");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - DAPOER MINANG</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #121618, #1a2124);
            min-height: 100vh;
            color: #fff;
        }
        .reset-container {
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .reset-header h2 {
            color: #d4a017;
            font-family: 'Prata', serif;
            font-size: 32px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .reset-header p {
            color: #ccc;
            font-size: 16px;
        }
        .form-control {
            background-color: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            padding: 0.8rem;
            transition: all 0.3s;
        }
        .form-control:focus {
            background-color: rgba(255,255,255,0.12);
            border-color: rgba(212, 160, 23, 0.5);
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.15);
            color: #fff;
        }
        .form-control::placeholder {
            color: rgba(255,255,255,0.5);
        }
        .btn-reset {
            background: linear-gradient(135deg, #d4a017, #b28915);
            color: white;
            padding: 0.8rem;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-reset:hover {
            background: linear-gradient(135deg, #b28915, #d4a017);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(212, 160, 23, 0.3);
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255,255,255,0.5);
        }
        .password-input {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="reset-container">
            <div class="reset-header">
                <h2>Reset Password</h2>
                <p>Masukkan password baru Anda</p>
            </div>
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            <form action="process_reset_password.php" method="POST">
                <div class="mb-3 password-input">
                    <input type="password" name="password" class="form-control" placeholder="Password Baru" required>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                </div>
                <div class="mb-3 password-input">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi Password" required>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                </div>
                <button type="submit" class="btn btn-reset mb-3">Reset Password</button>
            </form>
        </div>
    </div>
    <script>
        function togglePassword(icon) {
            const input = icon.previousElementSibling;
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html> 