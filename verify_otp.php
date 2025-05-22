<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - DAPOER MINANG</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #121618, #1a2124);
            min-height: 100vh;
            color: #fff;
        }
        .verify-container {
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .verify-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .verify-header h2 {
            color: #d4a017;
            font-family: 'Prata', serif;
            font-size: 32px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .verify-header p {
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
            text-align: center;
            font-size: 24px;
            letter-spacing: 5px;
        }
        .form-control:focus {
            background-color: rgba(255,255,255,0.12);
            border-color: rgba(212, 160, 23, 0.5);
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.15);
            color: #fff;
        }
        .btn-verify {
            background: linear-gradient(135deg, #d4a017, #b28915);
            color: white;
            padding: 0.8rem;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-verify:hover {
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
    </style>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="verify-container">
            <div class="verify-header">
                <h2>Verifikasi OTP</h2>
                <p>Masukkan kode OTP yang dikirim ke email Anda</p>
            </div>
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            <form action="process_verify_otp.php" method="POST">
                <div class="mb-3">
                    <input type="text" name="otp" class="form-control" maxlength="6" pattern="[0-9]{6}" required>
                </div>
                <button type="submit" class="btn btn-verify mb-3">Verifikasi</button>
                <div class="text-center">
                    <p>Tidak menerima kode? <a href="forgot_password.php" class="text-warning">Kirim Ulang</a></p>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Auto-focus dan format input OTP
        document.querySelector('input[name="otp"]').focus();
        
        // Hanya menerima angka
        document.querySelector('input[name="otp"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html> 