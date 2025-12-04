<?php
/**
 * Simple JSON Test Endpoint
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

error_log("=== TEST JSON API Called ===");

try {
    session_start();
    error_log("Session started");
    
    require_once __DIR__ . '/../config.php';
    error_log("Config loaded");
    
    $response = [
        'success' => true,
        'message' => 'Test endpoint working',
        'timestamp' => date('Y-m-d H:i:s'),
        'session_user' => $_SESSION['username'] ?? 'not logged in',
        'post_data' => $_POST,
        'database_connected' => $conn->ping()
    ];
    
    error_log("Response prepared: " . json_encode($response));
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
