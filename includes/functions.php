<?php

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Direct access to this file is not permitted.');
}

if (ob_get_level() === 0) {
    ob_start();
}

// Ensure configuration and database connection are loaded
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// =========================================================================
// 1. SESSION & AUTHENTICATION HELPERS
// =========================================================================

/**
 * Check if a regular user is logged in
 */
function isUserLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Check if an admin is logged in
 */
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

/**
 * Enforce user authentication on protected pages
 */
function requireUserLogin(): void {
    if (!isUserLoggedIn()) {
        setFlash('warning', 'Please log in to access this page.');
        redirect('login.php');
    }
}

/**
 * Enforce admin authentication on admin pages
 */
function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        setFlash('warning', 'Admin access required.');
        redirect('admin/login.php');
    }
}

/**
 * Safely redirect to another URL (Handles both header and JS fallbacks)
 */
function redirect(string $url): void {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '/';
    $target = $baseUrl . ltrim($url, '/');

    if (!headers_sent()) {
        header("Location: " . $target);
        exit();
    } else {
        // Safe fallback if output was sent prior to redirecting
        echo "<script>window.location.href='" . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . "'></noscript>";
        exit();
    }
}

/**
 * Set a session flash message (success, error, warning, info)
 */
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display and clear the session flash message
 */
function displayFlash(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $msg = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);

        $alertType = match($type) {
            'error', 'danger' => 'danger',
            'success'          => 'success',
            'warning'          => 'warning',
            default            => 'info'
        };

        return "<div class='alert alert-{$alertType} alert-dismissible fade show' role='alert'>
                    " . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
    }
    return '';
}

// =========================================================================
// 2. FORMATTING HELPERS
// =========================================================================

/**
 * Format a numeric amount as currency (e.g., ₹220.00)
 */
function formatPrice(float|string $amount, string $symbol = '₹'): string {
    return $symbol . number_format((float)$amount, 2);
}

/**
 * Format MySQL timestamp into readable human format
 */
function formatDate(string $timestamp, string $format = 'M d, Y h:i A'): string {
    return date($format, strtotime($timestamp));
}

/**
 * Format order status into styled Bootstrap badge tags
 */
function getStatusBadge(string $status): string {
    $class = match($status) {
        'Pending'          => 'bg-warning text-dark',
        'Confirmed'        => 'bg-info text-dark',
        'Preparing'        => 'bg-primary',
        'Out for Delivery' => 'bg-secondary',
        'Delivered', 'Paid' => 'bg-success',
        'Cancelled'        => 'bg-danger',
        default            => 'bg-dark'
    };

    return "<span class='badge {$class}'>" . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . "</span>";
}

// =========================================================================
// 3. FILE UPLOAD HELPERS
// =========================================================================

/**
 * Handle secure image uploads for categories and food items
 */
function uploadImage(array $file, string $folder = 'foods', string $default = 'default_food.jpg'): array {
    if (!isset($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['status' => true, 'fileName' => $default, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'fileName' => $default, 'error' => 'Error during file upload.'];
    }

    $allowedTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp'
    ];

    $maxSize = 2 * 1024 * 1024; // 2MB Limit

    if ($file['size'] > $maxSize) {
        return ['status' => false, 'fileName' => $default, 'error' => 'File size exceeds 2MB limit.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!array_key_exists($ext, $allowedTypes)) {
        return ['status' => false, 'fileName' => $default, 'error' => 'Invalid file extension. Only JPG, PNG, and WEBP allowed.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mimeType !== $allowedTypes[$ext]) {
        return ['status' => false, 'fileName' => $default, 'error' => 'Invalid image format detected.'];
    }

    $newFileName = uniqid('img_', true) . '.' . $ext;
    $targetDir = __DIR__ . '/../uploads/' . trim($folder, '/') . '/';

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetFilePath = $targetDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return ['status' => true, 'fileName' => $newFileName, 'error' => null];
    }

    return ['status' => false, 'fileName' => $default, 'error' => 'Failed to move uploaded file.'];
}

/**
 * Safely delete an old image file from the server
 */
function deleteUploadedImage(string $fileName, string $folder = 'foods'): void {
    if (in_array($fileName, ['default_food.jpg', 'default_category.jpg'], true)) {
        return;
    }

    $filePath = __DIR__ . '/../uploads/' . trim($folder, '/') . '/' . $fileName;
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}

// =========================================================================
// 4. COMMON SHOPPING & DATABASE HELPERS
// =========================================================================

/**
 * Get total item count in user's shopping cart
 */
function getCartCount(mysqli $conn, int $userId): int {
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (int)($result['total'] ?? 0);
}

/**
 * Get cart total subtotal amount for a user
 */
function getCartSubtotal(mysqli $conn, int $userId): float {
    $sql = "SELECT SUM(c.quantity * f.price) AS subtotal 
            FROM cart c 
            JOIN foods f ON c.food_id = f.id 
            WHERE c.user_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (float)($result['subtotal'] ?? 0.00);
}

/**
 * Flexible input sanitizer (supports single string or $conn, $string signature)
 */
function sanitize(mixed $arg1 = null, string $arg2 = ''): string {
    $conn = null;
    $data = '';

    if ($arg1 instanceof mysqli) {
        $conn = $arg1;
        $data = $arg2;
    } else {
        $data = (string)($arg1 ?? '');
    }

    $data = trim($data);
    $data = stripslashes($data);

    if ($conn !== null) {
        $data = $conn->real_escape_string($data);
    }

    return $data;
}