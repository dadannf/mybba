<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Get and sanitize input
$username = esc($_POST['username'] ?? '');
$email = esc($_POST['email'] ?? '');
$role = esc($_POST['role'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate input
if (empty($username) || empty($password) || empty($confirm_password) || empty($role)) {
    $_SESSION['error'] = 'Semua field wajib diisi kecuali email!';
    header('Location: register.php');
    exit;
}

// Validate username length
if (strlen($username) < 4) {
    $_SESSION['error'] = 'Username minimal 4 karakter!';
    header('Location: register.php');
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password minimal 6 karakter!';
    header('Location: register.php');
    exit;
}

// Check if passwords match
if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Password dan Konfirmasi Password tidak cocok!';
    header('Location: register.php');
    exit;
}

// Validate role
if (!in_array($role, ['admin', 'siswa'])) {
    $_SESSION['error'] = 'Role tidak valid!';
    header('Location: register.php');
    exit;
}

// Validate email format if provided
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Format email tidak valid!';
    header('Location: register.php');
    exit;
}

// Check if username already exists
$checkUsername = "SELECT user_id FROM users WHERE username = '$username'";
$resultUsername = $conn->query($checkUsername);

if ($resultUsername->num_rows > 0) {
    $_SESSION['error'] = 'Username sudah digunakan! Silakan pilih username lain.';
    header('Location: register.php');
    exit;
}

// Check if email already exists (if email provided)
if (!empty($email)) {
    $checkEmail = "SELECT user_id FROM users WHERE email = '$email'";
    $resultEmail = $conn->query($checkEmail);
    
    if ($resultEmail->num_rows > 0) {
        $_SESSION['error'] = 'Email sudah terdaftar!';
        header('Location: register.php');
        exit;
    }
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare email value
$emailValue = empty($email) ? 'NULL' : "'$email'";

// Insert new user
$sql = "INSERT INTO users (username, password, role, email) 
        VALUES ('$username', '$hashed_password', '$role', $emailValue)";

if ($conn->query($sql)) {
    $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
    header('Location: login.php');
    exit;
} else {
    $_SESSION['error'] = 'Terjadi kesalahan saat registrasi: ' . $conn->error;
    header('Location: register.php');
    exit;
}

$conn->close();
?>
