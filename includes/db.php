<?php

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access to this file is not permitted.');
}

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "spices_foods_db";

// MySQLi error reporting
mysqli_report(MYSQLI_REPORT_OFF);

// Database connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Character encoding
$conn->set_charset("utf8mb4");