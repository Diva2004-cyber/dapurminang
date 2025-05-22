<?php
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

// Get order items
$stmt = $con->prepare("
    SELECT io.*, m.menu_name, m.menu_image
    FROM in_order io
    JOIN menus m ON io.menu_id = m.menu_id
    WHERE io.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format order date
$order_time = new DateTime($order['order_time']);
$formatted_date = $order_time->format('d F Y, H:i');

// Get estimated delivery time (30-45 minutes from order time)
$delivery_time = clone $order_time;
$delivery_time->add(new DateInterval('PT30M'));
$delivery_time_end = clone $order_time;
$delivery_time_end->add(new DateInterval('PT45M'));
$estimated_delivery = $delivery_time->format('H:i') . ' - ' . $delivery_time_end->format('H:i');
?>

<!-- CSS for Order Success Page -->
<style>
    body {
        background-color: #f8f9fa;
    }
    
    .success-container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 20px;
    }
    
    .success-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .success-icon {
        width: 100px;
        height: 100px;
        background-color: #28a745;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 50px;
    }
    
    .success-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #333;
    }
    
    .success-subtitle {
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .order-details {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .order-number {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .order-date {
        font-size: 14px;
        color: #888;
        font-weight: normal;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        color: #333;
    }
    
    .customer-info {
        display: flex;
        flex-wrap: wrap;
    }
    
    .info-group {
        flex: 1 0 50%;
        margin-bottom: 20px;
    }
    
    .info-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }
    
    .info-value {
        font-size: 16px;
        color: #333;
        font-weight: 500;
    }
    
    .order-items {
        margin-bottom: 30px;
    }
    
    .order-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        margin-right: 15px;
    }
    
    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .item-details {
        flex-grow: 1;
    }
    
    .item-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 3px;
    }
    
    .item-instructions {
        font-size: 13px;
        color: #666;
        font-style: italic;
    }
    
    .item-quantity {
        font-size: 14px;
        color: #666;
        margin-right: 20px;
    }
    
    .item-price {
        font-size: 16px;
        font-weight: 600;
        color: #d4a017;
        text-align: right;
        width: 100px;
    }
    
    .order-summary {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
        color: #555;
    }
    
    .summary-row.total {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        padding-top: 10px;
        margin-top: 10px;
        border-top: 1px solid #ddd;
    }
    
    .delivery-info {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .delivery-status {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
    }
    
    .delivery-time {
        text-align: center;
        flex: 1;
    }
    
    .delivery-time-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .delivery-time-value {
        font-size: 24px;
        font-weight: 700;
        color: #d4a017;
    }
    
    .delivery-address {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
    }
    
    .delivery-notes {
        margin-top: 20px;
        font-size: 14px;
        color: #666;
        font-style: italic;
    }
    
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 30px;
    }
    
    .action-button {
        padding: 15px 30px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
    }
    
    .action-button.primary {
        background-color: #d4a017;
        color: white;
    }
    
    .action-button.primary:hover {
        background-color: #b38613;
    }
    
    .action-button.secondary {
        background-color: #f8f9fa;
        color: #333;
        border: 1px solid #ddd;
    }
    
    .action-button.secondary:hover {
        background-color: #e9ecef;
    }
    
    .payment-info {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .payment-method {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .payment-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 24px;
        background-color: #f8f9fa;
    }
    
    .payment-details {
        flex-grow: 1;
    }
    
    .payment-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 3px;
    }
    
    .payment-status {
        font-size: 14px;
        color: #28a745;
    }
    
    @media (max-width: 768px) {
        .customer-info {
            flex-direction: column;
        }
        
        .info-group {
            flex: 1 0 100%;
        }
        
        .delivery-status {
            flex-direction: column;
            gap: 20px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .action-button {
            width: 100%;
        }
    }
</style>

<div class="success-container">
    <div class="success-header">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="success-title">Pesanan Berhasil!</h1>
        <p class="success-subtitle">Terima kasih atas pesanan Anda. Kami akan segera memproses pesanan Anda.</p>
    </div>
    
    <div class="order-details">
        <div class="order-number">
            <span>Pesanan #<?php echo $order_id; ?></span>
            <span class="order-date"><?php echo $formatted_date; ?></span>
        </div>
        
        <h3 class="section-title">Informasi Pelanggan</h3>
        <div class="customer-info">
            <div class="info-group">
                <div class="info-label">Nama</div>
                <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Email</div>
                <div class="info-value"><?php echo htmlspecialchars($order['client_email']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Nomor Telepon</div>
                <div class="info-value"><?php echo htmlspecialchars($order['client_phone']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Metode Pembayaran</div>
                <div class="info-value">
                    <?php 
                        switch($order['payment_method']) {
                            case 'cash':
                                echo 'Tunai';
                                break;
                            case 'transfer':
                                echo 'Transfer Bank';
                                break;
                            case 'qris':
                                echo 'QRIS';
                                break;
                            case 'ewallet':
                                echo 'E-Wallet';
                                break;
                            default:
                                echo ucfirst($order['payment_method']);
                        }
                    ?>
                </div>
            </div>
        </div>
        
        <h3 class="section-title">Detail Pesanan</h3>
        <div class="order-items">
            <?php foreach($order_items as $item): ?>
            <div class="order-item">
                <div class="item-image">
                    <img src="admin/Uploads/images/<?php echo $item['menu_image']; ?>" alt="<?php echo $item['menu_name']; ?>" onerror="this.src='Design/images/logo-restouran.png'">
                </div>
                <div class="item-details">
                    <div class="item-name"><?php echo htmlspecialchars($item['menu_name']); ?></div>
                    <?php if (!empty($item['special_instructions'])): ?>
                    <div class="item-instructions">
                        <i class="fas fa-utensils"></i> <?php echo htmlspecialchars($item['special_instructions']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="item-quantity">x<?php echo $item['quantity']; ?></div>
                <div class="item-price">Rp <?php echo number_format($item['quantity'] * $order['subtotal'] * 1000 / count($order_items), 0, ',', '.'); ?></div>
            </div>
            <?php endforeach; ?>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp <?php echo number_format($order['subtotal'] * 1000, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row">
                    <span>Pajak (10%)</span>
                    <span>Rp <?php echo number_format($order['tax'] * 1000, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row">
                    <span>Biaya Pengiriman</span>
                    <span>Rp <?php echo number_format($order['delivery_fee'] * 1000, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>Rp <?php echo number_format($order['total'] * 1000, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="delivery-info">
        <h3 class="section-title">Informasi Pengiriman</h3>
        
        <div class="delivery-status">
            <div class="delivery-time">
                <div class="delivery-time-label">Status Pesanan</div>
                <div class="delivery-time-value">
                    <i class="fas fa-tasks"></i> Diproses
                </div>
            </div>
            
            <div class="delivery-time">
                <div class="delivery-time-label">Estimasi Pengiriman</div>
                <div class="delivery-time-value">
                    <i class="fas fa-clock"></i> <?php echo $estimated_delivery; ?>
                </div>
            </div>
        </div>
        
        <div class="delivery-address">
            <div class="info-label">Alamat Pengiriman</div>
            <div class="info-value"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></div>
            
            <?php if (!empty($order['order_notes'])): ?>
            <div class="delivery-notes">
                <strong>Catatan:</strong> <?php echo htmlspecialchars($order['order_notes']); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="payment-info">
        <h3 class="section-title">Informasi Pembayaran</h3>
        
        <div class="payment-method">
            <?php 
            $paymentIcon = '';
            $paymentName = '';
            $paymentInstructions = '';
            
            switch($order['payment_method']) {
                case 'cash':
                    $paymentIcon = 'fa-money-bill-wave';
                    $paymentName = 'Pembayaran Tunai';
                    $paymentInstructions = 'Siapkan uang tunai untuk pembayaran saat pesanan diantar.';
                    break;
                case 'transfer':
                    $paymentIcon = 'fa-university';
                    $paymentName = 'Transfer Bank';
                    $paymentInstructions = 'Silakan transfer ke rekening bank kami:<br>Bank Mandiri<br>No. Rekening: 1234567890<br>Atas Nama: Dapoer Minang';
                    break;
                case 'qris':
                    $paymentIcon = 'fa-qrcode';
                    $paymentName = 'QRIS';
                    $paymentInstructions = 'Silakan gunakan QRIS di aplikasi e-wallet Anda untuk melakukan pembayaran.';
                    break;
                case 'ewallet':
                    $paymentIcon = 'fa-wallet';
                    $paymentName = 'E-Wallet';
                    $paymentInstructions = 'Silakan pilih e-wallet yang ingin Anda gunakan: OVO, GoPay, DANA, LinkAja, atau ShopeePay.';
                    break;
                default:
                    $paymentIcon = 'fa-credit-card';
                    $paymentName = ucfirst($order['payment_method']);
                    $paymentInstructions = 'Silakan melakukan pembayaran sesuai metode yang dipilih.';
            }
            ?>
            
            <div class="payment-icon">
                <i class="fas <?php echo $paymentIcon; ?>"></i>
            </div>
            <div class="payment-details">
                <div class="payment-name"><?php echo $paymentName; ?></div>
                <div class="payment-status">
                    <?php if ($order['payment_method'] == 'cash'): ?>
                    Bayar saat pesanan tiba
                    <?php else: ?>
                    Menunggu pembayaran
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="payment-instructions">
            <?php echo $paymentInstructions; ?>
        </div>
    </div>
    
    <div class="action-buttons">
        <a href="index.php" class="action-button primary">
            <i class="fas fa-home"></i> Kembali ke Beranda
        </a>
        <a href="#" onclick="window.print()" class="action-button secondary">
            <i class="fas fa-print"></i> Cetak Pesanan
        </a>
    </div>
</div>

<!-- WIDGET SECTION / FOOTER -->
<?php include "Includes/templates/footer.php"; ?>

<script>
    // Add print styles only when printing
    window.onbeforeprint = function() {
        var style = document.createElement('style');
        style.type = 'text/css';
        style.id = 'print-styles';
        style.innerHTML = `
            @media print {
                header, footer, .action-buttons, nav, .widget-section {
                    display: none !important;
                }
                
                body {
                    background-color: white !important;
                }
                
                .success-container {
                    max-width: 100%;
                    margin: 0;
                    padding: 0;
                }
                
                .success-header {
                    margin-bottom: 20px;
                }
                
                .success-icon {
                    width: 60px;
                    height: 60px;
                    font-size: 30px;
                    margin-bottom: 10px;
                }
                
                .order-details, .delivery-info, .payment-info {
                    box-shadow: none;
                    border: 1px solid #eee;
                    margin-bottom: 20px;
                    page-break-inside: avoid;
                }
            }
        `;
        document.head.appendChild(style);
    };
    
    window.onafterprint = function() {
        var printStyles = document.getElementById('print-styles');
        if (printStyles) {
            printStyles.remove();
        }
    };
</script> 