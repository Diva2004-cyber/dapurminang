<?php
    // Start the session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Count items in cart
    $cart_count = 0;
    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
    }
    
    // Add navbar styles to head section
    ob_start();
?>
<style>
    .cart-icon {
        position: relative;
    }
    
    .cart-badge {
        position: absolute;
        top: -5px;
        right: -8px;
        background-color: #e74c3c;
        color: white;
        font-size: 10px;
        font-weight: bold;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<?php
    $navbar_styles = ob_get_clean();
    if (function_exists('add_to_head')) {
        add_to_head($navbar_styles);
    } else {
        echo $navbar_styles; // Fallback if the function doesn't exist
    }
?>
    
    <!-- START NAVBAR SECTION -->

    <header id="header" class="header-section">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="navbar-brand" style="margin-right: 20px;">
                    <img src="Design/images/logo-restouran.png" alt="Restaurant Logo" style="width: 42px;">
                </a>
                <div class="d-flex menu-wrap align-items-center">
                    <div class="mainmenu" id="mainmenu">
                        <ul class="nav">
                            <li><a href="index.php#home">HOME</a></li>
                            <li><a href="index.php#menus">MENUS</a></li>
                            <li><a href="index.php#gallery">GALLERY</a></li>
                            <li><a href="index.php#home">ABOUT</a></li>
                            <li><a href="index.php#contact">CONTACT</a></li>
                            <?php if(isset($_SESSION['user_id'])): ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> PROFIL
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="profile.php">Lihat Profil</a></li>
                                    <li><a href="my_orders.php">Pesanan Saya</a></li>
                                    <li><a href="my_reservations.php">Reservasi Meja Saya</a></li>
                                    <li><a href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                            <?php else: ?>
                            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> LOGIN</a></li>
                            <?php endif; ?>
                            <li>
                                <a href="order_food.php" class="cart-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                    <?php if($cart_count > 0): ?>
                                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                     </ul>
                    </div>
                    <div class="header-btn" style="margin-left:10px">
                        <a href="order_food.php" target="_blank" class="menu-btn">PESAN DISINI</a>
                    </div>
                    <div class="header-btn" style="margin-left:10px">
                        <a href="table-reservation.php" target="_blank" class="menu-btn">RESERVASI</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

	<div class="header-height" style="height: 120px;"></div>

    <!-- END NAVBAR SECTION -->