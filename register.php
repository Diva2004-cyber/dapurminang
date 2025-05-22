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
    <title>Daftar - DAPOER MINANG</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #121618, #1a2124);
            min-height: 100vh;
            color: #fff;
            display: flex;
            flex-direction: column;
        }
        .register-container {
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin: 100px auto;
            width: 400px;
        }
        .wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header h2 {
            color: #d4a017;
            font-family: 'Prata', serif;
            font-size: 32px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .register-header p {
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
        .btn-register {
            background: linear-gradient(135deg, #d4a017, #b28915);
            color: white;
            padding: 0.8rem;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-register:hover {
            background: linear-gradient(135deg, #b28915, #d4a017);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(212, 160, 23, 0.3);
        }
        .login-link {
            color: #d4a017;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .login-link:hover {
            color: #b28915;
            text-decoration: underline;
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
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="register-container">
            <div class="register-header">
                <h2>Buat Akun Baru</h2>
                <p>Daftar untuk mulai memesan</p>
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
            <form action="process_register.php" method="POST">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi Password" required>
                </div>
                <button type="submit" class="btn btn-register mb-3">Daftar</button>
                <div class="text-center">
                    <p>Sudah punya akun? <a href="login.php" class="login-link">Masuk Sekarang</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php include "Includes/templates/footer.php"; ?>
