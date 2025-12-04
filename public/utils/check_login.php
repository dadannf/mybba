<?php
/**
 * Check Student Login Credentials
 * Tool untuk debug kenapa login gagal
 */

require_once 'config.php';

$nisToCheck = '22211611'; // NIS yang akan dicek

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Check Login Credentials</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { margin-bottom: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>🔍 Check Student Login - NIS: $nisToCheck</h2>
        <hr>";

// Step 1: Check if student exists in siswa table
echo "<div class='card'>
        <div class='card-header'><strong>Step 1: Check Siswa Table</strong></div>
        <div class='card-body'>";

$sqlSiswa = "SELECT nis, nama, kelas, jurusan FROM siswa WHERE nis = '$nisToCheck'";
$resultSiswa = $conn->query($sqlSiswa);

if ($resultSiswa->num_rows > 0) {
    $siswa = $resultSiswa->fetch_assoc();
    echo "<p class='success'>✅ Student found in siswa table!</p>";
    echo "<pre>";
    echo "NIS    : " . $siswa['nis'] . "\n";
    echo "Nama   : " . $siswa['nama'] . "\n";
    echo "Kelas  : " . $siswa['kelas'] . "\n";
    echo "Jurusan: " . $siswa['jurusan'];
    echo "</pre>";
} else {
    echo "<p class='error'>❌ Student NOT found in siswa table!</p>";
    echo "<p>NIS <strong>$nisToCheck</strong> tidak ada di tabel siswa.</p>";
}

echo "</div></div>";

// Step 2: Check if user exists in users table
echo "<div class='card'>
        <div class='card-header'><strong>Step 2: Check Users Table</strong></div>
        <div class='card-body'>";

$sqlUser = "SELECT user_id, username, role, email, password FROM users WHERE username = '$nisToCheck'";
$resultUser = $conn->query($sqlUser);

if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
    echo "<p class='success'>✅ User account found in users table!</p>";
    echo "<pre>";
    echo "User ID : " . $user['user_id'] . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Role    : " . $user['role'] . "\n";
    echo "Email   : " . ($user['email'] ?? 'N/A') . "\n";
    echo "Password Hash: " . substr($user['password'], 0, 50) . "...";
    echo "</pre>";
    
    $userExists = true;
    $storedHash = $user['password'];
} else {
    echo "<p class='error'>❌ User account NOT found in users table!</p>";
    echo "<p>Account untuk username <strong>$nisToCheck</strong> belum dibuat.</p>";
    echo "<p class='warning'>⚠️ Anda perlu create account dulu via <a href='create_student_accounts.php'>create_student_accounts.php</a></p>";
    
    $userExists = false;
}

echo "</div></div>";

// Step 3: Generate expected password
echo "<div class='card'>
        <div class='card-header'><strong>Step 3: Expected Password Format</strong></div>
        <div class='card-body'>";

$last4Digits = substr($nisToCheck, -4);
$expectedPassword = 'bba#' . $last4Digits;

echo "<p>Berdasarkan NIS <strong>$nisToCheck</strong>:</p>";
echo "<pre>";
echo "4 Angka Terakhir: $last4Digits\n";
echo "Expected Password: <strong>$expectedPassword</strong>";
echo "</pre>";

echo "</div></div>";

// Step 4: Test password verification
if ($userExists) {
    echo "<div class='card'>
            <div class='card-header'><strong>Step 4: Password Verification Test</strong></div>
            <div class='card-body'>";
    
    $testPassword = $expectedPassword;
    $isValid = password_verify($testPassword, $storedHash);
    
    if ($isValid) {
        echo "<p class='success'>✅ Password verification SUCCESS!</p>";
        echo "<p>Password <strong>$testPassword</strong> adalah VALID untuk user ini.</p>";
    } else {
        echo "<p class='error'>❌ Password verification FAILED!</p>";
        echo "<p>Password <strong>$testPassword</strong> TIDAK COCOK dengan hash yang tersimpan.</p>";
        echo "<p class='warning'>⚠️ Kemungkinan penyebab:</p>";
        echo "<ul>";
        echo "<li>Password di database di-set manual dengan value berbeda</li>";
        echo "<li>Account dibuat sebelum update format password</li>";
        echo "<li>Hash corruption</li>";
        echo "</ul>";
        
        echo "<p class='warning'><strong>Solusi:</strong></p>";
        echo "<ol>";
        echo "<li>Hapus user lama: <code>DELETE FROM users WHERE username = '$nisToCheck';</code></li>";
        echo "<li>Buat ulang via: <a href='create_student_accounts.php'>create_student_accounts.php</a></li>";
        echo "</ol>";
        
        // Show what the correct hash should be
        echo "<p><strong>Correct hash untuk password '$testPassword' adalah:</strong></p>";
        $correctHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "<pre style='font-size: 10px;'>$correctHash</pre>";
        
        echo "<p class='warning'><strong>Quick Fix SQL:</strong></p>";
        echo "<pre>UPDATE users 
SET password = '$correctHash' 
WHERE username = '$nisToCheck';</pre>";
    }
    
    echo "</div></div>";
}

// Step 5: Action buttons
echo "<div class='card'>
        <div class='card-header'><strong>Actions</strong></div>
        <div class='card-body'>";

if (!$userExists) {
    echo "<a href='create_student_accounts.php' class='btn btn-primary'>Create All Student Accounts</a> ";
    echo "<span class='text-muted'>atau</span> ";
    echo "<a href='register.php' class='btn btn-secondary'>Manual Register</a>";
} else {
    if (!$isValid) {
        echo "<div class='alert alert-warning'>";
        echo "<strong>Password tidak cocok!</strong><br>";
        echo "Klik tombol di bawah untuk reset password ke format yang benar.";
        echo "</div>";
        echo "<a href='fix_student_password.php?nis=$nisToCheck' class='btn btn-danger'>Fix Password Now</a>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<strong>Everything looks good!</strong><br>";
        echo "You can login with:<br>";
        echo "Username: <strong>$nisToCheck</strong><br>";
        echo "Password: <strong>$expectedPassword</strong>";
        echo "</div>";
        echo "<a href='login.php' class='btn btn-success'>Go to Login Page</a>";
    }
}

echo " <a href='javascript:history.back()' class='btn btn-outline-secondary'>Back</a>";

echo "</div></div>";

echo "    </div>
</body>
</html>";

$conn->close();
?>
