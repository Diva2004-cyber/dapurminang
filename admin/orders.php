<?php
    // Set page title
    $pageTitle = 'Kelola Pesanan';
    
    include "../connect.php";
    include '../Includes/functions/functions.php';
    include "../Includes/templates/header.php";
    
    
    
    // Get orders with client information
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
            END AS customer_email,
            CASE 
                WHEN c.client_city IS NOT NULL THEN c.client_city 
                WHEN u.kota IS NOT NULL THEN u.kota 
                ELSE '' 
            END AS customer_city,
            COALESCE(o.order_status, 'Menunggu') as order_status,
            (
                SELECT SUM(io.quantity * m.menu_price)
                FROM in_order io
                JOIN menus m ON io.menu_id = m.menu_id
                WHERE io.order_id = o.order_id
            ) as order_amount,
            o.shipping_cost
        FROM placed_orders o
        LEFT JOIN clients c ON o.client_id = c.client_id AND o.client_id NOT IN (SELECT user_id FROM users)
        LEFT JOIN users u ON o.client_id = u.user_id
        ORDER BY o.order_time DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update order status if requested
    if(isset($_GET['action']) && $_GET['action'] == 'update') {
        $order_id = $_GET['order_id'] ?? 0;
        $new_status = $_GET['status'] ?? '';
        
        if($order_id && in_array($new_status, ['Menunggu', 'Sedang Diproses', 'Terkirim', 'Dibatalkan'])) {
            $stmt = $con->prepare("UPDATE placed_orders SET order_status = ? WHERE order_id = ?");
            $stmt->execute([$new_status, $order_id]);
            
            // Success message
            $success_message = "Status pesanan #" . $order_id . " diubah menjadi " . $new_status;
        }
    }
    
    // Get order items for a specific order if requested
    $order_items = null;
    $selected_order = null;
    
    if(isset($_GET['view']) && !empty($_GET['view'])) {
        $order_id = $_GET['view'];
        
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
                END AS customer_email,
                CASE 
                    WHEN c.client_city IS NOT NULL THEN c.client_city 
                    WHEN u.kota IS NOT NULL THEN u.kota 
                    ELSE '' 
                END AS customer_city,
                COALESCE(o.order_status, 'Menunggu') as order_status
            FROM placed_orders o
            LEFT JOIN clients c ON o.client_id = c.client_id AND o.client_id NOT IN (SELECT user_id FROM users)
            LEFT JOIN users u ON o.client_id = u.user_id
            WHERE o.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $selected_order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($selected_order) {
            // Get order items and calculate total
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
            $selected_order['order_amount'] = $total_amount;
        }
    }
?>

<!-- NAVBAR MENU -->
<?php include "Includes/templates/navbar.php"; ?>

