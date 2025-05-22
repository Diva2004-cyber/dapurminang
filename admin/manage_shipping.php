<?php
include "../connect.php";
include "../Includes/functions/functions.php";
include "../Includes/templates/header.php";
include "../Includes/templates/navbar.php";

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Process shipping cost update
if (isset($_POST['update_shipping'])) {
    $order_id = $_POST['order_id'];
    $shipping_cost = floatval($_POST['shipping_cost']);
    $shipping_notes = test_input($_POST['shipping_notes']);
    $shipping_status = 'calculated';
    
    $stmt = $con->prepare("UPDATE placed_orders SET 
                          shipping_cost = ?, 
                          shipping_status = ?, 
                          shipping_notes = ? 
                          WHERE order_id = ?");
    $stmt->execute([$shipping_cost, $shipping_status, $shipping_notes, $order_id]);
    
    // Send notification to user
    $stmt = $con->prepare("SELECT client_email FROM placed_orders po 
                          JOIN clients c ON po.client_id = c.client_id 
                          WHERE po.order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Send email notification
        $to = $order['client_email'];
        $subject = "Ongkos Kirim Pesanan Anda Telah Dihitung";
        $message = "Halo,\n\nOngkos kirim untuk pesanan Anda telah dihitung.\n";
        $message .= "Biaya ongkos kirim: Rp " . number_format($shipping_cost, 0, ',', '.') . "\n";
        $message .= "Catatan: " . $shipping_notes . "\n\n";
        $message .= "Silakan login ke akun Anda untuk melihat detail pesanan.\n\n";
        $message .= "Salam,\nTim Dapur";
        
        mail($to, $subject, $message);
    }
    
    header("Location: manage_shipping.php?success=1");
    exit();
}

// Get orders with pending shipping calculation
$stmt = $con->prepare("SELECT po.*, c.client_name, c.client_email, c.client_phone 
                      FROM placed_orders po 
                      JOIN clients c ON po.client_id = c.client_id 
                      WHERE po.shipping_status = 'pending' 
                      ORDER BY po.order_date DESC");
$stmt->execute();
$pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Kelola Ongkos Kirim</h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Ongkos kirim berhasil diperbarui!
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h4>Pesanan Menunggu Perhitungan Ongkos Kirim</h4>
        </div>
        <div class="card-body">
            <?php if (empty($pending_orders)): ?>
                <p>Tidak ada pesanan yang menunggu perhitungan ongkos kirim.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Tanggal</th>
                                <th>Nama Pelanggan</th>
                                <th>Alamat Pengiriman</th>
                                <th>Total Pesanan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_id']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <?php echo $order['client_name']; ?><br>
                                        <small><?php echo $order['client_phone']; ?></small>
                                    </td>
                                    <td><?php echo $order['delivery_address']; ?></td>
                                    <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                    <td>
                                        <form method="post" action="process_shipping.php" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <input type="hidden" name="user_email" value="<?php echo $order['client_email']; ?>">
                                            <input type="hidden" name="shipping_cost" value="15000">
                                            <input type="hidden" name="shipping_notes" value="">
                                            <button type="submit" class="btn btn-success btn-sm">Set Ongkir Rp 15.000</button>
                                        </form>
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

<?php include "../Includes/templates/footer.php"; ?> 