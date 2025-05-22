<?php
    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DAPOER MINANG</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #121618, #1a2124);
            min-height: 100vh;
            color: #fff;
        }
        .login-container {
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin: 100px auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h2 {
            color: #d4a017;
            font-family: 'Prata', serif;
            font-size: 32px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .login-header p {
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
        .btn-login {
            background: linear-gradient(135deg, #d4a017, #b28915);
            color: white;
            padding: 0.8rem;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #b28915, #d4a017);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(212, 160, 23, 0.3);
        }
        .form-check-input {
            background-color: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.1);
        }
        .form-check-input:checked {
            background-color: #d4a017;
            border-color: #d4a017;
        }
        .form-check-label {
            color: #ccc;
        }
        .forgot-password {
            color: #d4a017;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .forgot-password:hover {
            color: #b28915;
            text-decoration: underline;
        }
        .register-link {
            color: #d4a017;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .register-link:hover {
            color: #b28915;
            text-decoration: underline;
        }
        .divider {
            width: 100%;
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 1.5rem 0;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
            padding: 1rem;
            font-size: 0.9rem;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            border: 1px solid rgba(25, 135, 84, 0.2);
            color: #198754;
        }
        .login-info {
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Selamat Datang</h2>
            <p>Silakan login untuk melanjutkan</p>
        </div>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        ?>
        <div class="login-info">
            <i class="fas fa-info-circle"></i> Anda bisa login menggunakan username atau email
        </div>
        <form action="process_login.php" method="POST">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username atau Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Ingat saya</label>
                </div>
                <a href="forgot_password.php" class="forgot-password">Lupa Password?</a>
            </div>
            <button type="submit" class="btn btn-login mb-3">Login</button>
            <div class="divider"></div>
            <div class="text-center">
                <p>Belum punya akun? <a href="register.php" class="register-link">Daftar Sekarang</a></p>
            </div>
        </form>
        <div class="text-center">
            <p>Lupa password? <a href="forgot_password.php" class="login-link">Reset Password</a></p>
        </div>
    </div>
</body>
</html>

<?php include "Includes/templates/footer.php"; ?>