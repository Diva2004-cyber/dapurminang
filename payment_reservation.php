<?php
    // Initialize the session before any output
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }

    // Check if order_id is provided
    if (!isset($_GET['reservation_id'])) {
        header("Location: table-reservation.php");
        exit();
    }

    include "connect.php";
    include "Includes/functions/functions.php";
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";

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

    // Hitung biaya reservasi
    $payment_amount = $reservation['nbr_guests'] * 50000; // Rp 50.000 per orang
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>Pembayaran Reservasi</h4>
                </div>
                <div class="card-body">
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
                            <p>Biaya per orang: Rp 50.000</p>
                            <p>Total: Rp <?php echo number_format($payment_amount, 0, ',', '.'); ?></p>
                        </div>
                    </div>

                    <form method="POST" action="process_payment.php">
                        <input type="hidden" name="reservation_id" value="<?php echo $reservation_id; ?>">
                        <input type="hidden" name="payment_amount" value="<?php echo $payment_amount; ?>">
                        
                        <div class="form-group mb-4">
                            <h5>Metode Pembayaran</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" checked>
                                <label class="form-check-label" for="bank_transfer">
                                    Transfer Bank
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                                <label class="form-check-label" for="cash">
                                    Bayar di Tempat
                                </label>
                            </div>
                        </div>

                        <div id="bank_details" class="mb-4">
                            <h5>Rekening Bank</h5>
                            <div class="alert alert-info">
                                <p class="mb-1">Bank: BCA</p>
                                <p class="mb-1">No. Rekening: 1234567890</p>
                                <p class="mb-1">Atas Nama: DAPOER MINANG</p>
                                <p class="mb-0">Jumlah: Rp <?php echo number_format($payment_amount, 0, ',', '.'); ?></p>
                            </div>
                            <div class="form-group">
                                <label for="payment_proof">Upload Bukti Transfer</label>
                                <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tampilkan/sembunyikan detail bank berdasarkan metode pembayaran
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('bank_details').style.display = 
                this.value === 'bank_transfer' ? 'block' : 'none';
        });
    });
</script>

<?php include "Includes/templates/footer.php"; ?> 