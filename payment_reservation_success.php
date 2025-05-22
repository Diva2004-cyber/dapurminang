<?php
    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";

    if(!isset($_GET['reservation_id'])) {
        header("Location: table-reservation.php");
        exit();
    }

    $reservation_id = $_GET['reservation_id'];
    
    // Ambil data reservasi
    $stmt = $con->prepare("SELECT r.*, c.client_name, c.client_email 
                         FROM reservations r 
                         JOIN clients c ON r.client_id = c.client_id 
                         WHERE r.reservation_id = ?");
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$reservation) {
        header("Location: table-reservation.php");
        exit();
    }
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4>Pembayaran Berhasil</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h5>Terima kasih atas pembayaran Anda!</h5>
                        <p>Reservasi Anda telah dikonfirmasi. Berikut detail reservasi Anda:</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Detail Reservasi</h5>
                            <p>Meja: <?php echo $reservation['table_id']; ?></p>
                            <p>Tanggal: <?php echo date('d/m/Y', strtotime($reservation['selected_time'])); ?></p>
                            <p>Waktu: <?php echo date('H:i', strtotime($reservation['selected_time'])); ?></p>
                            <p>Jumlah Tamu: <?php echo $reservation['nbr_guests']; ?> orang</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Detail Pembayaran</h5>
                            <p>Metode: <?php echo ucfirst(str_replace('_', ' ', $reservation['payment_method'])); ?></p>
                            <p>Total: Rp <?php echo number_format($reservation['payment_amount'], 0, ',', '.'); ?></p>
                            <p>Status: <span class="badge bg-success">Lunas</span></p>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h5>Informasi Penting</h5>
                        <ul>
                            <li>Silakan datang tepat waktu sesuai dengan waktu reservasi</li>
                            <li>Bawa bukti reservasi (ID: <?php echo $reservation_id; ?>)</li>
                            <li>Jika ada perubahan, silakan hubungi kami minimal 1 jam sebelum waktu reservasi</li>
                        </ul>
                    </div>

                    <div class="text-center">
                        <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "Includes/templates/footer.php"; ?> 