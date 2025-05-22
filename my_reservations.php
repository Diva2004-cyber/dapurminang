<?php
    // Set page title
    $pageTitle = 'Reservasi Saya';

    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";
    
    // Cek login
    if(!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=my_reservations.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Ambil data user
    $stmt = $con->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ambil client_id berdasarkan email user
    $stmt = $con->prepare("SELECT client_id FROM clients WHERE client_email = ?");
    $stmt->execute([$user['email']]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$client) {
        $no_reservation = true;
    } else {
        $client_id = $client['client_id'];
        
        // Ambil reservasi user
        $stmt = $con->prepare("SELECT * FROM reservations WHERE client_id = ? ORDER BY selected_time DESC");
        $stmt->execute([$client_id]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($reservations) == 0) {
            $no_reservation = true;
        }
    }
    
    // Proses pembatalan reservasi
    if(isset($_GET['cancel']) && isset($_GET['id'])) {
        $reservation_id = intval($_GET['id']);
        
        // Verifikasi bahwa reservasi milik user ini
        $stmt = $con->prepare("SELECT r.* FROM reservations r 
                             JOIN clients c ON r.client_id = c.client_id 
                             JOIN users u ON c.client_email = u.email 
                             WHERE r.reservation_id = ? AND u.user_id = ?");
        $stmt->execute([$reservation_id, $user_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($reservation) {
            try {
                // Perbarui status reservasi
                $stmt = $con->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ?");
                $stmt->execute([$reservation_id]);
                
                $success_msg = "Reservasi berhasil dibatalkan!";
                
                // Refresh data reservasi
                $stmt = $con->prepare("SELECT * FROM reservations WHERE client_id = ? ORDER BY selected_time DESC");
                $stmt->execute([$client_id]);
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(Exception $e) {
                $error_msg = "Gagal membatalkan reservasi: " . $e->getMessage();
            }
        } else {
            $error_msg = "Reservasi tidak ditemukan atau bukan milik Anda!";
        }
    }
?>

<style>
    html, body {
        height: 100%;
    }
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .main-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .container {
        flex: 1 0 auto;
        margin-bottom: 0;
    }
</style>
<div class="main-wrapper">
<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-12">
            <h2>Histori Reservasi Meja</h2>
            
            <?php if(isset($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success_msg; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error_msg; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if(isset($no_reservation) && $no_reservation): ?>
                <div class="alert alert-info">
                    Anda belum pernah melakukan reservasi meja. <a href="table-reservation.php" class="btn btn-sm btn-primary ml-2">Reservasi Sekarang</a>
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No. Reservasi</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Meja</th>
                                        <th>Jumlah Tamu</th>
                                        <th>Status</th>
                                        <th>Permintaan Khusus</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($reservations as $reservation): ?>
                                        <tr>
                                            <td>#<?php echo $reservation['reservation_id']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($reservation['selected_time'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($reservation['selected_time'])) . " - " . date('H:i', strtotime($reservation['end_time'])); ?></td>
                                            <td>Meja <?php echo $reservation['table_id']; ?></td>
                                            <td><?php echo $reservation['nbr_guests']; ?> orang</td>
                                            <td>
                                                <?php 
                                                    switch($reservation['status']) {
                                                        case 'pending':
                                                            echo '<span class="badge badge-warning">Menunggu Konfirmasi</span>';
                                                            break;
                                                        case 'confirmed':
                                                            echo '<span class="badge badge-success">Dikonfirmasi</span>';
                                                            break;
                                                        case 'cancelled':
                                                            echo '<span class="badge badge-danger">Dibatalkan</span>';
                                                            break;
                                                        case 'completed':
                                                            echo '<span class="badge badge-info">Selesai</span>';
                                                            break;
                                                    }
                                                ?>
                                            </td>
                                            <td><?php echo $reservation['special_requests'] ? $reservation['special_requests'] : '-'; ?></td>
                                            <td>
                                                <?php if($reservation['status'] == 'pending' || $reservation['status'] == 'confirmed'): ?>
                                                    <?php if(strtotime($reservation['selected_time']) > strtotime('+2 hours')): ?>
                                                        <a href="my_reservations.php?cancel=true&id=<?php echo $reservation['reservation_id']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Anda yakin ingin membatalkan reservasi ini?')">
                                                            Batalkan
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Tidak dapat dibatalkan (H-2 jam)</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="table-reservation.php" class="btn btn-primary">Reservasi Meja Baru</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script>
    // Fungsi untuk menghilangkan pesan alert setelah beberapa detik
    $(document).ready(function() {
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
</script>

<?php include "Includes/templates/footer.php"; ?> 