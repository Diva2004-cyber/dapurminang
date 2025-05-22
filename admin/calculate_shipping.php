<?php
//Start session
session_start();

//Set page title
$pageTitle = 'Hitung Ongkir';

//PHP INCLUDES
include 'connect.php';
include 'Includes/functions/functions.php'; 
include 'Includes/templates/header.php';

//TEST IF THE SESSION HAS BEEN CREATED BEFORE
if(isset($_SESSION['username_restaurant_qRewacvAqzA']) && isset($_SESSION['password_restaurant_qRewacvAqzA']))
{
    include 'Includes/templates/navbar.php';
    ?>

    <script type="text/javascript">
        var vertical_menu = document.getElementById("vertical-menu");
        var current = vertical_menu.getElementsByClassName("active_link");
        if(current.length > 0)
        {
            current[0].classList.remove("active_link");   
        }
        vertical_menu.getElementsByClassName('calculate_shipping_link')[0].className += " active_link";
    </script>

    <div class="card" style="margin: 20px 10px">
        <div class="card-header">
            <h3>Daftar Pesanan yang Perlu Dihitung Ongkir</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Alamat</th>
                            <th>Tanggal Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $con->prepare("SELECT o.*, c.client_name, c.client_email 
                                             FROM placed_orders o 
                                             JOIN clients c ON o.client_id = c.client_id 
                                             WHERE o.shipping_status = 'pending' 
                                             ORDER BY o.order_time DESC");
                        $stmt->execute();
                        $orders = $stmt->fetchAll();
                        
                        if(count($orders) == 0) {
                            echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada pesanan yang perlu dihitung ongkir</td></tr>";
                        } else {
                            foreach($orders as $order) {
                                echo "<tr>";
                                echo "<td>#" . $order['order_id'] . "</td>";
                                echo "<td>" . $order['client_name'] . "<br><small>" . $order['client_email'] . "</small></td>";
                                echo "<td>" . nl2br(htmlspecialchars($order['delivery_address'])) . "</td>";
                                echo "<td>" . $order['order_time'] . "</td>";
                                echo "<td><span class='badge bg-warning'>Menunggu Perhitungan</span></td>";
                                echo "<td>";
                                echo "<form method='post' action='process_shipping.php' style='display:inline;'>";
                                echo "<input type='hidden' name='order_id' value='" . $order['order_id'] . "'>";
                                echo "<input type='hidden' name='user_email' value='" . $order['client_email'] . "'>";
                                echo "<input type='hidden' name='shipping_cost' value='15000'>";
                                echo "<input type='hidden' name='shipping_notes' value=''>";
                                echo "<button type='submit' class='btn btn-success btn-sm'>Set Ongkir Rp 15.000</button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php
    include 'Includes/templates/footer.php';
}
else
{
    header("Location: index.php");
    exit();
}
?>