<div class="card">
    <div class="card-header">
        <?php if($selected_order): ?>
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Detail Pesanan #<?php echo $selected_order['order_id']; ?></h3>
                <a href="orders.php" class="btn btn-primary btn-sm">Kembali ke Daftar Pesanan</a>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Kelola Pesanan</h3>
                <a href="index.php" class="btn btn-primary btn-sm">Kembali ke Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if($selected_order): ?>
            <!-- Order Details View -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Pelanggan</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Nama:</strong> <?php echo $selected_order['customer_name']; ?></p>
                            <p><strong>No. Telepon:</strong> <?php echo $selected_order['customer_phone']; ?></p>
                            <p><strong>Email:</strong> <?php echo $selected_order['customer_email']; ?></p>
                            <p><strong>Alamat Pengiriman:</strong> <?php echo $selected_order['delivery_address']; ?></p>
                            <p><strong>Kota:</strong> <?php echo $selected_order['customer_city'] ? $selected_order['customer_city'] : '-'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Pesanan</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>ID Pesanan:</strong> #<?php echo $selected_order['order_id']; ?></p>
                            <p><strong>Tanggal Pesanan:</strong> <?php echo date('d M Y, H:i', strtotime($selected_order['order_time'])); ?></p>
                            <p><strong>Metode Pembayaran:</strong> <?php echo $selected_order['payment_method']; ?></p>
                            <p>
                                <strong>Status Pesanan:</strong>
                                <span class="badge 
                                    <?php 
                                        switch($selected_order['order_status']) {
                                            case 'Menunggu': echo 'bg-warning'; break;
                                            case 'Sedang Diproses': echo 'bg-primary'; break;
                                            case 'Terkirim': echo 'bg-success'; break;
                                            case 'Dibatalkan': echo 'bg-danger'; break;
                                            default: echo 'bg-secondary';
                                        }
                                    ?>">
                                    <?php echo $selected_order['order_status']; ?>
                                </span>
                            </p>
                            <?php if(!empty($selected_order['order_notes'])): ?>
                                <p><strong>Catatan:</strong> <?php echo $selected_order['order_notes']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Perbarui Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <a href="orders.php?action=update&order_id=<?php echo $selected_order['order_id']; ?>&status=Menunggu" class="btn btn-outline-warning btn-sm">Menunggu</a>
                                <a href="orders.php?action=update&order_id=<?php echo $selected_order['order_id']; ?>&status=Sedang Diproses" class="btn btn-outline-primary btn-sm">Sedang Diproses</a>
                                <a href="orders.php?action=update&order_id=<?php echo $selected_order['order_id']; ?>&status=Terkirim" class="btn btn-outline-success btn-sm">Terkirim</a>
                                <a href="orders.php?action=update&order_id=<?php echo $selected_order['order_id']; ?>&status=Dibatalkan" class="btn btn-outline-danger btn-sm">Dibatalkan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Item Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Catatan</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td><?php echo $item['menu_name']; ?></td>
                                    <td><?php echo isset($item['item_notes']) && $item['item_notes'] ? $item['item_notes'] : '-'; ?></td>
                                    <td>Rp <?php echo number_format($item['menu_price'] * 1000, 0, ',', '.'); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>Rp <?php echo number_format($item['menu_price'] * $item['quantity'] * 1000, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Ongkos Kirim:</strong></td>
                                    <td><strong>Rp <?php echo number_format(9000, 0, ',', '.'); ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>Rp <?php echo number_format(($selected_order['order_amount'] * 1000) + 9000, 0, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Orders List View -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="orders-table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal & Waktu</th>
                            <th>Total</th>
                            <th>Metode Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td>
                                <?php echo $order['customer_name']; ?><br>
                                <small class="text-muted"><?php echo $order['customer_phone']; ?></small>
                            </td>
                            <td><?php echo date('d M Y', strtotime($order['order_time'])); ?><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($order['order_time'])); ?></small>
                            </td>
                            <td>Rp <?php echo number_format(($order['order_amount'] * 1000) + 9000, 0, ',', '.'); ?></td>
                            <td><?php echo $order['payment_method']; ?></td>
                            <td>
                                <span class="badge <?php 
                                    switch($order['order_status']) {
                                        case 'Menunggu': echo 'bg-warning text-dark'; break;
                                        case 'Sedang Diproses': echo 'bg-primary text-white'; break;
                                        case 'Terkirim': echo 'bg-success text-white'; break;
                                        case 'Dibatalkan': echo 'bg-danger text-white'; break;
                                        default: echo 'bg-secondary text-white';
                                    }
                                ?>">
                                    <?php echo $order['order_status'] ?? 'Menunggu'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="orders.php?view=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                    <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        Status
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="orders.php?action=update&order_id=<?php echo $order['order_id']; ?>&status=Menunggu">Menunggu</a></li>
                                        <li><a class="dropdown-item" href="orders.php?action=update&order_id=<?php echo $order['order_id']; ?>&status=Sedang Diproses">Sedang Diproses</a></li>
                                        <li><a class="dropdown-item" href="orders.php?action=update&order_id=<?php echo $order['order_id']; ?>&status=Terkirim">Terkirim</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="orders.php?action=update&order_id=<?php echo $order['order_id']; ?>&status=Dibatalkan">Dibatalkan</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTables
        $('#orders-table').DataTable({
            "order": [[2, "desc"]] // Sort by date column descending
        });
    });
</script>

<?php include "Includes/templates/footer.php"; ?> 