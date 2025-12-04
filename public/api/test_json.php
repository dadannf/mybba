<?php
/**
 * Test API Response
 * Untuk memastikan JSON response bekerja dengan baik
 */

session_start();

// Set header JSON di awal
header('Content-Type: application/json');

// Test basic response
echo json_encode([
    'success' => true,
    'message' => 'API endpoint working correctly',
    'session_active' => isset($_SESSION['username']),
    'username' => $_SESSION['username'] ?? null,
    'role' => $_SESSION['role'] ?? null,
    'timestamp' => date('Y-m-d H:i:s'),
    'post_data' => $_POST,
    'get_data' => $_GET
]);
?>
