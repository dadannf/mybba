<?php
/**
 * Auto-Generate Student User Accounts
 * 
 * Format:
 * - Username: NIS siswa
 * - Password: bba#[4 angka terakhir NIS]
 * - Role: siswa
 * 
 * Example:
 * - NIS: 12345 → Username: 12345, Password: bba#2345
 * - NIS: 67890 → Username: 67890, Password: bba#7890
 */

require_once 'config.php';

// Check if running from command line or browser
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    // Simple security check for browser access
    // You can add more security here (IP whitelist, admin login, etc.)
    echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Create Student Accounts</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; }
        .alert { margin-top: 10px; }
        .log { background: #fff; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .log pre { margin: 0; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Create Student User Accounts</h2>
        <p class='text-muted'>Generate user accounts untuk semua siswa dengan format password: bba#[4 angka terakhir NIS]</p>
        <div class='log'>";
}

// Get all students from database
$query = "SELECT nis, nama, kelas FROM siswa ORDER BY nis";
$result = $conn->query($query);

if (!$result) {
    $error = "Error querying siswa table: " . $conn->error;
    if ($isCLI) {
        echo $error . "\n";
    } else {
        echo "<div class='alert alert-danger'>$error</div>";
    }
    exit;
}

$totalStudents = $result->num_rows;
$created = 0;
$skipped = 0;
$errors = 0;

echo $isCLI ? "Processing $totalStudents students...\n\n" : "<pre>Processing $totalStudents students...\n\n";

while ($siswa = $result->fetch_assoc()) {
    $nis = $siswa['nis'];
    $nama = $siswa['nama'];
    $kelas = $siswa['kelas'];
    
    // Generate password: bba#[4 angka terakhir NIS]
    $last4Digits = substr($nis, -4);
    $password = 'bba#' . $last4Digits;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if username already exists
    $checkQuery = "SELECT user_id FROM users WHERE username = '$nis'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $skipped++;
        echo "⏭️  SKIP: $nis - $nama ($kelas) - Already exists\n";
        continue;
    }
    
    // Insert new user
    $email = strtolower(str_replace(' ', '', $nis . '@siswa.com'));
    $insertQuery = "INSERT INTO users (username, password, role, email) 
                    VALUES ('$nis', '$hashedPassword', 'siswa', '$email')";
    
    if ($conn->query($insertQuery)) {
        $created++;
        echo "✅ CREATE: $nis - $nama ($kelas) - Password: $password\n";
    } else {
        $errors++;
        echo "❌ ERROR: $nis - $nama ($kelas) - " . $conn->error . "\n";
    }
}

echo "\n" . str_repeat('-', 60) . "\n";
echo "SUMMARY:\n";
echo "Total Students: $totalStudents\n";
echo "Created: $created\n";
echo "Skipped (already exists): $skipped\n";
echo "Errors: $errors\n";
echo str_repeat('-', 60) . "\n";

if ($created > 0) {
    echo "\n✅ Student accounts created successfully!\n";
    echo "\nLogin credentials:\n";
    echo "- Username: [NIS siswa]\n";
    echo "- Password: bba#[4 angka terakhir NIS]\n";
    echo "\nExample:\n";
    
    // Show example from created accounts
    $exampleQuery = "SELECT s.nis, s.nama FROM siswa s 
                     INNER JOIN users u ON s.nis = u.username 
                     WHERE u.role = 'siswa' 
                     LIMIT 3";
    $exampleResult = $conn->query($exampleQuery);
    
    if ($exampleResult && $exampleResult->num_rows > 0) {
        while ($ex = $exampleResult->fetch_assoc()) {
            $exNis = $ex['nis'];
            $exLast4 = substr($exNis, -4);
            echo "- {$ex['nama']} → Username: $exNis, Password: bba#$exLast4\n";
        }
    }
}

if (!$isCLI) {
    echo "</pre></div>";
    
    if ($created > 0) {
        echo "<div class='alert alert-success'>
            <strong>Success!</strong> Created $created student accounts.
            <br>Skipped $skipped existing accounts.
            " . ($errors > 0 ? "<br>Errors: $errors" : "") . "
        </div>";
        
        echo "<div class='alert alert-info'>
            <strong>Login Information:</strong><br>
            Username: [NIS siswa]<br>
            Password: bba#[4 angka terakhir NIS]<br><br>
            <strong>Example:</strong><br>
            NIS: 12345 → Password: bba#2345
        </div>";
    }
    
    echo "<div class='mt-3'>
        <a href='login.php' class='btn btn-primary'>Go to Login</a>
        <a href='javascript:history.back()' class='btn btn-secondary'>Back</a>
    </div>";
    
    echo "</div></body></html>";
}

$conn->close();
?>
