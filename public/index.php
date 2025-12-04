<?php
/**
 * Default landing page
 * Always redirect to login if not authenticated
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    
    // Redirect based on role
    if ($role === 'admin') {
        header('Location: /admin/finance/index.php');
        exit;
    } elseif ($role === 'siswa') {
        header('Location: /siswa/dashboard.php');
        exit;
    }
}

// Not logged in - redirect to login ONLY ONCE
if (!isset($_GET['redirected'])) {
    header('Location: /auth/login.php?from=index');
    exit;
}

// If already redirected but still here, show error
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - MyBBA System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        h1 { color: #e74c3c; }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Session Error</h1>
        <p>Terjadi masalah dengan session. Silakan hapus cookies dan coba lagi.</p>
        <a href="/login.php">Kembali ke Login</a>
    </div>
</body>
</html>
