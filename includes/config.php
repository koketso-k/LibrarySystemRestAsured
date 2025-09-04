<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session management
session_start();

// Base URL
define('BASE_URL', 'http://localhost/library-system/');

// Database configuration - USING YOUR PORT 4306
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '4306'); // Add this line for your MySQL port
?>