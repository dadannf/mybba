<?php
// Suppress any output before JSON
ob_start();

session_start();
require_once __DIR__ . '/../config.php';

// Clear any output buffer and set JSON header
ob_end_clean();

// Set header untuk JSON response
header('Content-Type: application/json');

// Function to calculate monthly payment based on class
function getTagihanPerBulan($kelas) {
  // Extract class number from format like "10 TKJ 1", "11 RPL", "12-A", etc
  preg_match('/^(\d+)/', $kelas, $matches);
  $kelasNumber = isset($matches[1]) ? intval($matches[1]) : 0;
  
  // Kelas 10: Rp 200,000 per bulan
  // Kelas 11-12: Rp 190,000 per bulan
  if ($kelasNumber == 10) {
    return 200000;
  } else if ($kelasNumber >= 11 && $kelasNumber <= 12) {
    return 190000;
  }
  
  // Default untuk kelas yang tidak dikenali
  return 200000;
}

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
  ]);
  exit;
}

// Ambil data dari POST
$keuanganId = isset($_POST['keuangan_id']) ? intval($_POST['keuangan_id']) : 0;
$indexBulan = isset($_POST['index_bulan']) ? intval($_POST['index_bulan']) : 0;
$tanggalBayar = isset($_POST['tanggal_bayar']) ? trim($_POST['tanggal_bayar']) : '';
$nominalBayar = isset($_POST['nominal_bayar']) ? intval($_POST['nominal_bayar']) : 0;
$metode = isset($_POST['metode']) ? trim($_POST['metode']) : '';

// Handle tempat_bayar for different payment methods
if ($metode === 'Tunai') {
  $tempatBayar = isset($_POST['tempat_bayar_tunai']) ? trim($_POST['tempat_bayar_tunai']) : 'Kas Sekolah';
} else {
  $tempatBayar = isset($_POST['tempat_bayar']) ? trim($_POST['tempat_bayar']) : '';
}

$catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
$penerimaKasir = isset($_POST['penerima_kasir']) ? trim($_POST['penerima_kasir']) : '';

// For tunai payment, store diterima_oleh separately
$diterimaOleh = '';
if ($metode === 'Tunai' && !empty($penerimaKasir)) {
  $diterimaOleh = $penerimaKasir;
}

// Get student class for tagihan calculation
$sqlKelas = "SELECT s.kelas FROM keuangan k JOIN siswa s ON k.nis = s.nis WHERE k.keuangan_id = ?";
$stmtKelas = $conn->prepare($sqlKelas);
$stmtKelas->bind_param("i", $keuanganId);
$stmtKelas->execute();
$resultKelas = $stmtKelas->get_result();
$dataKelas = $resultKelas->fetch_assoc();
$stmtKelas->close();

if (!$dataKelas) {
  echo json_encode([
    'success' => false,
    'message' => 'Data kelas siswa tidak ditemukan'
  ]);
  exit;
}

// Calculate tagihan based on class
$kelas = $dataKelas['kelas'];
$tagihanPerBulan = getTagihanPerBulan($kelas);

// For Tunai payment: Handle non-SPP payments (seragam, ujian, kegiatan, dll)
$nominalSPP = $nominalBayar; // SPP amount for finance calculation
$nominalNonSPP = 0; // Non-SPP payment (uniform, exam, activities, etc)

if ($metode === 'Tunai' && $nominalBayar > $tagihanPerBulan) {
  $nominalNonSPP = $nominalBayar - $tagihanPerBulan;
  $nominalSPP = $tagihanPerBulan; // Only SPP amount for finance
  
  // Add non-SPP payment info to catatan
  $nonSPPFormatted = number_format($nominalNonSPP, 0, ',', '.');
  $nonSPPNote = "Pembayaran Non-SPP: Rp " . $nonSPPFormatted . " (Seragam/Ujian/Kegiatan/dll)";
  
  if (!empty($catatan)) {
    $catatan .= "\n" . $nonSPPNote;
  } else {
    $catatan = $nonSPPNote;
  }
}

// Debug log (bisa dihapus nanti)
error_log("Proses Pembayaran - Tempat Bayar: " . $tempatBayar);
error_log("POST Data: " . print_r($_POST, true));

// Validasi input
if ($keuanganId <= 0) {
  echo json_encode([
    'success' => false,
    'message' => 'ID Keuangan tidak valid'
  ]);
  exit;
}

if ($indexBulan < 1 || $indexBulan > 12) {
  echo json_encode([
    'success' => false,
    'message' => 'Index bulan tidak valid (1-12)'
  ]);
  exit;
}

if (empty($tanggalBayar)) {
  echo json_encode([
    'success' => false,
    'message' => 'Tanggal bayar harus diisi'
  ]);
  exit;
}

if ($nominalBayar <= 0) {
  echo json_encode([
    'success' => false,
    'message' => 'Nominal bayar harus lebih dari 0'
  ]);
  exit;
}

if (empty($metode)) {
  echo json_encode([
    'success' => false,
    'message' => 'Metode pembayaran harus dipilih'
  ]);
  exit;
}

if (empty($tempatBayar)) {
  echo json_encode([
    'success' => false,
    'message' => 'Tempat bayar harus diisi'
  ]);
  exit;
}

// Cek apakah bulan ini sudah pernah dibayar
$sqlCheck = "SELECT pembayaran_id FROM pembayaran WHERE keuangan_id = ? ORDER BY pembayaran_id ASC";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $keuanganId);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

