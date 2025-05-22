<!-- PHP INCLUDES -->
<?php
    // Start output buffering to prevent "headers already sent" errors
    ob_start();
    
    // Set page title
    $pageTitle = 'Order Food';

    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";
    
    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Anda harus login terlebih dahulu untuk melakukan pemesanan.";
        header("Location: login.php");
        exit();
    }
    
    // Get user data if logged in
    $userData = null;
    if(isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $con->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Initialize cart if not exists
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Handle cart actions
    if(isset($_POST['add_to_cart'])) {
        $menu_id = $_POST['menu_id'];
        $quantity = $_POST['quantity'];
        $notes = $_POST['notes'];
        
        // Check if item already exists in cart
        $item_exists = false;
        foreach($_SESSION['cart'] as $key => $item) {
            if($item['menu_id'] == $menu_id) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
                $item_exists = true;
                break;
            }
        }
        
        if(!$item_exists) {
            // Get menu details
            $stmt = $con->prepare("SELECT * FROM menus WHERE menu_id = ?");
            $stmt->execute(array($menu_id));
            $menu = $stmt->fetch();
            
            $_SESSION['cart'][] = array(
                'menu_id' => $menu_id,
                'menu_name' => $menu['menu_name'],
                'menu_price' => $menu['menu_price'],
                'menu_image' => $menu['menu_image'],
                'quantity' => $quantity,
                'notes' => $notes
            );
        }
        
        // Redirect to prevent form resubmission
        header("Location: order_food.php");
        exit();
    }

    // Remove from cart
    if(isset($_POST['remove_from_cart'])) {
        $index = $_POST['cart_index'];
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
        header("Location: order_food.php");
        exit();
    }

    // Update cart quantity
    if(isset($_POST['update_quantity'])) {
        $index = $_POST['cart_index'];
        $quantity = $_POST['quantity'];
        
        if($quantity <= 0) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
        } else {
            $_SESSION['cart'][$index]['quantity'] = $quantity;
        }
        
        header("Location: order_food.php");
        exit();
    }
    
    // Clear cart
    if(isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = array();
        header("Location: order_food.php");
        exit();
    }

    // Handle voucher input
    if(isset($_POST['apply_voucher'])) {
        $voucher_code = strtoupper(trim($_POST['voucher_code']));
        $stmt = $con->prepare("SELECT * FROM vouchers WHERE code = ? AND is_active = 1");
        $stmt->execute([$voucher_code]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $_SESSION['user_id'];
        $total_belanja = 0;
        foreach($_SESSION['cart'] as $item) {
            $total_belanja += $item['menu_price'] * $item['quantity'] * 1000;
        }
        $voucher_msg = '';
        if($voucher) {
            // Cek masa berlaku
            $now = date('Y-m-d H:i:s');
            if(($voucher['valid_from'] && $now < $voucher['valid_from']) || ($voucher['valid_until'] && $now > $voucher['valid_until'])) {
                $voucher_msg = '<span class="text-danger">Voucher belum/tidak berlaku.</span>';
            }
            // Cek minimal belanja
            elseif($voucher['min_order'] > 0 && $total_belanja < $voucher['min_order']) {
                $voucher_msg = '<span class="text-danger">Minimal belanja untuk voucher ini adalah Rp '.number_format($voucher['min_order'],0,',','.').'</span>';
            }
            // Cek kuota
            elseif($voucher['quota'] !== null && $voucher['used'] >= $voucher['quota']) {
                $voucher_msg = '<span class="text-danger">Kuota voucher sudah habis.</span>';
            }
            // Cek voucher personal
            elseif($voucher['user_id'] && $voucher['user_id'] != $user_id) {
                $voucher_msg = '<span class="text-danger">Voucher ini hanya untuk user tertentu.</span>';
            }
            // Cek sudah pernah pakai
            else {
                $stmt = $con->prepare("SELECT 1 FROM voucher_usages WHERE user_id = ? AND voucher_id = ?");
                $stmt->execute([$user_id, $voucher['id']]);
                if($stmt->fetch()) {
                    $voucher_msg = '<span class="text-danger">Anda sudah pernah menggunakan voucher ini.</span>';
                } else {
                    // Notifikasi expired/kuota hampir habis
                    $notif = '';
                    if($voucher['valid_until']) {
                        $exp = strtotime($voucher['valid_until']);
                        $now_time = time();
                        if($exp - $now_time < 86400) $notif .= '<br><span class="text-warning">Voucher akan segera berakhir!</span>';
                    }
                    if($voucher['quota'] !== null && $voucher['quota'] - $voucher['used'] <= 5) {
                        $notif .= '<br><span class="text-warning">Kuota voucher hampir habis!</span>';
                    }
                    $_SESSION['voucher'] = $voucher;
                    $voucher_msg = '<span class="text-success">Voucher berhasil diterapkan!</span>' . $notif;
                }
            }
        } else {
            unset($_SESSION['voucher']);
            $voucher_msg = '<span class="text-danger">Kode voucher tidak valid atau sudah tidak berlaku.</span>';
        }
    }
    if(isset($_POST['remove_voucher'])) {
        unset($_SESSION['voucher']);
    }
?>

<!-- ORDER FOOD PAGE STYLE -->
<style type="text/css">
    body {
        background: #f7f7f7;
    }

    .text_header {
        margin-bottom: 5px;
        font-size: 18px;
        font-weight: bold;
        line-height: 1.5;
        margin-top: 22px;
        text-transform: capitalize;
    }

    .items_tab {
        border-radius: 4px;
        background-color: white;
        overflow: hidden;
        box-shadow: 0 0 5px 0 rgba(60, 66, 87, 0.04), 0 0 10px 0 rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
    }

    .itemListElement {
        font-size: 14px;
        line-height: 1.29;
        border-bottom: solid 1px #e5e5e5;
        cursor: pointer;
        padding: 16px 12px 18px 12px;
        display: flex;
        align-items: center;
    }

    .item_details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    /* Gambar menu ditampilkan di sebelah kiri */
    .menu_image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 20px;
    }

    .menu_name {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .menu_description {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }

    .menu_price_field {
        display: flex;
        margin-left: 20px;
        align-items: baseline;
    }

    .item_label {
        color: #fff;
        border-color: #9e8a78;
        background: #9e8a78;
        font-size: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    
    .item_label:hover {
        background: #876f5d;
    }

    .select_item_bttn {
        width: 55px;
        display: flex;
        margin-left: 30px;
        justify-content: flex-end;
    }

    .order_food_section {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0px 15px;
        display: flex;
        flex-wrap: wrap;
    }
    
    .menu_container {
        flex: 2;
        margin-right: 20px;
    }
    
    .cart_container {
        flex: 1;
        background: white;
        border-radius: 5px;
        box-shadow: 0 0 5px 0 rgba(60, 66, 87, 0.04), 0 0 10px 0 rgba(0, 0, 0, 0.04);
        padding: 15px;
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        margin-top: 10px;
    }
    
    .quantity-selector button {
        width: 30px;
        height: 30px;
        background: #9e8a78;
        color: white;
        border: none;
        border-radius: 3px;
        font-size: 16px;
        cursor: pointer;
    }
    
    .quantity-selector input {
        width: 40px;
        height: 30px;
        text-align: center;
        margin: 0 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    
    .cart-item {
        display: flex;
        border-bottom: 1px solid #eee;
        padding: 15px 0;
    }
    
    .cart-item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }
    
    .cart-item-details {
        flex: 1;
        padding-left: 15px;
    }
    
    .cart-item-name {
        font-weight: bold;
    }
    
    .cart-item-notes {
        font-size: 12px;
        color: #666;
        font-style: italic;
    }
    
    .cart-item-price {
        text-align: right;
    }
    
    .cart-total {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #eee;
        font-weight: bold;
    }
    
    .checkout-btn {
        width: 100%;
        padding: 12px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 15px;
    }
    
    .menu-modal .modal-body {
        display: flex;
        flex-direction: column;
    }
    
    .menu-modal-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    
    .item-notes {
        margin-top: 15px;
    }
    
    .empty-cart {
        text-align: center;
        padding: 20px;
        color: #666;
    }
    
    @media (max-width: 768px) {
        .order_food_section {
            flex-direction: column;
        }
        
        .menu_container {
            margin-right: 0;
            margin-bottom: 20px;
        }
    }
</style>

<!-- START ORDER FOOD SECTION -->
<section class="order_food_section">
    <!-- MENU ITEMS SECTION -->
    <div class="menu_container">
        <?php
            $stmt = $con->prepare("SELECT * FROM menu_categories");
            $stmt->execute();
            $menu_categories = $stmt->fetchAll();

            foreach($menu_categories as $category) {
        ?>
            <div class="text_header">
                <span><?php echo $category['category_name']; ?></span>
            </div>
            <div class="items_tab">
            <?php
                $stmt = $con->prepare("SELECT * FROM menus WHERE category_id = ?");
                $stmt->execute(array($category['category_id']));
                $rows = $stmt->fetchAll();

                foreach($rows as $row) {
            ?>
                <div class="itemListElement" data-toggle="modal" data-target="#menuModal<?php echo $row['menu_id']; ?>">
                    <div class="item_details">
                        <!-- Menu Image -->
                        <div>
                            <?php if (!empty($row['menu_image'])): ?>
                                <img src="admin/Uploads/images/<?php echo $row['menu_image']; ?>" alt="<?php echo $row['menu_name']; ?>" class="menu_image">
                            <?php else: ?>
                                <img src="path_to_default_image/default.jpg" alt="Default image" class="menu_image">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Menu Info -->
                        <div style="flex: 1;">
                            <div class="menu_name"><?php echo $row['menu_name']; ?></div>
                            <div class="menu_description"><?php echo $row['menu_description']; ?></div>
                            <div style="font-weight: bold; color: #9e8a78;">
                                Rp <?php echo number_format($row['menu_price']*1000, 0, ',', '.'); ?>
                            </div>
                        </div>
                        
                        <!-- Add Button -->
                        <div>
                            <button class="btn item_label">Add to Cart</button>
                        </div>
                    </div>
                </div>
                
                <!-- Menu Detail Modal -->
                <div class="modal fade menu-modal" id="menuModal<?php echo $row['menu_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="menuModalLabel<?php echo $row['menu_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="menuModalLabel<?php echo $row['menu_id']; ?>"><?php echo $row['menu_name']; ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($row['menu_image'])): ?>
                                    <img src="admin/Uploads/images/<?php echo $row['menu_image']; ?>" alt="<?php echo $row['menu_name']; ?>" class="menu-modal-image">
                                <?php endif; ?>
                                
                                <p><?php echo $row['menu_description']; ?></p>
                                <p style="font-weight: bold; color: #9e8a78;">Rp <?php echo number_format($row['menu_price']*1000, 0, ',', '.'); ?></p>
                                
                                <form method="post" action="order_food.php">
                                    <input type="hidden" name="menu_id" value="<?php echo $row['menu_id']; ?>">
                                    
                                    <div class="quantity-selector">
                                        <button type="button" onclick="decrementQuantity(this)">-</button>
                                        <input type="number" name="quantity" value="1" min="1" max="10">
                                        <button type="button" onclick="incrementQuantity(this)">+</button>
                                    </div>
                                    
                                    <div class="item-notes">
                                        <label for="notes<?php echo $row['menu_id']; ?>">Special Instructions:</label>
                                        <textarea class="form-control" id="notes<?php echo $row['menu_id']; ?>" name="notes" placeholder="Add note (optional)"></textarea>
                                    </div>
                                    
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-block mt-3">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                }
            ?>
            </div>
        <?php
            }
        ?>
    </div>
    
    <!-- CART SECTION -->
    <div class="cart_container">
        <div class="text_header">Your Order</div>
        
        <?php if(empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <p>Add items to get started!</p>
            </div>
        <?php else: ?>
            <!-- Voucher Input -->
            <form method="post" class="mb-2">
                <div class="input-group">
                    <input type="text" name="voucher_code" class="form-control" placeholder="Kode Voucher" value="<?php echo isset($_SESSION['voucher']['code']) ? htmlspecialchars($_SESSION['voucher']['code']) : ''; ?>" <?php echo isset($_SESSION['voucher']) ? 'readonly' : ''; ?>>
                    <div class="input-group-append">
                        <?php if(isset($_SESSION['voucher'])): ?>
                            <button type="submit" name="remove_voucher" class="btn btn-danger">Hapus</button>
                        <?php else: ?>
                            <button type="submit" name="apply_voucher" class="btn btn-primary">Gunakan</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if(isset($voucher_msg)) echo '<div class="mt-1">'.$voucher_msg.'</div>'; ?>
            </form>
            <!-- Cart Items -->
            <?php 
                $total = 0;
                foreach($_SESSION['cart'] as $index => $item): 
                $item_total = $item['menu_price'] * $item['quantity'];
                $total += $item_total;
            ?>
                <div class="cart-item">
                    <div>
                        <?php if (!empty($item['menu_image'])): ?>
                            <img src="admin/Uploads/images/<?php echo $item['menu_image']; ?>" alt="<?php echo $item['menu_name']; ?>" class="cart-item-image">
                        <?php endif; ?>
                    </div>
                    
                    <div class="cart-item-details">
                        <div class="cart-item-name"><?php echo $item['menu_name']; ?></div>
                        <div class="cart-item-notes"><?php echo !empty($item['notes']) ? $item['notes'] : ''; ?></div>
                        
                        <form method="post" action="order_food.php" style="display: flex; align-items: center; margin-top: 5px;">
                            <input type="hidden" name="cart_index" value="<?php echo $index; ?>">
                            <div class="quantity-selector" style="margin-top: 0;">
                                <button type="button" onclick="decrementQuantityCart(this)">-</button>
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="10" style="width: 35px;">
                                <button type="button" onclick="incrementQuantityCart(this)">+</button>
                            </div>
                            <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-secondary ml-2">Update</button>
                            <button type="submit" name="remove_from_cart" class="btn btn-sm btn-outline-danger ml-2">Remove</button>
                        </form>
                    </div>
                    
                    <div class="cart-item-price">
                        Rp <?php echo number_format($item['menu_price'] * $item['quantity'] * 1000, 0, ',', '.'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Cart Total -->
            <div class="cart-total">
                <span>Total:</span>
                <span>Rp <?php echo number_format($total * 1000, 0, ',', '.'); ?></span>
            </div>
            
            <?php if(isset($_SESSION['voucher'])): 
                $voucher = $_SESSION['voucher'];
                $potongan = $voucher['type'] == 'fixed' ? $voucher['value'] : floor(($total * 1000) * $voucher['value'] / 100);
            ?>
            <div class="cart-total" style="color: #28a745;">
                <span>Voucher (<?php echo htmlspecialchars($voucher['code']); ?>):</span>
                <span>- Rp <?php echo number_format($potongan, 0, ',', '.'); ?></span>
            </div>
            <div class="cart-total" style="font-size:18px;">
                <span><b>Total Setelah Voucher:</b></span>
                <span><b>Rp <?php echo number_format(max(0, ($total * 1000) - $potongan), 0, ',', '.'); ?></b></span>
            </div>
            <?php endif; ?>
            
            <!-- Checkout Button -->
            <button class="checkout-btn" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
            
            <!-- Clear Cart -->
            <form method="post" action="order_food.php" class="mt-3 text-center">
                <button type="submit" name="clear_cart" class="btn btn-sm btn-outline-danger">Clear Cart</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<!-- END ORDER FOOD SECTION -->
</section>

<!-- JAVASCRIPT -->
<script>
    function incrementQuantity(button) {
        var input = button.previousElementSibling;
        var value = parseInt(input.value, 10);
        if(value < 10) {
            input.value = value + 1;
        }
    }
    
    function decrementQuantity(button) {
        var input = button.nextElementSibling;
        var value = parseInt(input.value, 10);
        if(value > 1) {
            input.value = value - 1;
        }
    }
    
    function incrementQuantityCart(button) {
        var input = button.previousElementSibling;
        var value = parseInt(input.value, 10);
        if(value < 10) {
            input.value = value + 1;
        }
    }
    
    function decrementQuantityCart(button) {
        var input = button.nextElementSibling;
        var value = parseInt(input.value, 10);
        if(value > 1) {
            input.value = value - 1;
        }
    }
</script>

<?php include "Includes/templates/footer.php"; ?>

<?php
// Flush the output buffer and send content to browser
ob_end_flush();
?>
