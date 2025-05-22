<?php
    include "connect.php";
    
    if(isset($_POST['reservation_date']) && isset($_POST['reservation_time'])) {
        $selected_date = $_POST['reservation_date'];
        $selected_time = $_POST['reservation_time'];
        $desired_date = $selected_date . " " . $selected_time;
        
        // Debug: Tampilkan data yang diterima
        error_log("Received Date: " . $selected_date);
        error_log("Received Time: " . $selected_time);
        error_log("Desired Date: " . $desired_date);
        
        // Get occupied tables for the selected date and time
        $stmt = $con->prepare("SELECT r.table_id, c.client_name, c.client_email, c.client_phone 
                             FROM reservations r
                             JOIN clients c ON r.client_id = c.client_id
                             WHERE r.status IN ('pending', 'confirmed')
                             AND (
                                 (? BETWEEN r.selected_time AND r.end_time)
                                 OR (r.selected_time BETWEEN ? AND ?)
                                 OR (r.end_time BETWEEN ? AND ?)
                                 OR (r.selected_time <= ? AND r.end_time >= ?)
                             )
                             AND r.end_time > NOW()
                             AND r.selected_time <= DATE_ADD(?, INTERVAL 2 HOUR)
                             ORDER BY r.table_id");
        $stmt->execute([
            $desired_date,
            $desired_date,
            date('Y-m-d H:i', strtotime($desired_date . ' +2 hours')),
            $desired_date,
            date('Y-m-d H:i', strtotime($desired_date . ' +2 hours')),
            $desired_date,
            date('Y-m-d H:i', strtotime($desired_date . ' +2 hours')),
            $desired_date
        ]);
        $occupied_tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Tampilkan hasil query
        error_log("Query Result: " . print_r($occupied_tables, true));
        error_log("Desired Date: " . $desired_date);
        error_log("Current Time: " . date('Y-m-d H:i:s'));
        
        // Return data as JSON with additional status information
        header('Content-Type: application/json');
        echo json_encode([
            'occupied_tables' => $occupied_tables,
            'status' => 'success',
            'message' => count($occupied_tables) > 0 ? 'Meja sedang dipesan' : 'Meja tersedia',
            'debug' => [
                'desired_date' => $desired_date,
                'current_time' => date('Y-m-d H:i:s'),
                'occupied_count' => count($occupied_tables)
            ]
        ]);
    } else {
        // Return empty array if no data received
        header('Content-Type: application/json');
        echo json_encode(['occupied_tables' => []]);
    }
?> 