<?php
// Set page title
$pageTitle = 'Menu Detail';

include "connect.php";
include 'Includes/functions/functions.php';
include "Includes/templates/header.php";
include "Includes/templates/navbar.php";

// Get menu_id from URL
if (isset($_GET['menu_id'])) {
    $menu_id = intval($_GET['menu_id']);
    
    // Get menu details
    $stmt = $con->prepare("SELECT m.*, c.category_name 
                          FROM menus m 
                          JOIN menu_categories c ON m.category_id = c.category_id 
                          WHERE m.menu_id = ?");
    $stmt->execute(array($menu_id));
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If menu not found
    if (!$menu) {
        header("Location: index.php");
        exit();
    }
    
    // Get related items from same category
    $stmt = $con->prepare("SELECT * FROM menus 
                          WHERE category_id = ? AND menu_id != ? 
                          ORDER BY RAND() LIMIT 4");
    $stmt->execute(array($menu['category_id'], $menu_id));
    $related_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: index.php");
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

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    $special_instructions = test_input($_POST['special_instructions']);
    
    // Save in session cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Check if already in cart
    $found = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['menu_id'] == $menu_id) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = array(
            'menu_id' => $menu_id,
            'menu_name' => $menu['menu_name'],
            'menu_price' => $menu['menu_price'],
            'menu_image' => $menu['menu_image'],
            'quantity' => $quantity,
            'special_instructions' => $special_instructions
        );
    }
    
    // Redirect to cart page
    header("Location: cart.php");
    exit();
}
?>

