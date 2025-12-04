<?php
session_start();
require_once __DIR__ . '/../config.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
  ]);
  exit;
}

// Ambil data dari POST
$pembayaranId = isset($_POST['pembayaran_id']) ? intval($_POST['pembayaran_id']) : 0;
$newStatus = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validasi input
if ($pembayaranId <= 0) {
  echo json_encode([
    'success' => false,
    'message' => 'ID Pembayaran tidak valid'
  ]);
  exit;
}

if (!in_array($newStatus, ['valid', 'menunggu', 'tolak'])) {
  echo json_encode([
    'success' => false,
    'message' => 'Status tidak valid. Gunakan: valid, menunggu, atau tolak'
  ]);
  exit;
}

// Update status pembayaran
$sql = "UPDATE pembayaran SET status = ? WHERE pembayaran_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newStatus, $pembayaranId);

if ($stmt->execute()) {
  // Jika status ditolak, hapus pembayaran tersebut
  if ($newStatus === 'tolak') {
    $sqlDelete = "DELETE FROM pembayaran WHERE pembayaran_id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $pembayaranId);
    $stmtDelete->execute();
    $stmtDelete->close();
    
    echo json_encode([
      'success' => true,
      'message' => 'Pembayaran ditolak dan telah dihapus. Siswa dapat melakukan pembayaran ulang.'
    ]);
    
    $stmt->close();
    $conn->close();
    exit;
  }
  
  // Jika berhasil, update juga total_bayar di tabel keuangan
  // Ambil data pembayaran dan keuangan_id
  $sqlGetData = "SELECT keuangan_id, nominal_bayar FROM pembayaran WHERE pembayaran_id = ?";
  $stmtGetData = $conn->prepare($sqlGetData);
  $stmtGetData->bind_param("i", $pembayaranId);
  $stmtGetData->execute();
  $resultGetData = $stmtGetData->get_result();
  
  if ($dataRow = $resultGetData->fetch_assoc()) {
    $keuanganId = $dataRow['keuangan_id'];
    
    // Hitung ulang total_bayar dari pembayaran yang sudah valid
    $sqlCalc = "SELECT COALESCE(SUM(nominal_bayar), 0) as total 
                FROM pembayaran 
                WHERE keuangan_id = ? AND status = 'valid'";
    $stmtCalc = $conn->prepare($sqlCalc);
    $stmtCalc->bind_param("i", $keuanganId);
    $stmtCalc->execute();
    $resultCalc = $stmtCalc->get_result();
    $calcRow = $resultCalc->fetch_assoc();
    $totalBayar = $calcRow['total'];
    
    // Update total_bayar di tabel keuangan
    $sqlUpdateKeuangan = "UPDATE keuangan SET total_bayar = ? WHERE keuangan_id = ?";
    $stmtUpdateKeuangan = $conn->prepare($sqlUpdateKeuangan);
    $stmtUpdateKeuangan->bind_param("ii", $totalBayar, $keuanganId);
    $stmtUpdateKeuangan->execute();
    
    $stmtCalc->close();
    $stmtUpdateKeuangan->close();
  }
  
  $stmtGetData->close();
  
  // Tentukan message berdasarkan status
  $statusText = $newStatus === 'valid' ? 'divalidasi' : ($newStatus === 'tolak' ? 'ditolak' : 'diubah menjadi menunggu');
  
  echo json_encode([
    'success' => true,
    'message' => "Pembayaran berhasil {$statusText}!"
  ]);
} else {
  echo json_encode([
    'success' => false,
    'message' => 'Gagal mengupdate status pembayaran: ' . $conn->error
  ]);
}

$stmt->close();
$conn->close();
?>
