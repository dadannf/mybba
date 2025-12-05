<?php
/**
 * Database Configuration & Connection for Docker
 * 
 * This file is used when running in Docker containers
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration - Use environment variables or defaults
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_USER', getenv('DB_USER') ?: 'mybba');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'mybba123');
define('DB_NAME', getenv('DB_NAME') ?: 'dbsekolah');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// OCR API Configuration - Use internal Docker network
define('OCR_API_URL', getenv('OCR_API_URL') ?: 'http://ocr:8000');

// Include helper functions
require_once __DIR__ . '/shared/helpers/functions.php';

// Log configuration for debugging
if (getenv('DEBUG') === 'true') {
    error_log("DB_HOST: " . DB_HOST);
    error_log("OCR_API_URL: " . OCR_API_URL);
}
