<?php
/**
 * Simple Router for Clean URLs
 * 
 * Usage in .htaccess:
 * RewriteEngine On
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} !-d
 * RewriteRule ^(.*)$ router.php?route=$1 [QSA,L]
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get route from query string
$route = isset($_GET['route']) ? trim($_GET['route'], '/') : '';

// Route mapping
$routes = [
    // Public routes
    '' => 'index.php',
    'login' => 'auth/login.php',
    'register' => 'auth/register.php',
    'logout' => 'auth/logout.php',
    
    // Admin routes
    'admin' => 'admin/index.php',
    'admin/dashboard' => 'admin/index.php',
    'admin/students' => 'admin/students/index.php',
    'admin/students/create' => 'admin/students/create.php',
    'admin/students/edit' => 'admin/students/edit.php',
    'admin/finance' => 'admin/finance/index.php',
    'admin/finance/create' => 'admin/finance/create.php',
    'admin/finance/edit' => 'admin/finance/edit.php',
    'admin/finance/detail' => 'admin/finance/detail.php',
    'admin/information' => 'admin/information/index.php',
    
    // Student routes
    'student' => 'student/index.php',
    'student/dashboard' => 'student/index.php',
    'student/finance' => 'student/finance.php',
    'student/profile' => 'student/profile.php',
];

// Check if route exists
if (array_key_exists($route, $routes)) {
    $file = __DIR__ . '/' . $routes[$route];
    
    if (file_exists($file)) {
        include $file;
    } else {
        http_response_code(404);
        echo "404 - File not found: $file";
    }
} else {
    // Try to find file directly
    $file = __DIR__ . '/' . $route;
    
    if (file_exists($file) && is_file($file)) {
        include $file;
    } else {
        http_response_code(404);
        include __DIR__ . '/errors/404.php';
    }
}
