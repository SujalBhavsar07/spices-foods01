<?php
// Start session globally if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Change if necessary
define('DB_PASS', '');          // Change if necessary
define('DB_NAME', 'spices_foods_db');
define('DB_PORT', 3306);        // Default MySQL port

// Site Configuration
define('SITE_NAME', 'Spices Foods');
// Change BASE_URL to your exact subfolder path:
define('BASE_URL', 'http://localhost/Php_test/spices-foods/');

// Environment setting ('development' or 'production')
define('ENVIRONMENT', 'development');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}