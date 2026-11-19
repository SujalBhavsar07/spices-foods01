<?php
// Prevent direct access to this file from the browser
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access to this file is not permitted.');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the logged-in user is an admin.
 */
if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn(): bool {
        return !empty($_SESSION['admin_id']);
    }
}

/**
 * Check if a regular user/customer is logged in.
 */
if (!function_exists('isUserLoggedIn')) {
    function isUserLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }
}

/**
 * Require normal user login or redirect to login page.
 */
if (!function_exists('requireUserLogin')) {
    function requireUserLogin(): void {
        if (!isUserLoggedIn()) {
            $_SESSION['error_msg'] = "Please log in to continue.";
            redirect('login.php');
        }
    }
}

/**
 * Require admin login or redirect to admin login page.
 */
if (!function_exists('requireAdminLogin')) {
    function requireAdminLogin(): void {
        if (!isAdminLoggedIn()) {
            $_SESSION['error_msg'] = "Admin access required.";
            redirect('../admin/login.php');
        }
    }
}

/**
 * Safe redirect helper function.
 * Includes a fallback in case output was accidentally sent before the header.
 */
if (!function_exists('redirect')) {
    function redirect(string $url): void {
        if (!headers_sent()) {
            header("Location: " . $url);
            exit();
        } else {
            // Fallback JavaScript redirect if headers are already sent
            echo "<script>window.location.href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'></noscript>";
            exit();
        }
    }
}

/**
 * Helper function to format currency prices (e.g. ₹220.00).
 */
if (!function_exists('formatPrice')) {
    function formatPrice(float|string $amount): string {
        return '₹' . number_format((float)$amount, 2);
    }
}

// No closing ?> tag. 
// Omitting the closing tag prevents accidental whitespace at the end of the file, 
// which can cause "Cannot modify header information - headers already sent" errors.