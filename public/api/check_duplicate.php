<?php
// =============================================
// File: check_duplicate.php
// Deskripsi: Cek duplicate NIS atau NISN
// =============================================

require_once __DIR__ . '/../config.php';

$field = $_GET['field'] ?? ''; // 'nis' atau 'nisn'
$value = esc($_GET['value'] ?? '');
$currentNis = esc($_GET['current_nis'] ?? ''); // untuk edit, skip current record

if (empty($field) || empty($value)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Query untuk cek apakah nilai sudah ada
if ($field === 'nis') {
    $sql = "SELECT COUNT(*) as count FROM siswa WHERE nis = '$value'";
    if ($currentNis) {
        $sql .= " AND nis != '$currentNis'";
    }
} elseif ($field === 'nisn') {
    $sql = "SELECT COUNT(*) as count FROM siswa WHERE nisn = '$value'";
    if ($currentNis) {
        $sql .= " AND nis != '$currentNis'";
    }
} else {
    echo json_encode(['exists' => false]);
    exit;
}

$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo json_encode(['exists' => $row['count'] > 0]);

$conn->close();
?>
