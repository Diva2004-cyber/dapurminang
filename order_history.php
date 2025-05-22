<?php
// Include session.php at the very beginning
include "session.php";

// Include database connection and functions
include "connect.php";
include "Includes/functions/functions.php";

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Set page title
$pageTitle = "Order History";

// Get user's email from users table
$stmt = $con->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch();

if (!$user) {
    header("location: login.php");
    exit;
}

// Get client_id from clients table using email
$stmt = $con->prepare("SELECT client_id FROM clients WHERE client_email = ?");
$stmt->execute([$user['email']]);
$client = $stmt->fetch();

if (!$client) {
    // If client doesn't exist, create a new client record
    $stmt = $con->prepare("INSERT INTO clients (client_name, client_phone, client_email) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION["full_name"], "", $user['email']]);
    $client_id = $con->lastInsertId();
} else {
    $client_id = $client['client_id'];
}

// Get orders for this client
$stmt = $con->prepare("SELECT * FROM placed_orders WHERE client_id = ? ORDER BY order_time DESC");
$stmt->execute([$client_id]);
$orders = $stmt->fetchAll();

// Include header and navbar after all redirects
include "Includes/templates/header.php";
include "Includes/templates/navbar.php";
?>

<section class="order-history-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="order-history-card">
                    <div class="card-header">
                        <h2 class="section-title">
                            <i class="fas fa-history"></i> Riwayat Pesanan
                        </h2>
                        <p class="section-subtitle">Lihat semua pesanan yang telah Anda buat</p>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <h3>Belum Ada Pesanan</h3>
                                <p>Anda belum memiliki pesanan. Mulai pesan makanan favorit Anda sekarang!</p>
                                <a href="order_food.php" class="btn btn-primary">
                                    <i class="fas fa-utensils"></i> Pesan Sekarang
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID Pesanan</th>
                                            <th>Tanggal</th>
                                            <th>Alamat Pengiriman</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <span class="order-id">#<?php echo $order['order_id']; ?></span>
                                                </td>
                                                <td>
                                                    <div class="order-date">
                                                        <i class="far fa-calendar-alt"></i>
                                                        <?php echo date('d M Y', strtotime($order['order_time'])); ?>
                                                        <div class="order-time">
                                                            <?php echo date('H:i', strtotime($order['order_time'])); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="delivery-address">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        <?php echo htmlspecialchars($order['delivery_address']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($order['canceled']): ?>
                                                        <span class="status-badge status-canceled">
                                                            <i class="fas fa-times-circle"></i> Dibatalkan
                                                        </span>
                                                    <?php elseif ($order['delivered']): ?>
                                                        <span class="status-badge status-delivered">
                                                            <i class="fas fa-check-circle"></i> Selesai
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-processing">
                                                            <i class="fas fa-clock"></i> Dalam Proses
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="payment.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-action">
                                                        <i class="fas fa-eye"></i> Lihat Detail
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
        </div>
    </div>
</section>

<style>
/* Base styles */
* {
    box-sizing: border-box;
}

html, body {
    overflow-x: hidden;
    width: 100%;
    position: relative;
}

.order-history-section {
    padding: 80px 0;
    background-color: #f8f9fa;
    min-height: calc(100vh - 70px);
    width: 100%;
    overflow-x: hidden;
}

.order-history-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 30px;
    width: 100%;
}

.card-header {
    background: #ffffff;
    padding: 25px 30px;
    border-bottom: 1px solid #f0f0f0;
    width: 100%;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    word-break: break-word;
}

.section-title i {
    color: #4CAF50;
    margin-right: 10px;
    flex-shrink: 0;
}

.section-subtitle {
    color: #777;
    margin-bottom: 0;
    font-size: 1rem;
    word-break: break-word;
}

.card-body {
    padding: 30px;
    width: 100%;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
    width: 100%;
}

.empty-state-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 10px;
    word-break: break-word;
}

