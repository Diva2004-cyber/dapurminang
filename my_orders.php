<?php
    // Start output buffering to prevent "headers already sent" errors
    ob_start();
    
    // Set page title
    $pageTitle = 'My Orders';

    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";
    
    // Redirect if not logged in
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Ambil email user dari tabel users
    $stmt = $con->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_email = $user ? $user['email'] : '';

    // Ambil client_id dari tabel clients berdasarkan email user
    $stmt = $con->prepare("SELECT client_id FROM clients WHERE client_email = ?");
    $stmt->execute([$user_email]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    $client_id = $client ? $client['client_id'] : 0;

    // Get user's orders berdasarkan client_id
    $orders = [];
    if ($client_id) {
        $stmt = $con->prepare("SELECT po.*, c.client_name, c.client_phone, c.client_email FROM placed_orders po JOIN clients c ON po.client_id = c.client_id WHERE po.client_id = ? ORDER BY po.order_time DESC");
        $stmt->execute([$client_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get order details if an order is selected
    $selected_order = null;
    $order_items = null;
    
    if(isset($_GET['order_id']) && !empty($_GET['order_id']) && $client_id) {
        $order_id = $_GET['order_id'];
        
        // Get order
        $stmt = $con->prepare("SELECT po.*, c.client_name, c.client_phone, c.client_email FROM placed_orders po JOIN clients c ON po.client_id = c.client_id WHERE po.order_id = ? AND po.client_id = ?");
        $stmt->execute([$order_id, $client_id]);
        $selected_order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($selected_order) {
            // Get order items
            $stmt = $con->prepare("SELECT io.*, m.menu_name, m.menu_price FROM in_order io JOIN menus m ON io.menu_id = m.menu_id WHERE io.order_id = ?");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Cek apakah ada pesan error atau sukses
    $error_message = isset($_GET['error']) ? $_GET['error'] : '';
    $success_message = isset($_GET['success']) ? $_GET['success'] : '';

    // Add filter handling
    if(isset($_GET['filter']) && $_GET['filter'] != 'all') {
        $filter = $_GET['filter'];
        $status_map = [
            'menunggu' => 'Menunggu',
            'diproses' => 'Sedang Diproses',
            'terkirim' => 'Terkirim',
            'dibatalkan' => 'Dibatalkan'
        ];
        
        if(isset($status_map[$filter])) {
            $filtered_orders = array_filter($orders, function($order) use ($status_map, $filter) {
                return ($order['order_status'] ?? 'Menunggu') == $status_map[$filter];
            });
            $orders = $filtered_orders;
        }
    }
?>

<style>
    /* Base Styles */
    :root {
        --primary-color: #9e8a78;
        --primary-light: rgba(158, 138, 120, 0.1);
        --accent-color: #ffc851;
        --text-dark: #333;
        --text-muted: #666;
        --border-color: #eee;
        --status-pending: #FFC107;
        --status-processing: #2196F3;
        --status-delivered: #4CAF50;
        --status-canceled: #F44336;
        --shadow: 0 0 15px rgba(0,0,0,0.1);
        --border-radius: 8px;
        --spacing: 20px;
    }
    
    .orders-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 15px;
    }
    
    /* Breadcrumbs */
    .breadcrumbs {
        display: flex;
        margin-bottom: 20px;
        font-size: 14px;
        color: var(--text-muted);
    }
    
    .breadcrumbs a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    .breadcrumbs span {
        margin: 0 8px;
    }
    
    /* Page Title */
    .page-title {
        font-size: 28px;
        font-weight: 600;
        margin-bottom: 25px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
    }
    
    .page-title i {
        margin-right: 10px;
        color: var(--primary-color);
    }
    
    /* Search and Filter */
    .search-filter-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .search-box {
        flex: 1;
        max-width: 300px;
        position: relative;
    }
    
    .search-box input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 14px;
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    
    .filter-dropdown {
        position: relative;
    }
    
    .filter-btn {
        background-color: white;
        border: 1px solid var(--border-color);
        padding: 10px 15px;
        border-radius: var(--border-radius);
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
    }
    
    .filter-btn i {
        margin-right: 5px;
    }
    
    .filter-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 160px;
        box-shadow: var(--shadow);
        border-radius: var(--border-radius);
        z-index: 10;
        right: 0;
        margin-top: 5px;
    }
    
    .filter-content a {
        color: var(--text-dark);
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        font-size: 14px;
    }
    
    .filter-content a:hover {
        background-color: var(--primary-light);
    }
    
    .filter-dropdown:hover .filter-content {
        display: block;
    }
    
    /* Orders Grid */
    .orders-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
    }
    
    @media (max-width: 768px) {
        .orders-grid {
            grid-template-columns: 1fr;
        }
        
        .search-filter-container {
            flex-direction: column;
        }
        
        .search-box {
            max-width: 100%;
        }
    }
    
    /* Orders List */
    .orders-list {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 20px;
        height: fit-content;
    }
    
    .order-card {
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .order-card:hover {
        box-shadow: var(--shadow);
        transform: translateY(-2px);
    }
    
    .order-card.active {
        border-color: var(--primary-color);
        background-color: var(--primary-light);
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .order-id {
        font-weight: 600;
    }
    
    .order-date {
        font-size: 14px;
        color: var(--text-muted);
    }
    
    .order-summary {
        margin-top: 10px;
        font-size: 13px;
        color: var(--text-muted);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    /* Status Labels */
    .order-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .order-status i {
        margin-right: 5px;
    }
    
    .status-pending {
        background-color: var(--status-pending);
        color: #333;
    }
    
    .status-processing {
        background-color: var(--status-processing);
        color: white;
    }
    
    .status-delivered {
        background-color: var(--status-delivered);
        color: white;
    }
    
    .status-canceled {
        background-color: var(--status-canceled);
        color: white;
    }
    
    .order-total {
        font-weight: bold;
        color: var(--text-dark);
    }
    
    /* Order Details */
    .order-details {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 16px 20px 16px 20px;
        align-self: flex-start;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--text-dark);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .section-title i {
        margin-right: 10px;
    }
    
    .detail-card {
        background-color: #f9f9f9;
        border-radius: var(--border-radius);
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .detail-label {
        font-weight: 500;
        color: var(--text-muted);
    }
    
    .order-items {
        margin-top: 16px;
        margin-bottom: 0;
    }
    
    .item-row {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .item-info {
        display: flex;
    }
    
    .item-quantity {
        background: var(--primary-color);
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 13px;
        font-weight: 500;
    }
    
    .item-details {
        flex: 1;
    }
    
    .item-name {
        font-weight: 500;
    }
    
    .item-notes {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 3px;
    }
    
    .item-price {
        font-weight: 500;
    }
    
    /* Empty States */
    .no-orders {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    
    .no-orders i {
        font-size: 48px;
        color: var(--text-muted);
        margin-bottom: 15px;
    }
    
    .no-orders h3 {
        font-size: 22px;
        margin-bottom: 10px;
    }
    
    .no-orders p {
        color: var(--text-muted);
        margin-bottom: 20px;
    }
    
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-muted);
    }
    
    .empty-state i {
        font-size: 32px;
        margin-bottom: 10px;
    }
    
    /* Buttons */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 15px;
        border-radius: var(--border-radius);
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
        border: none;
    }
    
    .btn-primary:hover {
        opacity: 0.9;
    }
    
    .btn-outline {
        background-color: transparent;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
    }
    
    .btn-outline:hover {
        background-color: var(--primary-light);
    }
    
    .support-link {
        display: inline-flex;
        align-items: center;
        color: var(--primary-color);
        font-size: 14px;
        margin-top: 15px;
    }
    
    .support-link i {
        margin-right: 5px;
    }

    /* Wrapper for footer positioning */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    .wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
</style>

<div class="wrapper">
<div class="orders-container">
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

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
            <a href="index.php">Home</a>
        <span>/</span>
        <span>Pesanan Saya</span>
    </div>

    <!-- Page Title -->
    <h1 class="page-title"><i class="fas fa-shopping-bag"></i> Pesanan Saya</h1>
    
    <?php if(empty($orders)): ?>
    
    <div class="no-orders">
        <i class="fas fa-shopping-cart"></i>
        <h3>Anda belum memiliki pesanan</h3>
        <p>Mulai pesan makanan lezat dari menu kami!</p>
        <a href="order_food.php" class="action-btn btn-primary">
            <i class="fas fa-utensils"></i> Pesan Sekarang
        </a>
    </div>
    
    <?php else: ?>
    
    <!-- Search and Filter -->
    <div class="search-filter-container">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="orderSearch" placeholder="Cari pesanan..." onkeyup="searchOrders()">
        </div>
        <div class="filter-dropdown">
            <button class="filter-btn">
                <i class="fas fa-filter"></i> Filter
            </button>
            <div class="filter-content">
                <a href="?filter=all">Semua Pesanan</a>
                <a href="?filter=menunggu">Menunggu</a>
                <a href="?filter=diproses">Sedang Diproses</a>
                <a href="?filter=terkirim">Terkirim</a>
                <a href="?filter=dibatalkan">Dibatalkan</a>
            </div>
        </div>
    </div>
    
    <div class="orders-grid">
        <!-- Orders List -->
        <div class="orders-list">
            <div class="section-title">
                <i class="fas fa-history"></i> Riwayat Pesanan
            </div>
            
            <div id="ordersList">
                <?php foreach($orders as $order): ?>
                    <?php
                    // Get order items to calculate total
                    $stmt = $con->prepare("
                        SELECT io.*, m.menu_price, m.menu_name
                        FROM in_order io
                        JOIN menus m ON io.menu_id = m.menu_id
                        WHERE io.order_id = ?
                    ");
                    $stmt->execute([$order['order_id']]);
                    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $total = 0;
                    $item_names = [];
                    foreach($items as $item) {
                        $total += ($item['menu_price'] * $item['quantity']);
                        $item_names[] = $item['quantity'] . 'x ' . $item['menu_name'];
                    }
                    
                    // Status handling in the view
                    $status = $order['order_status'] ?? 'Menunggu';
                    $status_icon = 'clock';
                    switch($status) {
                        case 'Terkirim':
                            $status_icon = 'check-circle';
                            $status_class = 'status-delivered';
                            break;
                        case 'Sedang Diproses':
                            $status_icon = 'spinner';
                            $status_class = 'status-processing';
                            break;
                        case 'Dibatalkan':
                            $status_icon = 'times-circle';
                            $status_class = 'status-canceled';
                            break;
                        default: // Menunggu
                            $status_icon = 'clock';
                            $status_class = 'status-pending';
                            break;
                    }
                    
                    // Display status
                    echo '<span class="order-status '.$status_class.'">';
                    echo '<i class="fas fa-'.$status_icon.'"></i> '.$status;
                    echo '</span>';
                    ?>
                    <a href="my_orders.php?order_id=<?php echo $order['order_id']; ?>" class="text-decoration-none">
                        <div class="order-card <?php echo (isset($_GET['order_id']) && $_GET['order_id'] == $order['order_id']) ? 'active' : ''; ?>">
                            <div class="order-header">
                                <div class="order-id">Pesanan #<?php echo $order['order_id']; ?></div>
                                <div class="order-date"><?php echo date('d M Y, H:i', strtotime($order['order_time'])); ?></div>
                            </div>
                            
                            <div class="order-summary">
                                <?php echo implode(', ', array_slice($item_names, 0, 2)); ?>
                                <?php if(count($item_names) > 2): ?>
                                    , dan <?php echo count($item_names) - 2; ?> lainnya
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="order-status status-<?php echo strtolower($status); ?>">
                                    <i class="fas fa-<?php echo $status_icon; ?>"></i> <?php echo $status; ?>
                                </span>
                                <span class="order-total">Rp <?php echo number_format(($total * 1000) + 9000, 0, ',', '.'); ?></span>
                            </div>
                            
                            <!-- Payment Status -->
                            <?php if(isset($order['payment_method']) && in_array($order['payment_method'], ['Bank Transfer', 'GoPay', 'DANA'])): ?>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="order-status <?php echo $status_class; ?>" style="font-size: 10px;">
                                    <i class="fas fa-<?php echo $status_icon; ?>"></i> <?php echo $status; ?>
                                </span>
                                <?php if($status == 'Menunggu' && !isset($order['payment_proof'])): ?>
                                    <span class="badge rounded-pill bg-danger text-white px-3 py-2" style="font-size: 12px;">
                                        Belum Upload Bukti
                                        </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Order Details -->
        <div class="order-details">
            <?php if($selected_order): ?>
                <?php
                // Calculate total for selected order
                $total = 0;
                foreach($order_items as $item) {
                    $total += ($item['menu_price'] * $item['quantity']);
                }
                
                // Status handling in the view
                $status = $selected_order['order_status'] ?? 'Menunggu';
                $status_icon = 'clock';
                switch($status) {
                    case 'Terkirim':
                        $status_icon = 'check-circle';
                        $status_class = 'status-delivered';
                        break;
                    case 'Sedang Diproses':
                        $status_icon = 'spinner';
                        $status_class = 'status-processing';
                        break;
                    case 'Dibatalkan':
                        $status_icon = 'times-circle';
                        $status_class = 'status-canceled';
                        break;
                    default: // Menunggu
                        $status_icon = 'clock';
                        $status_class = 'status-pending';
                        break;
                }
                
                // Display status
                echo '<span class="order-status '.$status_class.'">';
                echo '<i class="fas fa-'.$status_icon.'"></i> '.$status;
                echo '</span>';
                
                // Payment status
                $payment_status = isset($selected_order['payment_status']) ? $selected_order['payment_status'] : 'pending';
                $payment_icon = 'money-bill-wave';
                $payment_text = 'Menunggu Pembayaran';
                $payment_status_class = 'status-pending';
                
                if($payment_status == 'sukses') {
                    $payment_icon = 'check-circle';
                    $payment_text = 'Pembayaran Sukses';
                    $payment_status_class = 'status-delivered';
                } elseif($payment_status == 'menunggu-verifikasi') {
                    $payment_icon = 'clock';
                    $payment_text = 'Menunggu Verifikasi';
                    $payment_status_class = 'status-pending';
                } elseif($payment_status == 'ditolak') {
                    $payment_icon = 'times-circle';
                    $payment_text = 'Pembayaran Ditolak';
                    $payment_status_class = 'status-canceled';
                }
                
                $payment_method = isset($selected_order['payment_method']) ? $selected_order['payment_method'] : 'Cash on Delivery';
                ?>
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> Detail Pesanan #<?php echo $selected_order['order_id']; ?>
                </div>
                
                <div class="detail-card">
                    <div class="detail-row">
                        <span class="detail-label">Status Pesanan:</span>
                        <span class="order-status status-<?php echo strtolower($status); ?>">
                            <i class="fas fa-<?php echo $status_icon; ?>"></i> <?php echo $status; ?>
                        </span>
                    </div>
                    
                    <?php if(in_array($payment_method, ['Bank Transfer', 'GoPay', 'DANA'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Status Pembayaran:</span>
                        <span class="order-status <?php echo $payment_status_class; ?>">
                            <i class="fas fa-<?php echo $payment_icon; ?>"></i> <?php echo $payment_text; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-row">
                        <span class="detail-label">Tanggal Pesanan:</span>
                        <span><?php echo date('d M Y, H:i', strtotime($selected_order['order_time'])); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Metode Pembayaran:</span>
                        <span>
                            <?php
                            $payment_icon = 'money-bill-wave';
                            $payment_text = 'Tunai Saat Pengiriman';
                            
                            if(isset($selected_order['payment_method'])) {
                                if($selected_order['payment_method'] == 'Bank Transfer') {
                                    $payment_icon = 'university';
                                    $payment_text = 'Transfer Bank';
                                } elseif($selected_order['payment_method'] == 'GoPay') {
                                    $payment_icon = 'wallet';
                                    $payment_text = 'GoPay';
                                } elseif($selected_order['payment_method'] == 'DANA') {
                                    $payment_icon = 'wallet';
                                    $payment_text = 'DANA';
                                }
                            }
                            ?>
                            <i class="fas fa-<?php echo $payment_icon; ?>"></i> <?php echo $payment_text; ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Alamat Pengiriman:</span>
                        <span><?php echo $selected_order['delivery_address']; ?></span>
                    </div>
                    
                    <?php if(isset($selected_order['order_notes']) && !empty($selected_order['order_notes'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Catatan Pesanan:</span>
                        <span><?php echo $selected_order['order_notes']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($selected_order['payment_notes']) && !empty($selected_order['payment_notes'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Catatan Pembayaran:</span>
                        <span><?php echo $selected_order['payment_notes']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Upload Bukti Pembayaran jika belum -->
                <?php if(in_array($payment_method, ['Bank Transfer', 'GoPay', 'DANA']) && 
                      ($payment_status == 'pending' || $payment_status == 'ditolak') && 
                      !isset($selected_order['payment_proof'])): ?>
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
                            <input type="hidden" name="order_id" value="<?php echo $selected_order['order_id']; ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Tampilkan Bukti Pembayaran -->
                <?php if(in_array($payment_method, ['Bank Transfer', 'GoPay', 'DANA']) && 
                      isset($selected_order['payment_proof']) && !empty($selected_order['payment_proof'])): ?>
                <div class="mt-4 card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Bukti Pembayaran</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $file_ext = pathinfo($selected_order['payment_proof'], PATHINFO_EXTENSION);
                        if(in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png'])):
                        ?>
                        <img src="<?php echo $selected_order['payment_proof']; ?>" alt="Bukti Pembayaran" class="img-fluid" style="max-height: 300px;">
                        <?php else: ?>
                        <a href="<?php echo $selected_order['payment_proof']; ?>" target="_blank" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Lihat Bukti Pembayaran
                        </a>
                        <?php endif; ?>
                        
                        <?php if($payment_status == 'ditolak'): ?>
                        <div class="alert alert-danger mt-3">
                            <p><i class="fas fa-exclamation-circle"></i> Bukti pembayaran Anda ditolak.</p>
                            <?php if(isset($selected_order['payment_notes']) && !empty($selected_order['payment_notes'])): ?>
                            <p><strong>Alasan:</strong> <?php echo $selected_order['payment_notes']; ?></p>
                            <?php endif; ?>
                            <p>Silakan upload bukti pembayaran yang valid.</p>
                        </div>
                        
                        <form action="upload_bukti.php" method="POST" enctype="multipart/form-data" class="mt-3">
                            <div class="mb-3">
                                <label for="bukti_transfer" class="form-label">Upload Bukti Pembayaran Baru</label>
                                <input type="file" class="form-control" id="bukti_transfer" name="bukti_transfer" required>
                                <small class="form-text text-muted">Format yang diterima: JPG, PNG, PDF. Maksimal 2MB.</small>
                            </div>
                            <input type="hidden" name="order_id" value="<?php echo $selected_order['order_id']; ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Bukti Pembayaran Baru
                            </button>
                        </form>
                        <?php elseif($payment_status == 'menunggu-verifikasi'): ?>
                        <div class="alert alert-warning mt-3">
                            <p><i class="fas fa-clock"></i> Bukti pembayaran Anda sedang diverifikasi oleh admin.</p>
                            <p>Mohon tunggu konfirmasi dari kami.</p>
                        </div>
                        <?php elseif($payment_status == 'sukses'): ?>
                        <div class="alert alert-success mt-3">
                            <p><i class="fas fa-check-circle"></i> Pembayaran Anda telah diverifikasi dan sukses.</p>
                            <p>Pesanan Anda segera diproses.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="order-items">
                    <div class="section-title">
                        <i class="fas fa-utensils"></i> Item Pesanan
                    </div>
                    
                    <?php foreach($order_items as $item): ?>
                    <div class="item-row">
                        <div class="item-info">
                            <div class="item-quantity"><?php echo $item['quantity']; ?></div>
                            <div class="item-details">
                                <div class="item-name"><?php echo $item['menu_name']; ?></div>
                                <?php if(isset($item['item_notes']) && !empty($item['item_notes'])): ?>
                                    <div class="item-notes"><?php echo $item['item_notes']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="item-price">
                            Rp <?php echo number_format($item['menu_price'] * 1000, 0, ',', '.'); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="detail-row mt-3">
                        <span class="detail-label">Ongkos Kirim:</span>
                        <span class="order-total">Rp <?php echo number_format(9000, 0, ',', '.'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total:</span>
                        <span class="order-total">Rp <?php echo number_format(($total * 1000) + 9000, 0, ',', '.'); ?></span>
                    </div>
                </div>
                
                <?php if(isset($selected_order['payment_method']) && $selected_order['payment_method'] == 'Bank Transfer' && $payment_status == 'pending'): ?>
                <div class="mt-4">
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-info-circle"></i> Instruksi Pembayaran:</strong><br>
                        Silakan transfer jumlah total ke rekening bank kami:<br>
                        <strong>Bank Mandiri - 1234567890 (Dapoer Minang)</strong><br>
                        Setelah melakukan pembayaran, harap upload bukti pembayaran Anda melalui form di atas.
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Actions Button -->
                <div class="mt-4 d-flex justify-content-between">
                    <?php if($status == 'Menunggu'): ?>
                    <button class="action-btn btn-outline" onclick="cancelOrder(<?php echo $selected_order['order_id']; ?>)">
                        <i class="fas fa-times"></i> Batalkan Pesanan
                    </button>
                    <?php endif; ?>
                    
                    <a href="#" class="support-link">
                        <i class="fas fa-headset"></i> Butuh Bantuan? Hubungi Kami
                    </a>
                </div>
                
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <p>Pilih pesanan untuk melihat detail</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php endif; ?>
    </div>
</div>

<!-- Add JavaScript for search functionality -->
<script>
    function searchOrders() {
        var input, filter, cards, i, txtValue;
        input = document.getElementById("orderSearch");
        filter = input.value.toUpperCase();
        cards = document.querySelectorAll("#ordersList .order-card");
        
        for (i = 0; i < cards.length; i++) {
            txtValue = cards[i].textContent || cards[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                cards[i].parentElement.style.display = "";
            } else {
                cards[i].parentElement.style.display = "none";
            }
        }
    }
    
    function cancelOrder(orderId) {
        if (confirm("Apakah Anda yakin ingin membatalkan pesanan ini?")) {
            // Here you would typically send an AJAX request to cancel the order
            alert("Fitur pembatalan pesanan akan segera tersedia!");
        }
    }
</script>

<?php include "Includes/templates/footer.php"; ?> 

<?php
// Flush the output buffer and send content to browser
ob_end_flush();
?> 