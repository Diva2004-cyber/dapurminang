<?php
    // Set zona waktu ke WIB
    date_default_timezone_set('Asia/Jakarta');
    
    // Start output buffering
    ob_start();
    
    // Set page title
    $pageTitle = 'Verifikasi Pembayaran';
    
    // Include header and navbar
    include "init.php";
    
    // Initialize variables
    $success_message = '';
    $error_message = '';
    
    // Handle payment verification
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action']) && isset($_POST['order_id'])) {
            $action = $_POST['action'];
            $order_id = intval($_POST['order_id']);
            $payment_notes = isset($_POST['payment_notes']) ? $_POST['payment_notes'] : '';
            
            if ($action == 'approve') {
                // Approve payment
                $stmt = $con->prepare("
                    UPDATE placed_orders 
                    SET payment_status = 'sukses', payment_notes = ?, order_status = 'Sedang Diproses'
                    WHERE order_id = ?
                ");
                $stmt->execute([$payment_notes, $order_id]);
                
                // Get user_id from placed_orders
                $stmt = $con->prepare("
                    SELECT client_id 
                    FROM placed_orders 
                    WHERE order_id = ? AND client_id IN (SELECT user_id FROM users)
                ");
                $stmt->execute([$order_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Add notification for registered user
                    $stmt = $con->prepare("
                        INSERT INTO notifications (user_id, order_id, message, status)
                        VALUES (?, ?, CONCAT('Pembayaran untuk pesanan #', ?, ' telah diverifikasi dan diterima.'), 'unread')
                    ");
                    $stmt->execute([$user['client_id'], $order_id, $order_id]);
                }
                
                $success_message = "Pembayaran untuk pesanan #$order_id berhasil diverifikasi.";
            } else if ($action == 'reject') {
                // Check if payment notes are provided
                if (empty($payment_notes)) {
                    $error_message = "Silakan berikan alasan penolakan pembayaran.";
                } else {
                    // Reject payment
                    $stmt = $con->prepare("
                        UPDATE placed_orders 
                        SET payment_status = 'ditolak', payment_notes = ?, order_status = 'Dibatalkan'
                        WHERE order_id = ?
                    ");
                    $stmt->execute([$payment_notes, $order_id]);
                    
                    // Get user_id from placed_orders
                    $stmt = $con->prepare("
                        SELECT client_id 
                        FROM placed_orders 
                        WHERE order_id = ? AND client_id IN (SELECT user_id FROM users)
                    ");
                    $stmt->execute([$order_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user) {
                        // Add notification for registered user
                        $stmt = $con->prepare("
                            INSERT INTO notifications (user_id, order_id, message, status)
                            VALUES (?, ?, CONCAT('Pembayaran untuk pesanan #', ?, ' ditolak. Alasan: ', ?), 'unread')
                        ");
                        $stmt->execute([$user['client_id'], $order_id, $order_id, $payment_notes]);
                    }
                    
                    $success_message = "Pembayaran untuk pesanan #$order_id telah ditolak.";
                }
            }
        }
    }
    
    // Get orders with payment proof
    $stmt = $con->prepare("
        SELECT o.*, 
            CASE 
                WHEN c.client_name IS NOT NULL THEN c.client_name 
                WHEN u.username IS NOT NULL THEN u.username 
                ELSE 'Unknown' 
            END AS customer_name,
            CASE 
                WHEN c.client_phone IS NOT NULL THEN c.client_phone 
                ELSE '' 
            END AS customer_phone,
            CASE 
                WHEN c.client_email IS NOT NULL THEN c.client_email 
                WHEN u.email IS NOT NULL THEN u.email 
                ELSE '' 
            END AS customer_email
        FROM placed_orders o
        LEFT JOIN clients c ON o.client_id = c.client_id AND o.client_id NOT IN (SELECT user_id FROM users)
        LEFT JOIN users u ON o.client_id = u.user_id
        WHERE o.payment_method IN ('Bank Transfer', 'GoPay', 'DANA') 
        AND o.payment_status != 'sukses'
        ORDER BY CASE 
            WHEN o.payment_status = 'menunggu-verifikasi' THEN 0 
            WHEN o.payment_status = 'ditolak' THEN 1 
            ELSE 2 
        END, o.order_time DESC
    ");
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order details if needed
    $selected_payment = null;
    $order_items = null;
    
    if (isset($_GET['view']) && !empty($_GET['view'])) {
        $order_id = intval($_GET['view']);
        
        // Get order details
        $stmt = $con->prepare("
            SELECT o.*, 
                CASE 
                    WHEN c.client_name IS NOT NULL THEN c.client_name 
                    WHEN u.username IS NOT NULL THEN u.username 
                    ELSE 'Unknown' 
                END AS customer_name,
                CASE 
                    WHEN c.client_phone IS NOT NULL THEN c.client_phone 
                    ELSE '' 
                END AS customer_phone,
                CASE 
                    WHEN c.client_email IS NOT NULL THEN c.client_email 
                    WHEN u.email IS NOT NULL THEN u.email 
                    ELSE '' 
                END AS customer_email
            FROM placed_orders o
            LEFT JOIN clients c ON o.client_id = c.client_id AND o.client_id NOT IN (SELECT user_id FROM users)
            LEFT JOIN users u ON o.client_id = u.user_id
            WHERE o.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $selected_payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($selected_payment) {
            // Get order items
            $stmt = $con->prepare("
                SELECT io.*, m.menu_name, m.menu_price
                FROM in_order io
                JOIN menus m ON io.menu_id = m.menu_id
                WHERE io.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Verifikasi Pembayaran</h1>
    </div>
    
    <!-- Notifications -->
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- List of Payments -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pembayaran yang Perlu Diverifikasi</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="mb-0">Tidak ada pembayaran yang perlu diverifikasi saat ini.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): 
                                    // Calculate total
                                    $stmtTotal = $con->prepare("
                                        SELECT SUM(io.quantity * m.menu_price) as total
                                        FROM in_order io
                                        JOIN menus m ON io.menu_id = m.menu_id
                                        WHERE io.order_id = ?
                                    ");
                                    $stmtTotal->execute([$payment['order_id']]);
                                    $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                                ?>
                                <tr>
                                    <td>#<?php echo $payment['order_id']; ?></td>
                                    <td><?php echo $payment['customer_name']; ?></td>
                                    <td>Rp <?php echo number_format(($total * 1000) + 9000, 0, ',', '.'); ?></td>
                                    <td><?php echo $payment['payment_method']; ?></td>
                                    <td>
                                        <?php if ($payment['payment_status'] == 'menunggu-verifikasi'): ?>
                                            <span class="badge badge-warning">Menunggu Verifikasi</span>
                                        <?php elseif ($payment['payment_status'] == 'ditolak'): ?>
                                            <span class="badge badge-danger">Ditolak</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Menunggu Pembayaran</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?view=<?php echo $payment['order_id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Payment Details -->
        <div class="col-lg-6">
            <?php if ($selected_payment): 
                // Calculate total
                $total = 0;
                foreach ($order_items as $item) {
                    $total += ($item['menu_price'] * $item['quantity']);
                }
            ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detail Pembayaran #<?php echo $selected_payment['order_id']; ?></h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Detail Pesanan</h5>
                            <p><strong>ID Pesanan:</strong> #<?php echo $selected_payment['order_id']; ?></p>
                            <p><strong>Tanggal:</strong> <?php echo date('d M Y, H:i', strtotime($selected_payment['order_time'])); ?></p>
                            <p><strong>Metode Pembayaran:</strong> <?php echo $selected_payment['payment_method']; ?></p>
                            <p><strong>Status Pembayaran:</strong> 
                                <?php if ($selected_payment['payment_status'] == 'menunggu-verifikasi'): ?>
                                <span class="badge badge-warning">Menunggu Verifikasi</span>
                                <?php elseif ($selected_payment['payment_status'] == 'ditolak'): ?>
                                <span class="badge badge-danger">Ditolak</span>
                                <?php elseif ($selected_payment['payment_status'] == 'sukses'): ?>
                                <span class="badge badge-success">Sukses</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">Menunggu</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Detail Pelanggan</h5>
                            <p><strong>Nama:</strong> <?php echo $selected_payment['customer_name']; ?></p>
                            <p><strong>Telepon:</strong> <?php echo $selected_payment['customer_phone']; ?></p>
                            <p><strong>Email:</strong> <?php echo $selected_payment['customer_email']; ?></p>
                            <p><strong>Alamat:</strong> <?php echo $selected_payment['delivery_address']; ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Bukti Pembayaran</h5>
                        <?php
                            $file_ext = pathinfo($selected_payment['payment_proof'], PATHINFO_EXTENSION);
                            if (in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png'])):
                        ?>
                        <div class="text-center">
                            <img src="../<?php echo $selected_payment['payment_proof']; ?>" alt="Bukti Pembayaran" class="img-fluid border" style="max-height: 400px;">
                        </div>
                        <?php else: ?>
                        <div class="text-center">
                            <a href="../<?php echo $selected_payment['payment_proof']; ?>" target="_blank" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> Lihat Bukti Pembayaran
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Detail Item</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['menu_name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rp <?php echo number_format($item['menu_price'] * 1000, 0, ',', '.'); ?></td>
                                        <td>Rp <?php echo number_format($item['menu_price'] * $item['quantity'] * 1000, 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Ongkos Kirim:</th>
                                        <th>Rp <?php echo number_format(9000, 0, ',', '.'); ?></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-right">Total:</th>
                                        <th>Rp <?php echo number_format(($total * 1000) + 9000, 0, ',', '.'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($selected_payment['payment_status'] != 'sukses'): ?>
                    <div class="mb-4">
                        <h5>Verifikasi Pembayaran</h5>
                        <form method="POST" id="verifyForm">
                            <input type="hidden" name="order_id" value="<?php echo $selected_payment['order_id']; ?>">
                            
                            <div class="form-group">
                                <label for="payment_notes">Catatan (opsional untuk menyetujui, wajib untuk menolak):</label>
                                <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3"></textarea>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" name="action" value="approve" class="btn btn-success mr-2">
                                    <i class="fas fa-check"></i> Setujui Pembayaran
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Tolak Pembayaran
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="card shadow mb-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                    <p>Pilih pembayaran dari daftar untuk melihat detail.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- End of Main Content -->

<!-- Custom JavaScript for Verification -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation for rejections
        const form = document.getElementById('verifyForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const action = e.submitter.value;
                const notes = document.getElementById('payment_notes').value.trim();
                
                if (action === 'reject' && notes === '') {
                    e.preventDefault();
                    alert('Silakan berikan alasan penolakan pembayaran.');
                    return false;
                }
                
                if (action === 'approve') {
                    if (!confirm('Apakah Anda yakin ingin menyetujui pembayaran ini?')) {
                        e.preventDefault();
                        return false;
                    }
                } else if (action === 'reject') {
                    if (!confirm('Apakah Anda yakin ingin menolak pembayaran ini?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        }
    });
</script>

<?php include "includes/templates/footer.php"; ?>
<?php ob_end_flush(); ?> 