<?php
// Include database connection
require("connect.php");
session_start();

// Check if we have data from an external provider
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['provider'])) {
    $provider = $_POST['provider'];
    
    // Generate a random password for users registering through social login
    function generateRandomPassword($length = 12) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        return substr(str_shuffle($chars), 0, $length);
    }
    
    // Process Facebook login
    if ($provider === 'facebook') {
        // Check if we have the necessary data
        if (isset($_POST['id']) && isset($_POST['email']) && isset($_POST['name'])) {
            $social_id = $_POST['id'];
            $email = $_POST['email'];
            $name = $_POST['name'];
            $username = 'fb_' . $social_id; // Create a unique username
            
            // Check if user exists with this email
            try {
                $stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // User exists, log them in
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["username"] = $user["username"];
                    header("Location: index.php");
                    exit();
                } else {
                    // User doesn't exist, register them
                    $password = generateRandomPassword();
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $con->prepare("INSERT INTO users (username, email, full_name, password) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $name, $hashed_password]);
                    
                    // Get the new user's ID and log them in
                    $user_id = $con->lastInsertId();
                    $_SESSION["user_id"] = $user_id;
                    $_SESSION["username"] = $username;
                    
                    header("Location: index.php");
                    exit();
                }
            } catch (PDOException $e) {
                die("Error processing Facebook login: " . $e->getMessage());
            }
        } else {
            die("Missing required data from Facebook");
        }
    }
    
    // Process Google login
    elseif ($provider === 'google') {
        // Check if we have the necessary data
        if (isset($_POST['sub']) && isset($_POST['email']) && isset($_POST['name'])) {
            $social_id = $_POST['sub'];
            $email = $_POST['email'];
            $name = $_POST['name'];
            $username = 'g_' . $social_id; // Create a unique username
            $picture = $_POST['picture'] ?? '';
            
            // Check if user exists with this email
            try {
                $stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // User exists, log them in
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["username"] = $user["username"];
                    header("Location: index.php");
                    exit();
                } else {
                    // User doesn't exist, register them
                    $password = generateRandomPassword();
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $con->prepare("INSERT INTO users (username, email, full_name, password) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $name, $hashed_password]);
                    
                    // Get the new user's ID and log them in
                    $user_id = $con->lastInsertId();
                    $_SESSION["user_id"] = $user_id;
                    $_SESSION["username"] = $username;
                    
                    header("Location: index.php");
                    exit();
                }
            } catch (PDOException $e) {
                die("Error processing Google login: " . $e->getMessage());
            }
        } else {
            die("Missing required data from Google");
        }
    }
    
    else {
        die("Unknown provider");
    }
} else {
    // Redirect to login page if accessed directly
    header("Location: login.php");
    exit();
}
?> 