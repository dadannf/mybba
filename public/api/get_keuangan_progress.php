<?php
/**
 * API: Get Real-time Progress Keuangan
 * Mengembalikan progress terbaru dari database untuk update real-time
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

session_start();
require_once __DIR__ . '/../config.php';

// Cek session
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Ambil keuangan_id dari request
$keuangan_id = isset($_GET['keuangan_id']) ? intval($_GET['keuangan_id']) : 0;

if ($keuangan_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid keuangan_id']);
    exit;
}

// Query untuk mendapatkan data keuangan terbaru
$sql = "SELECT keuangan_id, total_tagihan, total_bayar, progress 
        FROM keuangan 
        WHERE keuangan_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $keuangan_id);
$stmt->execute();
$result = $stmt->get_result();
$keuangan = $result->fetch_assoc();
$stmt->close();

if (!$keuangan) {
    echo json_encode(['success' => false, 'error' => 'Keuangan not found']);
    exit;
}

// Hitung jumlah pembayaran valid
$sqlCount = "SELECT 
                COUNT(*) as total_pembayaran,
                SUM(CASE WHEN status = 'valid' THEN 1 ELSE 0 END) as pembayaran_valid,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as pembayaran_menunggu,
                SUM(CASE WHEN status = 'tolak' THEN 1 ELSE 0 END) as pembayaran_ditolak
             FROM pembayaran 
             WHERE keuangan_id = ?";

$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param("i", $keuangan_id);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$countData = $resultCount->fetch_assoc();
$stmtCount->close();

$conn->close();

// Response
echo json_encode([
    'success' => true,
    'data' => [
        'keuangan_id' => $keuangan['keuangan_id'],
        'total_tagihan' => floatval($keuangan['total_tagihan']),
        'total_bayar' => floatval($keuangan['total_bayar']),
        'progress' => floatval($keuangan['progress'] ?? 0),
        'sisa_tunggakan' => floatval($keuangan['total_tagihan']) - floatval($keuangan['total_bayar']),
        'is_lunas' => floatval($keuangan['progress'] ?? 0) >= 100,
        'stats' => [
            'total_pembayaran' => intval($countData['total_pembayaran']),
            'pembayaran_valid' => intval($countData['pembayaran_valid']),
            'pembayaran_menunggu' => intval($countData['pembayaran_menunggu']),
            'pembayaran_ditolak' => intval($countData['pembayaran_ditolak'])
        ]
    ]
]);
?>
