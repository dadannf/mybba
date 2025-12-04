<?php
// =============================================
// API: Get Real-Time Progress Data
// Deskripsi: Mengambil data progress pembayaran secara real-time
// =============================================

require_once __DIR__ . '/../../config.php';

// Check authentication  
require_once __DIR__ . '/../../auth_check.php';

header('Content-Type: application/json');

// Get filter parameters
$tahunFilter = isset($_GET['tahun']) ? esc($_GET['tahun']) : '';
$kelasFilter = isset($_GET['kelas']) ? esc($_GET['kelas']) : '';
$searchInput = isset($_GET['search']) ? esc($_GET['search']) : '';

$where = "1=1";
if ($tahunFilter) {
    $where .= " AND k.tahun = '$tahunFilter'";
}
if ($kelasFilter) {
    $where .= " AND s.kelas = '$kelasFilter'";
}
if ($searchInput) {
    $where .= " AND (s.nama LIKE '%$searchInput%' OR s.nis LIKE '%$searchInput%')";
}

$sql = "SELECT k.keuangan_id, k.total_tagihan, k.total_bayar, k.progress 
        FROM keuangan k 
        INNER JOIN siswa s ON k.nis = s.nis 
        WHERE $where 
        ORDER BY k.tahun DESC, s.nama ASC";

$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'keuangan_id' => $row['keuangan_id'],
            'total_tagihan' => number_format($row['total_tagihan'], 0, ',', '.'),
            'total_bayar' => number_format($row['total_bayar'], 0, ',', '.'),
            'progress' => floatval($row['progress'])
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $data
]);

$conn->close();