<!-- CSS for Order Detail Page -->
<style>
    .order-detail-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        border-radius: 10px;
    }
    
    .menu-detail-header {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .menu-image-container {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        height: 400px;
    }
    
    .menu-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .category-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background-color: rgba(0,0,0,0.7);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .menu-detail-content {
        padding: 20px 0;
    }
    
    .menu-title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .menu-price {
        font-size: 24px;
        font-weight: 700;
        color: #d4a017;
        margin-bottom: 20px;
    }
    
    .menu-description {
        font-size: 16px;
        color: #555;
        line-height: 1.6;
        margin-bottom: 30px;
    }
    
    .menu-specs {
        display: flex;
        margin: 25px 0;
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
    }
    
    .menu-spec {
        flex: 1;
        text-align: center;
        padding: 10px;
    }
    
    .menu-spec:not(:last-child) {
        border-right: 1px solid #e0e0e0;
    }
    
    .menu-spec-label {
        font-size: 12px;
        color: #777;
        margin-bottom: 5px;
    }
    
    .menu-spec-value {
        font-size: 16px;
        font-weight: 600;
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .quantity-btn {
        width: 40px;
        height: 40px;
        background-color: #f0f0f0;
        border: none;
        border-radius: 50%;
        font-size: 18px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .quantity-btn:hover {
        background-color: #e0e0e0;
    }
    
    .quantity-value {
        width: 60px;
        height: 40px;
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        margin: 0 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .special-instructions {
        margin-bottom: 30px;
    }
    
    .special-instructions textarea {
        width: 100%;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        resize: none;
        min-height: 120px;
    }
    
    .add-to-cart-btn {
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
        margin-bottom: 20px;
    }
    
    .add-to-cart-btn:hover {
        background-color: #b38613;
    }
    
    .related-items {
        margin-top: 50px;
    }
    
    .related-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .related-item {
        padding: 15px;
    }
    
    .related-item-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        transition: all 0.3s;
        height: 100%;
    }
    
    .related-item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .related-item-image {
        height: 180px;
        overflow: hidden;
    }
    
    .related-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.5s;
    }
    
    .related-item-card:hover .related-item-image img {
        transform: scale(1.1);
    }
    
    .related-item-content {
        padding: 15px;
    }
    
    .related-item-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .related-item-price {
        font-size: 14px;
        color: #d4a017;
        font-weight: 600;
    }
    
    .related-item-btn {
        display: block;
        text-align: center;
        background-color: #f0f0f0;
        color: #333;
        padding: 8px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 10px;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .related-item-btn:hover {
        background-color: #d4a017;
        color: white;
    }
    
    .estimated-delivery {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .estimated-delivery i {
        font-size: 24px;
        margin-right: 15px;
        color: #d4a017;
    }
    
    .estimated-delivery-text {
        font-size: 14px;
        color: #555;
    }
    
    .estimated-delivery-time {
        font-weight: 600;
        color: #333;
    }
    
    .rating {
        display: flex;
        margin-bottom: 20px;
        align-items: center;
    }
    
    .rating-stars {
        color: #d4a017;
        font-size: 20px;
        margin-right: 10px;
    }
    
    .rating-count {
        color: #777;
        font-size: 14px;
    }
    
    @media (max-width: 991px) {
        .menu-image-container {
            height: 300px;
        }
        
        .menu-title {
            font-size: 28px;
        }
        
        .menu-spec {
            padding: 5px;
        }
    }
    
    @media (max-width: 767px) {
        .menu-image-container {
            height: 250px;
        }
        
        .menu-title {
            font-size: 24px;
        }
        
        .menu-specs {
            flex-wrap: wrap;
        }
        
        .menu-spec {
            flex: 1 0 50%;
        }
        
        .menu-spec:nth-child(2n) {
            border-right: none;
        }
    }
</style>

<div class="order-detail-container">
    <div class="row">
        <div class="col-md-7">
            <div class="menu-detail-header">
                <div class="menu-image-container">
                    <img src="admin/Uploads/images/<?php echo $menu['menu_image']; ?>" alt="<?php echo $menu['menu_name']; ?>" onerror="this.src='Design/images/logo-restouran.png'">
                    <div class="category-badge"><?php echo $menu['category_name']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="menu-detail-content">
                <h1 class="menu-title"><?php echo $menu['menu_name']; ?></h1>
                
                <div class="rating">
                    <div class="rating-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="rating-count">4.8 (120+ ratings)</div>
                </div>
                
                <h2 class="menu-price">Rp <?php echo number_format($menu['menu_price']*1000, 0, ',', '.'); ?></h2>
                
                <div class="menu-description">
                    <?php echo $menu['menu_description']; ?>
                </div>
                
                <div class="estimated-delivery">
                    <i class="fas fa-motorcycle"></i>
                    <div>
                        <div class="estimated-delivery-text">Estimasi waktu pengiriman</div>
                        <div class="estimated-delivery-time">25-40 menit</div>
                    </div>
                </div>
                
                <form method="post" action="order-detail.php?menu_id=<?php echo $menu_id; ?>">
                    <div class="quantity-selector">
                        <button type="button" class="quantity-btn" onclick="decreaseQuantity()">-</button>
                        <input type="number" class="quantity-value" id="quantity" name="quantity" value="1" min="1" max="10" readonly>
                        <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                    </div>
                    
                    <div class="special-instructions">
                        <h4>Instruksi Khusus</h4>
                        <textarea name="special_instructions" placeholder="Contoh: Tidak pedas, tanpa daun bawang, dll."></textarea>
                    </div>
                    
                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                        <i class="fas fa-shopping-cart"></i> Tambahkan ke Keranjang
                    </button>
                    
                    <a href="order_food.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-utensils"></i> Lihat Menu Lainnya
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="menu-specs">
                <div class="menu-spec">
                    <div class="menu-spec-label">Kategori</div>
                    <div class="menu-spec-value"><?php echo $menu['category_name']; ?></div>
                </div>
                <div class="menu-spec">
                    <div class="menu-spec-label">Porsi</div>
                    <div class="menu-spec-value">1 Orang</div>
                </div>
                <div class="menu-spec">
                    <div class="menu-spec-label">Kalori</div>
                    <div class="menu-spec-value">350 kkal</div>
                </div>
                <div class="menu-spec">
                    <div class="menu-spec-label">Waktu Penyajian</div>
                    <div class="menu-spec-value">15-20 menit</div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if(!empty($related_menus)): ?>
    <div class="related-items">
        <h3 class="related-title">Menu Serupa</h3>
        <div class="row">
            <?php foreach($related_menus as $related_menu): ?>
            <div class="col-md-3 col-sm-6 related-item">
                <div class="related-item-card">
                    <div class="related-item-image">
                        <img src="admin/Uploads/images/<?php echo $related_menu['menu_image']; ?>" alt="<?php echo $related_menu['menu_name']; ?>" onerror="this.src='Design/images/logo-restouran.png'">
                    </div>
                    <div class="related-item-content">
                        <h4 class="related-item-title"><?php echo $related_menu['menu_name']; ?></h4>
                        <div class="related-item-price">Rp <?php echo number_format($related_menu['menu_price']*1000, 0, ',', '.'); ?></div>
                        <a href="order-detail.php?menu_id=<?php echo $related_menu['menu_id']; ?>" class="related-item-btn">Lihat Detail</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- WIDGET SECTION / FOOTER -->
<?php include "Includes/templates/footer.php"; ?>

<script>
    function increaseQuantity() {
        var input = document.getElementById('quantity');
        var value = parseInt(input.value, 10);
        if (value < 10) {
            input.value = value + 1;
        }
    }
    
    function decreaseQuantity() {
        var input = document.getElementById('quantity');
        var value = parseInt(input.value, 10);
        if (value > 1) {
            input.value = value - 1;
        }
    }
</script> 