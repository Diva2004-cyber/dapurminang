<?php
// Include database connection
include 'connect.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if menu_id is provided
if (!isset($_GET['menu_id']) || empty($_GET['menu_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Menu ID is required'
    ]);
    exit;
}

$menu_id = intval($_GET['menu_id']);

try {
    // Prepare and execute the query
    $stmt = $con->prepare("SELECT * FROM menus WHERE menu_id = ?");
    $stmt->execute(array($menu_id));
    
    if ($stmt->rowCount() > 0) {
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'menu' => $menu
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Menu not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 