<?php
// =============================================
// AJAX: Check Keuangan Duplicate
// =============================================

require_once __DIR__ . '/../config.php';

$nis = esc($_GET['nis'] ?? '');
$tahun = esc($_GET['tahun'] ?? '');

header('Content-Type: application/json');

if (empty($nis) || empty($tahun)) {
    echo json_encode(['exists' => false]);
    exit;
}

$sql = "SELECT COUNT(*) as count FROM keuangan WHERE nis = '$nis' AND tahun = '$tahun'";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

echo json_encode(['exists' => $data['count'] > 0]);
