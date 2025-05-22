<!-- PHP INCLUDES -->

<?php
    //Set page title
    $pageTitle = 'Table Reservation';

    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";

    // Cek apakah user sudah login
    $userData = null;
    if(isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $con->prepare("SELECT u.*, c.* FROM users u 
                              LEFT JOIN clients c ON u.email = c.client_email 
                              WHERE u.user_id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
?>
    
    <style type="text/css">
        .table_reservation_section
        {
            max-width: 1200px;
            margin: 50px auto;
            min-height: 500px;
        }

        .check_availability_submit
        {
            background: #ffc851;
            color: white;
            border-color: #ffc851;
            font-family: work sans,sans-serif;
        }
        .client_details_tab  .form-control
        {
            background-color: #fff;
            border-radius: 0;
            padding: 25px 10px;
            box-shadow: none;
            border: 2px solid #eee;
        }

        .client_details_tab  .form-control:focus 
        {
            border-color: #ffc851;
            box-shadow: none;
            outline: none;
        }
        .text_header
        {
            margin-bottom: 5px;
            font-size: 18px;
            font-weight: bold;
            line-height: 1.5;
            margin-top: 22px;
            text-transform: capitalize;
        }
        .layer
        {
            height: 100%;
    background: -moz-linear-gradient(top, rgba(45,45,45,0.4) 0%, rgba(45,45,45,0.9) 100%);
background: -webkit-linear-gradient(top, rgba(45,45,45,0.4) 0%, rgba(45,45,45,0.9) 100%);
background: linear-gradient(to bottom, rgba(45,45,45,0.4) 0%, rgba(45,45,45,0.9) 100%);
        }

        .table-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 20px;
            margin: 30px 0;
        }

        .table-item {
            position: relative;
            width: 100%;
            padding-bottom: 100%;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .table-item:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .table-item.occupied {
            background: #ff4444 !important;
            border-color: #ff4444 !important;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .table-item.occupied .table-number {
            color: white !important;
        }

        .table-item.occupied .table-seats .seat {
            background: #ff6666 !important;
            border-color: #ff6666 !important;
        }

        .table-item.selected {
            background: #ffc851 !important;
            border-color: #ffc851 !important;
            cursor: pointer;
        }

        .table-item.selected .table-number {
            color: white !important;
        }

        .table-item.selected .table-seats .seat {
            background: #fff !important;
            border-color: #fff !important;
        }

        .table-number {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            color: #333;
        }

        .table-seats {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .seat {
            position: absolute;
            width: 20%;
            height: 20%;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 50%;
        }

        .seat.front-left { top: 10%; left: 10%; }
        .seat.front-right { top: 10%; right: 10%; }
        .seat.back-left { bottom: 10%; left: 10%; }
        .seat.back-right { bottom: 10%; right: 10%; }

        .legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .available { background: #f8f9fa; border: 2px solid #dee2e6; }
        .selected-legend { background: #ffc851; border: 2px solid #ffc851; }
        .occupied-legend { background-color: #ff4444; border: 2px solid #ff4444; }

    </style>

    <!-- START ORDER FOOD SECTION -->

    <section style="
    background: url(Design/images/food_pic.jpg);
    background-position: center bottom;
    background-repeat: no-repeat;
    background-size: cover;">
        <div class="layer">
            <div style="text-align: center;padding: 15px;">
                <h1 style="font-size: 120px; color: white;font-family: 'Roboto'; font-weight: 100;
">Book a Table</h1>
            </div>
        </div>
        
    </section>

	<section class="table_reservation_section">

        <div class="container">
            <?php

            if(isset($_POST['submit_table_reservation_form']) && $_SERVER['REQUEST_METHOD'] === 'POST')
            {
                $selected_date = $_POST['selected_date'];
                $selected_time = $_POST['selected_time'];
                $desired_date = $selected_date." ".$selected_time;
                $end_time = date('Y-m-d H:i', strtotime($desired_date . ' +2 hours')); // Durasi 2 jam
                $number_of_guests = $_POST['number_of_guests'];
                $table_id = $_POST['table_id'];
                $client_full_name = test_input($_POST['client_full_name']);
                $client_phone_number = test_input($_POST['client_phone_number']);
                $client_email = test_input($_POST['client_email']);
                $special_requests = test_input($_POST['special_requests']);

                $con->beginTransaction();
                try
                {
                    // Cek apakah meja sudah dipesan
                    $stmt_check = $con->prepare("SELECT COUNT(*) as count 
                                               FROM reservations 
                                               WHERE table_id = ? 
                                               AND status IN ('pending', 'confirmed')
                                               AND (
                                                   (? BETWEEN selected_time AND end_time)
                                                   OR (selected_time BETWEEN ? AND ?)
                                                   OR (end_time BETWEEN ? AND ?)
                                                   OR (selected_time <= ? AND end_time >= ?)
                                               )");
                    $stmt_check->execute([
                        $table_id,
                        $desired_date,
                        $desired_date,
                        date('Y-m-d H:i', strtotime($desired_date . ' +2 hours')),
                        $desired_date,
                        date('Y-m-d H:i', strtotime($desired_date . ' +2 hours')),
                        $desired_date,
                        date('Y-m-d H:i', strtotime($desired_date . ' +2 hours'))
                    ]);
                    $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

                    if($result['count'] > 0) {
                        throw new Exception("Maaf, meja ini sudah dipesan untuk waktu tersebut. Silakan pilih meja atau waktu lain.");
                    }

                    // Cek apakah client sudah ada
                    $stmt = $con->prepare("SELECT client_id FROM clients WHERE client_email = ?");
                    $stmt->execute([$client_email]);
                    $client = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if($client) {
                        $client_id = $client['client_id'];
                    } else {
                        // Jika client belum ada, buat baru
                        $stmtClient = $con->prepare("INSERT INTO clients(client_name, client_phone, client_email) VALUES(?, ?, ?)");
                        $stmtClient->execute([$client_full_name, $client_phone_number, $client_email]);
                        $client_id = $con->lastInsertId();
                    }

                    // Buat reservasi
                    $stmt_reservation = $con->prepare("INSERT INTO reservations(
                        date_created, client_id, client_name, client_email, client_phone,
                        selected_time, end_time, nbr_guests, table_id, status, special_requests
                    ) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
                    
                    $stmt_reservation->execute([
                        Date("Y-m-d H:i"),
                        $client_id,
                        $client_full_name,
                        $client_email,
                        $client_phone_number,
                        $desired_date,
                        $end_time,
                        $number_of_guests,
                        $table_id,
                        $special_requests
                    ]);
                    
                    echo "<div class='alert alert-success'>Great! Your Reservation has been created successfully.</div>";
                    $con->commit();
                }
                catch(Exception $e)
                {
                    $con->rollBack();
                    echo "<div class = 'alert alert-danger'>"; 
                        echo $e->getMessage();
                    echo "</div>";
                }
            }

        ?>



            <div class="text_header">
                <span>
                    1. Select Date & Time
                </span>
            </div>
            <form method="POST" action="table-reservation.php" id="dateTimeForm">
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label for="reservation_date">Date</label>
                            <input type="date" min="<?php echo date('Y-m-d',strtotime("+1day")); ?>" 
                            value="<?php echo isset($_POST['reservation_date']) ? $_POST['reservation_date'] : date('Y-m-d',strtotime("+1day")); ?>"
                            class="form-control" name="reservation_date" id="reservation_date">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label for="reservation_time">Time</label>
                            <input type="time" value="<?php echo isset($_POST['reservation_time']) ? $_POST['reservation_time'] : date('H:i'); ?>" class="form-control" name="reservation_time" id="reservation_time">
                        </div>
                    </div> 
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label for="number_of_guests">How many people?</label>
                            <select class="form-control" name="number_of_guests" id="number_of_guests">
                                <option value="1" <?php echo isset($_POST['number_of_guests']) && $_POST['number_of_guests'] == 1 ? "selected" : ""; ?>>
                                    One person
                                </option>
                                <option value="2" <?php echo isset($_POST['number_of_guests']) && $_POST['number_of_guests'] == 2 ? "selected" : ""; ?>>Two people</option>
                                <option value="3" <?php echo isset($_POST['number_of_guests']) && $_POST['number_of_guests'] == 3 ? "selected" : ""; ?>>Three people</option>
                                <option value="4" <?php echo isset($_POST['number_of_guests']) && $_POST['number_of_guests'] == 4 ? "selected" : ""; ?>>Four people</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label for="check_availability" style="visibility: hidden;">Check Availability</label>
                            <input type="submit" class="form-control check_availability_submit" name="check_availability_submit" value="Check Availability">
                        </div>
                    </div>
                </div>
            </form>

            <!-- CHECKING AVAILABILITY OF TABLES -->

            <?php if(isset($_POST['check_availability_submit'])): ?>
                <div class="text_header">
                    <span>2. Select Your Table</span>
                </div>
                
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color available"></div>
                        <span>Available</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color selected-legend"></div>
                        <span>Selected</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color occupied-legend"></div>
                        <span>Occupied</span>
                    </div>
                </div>

                <div class="table-grid">
            <?php
                    $selected_date = $_POST['reservation_date'];
                    $selected_time = $_POST['reservation_time'];
                    $desired_date = $selected_date . " " . $selected_time;
                    
                    // Get occupied tables for the selected date and time
                    $stmt = $con->prepare("SELECT r.table_id, r.client_name, r.client_email, r.client_phone 
                                         FROM reservations r
                                         WHERE DATE(r.selected_time) = DATE(?) 
                                         AND TIME(r.selected_time) = TIME(?)
                                         AND r.status IN ('pending', 'confirmed')
                                         AND r.end_time > NOW()
                                         ORDER BY r.table_id");
                    $stmt->execute([$desired_date, $desired_date]);
                    $occupied_tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Debug: Tampilkan data meja yang terisi
                    error_log("Selected Date: " . $selected_date);
                    error_log("Selected Time: " . $selected_time);
                    error_log("Desired Date: " . $desired_date);
                    error_log("Occupied Tables: " . print_r($occupied_tables, true));
                    
                    // Generate 50 tables
                    for($i = 1; $i <= 50; $i++):
                        $is_occupied = false;
                        $reservation_details = null;
                        
                        // Cek apakah meja terisi pada tanggal yang dipilih
                        foreach($occupied_tables as $table) {
                            if($table['table_id'] == $i) {
                                $is_occupied = true;
                                $reservation_details = $table;
                                break;
                            }
                        }
                        
                        // Debug: Tampilkan status setiap meja
                        error_log("Table " . $i . " is occupied: " . ($is_occupied ? "Yes" : "No"));
                        
                        // Debug: Tampilkan detail reservasi jika meja terisi
                        if($is_occupied) {
                            error_log("Reservation details for table " . $i . ": " . print_r($reservation_details, true));
                        }
                    ?>
                        <div class="table-item <?php echo $is_occupied ? 'occupied' : ''; ?>" 
                             data-table-id="<?php echo $i; ?>"
                             <?php echo $is_occupied ? 'disabled' : ''; ?>
                             <?php if($is_occupied): ?>
                                title="Reserved by: <?php echo htmlspecialchars($reservation_details['client_name']); ?>&#10;Email: <?php echo htmlspecialchars($reservation_details['client_email']); ?>&#10;Phone: <?php echo htmlspecialchars($reservation_details['client_phone']); ?>&#10;Date: <?php echo $selected_date; ?>"
                             <?php endif; ?>>
                            <div class="table-number"><?php echo $i; ?></div>
                            <div class="table-seats">
                                <div class="seat front-left"></div>
                                <div class="seat front-right"></div>
                                <div class="seat back-left"></div>
                                <div class="seat back-right"></div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <form method="POST" action="table-reservation.php" id="reservationForm" style="display: none;">
                    <input type="hidden" name="selected_date" value="<?php echo $selected_date; ?>">
                    <input type="hidden" name="selected_time" value="<?php echo $selected_time; ?>">
                    <input type="hidden" name="number_of_guests" value="<?php echo $_POST['number_of_guests']; ?>">
                    <input type="hidden" name="table_id" id="selected_table_id">
                    
                            <div class="text_header">
                        <span>3. Client Details</span>
                            </div>
                    
                                <div class="client_details_tab">
                                    <div class="form-group colum-row row">
                                        <div class="col-sm-12">
                                <input type="text" name="client_full_name" id="client_full_name" 
                                       class="form-control" placeholder="Full name" required
                                       value="<?php echo isset($userData['full_name']) ? htmlspecialchars($userData['full_name']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                <input type="email" name="client_email" id="client_email" 
                                       class="form-control" placeholder="E-mail" required
                                       value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>">
                                        </div>
                                        <div class="col-sm-6">
                                <input type="text" name="client_phone_number" id="client_phone_number" 
                                       class="form-control" placeholder="Phone number" required
                                       value="<?php echo isset($userData['phone_number']) ? htmlspecialchars($userData['phone_number']) : ''; ?>">
                                            </div>
                                        </div>
                        <div class="form-group">
                            <textarea name="special_requests" id="special_requests" 
                                      class="form-control" placeholder="Special requests (optional)"
                                      rows="3"></textarea>
                        </div>
                                    </div>
                                    
                                <div class="form-group">
                                    <input type="submit" name="submit_table_reservation_form" class="btn btn-info" value="Make a Reservation">
                                </div>
                            </form>
            <?php endif; ?>
        </div>
    </section>

    <style type="text/css">
        .details_card
        {
            display: flex;
            align-items: center;
            margin: 150px 0px;
        }
        .details_card>span
        {
            float: left;
            font-size: 60px;
        }
        
        .details_card>div
        {
            float: left;
            font-size: 20px;
            margin-left: 20px;
            letter-spacing: 2px
        }
    </style>

    <section class="restaurant_details" style="background: url(Design/images/food_pic_2.jpg);
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: 50% 0%;
    background-size: cover;
    color:white !important;
    min-height: 300px;">
        <div class="layer">
            <div class="container">
            <div class="row">
            <div class="col-md-3 details_card">
                <span>30</span>
                <div>
                    Total 
                    <br>
                    Reservations
                </div>
            </div>
            <div class="col-md-3 details_card">
                <span>30</span>
                <div>
                    Total 
                    <br>
                    Menus
                </div>
            </div>
            <div class="col-md-3 details_card">
                <span>30</span>
                <div>
                    Years of 
                    <br>
                    Experience
                </div>
            </div>
            <div class="col-md-3 details_card">
                <span>30</span>
                <div>
                    Profesionnal 
                    <br>
                    Cook
                </div>
            </div>
        </div>
        </div>
         </div>
    </section>

    <!-- FOOTER BOTTOM  -->

    <?php include "Includes/templates/footer.php"; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableItems = document.querySelectorAll('.table-item');
        const reservationForm = document.getElementById('reservationForm');
        const selectedTableId = document.getElementById('selected_table_id');
        const reservationDate = document.getElementById('reservation_date');
        const reservationTime = document.getElementById('reservation_time');
        const checkAvailabilityBtn = document.getElementById('check_availability_submit');
        
        // Fungsi untuk menampilkan status meja
        function showTableStatus() {
            const selectedDate = reservationDate.value;
            const selectedTime = reservationTime.value;
            
            if (!selectedDate || !selectedTime) return;
            
            // Kirim request ke server untuk mendapatkan status meja
            fetch('check_table_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `reservation_date=${selectedDate}&reservation_time=${selectedTime}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Received data:', data);
                
                // Reset semua meja kecuali yang sudah dipilih
                tableItems.forEach(table => {
                    if (!table.classList.contains('selected')) {
                        table.classList.remove('occupied');
                        table.style.pointerEvents = 'auto';
                        table.style.opacity = '1';
                        table.style.backgroundColor = '#f8f9fa';
                        table.style.borderColor = '#dee2e6';
                    }
                });
                
                // Update status meja yang terisi
                if (data.occupied_tables && Array.isArray(data.occupied_tables)) {
                    data.occupied_tables.forEach(table => {
                        const tableElement = document.querySelector(`.table-item[data-table-id="${table.table_id}"]`);
                        if (tableElement && !tableElement.classList.contains('selected')) {
                            tableElement.classList.add('occupied');
                            tableElement.style.pointerEvents = 'none';
                            tableElement.style.opacity = '0.7';
                            tableElement.style.backgroundColor = '#ff4444';
                            tableElement.style.borderColor = '#ff4444';
                            tableElement.title = `Reserved by: ${table.client_name}\nEmail: ${table.client_email}\nPhone: ${table.client_phone}\nDate: ${selectedDate}`;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Event listener untuk perubahan tanggal dan waktu
        reservationDate.addEventListener('change', showTableStatus);
        reservationTime.addEventListener('change', showTableStatus);
        
        // Fungsi untuk memperbarui status meja secara real-time
        function updateTableStatus() {
            showTableStatus();
        }
        
        // Perbarui status meja setiap 2 detik
        setInterval(updateTableStatus, 2000);
        
        // Event listener untuk klik meja
        tableItems.forEach(table => {
            table.addEventListener('click', function() {
                // Skip jika meja sudah dipesan
                if(this.classList.contains('occupied')) {
                    alert('Meja ini sudah dipesan. Silakan pilih meja lain atau pilih waktu yang berbeda.');
                    return;
                }
                
                // Hapus class selected dari semua meja
                tableItems.forEach(t => {
                    t.classList.remove('selected');
                    t.style.backgroundColor = '#f8f9fa';
                    t.style.borderColor = '#dee2e6';
                });
                
                // Tambah class selected ke meja yang dipilih
                this.classList.add('selected');
                this.style.backgroundColor = '#ffc851';
                this.style.borderColor = '#ffc851';
                
                // Set nilai table_id
                selectedTableId.value = this.dataset.tableId;
                
                // Tampilkan form
                if (reservationForm) {
                    reservationForm.style.display = 'block';
                    reservationForm.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
        // Event listener untuk tombol Check Availability
        if (checkAvailabilityBtn) {
            checkAvailabilityBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showTableStatus();
            });
        }

        // Periksa apakah ada alert success (reservasi berhasil)
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            // Jika ada alert success, perbarui status meja
            showTableStatus();
        }

        // Jalankan showTableStatus saat halaman dimuat
        showTableStatus();
    });
    </script>
