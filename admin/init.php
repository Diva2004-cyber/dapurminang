<?php
// Start session
session_start();

// Include database connection
include '../connect.php';

// Include functions file if exists
if(file_exists('../Includes/functions/functions.php')) {
    include '../Includes/functions/functions.php';
}

// Include admin header
include 'Includes/templates/header.php';

// Set default page title if not set
if(!isset($pageTitle)) {
    $pageTitle = 'Admin Dashboard';
}

// Check if admin is logged in
if(!isset($_SESSION['userid_restaurant_qRewacvAqzA'])) {
    // Not logged in, redirect to login page
    if(basename($_SERVER['PHP_SELF']) != 'login.php') {
        header('Location: index.php');
        exit();
    }
}

// Include admin navbar
include 'Includes/templates/navbar.php';
?> 