$countPembayaran = 0;
while ($resultCheck->fetch_assoc()) {
  $countPembayaran++;
}
$stmtCheck->close();

// Index bulan dimulai dari 1 (Juli) sampai 12 (Juni)
// Jika sudah ada pembayaran untuk bulan ini, tolak
if ($countPembayaran >= $indexBulan) {
  echo json_encode([
    'success' => false,
    'message' => 'Pembayaran untuk bulan ini sudah pernah dilakukan!'
  ]);
  exit;
}

// Handle upload bukti bayar (file upload atau foto dari kamera)
$buktiBayar = null;

// Prioritas 1: Cek foto dari kamera (base64)
if (isset($_POST['camera_photo']) && !empty($_POST['camera_photo'])) {
  $photoData = $_POST['camera_photo'];
  
  // Validasi format base64
  if (preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $photoData, $matches)) {
    // Gunakan path absolut dari public directory
    $uploadDir = __DIR__ . '/../uploads/bukti_bayar/';
    
    // Buat direktori jika belum ada
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    
    // Extract image type
    $imageType = $matches[1];
    if ($imageType === 'jpg') $imageType = 'jpeg';
    
    // Remove base64 header
    $photoData = preg_replace('/^data:image\/\w+;base64,/', '', $photoData);
    $photoData = base64_decode($photoData);
    
    if ($photoData !== false) {
      // Generate unique filename
      $fileName = 'camera_' . $keuanganId . '_' . $indexBulan . '_' . time() . '.' . $imageType;
      $uploadPath = $uploadDir . $fileName;
      
      if (file_put_contents($uploadPath, $photoData)) {
        @chmod($uploadPath, 0644);
        $buktiBayar = $fileName;
        error_log("Foto dari kamera berhasil disimpan: " . $fileName);
      } else {
        error_log("Gagal menyimpan foto dari kamera");
      }
    }
  }
}

// Prioritas 2: Jika tidak ada foto kamera, cek file upload
if ($buktiBayar === null && isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_OK) {
  // Gunakan path absolut dari public directory
  $uploadDir = __DIR__ . '/../uploads/bukti_bayar/';
  
  // Buat direktori jika belum ada
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }
  
  $fileExtension = strtolower(pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION));
  $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
  
  if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode([
      'success' => false,
      'message' => 'Format file tidak valid. Gunakan: JPG, PNG, PDF, atau WEBP'
    ]);
    exit;
  }
  
  // Generate unique filename
  $fileName = 'bukti_' . $keuanganId . '_' . $indexBulan . '_' . time() . '.' . $fileExtension;
  $uploadPath = $uploadDir . $fileName;
  
  if (move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $uploadPath)) {
    @chmod($uploadPath, 0644);
    $buktiBayar = $fileName; // Simpan hanya nama file (konsisten dengan database)
    error_log("File upload berhasil disimpan: " . $fileName);
  }
}

// Insert pembayaran baru dengan status "menunggu" (or "valid" for tunai without bukti)
// For Tunai payments without bukti, auto-approve
if ($metode === 'Tunai' && empty($buktiBayar)) {
  $status = 'valid'; // Auto-approve tunai
} else {
  $status = 'menunggu'; // Pending for transfer
}

$sqlInsert = "INSERT INTO pembayaran (keuangan_id, tanggal_bayar, nominal_bayar, metode, tempat_bayar, bukti_bayar, status, catatan, diterima_oleh) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("isdssssss", $keuanganId, $tanggalBayar, $nominalSPP, $metode, $tempatBayar, $buktiBayar, $status, $catatan, $diterimaOleh);

if ($stmtInsert->execute()) {
  // Update total_bayar di tabel keuangan untuk real-time progress (hanya hitung yang valid)
  $sqlUpdateKeuangan = "UPDATE keuangan SET total_bayar = (
                          SELECT COALESCE(SUM(nominal_bayar), 0) 
                          FROM pembayaran 
                          WHERE keuangan_id = ? AND status = 'valid'
                        ) WHERE keuangan_id = ?";
  $stmtUpdateKeuangan = $conn->prepare($sqlUpdateKeuangan);
  $stmtUpdateKeuangan->bind_param("ii", $keuanganId, $keuanganId);
  $stmtUpdateKeuangan->execute();
  $stmtUpdateKeuangan->close();
  
  // Get the inserted payment ID
  $pembayaranId = $stmtInsert->insert_id;
  
  $bulanList = [
    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
  ];
  
  $namaBulan = $bulanList[$indexBulan];
  
  // For Tunai payment, return pembayaran_id for printing
  if ($metode === 'Tunai' && $status === 'valid') {
    echo json_encode([
      'success' => true,
      'message' => "Pembayaran Tunai bulan {$namaBulan} berhasil! Klik tombol cetak untuk mencetak kwitansi.",
      'pembayaran_id' => $pembayaranId,
      'metode' => 'Tunai',
      'status' => 'valid'
    ]);
  } else {
    echo json_encode([
      'success' => true,
      'message' => "Pembayaran bulan {$namaBulan} berhasil diajukan! Status: Menunggu Validasi Admin."
    ]);
  }
} else {
  echo json_encode([
    'success' => false,
    'message' => 'Gagal menyimpan pembayaran: ' . $conn->error
  ]);
}

$stmtInsert->close();
$conn->close();
?>
