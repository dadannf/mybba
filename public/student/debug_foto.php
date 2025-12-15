<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'siswa') {
    die('Unauthorized');
}

$username = $_SESSION['username'];

// Query untuk mendapatkan data siswa
$sql = "SELECT nis, nama, foto FROM siswa WHERE nis = '$username' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die('Data siswa tidak ditemukan');
}

$siswa = $result->fetch_assoc();

echo "<h3>Debug Info Foto Siswa</h3>";
echo "<hr>";

echo "<strong>NIS:</strong> " . htmlspecialchars($siswa['nis']) . "<br>";
echo "<strong>Nama:</strong> " . htmlspecialchars($siswa['nama']) . "<br>";
echo "<strong>Foto (database):</strong> " . htmlspecialchars($siswa['foto'] ?? 'NULL/EMPTY') . "<br>";
echo "<strong>Empty check:</strong> " . (empty($siswa['foto']) ? 'TRUE (kosong)' : 'FALSE (ada isi)') . "<br>";
echo "<hr>";

if (!empty($siswa['foto'])) {
    $relativePath = '../uploads/siswa/' . $siswa['foto'];
    $absolutePath = __DIR__ . '/../uploads/siswa/' . $siswa['foto'];
    
    // Coba path alternatif
    $cleanFilename = str_replace('uploads/siswa/', '', $siswa['foto']);
    $absolutePath2 = __DIR__ . '/../../uploads/siswa/' . $cleanFilename;
    $relativePath2 = '../../uploads/siswa/' . $cleanFilename;
    
    echo "<strong>Foto dari DB (raw):</strong> " . htmlspecialchars($siswa['foto']) . "<br>";
    echo "<strong>Clean Filename:</strong> " . htmlspecialchars($cleanFilename) . "<br>";
    echo "<hr>";
    
    echo "<h4>Attempt 1: Original Path</h4>";
    echo "<strong>Relative Path:</strong> " . htmlspecialchars($relativePath) . "<br>";
    echo "<strong>Absolute Path:</strong> " . htmlspecialchars($absolutePath) . "<br>";
    echo "<strong>File Exists:</strong> " . (file_exists($absolutePath) ? 'YES ✓' : 'NO ✗') . "<br>";
    echo "<hr>";
    
    echo "<h4>Attempt 2: Cleaned Path (2 levels up)</h4>";
    echo "<strong>Relative Path:</strong> " . htmlspecialchars($relativePath2) . "<br>";
    echo "<strong>Absolute Path:</strong> " . htmlspecialchars($absolutePath2) . "<br>";
    echo "<strong>File Exists:</strong> " . (file_exists($absolutePath2) ? 'YES ✓' : 'NO ✗') . "<br>";
    
    if (file_exists($absolutePath2)) {
        echo "<strong>File Size:</strong> " . filesize($absolutePath2) . " bytes<br>";
        echo "<hr>";
        echo "<h4>Preview Image:</h4>";
        echo "<img src='" . htmlspecialchars($relativePath2) . "' style='max-width: 200px; border: 2px solid #ccc;'>";
    }
    
    echo "<hr>";
    echo "<strong>__DIR__:</strong> " . htmlspecialchars(__DIR__) . "<br>";
    } else {
        // Cek kemungkinan path lain
        $altPath1 = __DIR__ . '/uploads/siswa/' . $siswa['foto'];
        $altPath2 = $_SERVER['DOCUMENT_ROOT'] . '/uploads/siswa/' . $siswa['foto'];
        
        echo "<strong>Alternative Path 1:</strong> " . htmlspecialchars($altPath1) . " - " . (file_exists($altPath1) ? 'EXISTS ✓' : 'NOT FOUND') . "<br>";
        echo "<strong>Alternative Path 2:</strong> " . htmlspecialchars($altPath2) . " - " . (file_exists($altPath2) ? 'EXISTS ✓' : 'NOT FOUND') . "<br>";
        
        // List isi folder uploads/siswa
        $uploadDir = __DIR__ . '/../uploads/siswa/';
        if (is_dir($uploadDir)) {
            echo "<hr><h4>Files in uploads/siswa/:</h4>";
            $files = scandir($uploadDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo "- " . htmlspecialchars($file) . "<br>";
                }
            }
        } else {
            echo "<br><strong style='color: red;'>Directory tidak ditemukan: " . htmlspecialchars($uploadDir) . "</strong>";
        }
    }
} else {
    echo "<strong style='color: orange;'>Foto field kosong di database</strong>";
}
