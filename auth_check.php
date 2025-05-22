<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Pages that require authentication
$protected_pages = ['profile.php'];

// Pages that should redirect if already logged in
$guest_only_pages = ['login.php', 'register.php'];

// Check protected pages (require login)
if (in_array($current_page, $protected_pages)) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
}

// Check guest-only pages (redirect if logged in)
if (in_array($current_page, $guest_only_pages)) {
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        header("location: profile.php");
        exit;
    }
}
?> 