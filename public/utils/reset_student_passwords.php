<?php
/**
 * Reset ALL Student Passwords to Correct Format
 * Format: bba#[4 angka terakhir NIS]
 */

require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Reset Student Passwords</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 900px; margin: 0 auto; }
        .log { background: #fff; padding: 15px; border-radius: 5px; margin-top: 20px; }
        pre { font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>🔄 Reset Student Passwords</h2>
        <p class='text-muted'>Update semua password siswa ke format: bba#[4 angka terakhir NIS]</p>
        
        <div class='alert alert-warning'>
            <strong>⚠️ Warning:</strong> Ini akan mereset password SEMUA siswa yang sudah terdaftar di tabel users.
        </div>
        
        <div class='log'><pre>";

// Get all student users
$query = "SELECT u.user_id, u.username, s.nama, s.kelas 
          FROM users u 
          LEFT JOIN siswa s ON u.username = s.nis 
          WHERE u.role = 'siswa' 
          ORDER BY u.username";

$result = $conn->query($query);

if (!$result) {
    echo "❌ Error: " . $conn->error;
    exit;
}

$total = $result->num_rows;
$updated = 0;
$errors = 0;

echo "Processing $total student accounts...\n\n";

while ($row = $result->fetch_assoc()) {
    $username = $row['username'];
    $nama = $row['nama'] ?? 'Unknown';
    $kelas = $row['kelas'] ?? '-';
    
    // Generate new password
    $last4 = substr($username, -4);
    $newPassword = 'bba#' . $last4;
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $updateSql = "UPDATE users SET password = '$hashedPassword' WHERE username = '$username'";
    
    if ($conn->query($updateSql)) {
        $updated++;
        echo "✅ UPDATED: $username - $nama ($kelas) - Password: $newPassword\n";
    } else {
        $errors++;
        echo "❌ ERROR: $username - " . $conn->error . "\n";
    }
}

echo "\n" . str_repeat('-', 70) . "\n";
echo "SUMMARY:\n";
echo "Total Accounts: $total\n";
echo "Updated: $updated\n";
echo "Errors: $errors\n";
echo str_repeat('-', 70) . "\n";

if ($updated > 0) {
    echo "\n✅ Passwords reset successfully!\n";
    echo "\nLogin Format:\n";
    echo "Username: [NIS siswa]\n";
    echo "Password: bba#[4 angka terakhir NIS]\n";
    
    echo "\nContoh:\n";
    $exampleQuery = "SELECT u.username, s.nama 
                     FROM users u 
                     LEFT JOIN siswa s ON u.username = s.nis 
                     WHERE u.role = 'siswa' 
                     LIMIT 5";
    $exampleResult = $conn->query($exampleQuery);
    
    if ($exampleResult) {
        while ($ex = $exampleResult->fetch_assoc()) {
            $nis = $ex['username'];
            $last4 = substr($nis, -4);
            echo "- {$ex['nama']} → Username: $nis, Password: bba#$last4\n";
        }
    }
}

echo "</pre></div>";

if ($updated > 0) {
    echo "<div class='alert alert-success mt-3'>
            <strong>✅ Success!</strong> Password untuk $updated siswa telah direset.
          </div>";
    
    echo "<div class='alert alert-info'>
            <strong>Login Information:</strong><br>
            Username: [NIS siswa]<br>
            Password: bba#[4 angka terakhir NIS]<br><br>
            <strong>Contoh untuk NIS 22211611:</strong><br>
            Username: <code>22211611</code><br>
            Password: <code>bba#1611</code>
          </div>";
    
    echo "<a href='login.php' class='btn btn-primary'>Go to Login</a>";
}

echo "    </div>
</body>
</html>";

$conn->close();
?>
