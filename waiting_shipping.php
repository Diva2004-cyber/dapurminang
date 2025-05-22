<?php
ob_start();
// Set page title
$pageTitle = 'Konfirmasi Ongkos Kirim';

include "connect.php";
include 'Includes/functions/functions.php';
include "Includes/templates/header.php";
include "Includes/templates/navbar.php";

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
if (!$order) {
    header("Location: index.php");
    exit();
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

// Handle address update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_address'])) {
    $new_address = trim($_POST['new_address']);
    $stmt = $con->prepare("UPDATE placed_orders SET delivery_address = ? WHERE order_id = ?");
    $stmt->execute([$new_address, $order_id]);
    $order['delivery_address'] = $new_address;
    $success_message = 'Alamat pengiriman berhasil diubah.';
}

// Handle confirm order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Update status shipping ke calculated dan set ongkir
    $stmt = $con->prepare("UPDATE placed_orders SET shipping_status = 'calculated', shipping_cost = 9000 WHERE order_id = ?");
    $stmt->execute([$order_id]);
    header("Location: order_success.php?order_id=" . $order_id);
    exit();
}

// Handle cancel order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    try {
        // Pastikan order_id valid
        if (!isset($order_id) || empty($order_id)) {
            throw new Exception("Order ID tidak valid");
        }
        // Update status pesanan
        $stmt = $con->prepare("UPDATE placed_orders SET order_status = 'dibatalkan', cancellation_reason = ? WHERE order_id = ?");
        $stmt->execute(['Dibatalkan oleh pelanggan', $order_id]);
        if ($stmt->rowCount() == 0) {
            throw new Exception("Gagal memperbarui status pesanan");
        }
        $_SESSION['success_message'] = 'Pesanan berhasil dibatalkan.';
        echo "<script>alert('Pesanan #" . $order_id . " telah dibatalkan.');window.location.href = 'index.php?msg=order_cancelled';</script>";
        exit();
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
        echo "<script>console.error('" . addslashes($error_message) . "');</script>";
    }
}

// Format order date
$order_time = new DateTime($order['order_time']);
$formatted_date = $order_time->format('d F Y, H:i');

// Ongkir tetap
$shipping_cost = 9000;

$voucher_discount = isset($order['voucher_discount']) ? $order['voucher_discount'] : 0;

?>
<div class="container" style="margin-top: 30px;">
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success"> <?php echo $success_message; ?> </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success"> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?> </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Status Ongkir: Ongkir Tetap</h4>
                </div>
                <div class="card-body">
                    <h5>Alamat Pengiriman</h5>
                    <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                    <button class="btn btn-sm btn-outline-primary mb-3" data-toggle="collapse" data-target="#editAddress">Ubah Alamat</button>
                    <div id="editAddress" class="collapse">
                        <form method="post" class="mt-2">
                            <textarea name="new_address" class="form-control" rows="3" required><?php echo htmlspecialchars($order['delivery_address']); ?></textarea>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">Simpan Alamat</button>
                        </form>
                    </div>
                    <h5 class="mt-4">Ringkasan Pesanan</h5>
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
                            <?php $total = 0; foreach($order_items as $item): $total += ($item['menu_price'] * $item['quantity']); ?>
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
                                <th colspan="3" class="text-right">Total:</th>
                                <th>Rp <?php echo number_format($total * 1000, 0, ',', '.'); ?></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-right">Ongkos Kirim:</th>
                                <th>Rp <?php echo number_format($shipping_cost, 0, ',', '.'); ?></th>
                            </tr>
                            <?php if($voucher_discount > 0): ?>
                            <tr>
                                <th colspan="3" class="text-right">Potongan Voucher:</th>
                                <th>- Rp <?php echo number_format($voucher_discount, 0, ',', '.'); ?></th>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th colspan="3" class="text-right">Total Bayar:</th>
                                <th>Rp <?php echo number_format(max(0, ($total * 1000) + $shipping_cost - $voucher_discount), 0, ',', '.'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="alert alert-info mt-3 shipping-confirmation-box">
                        <div class="text-center mb-3">
                            <i class="fas fa-truck fa-3x text-primary mb-2"></i>
                            <h4 class="mb-3">Ongkir Flat Rp 9.000</h4>
                        </div>
                        <div class="shipping-cost-summary p-3 mb-3" style="background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #17a2b8;">
                            <p><strong>Rincian Biaya:</strong></p>
                            <div class="d-flex justify-content-between">
                                <span>Subtotal Pesanan:</span>
                                <span>Rp <?php echo number_format($total * 1000, 0, ',', '.'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Ongkos Kirim:</span>
                                <span>Rp <?php echo number_format($shipping_cost, 0, ',', '.'); ?></span>
                            </div>
                            <?php if($voucher_discount > 0): ?>
                            <div class="d-flex justify-content-between">
                                <span>Potongan Voucher:</span>
                                <span>- Rp <?php echo number_format($voucher_discount, 0, ',', '.'); ?></span>
                            </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>Total Pembayaran:</span>
                                <span>Rp <?php echo number_format(max(0, ($total * 1000) + $shipping_cost - $voucher_discount), 0, ',', '.'); ?></span>
                            </div>
                        </div>
                        <p>Ongkir untuk pesanan Anda adalah <b>Rp 9.000</b> flat untuk seluruh area. Silakan lanjutkan pesanan Anda.</p>
                        <div class="d-flex justify-content-between mt-3">
                            <form method="post" class="d-inline">
                                <button type="submit" name="cancel_order" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                    <i class="fas fa-times"></i> Batalkan Pesanan
                                </button>
                            </form>
                            <form method="post" class="d-inline">
                                <button type="submit" name="confirm_order" class="btn btn-success">
                                    <i class="fas fa-check"></i> Lanjutkan Pesanan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "Includes/templates/footer.php";
ob_end_flush(); 