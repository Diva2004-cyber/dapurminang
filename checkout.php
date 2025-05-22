<?php
    // Start output buffering to prevent "headers already sent" errors
    ob_start();
    
    // Set page title
    $pageTitle = 'Checkout';

    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";
    
    // Redirect if cart is empty
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        header("Location: order_food.php");
        exit();
    }
    
    // Get user data if logged in
    $userData = null;
    if(isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $con->prepare("SELECT u.*, c.* FROM users u 
                              LEFT JOIN clients c ON u.email = c.client_email 
                              WHERE u.user_id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Inisialisasi ongkir tetap
    $shipping_cost = 9000;
    
    // Inisialisasi voucher dan potongan sebelum ringkasan order
    $voucher = isset($_SESSION['voucher']) ? $_SESSION['voucher'] : null;
    $potongan = 0;
    if($voucher) {
        if($voucher['discount_type'] == 'order_total') {
            $potongan = $voucher['type'] == 'fixed' ? $voucher['value'] : floor($total * $voucher['value'] / 100);
        } elseif($voucher['discount_type'] == 'shipping') {
            $potongan = $voucher['type'] == 'fixed' ? min($voucher['value'], $shipping_cost) : floor($shipping_cost * $voucher['value'] / 100);
        } elseif($voucher['discount_type'] == 'item' && $voucher['menu_id']) {
            foreach($_SESSION['cart'] as $item) {
                if($item['menu_id'] == $voucher['menu_id']) {
                    $item_total = $item['menu_price'] * $item['quantity'] * 1000;
                    $potongan = $voucher['type'] == 'fixed' ? min($voucher['value'], $item_total) : floor($item_total * $voucher['value'] / 100);
                    break;
                }
            }
        }
    }
    
    // Process order submission
    if(isset($_POST['place_order']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Client Details
        $client_full_name = test_input($_POST['client_full_name']);
        $delivery_address = test_input($_POST['client_delivery_address']);
        $client_phone_number = test_input($_POST['client_phone_number']);
        $client_email = test_input($_POST['client_email']);
        $payment_method = test_input($_POST['payment_method']);
        $notes = test_input($_POST['order_notes']);

        $order_success = false;
        $error_message = '';
        
        $con->beginTransaction();
        try {
            // Periksa dan tambahkan kolom order_notes jika belum ada
            $checkColumn = $con->prepare("SHOW COLUMNS FROM placed_orders LIKE 'order_notes'");
            $checkColumn->execute();
            
            if ($checkColumn->rowCount() == 0) {
                // Kolom belum ada, tambahkan kolom order_notes
                $alterTable = $con->prepare("ALTER TABLE placed_orders ADD COLUMN order_notes TEXT");
                $alterTable->execute();
            }
            
            // Periksa dan tambahkan kolom shipping_status jika belum ada
            $checkColumn = $con->prepare("SHOW COLUMNS FROM placed_orders LIKE 'shipping_status'");
            $checkColumn->execute();
            
            if ($checkColumn->rowCount() == 0) {
                // Kolom belum ada, tambahkan kolom shipping_status
                $alterTable = $con->prepare("ALTER TABLE placed_orders ADD COLUMN shipping_status VARCHAR(50) DEFAULT 'pending'");
                $alterTable->execute();
            }
            
            // Handle client info
            if(isset($_SESSION['user_id'])) {
                // For logged-in users, check if they exist in clients table
                $stmt = $con->prepare("SELECT client_id FROM clients WHERE client_email = ?");
                $stmt->execute([$client_email]);
                $client = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($client) {
                    $client_id = $client['client_id'];
                } else {
                    // If not in clients table, create new client record
                    $stmtClient = $con->prepare("INSERT INTO clients(client_name, client_phone, client_email) VALUES(?, ?, ?)");
                    $stmtClient->execute(array($client_full_name, $client_phone_number, $client_email));
                    $client_id = $con->lastInsertId();
                }
            } else {
                // For non-logged-in users, create new client record
                $stmtClient = $con->prepare("INSERT INTO clients(client_name, client_phone, client_email) VALUES(?, ?, ?)");
                $stmtClient->execute(array($client_full_name, $client_phone_number, $client_email));
                $client_id = $con->lastInsertId();
            }
            
            // Create order record
            $stmt_order = $con->prepare("INSERT INTO placed_orders(
                order_time, 
                client_id, 
                delivery_address, 
                payment_method, 
                order_notes,
                shipping_status
            ) VALUES(?, ?, ?, ?, ?, 'pending')");
            
            $stmt_order->execute(array(
                Date("Y-m-d H:i"),
                $client_id,
                $delivery_address,
                $payment_method,
                $notes
            ));
            
            $order_id = $con->lastInsertId();
            
            // Add all cart items to order
            $total_amount = 0;
            foreach($_SESSION['cart'] as $item) {
                $stmt = $con->prepare("INSERT INTO in_order(order_id, menu_id, quantity) VALUES(?, ?, ?)");
                $stmt->execute(array(
                    $order_id,
                    $item['menu_id'],
                    $item['quantity']
                ));
                
                $total_amount += ($item['menu_price'] * $item['quantity']);
            }
            
            // --- VOUCHER VALIDATION & CALCULATION ---
            $final_total = $total_amount;
            if($voucher && $voucher['discount_type'] == 'order_total') {
                $final_total = max(0, $total_amount - $potongan);
            }
            $update_total = $con->prepare("UPDATE placed_orders SET total_amount = ? WHERE order_id = ?");
            $update_total->execute(array($final_total, $order_id));
            
            // Update shipping_cost jika diskon ongkir
            if($voucher && $voucher['discount_type'] == 'shipping') {
                $final_shipping = max(0, $shipping_cost - $potongan);
                $update_shipping = $con->prepare("UPDATE placed_orders SET shipping_cost = ? WHERE order_id = ?");
                $update_shipping->execute(array($final_shipping, $order_id));
            }
            
            // Update voucher_discount
            $update_discount = $con->prepare("UPDATE placed_orders SET voucher_discount = ? WHERE order_id = ?");
            $update_discount->execute(array($potongan, $order_id));
            
            // Insert voucher usage & update used
            if($voucher) {
                $stmt = $con->prepare("INSERT INTO voucher_usages (user_id, voucher_id, order_id) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $voucher['id'], $order_id]);
                $stmt = $con->prepare("UPDATE vouchers SET used = used + 1 WHERE id = ?");
                $stmt->execute([$voucher['id']]);
                unset($_SESSION['voucher']);
            }
            
            $con->commit();
            $order_success = true;
            
            // Clear the cart after successful order
            $_SESSION['cart'] = array();
            
            // Redirect to waiting shipping page with order_id
            header("Location: waiting_shipping.php?order_id=" . $order_id);
            exit();
            
        } catch(Exception $e) {
            $con->rollBack();
            $error_message = "Error processing order: " . $e->getMessage();
        }
    }
?>

<style>
    .checkout-container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 0 15px;
    }
    
    .checkout-section {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
    }
    
    .customer-details {
        flex: 1;
        min-width: 300px;
    }
    
    .order-summary {
        flex: 1;
        min-width: 300px;
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 5px 0 rgba(60, 66, 87, 0.04), 0 0 10px 0 rgba(0, 0, 0, 0.04);
    }
    
    .section-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #333;
        border-bottom: 2px solid #9e8a78;
        padding-bottom: 10px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .item-details {
        display: flex;
    }
    
    .item-quantity {
        background: #9e8a78;
        color: white;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }
    
    .order-total {
        display: flex;
        justify-content: space-between;
        font-weight: bold;
        font-size: 18px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #eee;
    }
    
    .payment-methods {
        margin-top: 30px;
    }
    
    .payment-method {
        display: block;
        margin-bottom: 15px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .payment-method:hover, .payment-method.selected {
        border-color: #9e8a78;
        background-color: rgba(158, 138, 120, 0.1);
    }
    
    .place-order-btn {
        display: block;
        width: 100%;
        background: #4CAF50;
        color: white;
        border: none;
        padding: 15px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 20px;
    }
    
    @media (max-width: 768px) {
        .checkout-section {
            flex-direction: column;
        }
    }
    
    .shipping-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
        margin-top: 20px;
    }
    .shipping-details {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .shipping-details p {
        margin-bottom: 10px;
    }
    .shipping-status {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

<div class="checkout-container">
    <h1>Checkout</h1>
    
    <?php if(isset($error_message) && !empty($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <form method="post" action="checkout.php">
        <div class="checkout-section">
            <!-- CUSTOMER DETAILS -->
            <div class="customer-details">
                <div class="section-title">Shipping Details</div>
                
                <div class="form-group">
                    <label for="client_full_name">Full Name</label>
                    <input type="text" name="client_full_name" id="client_full_name" 
                        value="<?php echo isset($userData['full_name']) ? htmlspecialchars($userData['full_name']) : ''; ?>"
                        class="form-control" placeholder="Full name" required>
                </div>
                
                <div class="form-group">
                    <label for="client_email">Email</label>
                    <input type="email" name="client_email" id="client_email" 
                        value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>"
                        class="form-control" placeholder="Email address" required>
                </div>
                
                <div class="form-group">
                    <label for="client_phone_number">Phone Number</label>
                    <input type="text" name="client_phone_number" id="client_phone_number" 
                        value="<?php echo isset($userData['phone_number']) ? htmlspecialchars($userData['phone_number']) : ''; ?>"
                        class="form-control" placeholder="Phone number" required>
                </div>

                <div class="form-group">
                    <label for="client_city">City</label>
                    <input type="text" name="client_city" id="client_city" 
                        value="<?php echo isset($userData['kota']) ? htmlspecialchars($userData['kota']) : ''; ?>"
                        class="form-control" placeholder="City" required>
                </div>
                
                <div class="form-group">
                    <label for="client_delivery_address">Alamat Pengiriman Lengkap</label>
                    <textarea class="form-control" id="delivery_address" name="client_delivery_address" rows="3" required 
                        placeholder="Masukkan alamat lengkap pengiriman (termasuk RT/RW, Kecamatan, Kelurahan)"><?php echo isset($userData['alamat']) ? htmlspecialchars($userData['alamat']) : (isset($userData['delivery_address']) ? htmlspecialchars($userData['delivery_address']) : ''); ?></textarea>
                </div>
                
                <!-- Shipping Cost Information -->
                <div class="shipping-section mb-4">
                    <h4 class="mb-3">Informasi Ongkos Kirim</h4>
                    <div class="alert alert-info">
                        Ongkos kirim ke alamat Anda: <strong>Rp 9.000</strong> (flat untuk semua area)
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="order_notes">Order Notes (Optional)</label>
                    <textarea name="order_notes" id="order_notes" class="form-control" 
                        placeholder="Special instructions for delivery" rows="3"></textarea>
                </div>
                
                <!-- PAYMENT METHODS -->
                <div class="payment-methods">
                    <div class="section-title">Payment Method</div>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="Cash on Delivery" checked>
                        <strong>Cash on Delivery</strong>
                        <p class="mb-0">Pay with cash upon delivery</p>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="Bank Transfer">
                        <strong>Bank Transfer</strong>
                        <p class="mb-0">Make payment to our bank account, order will be processed after payment confirmation</p>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="GoPay">
                        <strong>GoPay</strong>
                        <div style="display: flex; align-items: center;">
                            <p class="mb-0" style="margin-right: 10px;">Pay using GoPay</p>
                            <img src="Design/images/gopay.png" alt="GoPay" style="height: 30px;">
                        </div>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="DANA">
                        <strong>DANA</strong>
                        <div style="display: flex; align-items: center;">
                            <p class="mb-0" style="margin-right: 10px;">Pay using DANA</p>
                            <img src="Design/images/dana.jpeg" alt="DANA" style="height: 30px;">
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- ORDER SUMMARY -->
            <div class="order-summary">
                <div class="section-title">Order Summary</div>
                
                <?php 
                    $total = 0;
                    foreach($_SESSION['cart'] as $item): 
                    $item_total = $item['menu_price'] * $item['quantity'] * 1000;
                    $total += $item_total;
                ?>
                <div class="order-item">
                    <div class="item-details">
                        <div class="item-quantity"><?php echo $item['quantity']; ?></div>
                        <div>
                            <div class="item-name"><?php echo $item['menu_name']; ?></div>
                            <?php if(!empty($item['notes'])): ?>
                                <div class="item-special-instructions text-muted">
                                    <small><?php echo $item['notes']; ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="item-price">
                        Rp <?php echo number_format($item_total, 0, ',', '.'); ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span class="subtotal-amount">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-item">
                    <span>Ongkos Kirim:</span>
                    <span class="shipping-amount">Rp <?php echo number_format($shipping_cost, 0, ',', '.'); ?></span>
                </div>
                <?php if($voucher && $potongan > 0): ?>
                    <div class="summary-item" style="color: #28a745;">
                        <span>Voucher (<?php echo htmlspecialchars($voucher['code']); ?>):</span>
                        <span>- Rp <?php echo number_format($potongan, 0, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-item total">
                    <span>Total:</span>
                    <span class="total-amount">
                        Rp <?php echo number_format(max(0, $total + $shipping_cost - $potongan), 0, ',', '.'); ?>
                    </span>
                </div>
                
                <button type="submit" name="place_order" class="place-order-btn">Place Order</button>
                
                <div class="mt-3 text-center">
                    <a href="order_food.php" class="text-decoration-none">← Return to order page</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Add selected class to payment method when clicked
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
</script>

<input type="hidden" name="shipping_cost" value="<?php echo $shipping_cost; ?>">

<?php include "Includes/templates/footer.php"; ?> 

<?php
// Flush the output buffer and send content to browser
ob_end_flush();
?> 