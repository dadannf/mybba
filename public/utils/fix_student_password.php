<?php
/**
 * Fix Student Password
 * Update password siswa ke format yang benar: bba#[4 angka terakhir NIS]
 */

require_once 'config.php';

$nis = $_GET['nis'] ?? '';

if (empty($nis)) {
    die("Error: NIS parameter required. Usage: fix_student_password.php?nis=22211611");
}

// Generate correct password
$last4Digits = substr($nis, -4);
$correctPassword = 'bba#' . $last4Digits;
$hashedPassword = password_hash($correctPassword, PASSWORD_DEFAULT);

// Update password
$sql = "UPDATE users SET password = '$hashedPassword' WHERE username = '$nis'";
$result = $conn->query($sql);

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix Password</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 600px; margin: 50px auto; text-align: center; }
    </style>
</head>
<body>
    <div class='container'>";

if ($result && $conn->affected_rows > 0) {
    echo "<div class='alert alert-success'>
            <h4>✅ Password Updated Successfully!</h4>
            <p>Password untuk NIS <strong>$nis</strong> telah diupdate.</p>
            <hr>
            <p><strong>Login Credentials:</strong></p>
            <p>Username: <strong>$nis</strong><br>
            Password: <strong>$correctPassword</strong></p>
          </div>";
    echo "<a href='login.php' class='btn btn-primary'>Go to Login</a> ";
    echo "<a href='check_login.php' class='btn btn-secondary'>Check Again</a>";
} elseif ($conn->affected_rows === 0) {
    echo "<div class='alert alert-warning'>
            <h4>⚠️ No Changes Made</h4>
            <p>User dengan username <strong>$nis</strong> tidak ditemukan atau password sudah benar.</p>
          </div>";
    echo "<a href='create_student_accounts.php' class='btn btn-primary'>Create Account</a> ";
    echo "<a href='check_login.php' class='btn btn-secondary'>Check Login</a>";
} else {
    echo "<div class='alert alert-danger'>
            <h4>❌ Update Failed</h4>
            <p>Error: " . $conn->error . "</p>
          </div>";
    echo "<a href='javascript:history.back()' class='btn btn-secondary'>Back</a>";
}

echo "    </div>
</body>
</html>";

$conn->close();
?>
