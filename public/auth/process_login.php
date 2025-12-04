<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get and sanitize input
$username = esc($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Username dan password harus diisi!';
    header('Location: login.php');
    exit;
}

// Query to find user
$sql = "SELECT user_id, username, password, role, email 
        FROM users 
        WHERE username = '$username' 
        LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Username atau password salah!';
    header('Location: login.php');
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = 'Username atau password salah!';
    header('Location: login.php');
    exit;
}

// Login successful - set session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['email'] = $user['email'];
$_SESSION['login_time'] = time();

// Close connection before redirect
$conn->close();

// Redirect based on role
if ($user['role'] === 'siswa') {
    header('Location: /student/index.php');
} else {
    header('Location: /admin/index.php');
}
exit;
?>
