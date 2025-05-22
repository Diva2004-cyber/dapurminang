<?php
// Set page title
$pageTitle = 'Keranjang Belanja';

include "connect.php";
include 'Includes/functions/functions.php';
include "Includes/templates/header.php";
include "Includes/templates/navbar.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Get user data if logged in
$userData = null;
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $con->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Remove item from cart
    if ($action == 'remove' && isset($_GET['item'])) {
        $item_index = intval($_GET['item']);
        if (isset($_SESSION['cart'][$item_index])) {
            unset($_SESSION['cart'][$item_index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
        }
    }
    
    // Update quantity
    else if ($action == 'update' && isset($_POST['quantities'])) {
        $quantities = $_POST['quantities'];
        foreach ($quantities as $index => $qty) {
            if (isset($_SESSION['cart'][$index])) {
                $_SESSION['cart'][$index]['quantity'] = max(1, intval($qty));
            }
        }
    }
    
    // Clear cart
    else if ($action == 'clear') {
        $_SESSION['cart'] = array();
    }
    
    // Update special instructions
    else if ($action == 'update_instructions' && isset($_POST['instructions'])) {
        $instructions = $_POST['instructions'];
        foreach ($instructions as $index => $instruction) {
            if (isset($_SESSION['cart'][$index])) {
                $_SESSION['cart'][$index]['special_instructions'] = test_input($instruction);
            }
        }
    }
    
    // Redirect back to cart page to avoid form resubmission
    header("Location: cart.php");
    exit();
}

// Calculate cart totals
$subtotal = 0;
$delivery_fee = 10000; // Base delivery fee
$tax_rate = 0.1; // 10% tax
$tax = 0;
$total = 0;

foreach ($_SESSION['cart'] as $item) {
    $item_subtotal = $item['menu_price'] * $item['quantity'] * 1000;
    $subtotal += $item_subtotal;
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $tax + $delivery_fee;

// Handle checkout
if (isset($_POST['checkout'])) {
    // Check if cart is empty
    if (empty($_SESSION['cart'])) {
        $checkout_error = "Keranjang belanja Anda kosong. Tambahkan menu terlebih dahulu.";
    } 
    // Check if user is logged in
    else if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=cart.php");
        exit();
    } 
    // Process checkout
    else {
        // Client details
        $client_name = test_input($_POST['client_name']);
        $client_phone = test_input($_POST['client_phone']);
        $client_email = test_input($_POST['client_email']);
        $delivery_address = test_input($_POST['delivery_address']);
        $payment_method = test_input($_POST['payment_method']);
        $order_notes = test_input($_POST['order_notes']);
        
        // Validate inputs
        $error = false;
        
        if (empty($client_name)) {
            $checkout_error = "Nama lengkap tidak boleh kosong.";
            $error = true;
        } else if (empty($client_phone)) {
            $checkout_error = "Nomor telepon tidak boleh kosong.";
            $error = true;
        } else if (empty($client_email)) {
            $checkout_error = "Email tidak boleh kosong.";
            $error = true;
        } else if (empty($delivery_address)) {
            $checkout_error = "Alamat pengiriman tidak boleh kosong.";
            $error = true;
        }
        
        // Process order if no errors
        if (!$error) {
            $con->beginTransaction();
            try {
                // Insert or get client id
                $stmt = $con->prepare("SELECT client_id FROM clients WHERE client_email = ?");
                $stmt->execute(array($client_email));
                $client = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($client) {
                    $client_id = $client['client_id'];
                    
                    // Update client info
                    $stmt = $con->prepare("UPDATE clients SET client_name = ?, client_phone = ? WHERE client_id = ?");
                    $stmt->execute(array($client_name, $client_phone, $client_id));
                } else {
                    // Insert new client
                    $stmt = $con->prepare("INSERT INTO clients(client_name, client_phone, client_email) VALUES(?, ?, ?)");
                    $stmt->execute(array($client_name, $client_phone, $client_email));
                    $client_id = $con->lastInsertId();
                }
                
                // Insert order
                $stmt = $con->prepare("INSERT INTO placed_orders(order_time, client_id, delivery_address, order_notes, payment_method, subtotal, tax, delivery_fee, total) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute(array(
                    date("Y-m-d H:i:s"),
                    $client_id,
                    $delivery_address,
                    $order_notes,
                    $payment_method,
                    $subtotal / 1000, // Store in database without the 1000 multiplier
                    $tax / 1000,
                    $delivery_fee / 1000,
                    $total / 1000
                ));
                
                $order_id = $con->lastInsertId();
                
                // Insert order items
                foreach ($_SESSION['cart'] as $item) {
                    $stmt = $con->prepare("INSERT INTO in_order(order_id, menu_id, quantity, special_instructions) VALUES(?, ?, ?, ?)");
                    $stmt->execute(array(
                        $order_id,
                        $item['menu_id'],
                        $item['quantity'],
                        $item['special_instructions']
                    ));
                }
                
                $con->commit();
                
                // Clear cart
                $_SESSION['cart'] = array();
                
                // Redirect to success page
                header("Location: order-success.php?order_id=" . $order_id);
                exit();
                
            } catch (Exception $e) {
                $con->rollBack();
                $checkout_error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}
?>

<!-- CSS for Cart Page -->
<style>
    body {
        background-color: #f8f9fa;
    }
    
    .cart-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
    }
    
    .cart-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #333;
    }
    
    .cart-empty {
        text-align: center;
        padding: 50px 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    .cart-empty i {
        font-size: 50px;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .cart-empty h3 {
        font-size: 24px;
        color: #555;
        margin-bottom: 20px;
    }
    
    .cart-empty p {
        color: #777;
        margin-bottom: 30px;
    }
    
    .cart-items-container {
        background-color: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .cart-header {
        background-color: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .cart-items {
        padding: 20px;
    }
    
    .cart-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
    
    .cart-item-image {
        width: 80px;
        height: 80px;
        overflow: hidden;
        border-radius: 8px;
        margin-right: 20px;
    }
    
    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .cart-item-details {
        flex-grow: 1;
    }
    
    .cart-item-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .cart-item-price {
        font-size: 14px;
        color: #777;
    }
    
    .cart-quantity {
        display: flex;
        align-items: center;
        margin: 0 20px;
    }
    
    .cart-quantity-input {
        width: 50px;
        height: 36px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .cart-item-subtotal {
        font-size: 16px;
        font-weight: 600;
        color: #d4a017;
        width: 120px;
        text-align: right;
    }
    
    .cart-item-remove {
        color: #dc3545;
        margin-left: 15px;
        cursor: pointer;
        font-size: 20px;
    }
    
    .cart-item-instructions {
        margin-top: 10px;
        width: 100%;
    }
    
    .cart-item-instructions textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: none;
        font-size: 14px;
    }
    
    .cart-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }
    
    .cart-summary {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    .cart-summary-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        color: #555;
    }
    
    .summary-row.total {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        padding-top: 15px;
        margin-top: 15px;
        border-top: 1px solid #f0f0f0;
    }
    
    .checkout-form {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-top: 30px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    .form-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .payment-methods {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .payment-method {
        flex: 1 0 calc(50% - 15px);
        display: flex;
        align-items: center;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .payment-method.active {
        border-color: #d4a017;
        background-color: rgba(212, 160, 23, 0.05);
    }
    
    .payment-method-icon {
        font-size: 24px;
        margin-right: 15px;
        color: #555;
    }
    
    .payment-method.active .payment-method-icon {
        color: #d4a017;
    }
    
    .payment-method-details {
        flex-grow: 1;
    }
    
    .payment-method-title {
        font-weight: 600;
        margin-bottom: 3px;
    }
    
    .payment-method-description {
        font-size: 12px;
        color: #777;
    }
    
    .checkout-btn {
        background-color: #d4a017;
        color: white;
        border: none;
        padding: 15px 30px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
        margin-top: 20px;
    }
    
    .checkout-btn:hover {
        background-color: #b38613;
    }
    
    .checkout-btn:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
    }
    
    .cart-item-special {
        font-size: 13px;
        color: #666;
        margin-top: 5px;
        font-style: italic;
    }
    
    @media (max-width: 768px) {
        .cart-item {
            flex-wrap: wrap;
        }
        
        .cart-item-subtotal {
            width: auto;
            margin-top: 10px;
        }
        
        .cart-quantity {
            margin: 10px 0;
        }
        
        .cart-actions {
            flex-direction: column;
        }
        
        .cart-actions .btn {
            margin-bottom: 10px;
        }
        
        .payment-method {
            flex: 1 0 100%;
        }
    }
</style>

<div class="cart-container">
    <h1 class="cart-title">Keranjang Belanja</h1>
    
    <?php if (isset($checkout_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <?php echo $checkout_error; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>
    
    <?php if (empty($_SESSION['cart'])): ?>
    <!-- Empty Cart -->
    <div class="cart-empty">
        <i class="fas fa-shopping-cart"></i>
        <h3>Keranjang Belanja Anda Kosong</h3>
        <p>Silakan tambahkan beberapa menu lezat ke keranjang Anda.</p>
        <a href="order_food.php" class="btn btn-primary">Lihat Menu</a>
    </div>
    <?php else: ?>
    <!-- Cart with Items -->
    <div class="row">
        <div class="col-lg-8">
            <div class="cart-items-container">
                <div class="cart-header">
                    <h4 class="mb-0">Daftar Menu (<?php echo count($_SESSION['cart']); ?> item)</h4>
                </div>
                <div class="cart-items">
                    <form action="cart.php?action=update" method="post">
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <img src="admin/Uploads/images/<?php echo $item['menu_image']; ?>" alt="<?php echo $item['menu_name']; ?>" onerror="this.src='Design/images/logo-restouran.png'">
                            </div>
                            <div class="cart-item-details">
                                <h5 class="cart-item-title"><?php echo $item['menu_name']; ?></h5>
                                <div class="cart-item-price">Rp <?php echo number_format($item['menu_price']*1000, 0, ',', '.'); ?></div>
                                <?php if (!empty($item['special_instructions'])): ?>
                                <div class="cart-item-special">
                                    <i class="fas fa-utensils"></i> <?php echo $item['special_instructions']; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="cart-quantity">
                                <input type="number" name="quantities[<?php echo $index; ?>]" class="cart-quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="10">
                            </div>
                            <div class="cart-item-subtotal">
                                Rp <?php echo number_format($item['menu_price']*$item['quantity']*1000, 0, ',', '.'); ?>
                            </div>
                            <a href="cart.php?action=remove&item=<?php echo $index; ?>" class="cart-item-remove" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini dari keranjang?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="cart-actions">
                            <div>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-sync-alt"></i> Perbarui Keranjang
                                </button>
                                <a href="cart.php?action=clear" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin mengosongkan keranjang?');">
                                    <i class="fas fa-trash"></i> Kosongkan Keranjang
                                </a>
                            </div>
                            <a href="order_food.php" class="btn btn-outline-secondary">
                                <i class="fas fa-shopping-basket"></i> Lanjutkan Belanja
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Special Instructions Form -->
            <div class="cart-items-container">
                <div class="cart-header">
                    <h4 class="mb-0">Instruksi Khusus</h4>
                </div>
                <div class="cart-items">
                    <form action="cart.php?action=update_instructions" method="post">
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <div class="form-group">
                            <label for="instruction<?php echo $index; ?>"><?php echo $item['menu_name']; ?></label>
                            <textarea id="instruction<?php echo $index; ?>" name="instructions[<?php echo $index; ?>]" class="form-control" placeholder="Contoh: Tidak pedas, tanpa daun bawang, dll."><?php echo $item['special_instructions']; ?></textarea>
                        </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Instruksi
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3 class="cart-summary-title">Ringkasan Pesanan</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row">
                    <span>Pajak (10%)</span>
                    <span>Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row">
                    <span>Biaya Pengiriman</span>
                    <span>Rp <?php echo number_format($delivery_fee, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <!-- Checkout Form -->
            <div class="checkout-form">
                <h3 class="form-title">Informasi Pengiriman</h3>
                <form method="post" action="cart.php">
                    <div class="form-group">
                        <label for="client_name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo isset($userData['nama_lengkap']) ? htmlspecialchars($userData['nama_lengkap']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="client_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="client_email" name="client_email" value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="client_phone" class="form-label">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="client_phone" name="client_phone" value="<?php echo isset($userData['no_telp']) ? htmlspecialchars($userData['no_telp']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="delivery_address" class="form-label">Alamat Pengiriman</label>
                        <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required><?php echo isset($userData['alamat']) ? htmlspecialchars($userData['alamat']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="order_notes" class="form-label">Catatan Pesanan (Opsional)</label>
                        <textarea class="form-control" id="order_notes" name="order_notes" rows="2" placeholder="Instruksi tambahan untuk pesanan Anda"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Metode Pembayaran</label>
                        <div class="payment-methods">
                            <div class="payment-method active" onclick="selectPayment('cash')">
                                <input type="radio" name="payment_method" id="payment_cash" value="cash" checked style="display:none;">
                                <div class="payment-method-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="payment-method-details">
                                    <div class="payment-method-title">Tunai</div>
                                    <div class="payment-method-description">Bayar dengan uang tunai saat pengiriman</div>
                                </div>
                            </div>
                            
                            <div class="payment-method" onclick="selectPayment('transfer')">
                                <input type="radio" name="payment_method" id="payment_transfer" value="transfer" style="display:none;">
                                <div class="payment-method-icon">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div class="payment-method-details">
                                    <div class="payment-method-title">Transfer Bank</div>
                                    <div class="payment-method-description">Transfer ke rekening bank kami</div>
                                </div>
                            </div>
                            
                            <div class="payment-method" onclick="selectPayment('qris')">
                                <input type="radio" name="payment_method" id="payment_qris" value="qris" style="display:none;">
                                <div class="payment-method-icon">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <div class="payment-method-details">
                                    <div class="payment-method-title">QRIS</div>
                                    <div class="payment-method-description">Bayar dengan scan QRIS</div>
                                </div>
                            </div>
                            
                            <div class="payment-method" onclick="selectPayment('ewallet')">
                                <input type="radio" name="payment_method" id="payment_ewallet" value="ewallet" style="display:none;">
                                <div class="payment-method-icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div class="payment-method-details">
                                    <div class="payment-method-title">E-Wallet</div>
                                    <div class="payment-method-description">OVO, GoPay, DANA, dll</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="checkout" class="checkout-btn">
                        <i class="fas fa-check-circle"></i> Selesaikan Pesanan
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- WIDGET SECTION / FOOTER -->
<?php include "Includes/templates/footer.php"; ?>

<script>
    function selectPayment(method) {
        // Remove active class from all payment methods
        document.querySelectorAll('.payment-method').forEach(function(el) {
            el.classList.remove('active');
        });
        
        // Add active class to selected payment method
        document.querySelector('#payment_' + method).parentNode.classList.add('active');
        
        // Check the radio button
        document.querySelector('#payment_' + method).checked = true;
    }
</script> 