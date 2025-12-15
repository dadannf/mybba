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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Foto - Comprehensive Analysis</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border: 1px solid #ddd; }
        td:first-child { font-weight: bold; width: 250px; background: #f9f9f9; }
        img { border: 3px solid #333; margin: 10px 0; }
    </style>
</head>
<body>

<h1>🔍 Comprehensive Photo Debug Analysis</h1>

<div class="section">
    <h2>1. Database Information</h2>
    <table>
        <tr>
            <td>NIS</td>
            <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
        </tr>
        <tr>
            <td>Nama</td>
            <td><?php echo htmlspecialchars($siswa['nama']); ?></td>
        </tr>
        <tr>
            <td>Foto Field (Raw)</td>
            <td><code><?php echo htmlspecialchars($siswa['foto'] ?? 'NULL'); ?></code></td>
        </tr>
        <tr>
            <td>Is Empty?</td>
            <td><?php echo empty($siswa['foto']) ? '<span class="error">YES (KOSONG)</span>' : '<span class="success">NO (ADA ISI)</span>'; ?></td>
        </tr>
    </table>
</div>

<?php if (!empty($siswa['foto'])): ?>

<div class="section">
    <h2>2. Server Path Information</h2>
    <table>
        <tr>
            <td>__DIR__ (current script location)</td>
            <td><code><?php echo htmlspecialchars(__DIR__); ?></code></td>
        </tr>
        <tr>
            <td>DOCUMENT_ROOT</td>
            <td><code><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A'); ?></code></td>
        </tr>
        <tr>
            <td>SCRIPT_FILENAME</td>
            <td><code><?php echo htmlspecialchars($_SERVER['SCRIPT_FILENAME'] ?? 'N/A'); ?></code></td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>3. Path Testing - Multiple Scenarios</h2>
    
    <?php
    // Clean filename dari database
    $fotoFromDB = $siswa['foto'];
    $cleanFilename = str_replace('uploads/siswa/', '', $fotoFromDB);
    
    // Test berbagai kemungkinan path
    $tests = [
        [
            'name' => 'Method 1: Direct from DB (1 level up)',
            'absolute' => __DIR__ . '/../' . $fotoFromDB,
            'relative' => '../' . $fotoFromDB
        ],
        [
            'name' => 'Method 2: Direct from DB (2 levels up)',
            'absolute' => __DIR__ . '/../../' . $fotoFromDB,
            'relative' => '../../' . $fotoFromDB
        ],
        [
            'name' => 'Method 3: Cleaned filename + path (1 level up)',
            'absolute' => __DIR__ . '/../uploads/siswa/' . $cleanFilename,
            'relative' => '../uploads/siswa/' . $cleanFilename
        ],
        [
            'name' => 'Method 4: Cleaned filename + path (2 levels up)',
            'absolute' => __DIR__ . '/../../uploads/siswa/' . $cleanFilename,
            'relative' => '../../uploads/siswa/' . $cleanFilename
        ],
        [
            'name' => 'Method 5: From DOCUMENT_ROOT',
            'absolute' => ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/uploads/siswa/' . $cleanFilename,
            'relative' => '/mybba/uploads/siswa/' . $cleanFilename
        ],
        [
            'name' => 'Method 6: Hardcoded root path',
            'absolute' => 'F:/laragon/www/mybba/uploads/siswa/' . $cleanFilename,
            'relative' => '/mybba/uploads/siswa/' . $cleanFilename
        ]
    ];
    
    foreach ($tests as $index => $test) {
        $exists = file_exists($test['absolute']);
        echo '<div style="margin: 15px 0; padding: 10px; background: ' . ($exists ? '#d4edda' : '#f8d7da') . '; border-radius: 5px;">';
        echo '<h3 style="margin-top: 0;">' . htmlspecialchars($test['name']) . '</h3>';
        echo '<table>';
        echo '<tr><td>Absolute Path</td><td><code>' . htmlspecialchars($test['absolute']) . '</code></td></tr>';
        echo '<tr><td>Relative Path (for img src)</td><td><code>' . htmlspecialchars($test['relative']) . '</code></td></tr>';
        echo '<tr><td>File Exists?</td><td>';
        if ($exists) {
            $filesize = filesize($test['absolute']);
            echo '<span class="success">✅ YES</span> (Size: ' . number_format($filesize) . ' bytes)';
        } else {
            echo '<span class="error">❌ NO</span>';
        }
        echo '</td></tr>';
        echo '</table>';
        
        if ($exists) {
            echo '<h4>Preview Test:</h4>';
            echo '<img src="' . htmlspecialchars($test['relative']) . '" style="max-width: 150px; height: auto;" onerror="this.style.border=\'3px solid red\'; this.alt=\'Failed to load\';">';
            echo '<p><span class="success">✅ THIS METHOD WORKS!</span></p>';
        }
        
        echo '</div>';
    }
    ?>
</div>

<div class="section">
    <h2>4. Directory Listing</h2>
    <h3>Contents of uploads/siswa/ directory:</h3>
    <?php
    $uploadDir = __DIR__ . '/../../uploads/siswa/';
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        echo '<ul>';
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $isMatch = ($file === $cleanFilename);
                echo '<li style="' . ($isMatch ? 'background: yellow; font-weight: bold;' : '') . '">';
                echo htmlspecialchars($file);
                if ($isMatch) echo ' <span class="success">← THIS IS YOUR FILE!</span>';
                echo '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<p class="error">Directory not found: ' . htmlspecialchars($uploadDir) . '</p>';
    }
    ?>
</div>

<?php else: ?>

<div class="section">
    <h2 class="error">❌ Foto field is EMPTY in database!</h2>
    <p>Siswa ini belum upload foto atau field foto kosong di database.</p>
</div>

<?php endif; ?>

<div class="section">
    <h2>5. Recommended Solution</h2>
    <p>Based on analysis above, the working method should be highlighted in green. Use that path structure in your PHP files.</p>
</div>

</body>
</html>
