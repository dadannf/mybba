<?php
// =============================================
// File: create_first_user.php
// Deskripsi: Script untuk membuat user pertama secara langsung
// PENTING: Hapus file ini setelah selesai digunakan!
// =============================================

require_once 'config.php';

// Set username dan password default
$username = 'admin';
$password = 'admin123';
$role = 'admin';
$email = null;

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check apakah tabel users sudah ada
$checkTable = "SHOW TABLES LIKE 'users'";
$resultTable = $conn->query($checkTable);

if ($resultTable->num_rows === 0) {
    echo "<h2>⚠️ Tabel 'users' belum dibuat!</h2>";
    echo "<p>Silakan jalankan script SQL berikut terlebih dahulu:</p>";
    echo "<pre>database/create_user_table.sql</pre>";
    echo "<p>Atau copy-paste SQL berikut ke phpMyAdmin:</p>";
    echo "<textarea style='width:100%; height:200px;'>";
    echo file_get_contents('../database/create_user_table.sql');
    echo "</textarea>";
    exit;
}

// Check apakah user sudah ada
$checkUser = "SELECT user_id FROM users WHERE username = '$username'";
$resultUser = $conn->query($checkUser);

if ($resultUser->num_rows > 0) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>User Already Exists</title>
        <style>
            body { font-family: Arial; padding: 40px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .success { color: #28a745; }
            .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0; }
            .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            .btn:hover { background: #5568d3; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2 class='success'>✅ User sudah ada!</h2>
            <div class='info'>
                <strong>Username:</strong> {$username}<br>
                <strong>Password:</strong> {$password}<br>
                <strong>Role:</strong> {$role}
            </div>
            <p>Silakan login menggunakan kredensial di atas.</p>
            <a href='login.php' class='btn'>🔐 Login Sekarang</a>
            <a href='register.php' class='btn' style='background: #28a745;'>➕ Registrasi User Baru</a>
        </div>
    </body>
    </html>";
    exit;
}

// Insert user baru
$sql = "INSERT INTO users (username, password, role, email) 
        VALUES ('$username', '$hashed_password', '$role', NULL)";

if ($conn->query($sql)) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>User Created Successfully</title>
        <style>
            body { font-family: Arial; padding: 40px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .success { color: #28a745; }
            .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
            .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0; }
            .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            .btn:hover { background: #5568d3; }
            code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2 class='success'>✅ User berhasil dibuat!</h2>
            
            <div class='info'>
                <strong>📝 Kredensial Login:</strong><br><br>
                <strong>Username:</strong> <code>{$username}</code><br>
                <strong>Password:</strong> <code>{$password}</code><br>
                <strong>Role:</strong> <code>{$role}</code>
            </div>
            
            <div class='warning'>
                <strong>⚠️ PENTING - KEAMANAN:</strong><br>
                1. Hapus file <code>create_first_user.php</code> setelah selesai<br>
                2. Ganti password default setelah login pertama kali<br>
                3. Jangan share kredensial ini!
            </div>
            
            <h3>🚀 Langkah Selanjutnya:</h3>
            <ol>
                <li>Klik tombol Login di bawah</li>
                <li>Masukkan username dan password di atas</li>
                <li>Setelah masuk, hapus file ini</li>
                <li>Buat user baru via halaman Registrasi jika diperlukan</li>
            </ol>
            
            <a href='login.php' class='btn'>🔐 Login Sekarang</a>
            <a href='register.php' class='btn' style='background: #28a745;'>➕ Registrasi User Baru</a>
        </div>
    </body>
    </html>";
} else {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error Creating User</title>
        <style>
            body { font-family: Arial; padding: 40px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .error { color: #dc3545; }
            .info { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2 class='error'>❌ Error saat membuat user!</h2>
            <div class='info'>
                <strong>Error Message:</strong><br>
                {$conn->error}
            </div>
            <p>Silakan cek:</p>
            <ul>
                <li>Koneksi database di config.php</li>
                <li>Apakah tabel 'user' sudah dibuat</li>
                <li>Permission database user</li>
            </ul>
        </div>
    </body>
    </html>";
}

$conn->close();
?>
