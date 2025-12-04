<?php
/**
 * Database Configuration & Connection
 * 
 * This file is included in every page that needs database access
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dbsekolah');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// OCR API Configuration
// Gunakan environment variable untuk ngrok, fallback ke localhost
define('OCR_API_URL', getenv('OCR_API_URL') ?: 'http://localhost:8000');

// Include helper functions
require_once __DIR__ . '/shared/helpers/functions.php';

// Legacy function for backward compatibility
function esc($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

// Legacy function for backward compatibility
function formatTanggalIndo($tanggal) {
    return formatTanggal($tanggal);
}
