<?php
    //Start session
    session_start();

    //Set page title
    $pageTitle = 'Reservations';

    //PHP INCLUDES
    include 'connect.php';
    include 'Includes/functions/functions.php'; 
    include 'Includes/templates/header.php';

    // Cek apakah user sudah login
    if(!isset($_SESSION['username_restaurant_qRewacvAqzA']) && !isset($_SESSION['password_restaurant_qRewacvAqzA']))
    {
        header('Location: index.php');
        exit();
    }

    include 'Includes/templates/navbar.php';
?>

<div class="card">
    <div class="card-header">
        <?php echo $pageTitle; ?>
    </div>
    <div class="card-body">
        <!-- RESERVATIONS TABLE -->
        <table class="table table-bordered reservations-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Tanggal Reservasi</th>
                    <th scope="col">Waktu</th>
                    <th scope="col">Meja</th>
                    <th scope="col">Nama Client</th>
                    <th scope="col">Email</th>
                    <th scope="col">Telepon</th>
                    <th scope="col">Jumlah Tamu</th>
                    <th scope="col">Status</th>
                    <th scope="col">Permintaan Khusus</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $stmt = $con->prepare("SELECT r.* 
                                         FROM reservations r 
                                         ORDER BY r.selected_time DESC");
                    $stmt->execute();
                    $reservations = $stmt->fetchAll();

                    foreach($reservations as $reservation) {
                        echo "<tr>";
                        echo "<td>" . $reservation['reservation_id'] . "</td>";
                        echo "<td>" . date('d/m/Y', strtotime($reservation['selected_time'])) . "</td>";
                        echo "<td>" . date('H:i', strtotime($reservation['selected_time'])) . " - " . date('H:i', strtotime($reservation['end_time'])) . "</td>";
                        echo "<td>Meja " . $reservation['table_id'] . "</td>";
                        echo "<td>" . $reservation['client_name'] . "</td>";
                        echo "<td>" . $reservation['client_email'] . "</td>";
                        echo "<td>" . $reservation['client_phone'] . "</td>";
                        echo "<td>" . $reservation['nbr_guests'] . " orang</td>";
                        echo "<td>";
                        switch($reservation['status']) {
                            case 'pending':
                                echo "<span class='badge badge-warning'>Menunggu</span>";
                                break;
                            case 'confirmed':
                                echo "<span class='badge badge-success'>Dikonfirmasi</span>";
                                break;
                            case 'cancelled':
                                echo "<span class='badge badge-danger'>Dibatalkan</span>";
                                break;
                            case 'completed':
                                echo "<span class='badge badge-info'>Selesai</span>";
                                break;
                        }
                        echo "</td>";
                        echo "<td>" . ($reservation['special_requests'] ? $reservation['special_requests'] : '-') . "</td>";
                        echo "<td>";
                        echo "<div class='btn-group'>";
                        if($reservation['status'] == 'pending') {
                            echo "<a href='update_reservation.php?id=" . $reservation['reservation_id'] . "&status=confirmed' class='btn btn-success btn-sm'>Konfirmasi</a>";
                            echo "<a href='update_reservation.php?id=" . $reservation['reservation_id'] . "&status=cancelled' class='btn btn-danger btn-sm'>Batalkan</a>";
                        } else if($reservation['status'] == 'confirmed') {
                            echo "<a href='update_reservation.php?id=" . $reservation['reservation_id'] . "&status=completed' class='btn btn-info btn-sm'>Selesai</a>";
                        }
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
    include 'Includes/templates/footer.php';
?> 