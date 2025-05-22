<?php
// Initialize the session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 