.empty-state p {
    color: #777;
    margin-bottom: 25px;
    word-break: break-word;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    margin-bottom: 0;
    width: 100%;
    min-width: 600px; /* Ensure table doesn't get too narrow */
}

.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #f0f0f0;
    color: #555;
    font-weight: 600;
    padding: 15px;
    font-size: 0.9rem;
    white-space: nowrap;
}

.table tbody td {
    padding: 20px 15px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
    word-break: break-word;
}

.order-id {
    font-weight: 600;
    color: #4CAF50;
    white-space: nowrap;
}

.order-date {
    display: flex;
    flex-direction: column;
    min-width: 100px;
}

.order-date i {
    color: #4CAF50;
    margin-right: 5px;
    flex-shrink: 0;
}

.order-time {
    font-size: 0.85rem;
    color: #777;
    margin-top: 3px;
}

.delivery-address {
    display: flex;
    align-items: flex-start;
    min-width: 150px;
}

.delivery-address i {
    color: #4CAF50;
    margin-right: 8px;
    margin-top: 3px;
    flex-shrink: 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    white-space: nowrap;
}

.status-badge i {
    margin-right: 5px;
    flex-shrink: 0;
}

.status-canceled {
    background-color: #ffe5e5;
    color: #e74c3c;
}

.status-delivered {
    background-color: #e5f7e5;
    color: #4CAF50;
}

.status-processing {
    background-color: #fff8e5;
    color: #f39c12;
}

.btn-action {
    background-color: #4CAF50;
    color: white;
    border-radius: 20px;
    padding: 6px 15px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
}

.btn-action:hover {
    background-color: #45a049;
    transform: translateY(-2px);
    color: white;
}

.btn-action i {
    margin-right: 5px;
    flex-shrink: 0;
}

/* Mobile Responsive Styles */
@media (max-width: 991px) {
    .order-history-section {
        padding: 60px 0;
    }
    
    .card-header {
        padding: 20px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
}

@media (max-width: 768px) {
    .order-history-section {
        padding: 50px 0;
    }
    
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .order-history-card {
        border-radius: 10px;
    }
    
    .card-header {
        padding: 15px;
    }
    
    .card-body {
        padding: 15px;
    }
    
    .section-title {
        font-size: 1.3rem;
    }
    
    .section-subtitle {
        font-size: 0.9rem;
    }
    
    .empty-state {
        padding: 30px 15px;
    }
    
    .empty-state-icon {
        font-size: 3rem;
    }
    
    .empty-state h3 {
        font-size: 1.3rem;
    }
    
    .empty-state p {
        font-size: 0.9rem;
    }
    
    .table thead th {
        padding: 10px;
        font-size: 0.8rem;
    }
    
    .table tbody td {
        padding: 12px 10px;
        font-size: 0.85rem;
    }
    
    .order-date {
        min-width: 90px;
    }
    
    .delivery-address {
        min-width: 120px;
    }
    
    .status-badge {
        padding: 4px 8px;
        font-size: 0.75rem;
    }
    
    .btn-action {
        padding: 4px 10px;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .order-history-section {
        padding: 40px 0;
    }
    
    .section-title {
        font-size: 1.2rem;
    }
    
    .section-subtitle {
        font-size: 0.8rem;
    }
    
    .empty-state-icon {
        font-size: 2.5rem;
    }
    
    .empty-state h3 {
        font-size: 1.2rem;
    }
    
    .empty-state p {
        font-size: 0.8rem;
    }
    
    .table thead th {
        padding: 8px;
        font-size: 0.75rem;
    }
    
    .table tbody td {
        padding: 10px 8px;
        font-size: 0.8rem;
    }
    
    .order-date {
        min-width: 80px;
    }
    
    .delivery-address {
        min-width: 100px;
    }
    
    .status-badge {
        padding: 3px 6px;
        font-size: 0.7rem;
    }
    
    .btn-action {
        padding: 3px 8px;
        font-size: 0.7rem;
    }
}
</style>

<?php include "Includes/templates/footer.php"; ?> 