<?php
// =============================================
// File: auth_check.php
// Deskripsi: Middleware untuk mengecek autentikasi user
// Cara pakai: Include di awal setiap halaman yang memerlukan login
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in - prioritas cek username & role
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu!';
    header('Location: /auth/login.php');
    exit;
}

// Optional: Check session timeout (30 minutes)
$timeout_duration = 1800; // 30 minutes in seconds
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout_duration) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['error'] = 'Session expired. Silakan login kembali!';
    header('Location: /auth/login.php');
    exit;
}

// Update last activity time
$_SESSION['login_time'] = time();

// Set admin name for display
$adminName = $_SESSION['username'] ?? 'User';
$userRole = $_SESSION['role'] ?? 'siswa';
?>
