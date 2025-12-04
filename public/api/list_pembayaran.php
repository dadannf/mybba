<?php
// List pembayaran dengan bukti bayar
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$sql = "SELECT p.pembayaran_id, p.bukti_bayar, p.tanggal_bayar, p.nominal_bayar, p.status,
               k.nis, k.bulan, s.nama
        FROM pembayaran p
        JOIN keuangan k ON p.keuangan_id = k.keuangan_id
        LEFT JOIN siswa s ON k.nis = s.nis
        WHERE p.bukti_bayar IS NOT NULL AND p.bukti_bayar != ''
        ORDER BY p.pembayaran_id DESC
        LIMIT 20";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $bukti_path = __DIR__ . '/../uploads/bukti_bayar/' . $row['bukti_bayar'];
    $row['file_exists'] = file_exists($bukti_path);
    $row['bukti_path'] = $bukti_path;
    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);
$conn->close();
