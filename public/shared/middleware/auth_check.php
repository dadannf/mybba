<?php
/**
 * Authentication Middleware
 * 
 * Usage: include __DIR__ . '/../../shared/middleware/auth_check.php';
 * 
 * Checks if user is logged in, redirects to login if not
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // Store current URL for redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login
    header('Location: /auth/login.php');
    exit;
}

// Optional: Check role-based access
if (isset($required_role)) {
    if ($_SESSION['role'] !== $required_role) {
        header('Location: /auth/login.php?error=unauthorized');
        exit;
    }
}
