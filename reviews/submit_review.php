<?php
require_once '../connect.php';
require_once '../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $menu_id = $_POST['menu_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    // Validasi rating
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating harus antara 1-5']);
        exit;
    }
    
    // Cek apakah user sudah memesan menu ini
    $check_order = "SELECT o.id FROM orders o 
                    JOIN order_items oi ON o.id = oi.order_id 
                    WHERE o.user_id = ? AND oi.menu_id = ? AND o.status = 'completed'";
    $stmt = $conn->prepare($check_order);
    $stmt->bind_param("ii", $user_id, $menu_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Anda harus memesan menu ini terlebih dahulu']);
        exit;
    }
    
    // Insert review
    $sql = "INSERT INTO reviews (user_id, menu_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $user_id, $menu_id, $rating, $comment);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review berhasil disimpan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan review']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}
?> 