<?php
/**
 * logout.php
 * Handles user logout functionality
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create auth instance and logout
$auth = new Auth();
$auth->logout();

// Redirect to home page with success message
$_SESSION['message'] = 'You have been successfully logged out.';
header('Location: index.php');
exit();
?>