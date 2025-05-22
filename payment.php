<?php
    // Initialize the session before any output
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }

    // Check if order_id is provided
    if (!isset($_GET['order_id'])) {
        header("location: index.php");
        exit;
    }

    include "connect.php";
    include "Includes/functions/functions.php";
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";

    $order_id = $_GET['order_id'];
    $success_message = isset($_GET['status']) && $_GET['status'] === 'success' ? true : false;

    // Get order details
    $stmt = $con->prepare("SELECT * FROM placed_orders WHERE id = ? AND user_id = ?");
    $stmt->execute(array($order_id, $_SESSION["user_id"]));
    $order = $stmt->fetch();

    if (!$order) {
        header("location: index.php");
        exit;
    }

    // Get order items
    $stmt = $con->prepare("SELECT oi.*, m.name, m.price FROM order_items oi 
                          JOIN menus m ON oi.menu_id = m.id 
                          WHERE oi.order_id = ?");
    $stmt->execute(array($order_id));
    $items = $stmt->fetchAll();

    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
?>

<section class="payment-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Payment Details</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                Payment confirmed successfully! Your order is being processed.
                            </div>
                        <?php endif; ?>

                        <div class="order-summary mb-4">
                            <h5>Order Summary</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                                <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="payment-method mb-4">
                            <h5>Payment Method</h5>
                            <p class="mb-2">Selected: <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong></p>
                            
                            <?php if ($order['payment_method'] === 'Bank Transfer'): ?>
                                <div class="bank-details p-3 bg-light rounded">
                                    <p class="mb-1">Please transfer to:</p>
                                    <p class="mb-1">Bank: BCA</p>
                                    <p class="mb-1">Account Number: 1234567890</p>
                                    <p class="mb-1">Account Name: DAPOER MINANG</p>
                                </div>
                            <?php elseif ($order['payment_method'] === 'E-Wallet'): ?>
                                <div class="ewallet-details p-3 bg-light rounded">
                                    <p class="mb-1">Available E-Wallets:</p>
                                    <ul class="mb-0">
                                        <li>GoPay</li>
                                        <li>OVO</li>
                                        <li>DANA</li>
                                        <li>LinkAja</li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($order['status'] === 'pending' && $order['payment_method'] !== 'Cash on Delivery'): ?>
                            <form action="confirm_payment.php" method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <button type="submit" class="btn btn-primary">Confirm Payment</button>
                            </form>
                        <?php else: ?>
                            <div class="payment-status">
                                <p class="mb-0">Status: <span class="badge bg-<?php echo $order['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.payment-section {
    background-color: #f8f9fa;
}

.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.card-header {
    border-bottom: none;
}

.table th {
    border-top: none;
    background-color: #f8f9fa;
}

.bank-details, .ewallet-details {
    border: 1px solid #dee2e6;
}

.btn-primary {
    padding: 10px 20px;
}
</style>

<?php include "Includes/templates/footer.php"; ?> 