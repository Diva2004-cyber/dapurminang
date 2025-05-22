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
    if (!isset($_POST['order_id'])) {
        header("location: index.php");
        exit;
    }

    include "connect.php";

    $order_id = $_POST['order_id'];

    // Verify that the order belongs to the logged-in user
    $stmt = $con->prepare("SELECT id FROM placed_orders WHERE id = ? AND user_id = ?");
    $stmt->execute(array($order_id, $_SESSION["user_id"]));
    $order = $stmt->fetch();

    if (!$order) {
        header("location: index.php");
        exit;
    }

    // Update order status to 'paid'
    $stmt = $con->prepare("UPDATE placed_orders SET status = 'paid' WHERE id = ?");
    $stmt->execute(array($order_id));

    // Redirect back to payment page with success message
    header("location: payment.php?order_id=" . $order_id . "&status=success");
    exit;
?> 