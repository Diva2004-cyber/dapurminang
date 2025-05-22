<?php
// Set zona waktu ke WIB
date_default_timezone_set('Asia/Jakarta');

ob_start();
// Set page title
$pageTitle = 'Pesanan Berhasil';

include "connect.php";
include 'Includes/functions/functions.php';
include "Includes/templates/header.php";
include "Includes/templates/navbar.php";

// Check if order_id is provided in URL
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Get order details
$stmt = $con->prepare("
    SELECT po.*, c.client_name, c.client_phone, c.client_email
    FROM placed_orders po
    JOIN clients c ON po.client_id = c.client_id
    WHERE po.order_id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if order not found
if (!$order) {
    header("Location: index.php");
    exit();
}

// Redirect to waiting_shipping.php if shipping_status is not 'calculated'
if ($order['shipping_status'] !== 'calculated') {
    header("Location: waiting_shipping.php?order_id=" . $order_id);
    exit();
}

// Redirect if order was cancelled
if (isset($order['order_status']) && $order['order_status'] === 'dibatalkan') {
    header("Location: index.php?msg=order_cancelled");
    exit();
}

// Update order status to 'confirmed' if coming from waiting_shipping page with confirmation
if (!isset($order['order_status']) || $order['order_status'] === 'pending') {
    $stmt = $con->prepare("UPDATE placed_orders SET order_status = 'confirmed' WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order['order_status'] = 'confirmed';
}

// Get order items
$stmt = $con->prepare("
    SELECT io.*, m.menu_name, m.menu_price
    FROM in_order io
    JOIN menus m ON io.menu_id = m.menu_id
    WHERE io.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total amount
$total_amount = 0;
foreach($order_items as $item) {
    $total_amount += ($item['menu_price'] * $item['quantity']);
}

// Format order date
$order_time = new DateTime($order['order_time']);
$formatted_date = $order_time->format('d F Y, H:i');

// Get estimated delivery time (30-45 minutes from order time)
$delivery_time = clone $order_time;
$delivery_time->add(new DateInterval('PT30M'));
$delivery_time_end = clone $order_time;
$delivery_time_end->add(new DateInterval('PT45M'));
$estimated_delivery = $delivery_time->format('H:i') . ' - ' . $delivery_time_end->format('H:i');

// Get payment method from order
$payment_method = $order['payment_method'] ?? 'Cash on Delivery';

// Cek apakah ada pesan error atau sukses
$error_message = isset($_GET['error']) ? $_GET['error'] : '';
$success_message = isset($_GET['success']) ? $_GET['success'] : '';

$voucher_discount = isset($order['voucher_discount']) ? $order['voucher_discount'] : 0;
?>

<div class="container" style="margin-top: 30px;">
    <!-- Tampilkan pesan error atau sukses jika ada -->
    <?php if(!empty($error_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
    </div>
    <?php endif; ?>
    
    <?php if(!empty($success_message)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Pesanan Berhasil!</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h5>Terima kasih atas pesanan Anda!</h5>
                        <p>Pesanan Anda telah berhasil diproses. Berikut detail pesanan Anda:</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Detail Pesanan</h5>
                            <p><strong>Nomor Pesanan:</strong> #<?php echo $order_id; ?></p>
                            <p><strong>Tanggal Pesanan:</strong> <?php echo $formatted_date; ?></p>
                            <p><strong>Estimasi Pengiriman:</strong> <?php echo $estimated_delivery; ?></p>
                            <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($payment_method); ?></p>
                            <?php if(!empty($order['payment_status'])): ?>
                            <p><strong>Status Pembayaran:</strong> 
                                <span class="badge <?php echo ($order['payment_status'] == 'sukses') ? 'bg-success' : 
                                    (($order['payment_status'] == 'menunggu-verifikasi') ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php 
                                    if($order['payment_status'] == 'sukses') echo 'Pembayaran Sukses';
                                    elseif($order['payment_status'] == 'menunggu-verifikasi') echo 'Menunggu Verifikasi';
                                    elseif($order['payment_status'] == 'ditolak') echo 'Pembayaran Ditolak';
                                    else echo 'Menunggu Pembayaran';
                                    ?>
                                </span>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h5>Detail Pelanggan</h5>
                            <p><strong>Nama:</strong> <?php echo htmlspecialchars($order['client_name']); ?></p>
                            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($order['client_phone']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['client_email']); ?></p>
                            <p><strong>Alamat Pengiriman:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <?php if (in_array($payment_method, ['Bank Transfer', 'GoPay', 'DANA']) && 
                          (!isset($order['payment_status']) || $order['payment_status'] !== 'sukses')): ?>
                    
                    <?php if ($payment_method == 'Bank Transfer'): ?>
                    <div class="mt-4 alert alert-info">
                        <h5>Instruksi Pembayaran</h5>
                        <p>Silakan transfer pembayaran ke rekening bank berikut:</p>
                        <p><strong>Bank:</strong> Bank Mandiri</p>
                        <p><strong>Nomor Rekening:</strong> 1234-5678-9012-3456</p>
                        <p><strong>Atas Nama:</strong> Dapoer Minang</p>
                        <p><strong>Jumlah:</strong> Rp <?php echo number_format($total_amount * 1000, 0, ',', '.'); ?></p>
                    </div>
                    <?php elseif ($payment_method == 'GoPay'): ?>
                    <div class="mt-4 alert alert-info">
                        <h5>Instruksi Pembayaran GoPay</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <p>Silakan scan QR Code atau transfer ke nomor GoPay berikut:</p>
                                <p><strong>Nomor GoPay:</strong> 081234567890</p>
                                <p><strong>Atas Nama:</strong> Dapoer Minang</p>
                                <p><strong>Jumlah:</strong> Rp <?php echo number_format($total_amount * 1000, 0, ',', '.'); ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <img src="Design/images/gopay.png" alt="GoPay" style="max-width: 100%; max-height: 150px;">
                            </div>
                        </div>
                    </div>
                    <?php elseif ($payment_method == 'DANA'): ?>
                    <div class="mt-4 alert alert-info">
                        <h5>Instruksi Pembayaran DANA</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <p>Silakan scan QR Code atau transfer ke nomor DANA berikut:</p>
                                <p><strong>Nomor DANA:</strong> 081234567890</p>
                                <p><strong>Atas Nama:</strong> Dapoer Minang</p>
                                <p><strong>Jumlah:</strong> Rp <?php echo number_format($total_amount * 1000, 0, ',', '.'); ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <img src="Design/images/dana.jpeg" alt="DANA" style="max-width: 100%; max-height: 150px;">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Form Upload Bukti Pembayaran -->
                    <?php if(!isset($order['payment_proof']) || empty($order['payment_proof'])): ?>
                    <div class="mt-4 card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Upload Bukti Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <form action="upload_bukti.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="bukti_transfer" class="form-label">Pilih File Bukti Pembayaran</label>
                                    <input type="file" class="form-control" id="bukti_transfer" name="bukti_transfer" required>
                                    <small class="form-text text-muted">Format yang diterima: JPG, PNG, PDF. Maksimal 2MB.</small>
                                </div>
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="mt-4 alert alert-warning">
                        <p><i class="fas fa-info-circle"></i> Bukti pembayaran telah diupload. Admin akan memverifikasi pembayaran Anda.</p>
                        <p>Silakan cek status pembayaran di halaman <a href="my_orders.php">Pesanan Saya</a>.</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php elseif ($payment_method == 'Cash on Delivery'): ?>
                    <div class="mt-4 alert alert-info">
                        <h5>Pembayaran Cash on Delivery</h5>
                        <p>Anda telah memilih metode pembayaran tunai saat pengiriman.</p>
                        <p>Mohon siapkan uang tunai sebesar Rp <?php echo number_format($total_amount * 1000, 0, ',', '.'); ?> saat makanan diantar.</p>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <h5>Detail Item Pesanan</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Jumlah</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['menu_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>Rp <?php echo number_format($item['menu_price'] * 1000, 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($item['menu_price'] * $item['quantity'] * 1000, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Ongkos Kirim:</th>
                                    <th>Rp <?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?></th>
                                </tr>
                                <?php if($voucher_discount > 0): ?>
                                <tr>
                                    <th colspan="3" class="text-right">Potongan Voucher:</th>
                                    <th>- Rp <?php echo number_format($voucher_discount, 0, ',', '.'); ?></th>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th>Rp <?php echo number_format(max(0, ($total_amount * 1000) + $order['shipping_cost'] - $voucher_discount), 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
                        <a href="my_orders.php" class="btn btn-success">Lihat Pesanan Saya</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "Includes/templates/footer.php";
ob_end_flush(); 