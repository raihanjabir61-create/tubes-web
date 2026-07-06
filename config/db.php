<?php
// Database configuration constants
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
    define('DB_NAME', getenv('DB_NAME') ?: 'db_donasi_buku');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}

// Disable default mysqli exceptions to handle missing database error manually
mysqli_report(MYSQLI_REPORT_OFF);

// Connect to database (will return false if database doesn't exist yet)
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

function get_db_connection() {
    global $conn;
    if (!$conn) {
        $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if (!$conn) {
            die("Koneksi Database Gagal: " . mysqli_connect_error());
        }
        mysqli_set_charset($conn, "utf8mb4");
    }
    return $conn;
}

// Helper function to sanitize input to prevent XSS
function sanitize($data) {
    if ($data === null) return '';
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Helper function to escape DB inputs
function db_escape($data) {
    global $conn;
    $db = get_db_connection();
    return mysqli_real_escape_string($db, trim($data));
}

// Start session safely
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
