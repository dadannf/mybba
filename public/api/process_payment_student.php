<?php
// =============================================
// File: Proses Pembayaran Siswa
// Deskripsi: Memproses pembayaran dari portal siswa
// =============================================

// Suppress semua error display (log saja)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Start output buffering untuk tangkap output tidak sengaja
ob_start();

session_start();

// Set header JSON di awal
header('Content-Type: application/json');

// Bersihkan output buffer dari session_start atau file included
ob_clean();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/notification_helper.php';
require_once __DIR__ . '/../includes/ocr_helper.php';  // OCR integration

// Cek session manual (tanpa redirect HTML)
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
    ]);
    exit;
}

$userRole = $_SESSION['role'];

// Pastikan yang login adalah siswa
if ($userRole !== 'siswa') {
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak! Hanya siswa yang dapat melakukan pembayaran.'
    ]);
    exit;
}

// Validasi input
if (!isset($_POST['keuangan_id']) || !isset($_POST['index_bulan'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap!'
    ]);
    exit;
}

$keuangan_id = esc($_POST['keuangan_id']);
$index_bulan = intval($_POST['index_bulan']);
$tanggal_bayar = esc($_POST['tanggal_bayar']);
$nominal_bayar = intval($_POST['nominal_bayar']);
$metode = esc($_POST['metode']);
$tempat_bayar = esc($_POST['tempat_bayar']);

// Validasi keuangan_id milik siswa yang login
$username = $_SESSION['username']; // NIS siswa
$sqlCheck = "SELECT k.keuangan_id FROM keuangan k 
             WHERE k.keuangan_id = '$keuangan_id' AND k.nis = '$username'";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Data keuangan tidak valid atau bukan milik Anda!'
    ]);
    exit;
}

// Validasi nominal minimal
if ($nominal_bayar < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Nominal pembayaran minimal Rp 1!'
    ]);
    exit;
}

// Validasi index bulan (1-12)
if ($index_bulan < 1 || $index_bulan > 12) {
    echo json_encode([
        'success' => false,
        'message' => 'Index bulan tidak valid!'
    ]);
    exit;
}

// =============================================
// LOGIKA PEMBAYARAN BERURUTAN
// =============================================
// Ambil semua pembayaran yang sudah ada (terurut dari bulan pertama)
$sqlGetAllPembayaran = "SELECT pembayaran_id, status, nominal_bayar 
                        FROM pembayaran 
                        WHERE keuangan_id = '$keuangan_id' 
                        ORDER BY pembayaran_id ASC";
$resultAllPembayaran = $conn->query($sqlGetAllPembayaran);

$pembayaranList = [];
$bulanTerakhirValid = 0; // Index bulan terakhir yang sudah valid/menunggu

while ($p = $resultAllPembayaran->fetch_assoc()) {
    $pembayaranList[] = $p;
    // Hitung bulan terakhir yang statusnya valid atau menunggu
    if ($p['status'] === 'valid' || $p['status'] === 'menunggu') {
        $bulanTerakhirValid++;
    }
}

$jumlahPembayaran = count($pembayaranList);

// VALIDASI PEMBAYARAN BERURUTAN
// User hanya bisa bayar bulan berikutnya setelah bulan sebelumnya VALID atau MENUNGGU
// Contoh: 
// - Bulan 1 = valid → bisa bayar bulan 2
// - Bulan 1 = menunggu → bisa bayar bulan 2 (sudah submit pembayaran)
// - Bulan 1 = tolak → TIDAK bisa bayar bulan 2, harus bayar ulang bulan 1
if ($index_bulan > ($bulanTerakhirValid + 1)) {
    echo json_encode([
        'success' => false,
        'message' => 'Pembayaran harus berurutan! Selesaikan pembayaran bulan sebelumnya terlebih dahulu.'
    ]);
    exit;
}

// CEK APAKAH BULAN INI SUDAH DIBAYAR
if ($index_bulan <= $jumlahPembayaran) {
    $pembayaranIndex = $index_bulan - 1; // Array index dimulai dari 0
    $pembayaranLama = $pembayaranList[$pembayaranIndex];
    
    // Jika status bukan 'tolak', tidak boleh bayar ulang
    if ($pembayaranLama['status'] !== 'tolak') {
        $statusText = $pembayaranLama['status'] === 'valid' ? 'sudah lunas' : 'sedang menunggu validasi';
        echo json_encode([
            'success' => false,
            'message' => 'Bulan ini ' . $statusText . '! Tidak dapat melakukan pembayaran ulang.'
        ]);
        exit;
    }
    
    // JIKA STATUS 'TOLAK', IZINKAN BAYAR ULANG
    $pembayaran_id = $pembayaranLama['pembayaran_id'];
    $bukti_bayar = null;
    
    // Handle upload bukti pembayaran (prioritas: kamera > file upload)
    // Prioritas 1: Cek foto dari kamera (base64)
    if (isset($_POST['camera_photo']) && !empty($_POST['camera_photo'])) {
        $photoData = $_POST['camera_photo'];
        
        if (preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $photoData, $matches)) {
            $uploadDir = __DIR__ . '/../uploads/bukti_bayar/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $imageType = $matches[1];
            if ($imageType === 'jpg') $imageType = 'jpeg';
            
            $photoData = preg_replace('/^data:image\/\w+;base64,/', '', $photoData);
            $photoData = base64_decode($photoData);
            
            if ($photoData !== false) {
                $fileName = 'camera_' . $keuangan_id . '_' . $index_bulan . '_' . time() . '.' . $imageType;
                $targetFile = $uploadDir . $fileName;
                
                if (file_put_contents($targetFile, $photoData)) {
                    @chmod($targetFile, 0644);
                    $bukti_bayar = $fileName;
                }
            }
        }
    }
    
    // Prioritas 2: Jika tidak ada foto kamera, cek file upload
    if ($bukti_bayar === null && isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/bukti_bayar/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
        
        if (in_array($fileExt, $allowedExt)) {
            $fileName = 'bukti_' . $keuangan_id . '_' . $index_bulan . '_' . time() . '.' . $fileExt;
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $targetFile)) {
                @chmod($targetFile, 0644);
                $bukti_bayar = $fileName;
            }
        }
    }
    
    // Update pembayaran yang ditolak
    if ($bukti_bayar) {
        $sqlUpdate = "UPDATE pembayaran SET 
                     tanggal_bayar = '$tanggal_bayar',
                     nominal_bayar = $nominal_bayar,
                     metode = '$metode',
                     tempat_bayar = '$tempat_bayar',
                     bukti_bayar = '$bukti_bayar',
                     status = 'menunggu'
                     WHERE pembayaran_id = $pembayaran_id";
    } else {
        $sqlUpdate = "UPDATE pembayaran SET 
                     tanggal_bayar = '$tanggal_bayar',
                     nominal_bayar = $nominal_bayar,
                     metode = '$metode',
                     tempat_bayar = '$tempat_bayar',
                     status = 'menunggu'
                     WHERE pembayaran_id = $pembayaran_id";
    }
    
    if ($conn->query($sqlUpdate)) {
        // Ambil data siswa untuk notifikasi
        $sqlSiswa = "SELECT s.nis, s.nama FROM siswa s 
                     JOIN keuangan k ON s.nis = k.nis 
                     WHERE k.keuangan_id = '$keuangan_id'";
        $resultSiswa = $conn->query($sqlSiswa);
        if ($resultSiswa && $row = $resultSiswa->fetch_assoc()) {
            // Buat notifikasi untuk admin (upload ulang)
            createPaymentNotification($conn, $row['nis'], $row['nama'], $nominal_bayar, $keuangan_id);
        }
        
        // Response
        $response = [
            'success' => true,
            'message' => 'Pembayaran ulang berhasil diajukan! Menunggu validasi admin.',
            'auto_validated' => false
        ];
        
        ob_end_clean();
        echo json_encode($response);
        exit;
    } else {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Gagal memproses pembayaran: ' . $conn->error
        ]);
        exit;
    }
    exit;
}

// =============================================
// PROSES PEMBAYARAN BARU
// =============================================
// Proses upload bukti pembayaran (prioritas: kamera > file upload)
$bukti_bayar = null;

// Prioritas 1: Cek foto dari kamera (base64)
if (isset($_POST['camera_photo']) && !empty($_POST['camera_photo'])) {
    $photoData = $_POST['camera_photo'];
    
    if (preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $photoData, $matches)) {
        $uploadDir = __DIR__ . '/../uploads/bukti_bayar/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $imageType = $matches[1];
        if ($imageType === 'jpg') $imageType = 'jpeg';
        
        $photoData = preg_replace('/^data:image\/\w+;base64,/', '', $photoData);
        $photoData = base64_decode($photoData);
        
        if ($photoData !== false) {
            $fileName = 'camera_' . $keuangan_id . '_' . $index_bulan . '_' . time() . '.' . $imageType;
            $targetFile = $uploadDir . $fileName;
            
            if (file_put_contents($targetFile, $photoData)) {
                @chmod($targetFile, 0644);
                $bukti_bayar = $fileName;
            }
        }
    }
}

// Prioritas 2: Jika tidak ada foto kamera, cek file upload
if ($bukti_bayar === null && isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/bukti_bayar/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExt = strtolower(pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
    
    if (in_array($fileExt, $allowedExt)) {
        $fileName = 'bukti_' . $keuangan_id . '_' . $index_bulan . '_' . time() . '.' . $fileExt;
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $targetFile)) {
            @chmod($targetFile, 0644);
            $bukti_bayar = $fileName;
        }
    }
}

// Insert pembayaran baru
$ocr_decision = null;
$ocr_validation_id = null;
$auto_approved = false;

// Jika ada bukti bayar, validasi dengan OCR
if ($bukti_bayar) {
    // Get siswa data for OCR
    $sqlSiswaData = "SELECT s.nis, s.nama FROM siswa s 
                     JOIN keuangan k ON s.nis = k.nis 
                     WHERE k.keuangan_id = '$keuangan_id'";
    $resultSiswaData = $conn->query($sqlSiswaData);
    
    if ($resultSiswaData && $rowSiswa = $resultSiswaData->fetch_assoc()) {
        // Call OCR validation
        $ocrResult = processTransferWithOCR(
            $targetFile,
            'siswa',
            $rowSiswa['nis'],
            $nominal_bayar,
            $rowSiswa['nis'],
            $rowSiswa['nama'],
            $keuangan_id
        );
        
        if ($ocrResult['success']) {
            $ocr_decision = $ocrResult['decision'];
            $ocr_validation_id = $ocrResult['validation_id'] ?? null;
            
            // Tentukan status berdasarkan OCR decision
            if ($ocr_decision === 'accept') {
                $status = 'valid';  // Auto approve
                $auto_approved = true;
            } elseif ($ocr_decision === 'reject') {
                $status = 'tolak';  // Auto reject (sesuai ENUM database)
            } else {
                $status = 'menunggu';  // Need review
            }
        } else {
            // OCR gagal, set manual review
            $status = 'menunggu';
        }
    } else {
        $status = 'menunggu';
    }
    
    $sqlInsert = "INSERT INTO pembayaran (keuangan_id, tanggal_bayar, nominal_bayar, metode, tempat_bayar, bukti_bayar, status) 
                  VALUES ('$keuangan_id', '$tanggal_bayar', $nominal_bayar, '$metode', '$tempat_bayar', '$bukti_bayar', '$status')";
} else {
    // Tidak ada bukti, manual review
    $sqlInsert = "INSERT INTO pembayaran (keuangan_id, tanggal_bayar, nominal_bayar, metode, tempat_bayar, status) 
                  VALUES ('$keuangan_id', '$tanggal_bayar', $nominal_bayar, '$metode', '$tempat_bayar', 'menunggu')";
}

if ($conn->query($sqlInsert)) {
    $pembayaran_id = $conn->insert_id;
    
    // Ambil data siswa untuk notifikasi
    $sqlSiswa = "SELECT s.nis, s.nama FROM siswa s 
                 JOIN keuangan k ON s.nis = k.nis 
                 WHERE k.keuangan_id = '$keuangan_id'";
    $resultSiswa = $conn->query($sqlSiswa);
    if ($resultSiswa && $row = $resultSiswa->fetch_assoc()) {
        // Buat notifikasi untuk admin hanya jika bukan auto-approved
        if (!$auto_approved) {
            createPaymentNotification($conn, $row['nis'], $row['nama'], $nominal_bayar, $keuangan_id);
        }
    }
    
    // Update total_bayar di tabel keuangan (hanya hitung yang status 'valid')
    $sqlUpdateKeuangan = "UPDATE keuangan k
                          SET k.total_bayar = (
                              SELECT COALESCE(SUM(p.nominal_bayar), 0)
                              FROM pembayaran p
                              WHERE p.keuangan_id = k.keuangan_id AND p.status = 'valid'
                          )
                          WHERE k.keuangan_id = '$keuangan_id'";
    $conn->query($sqlUpdateKeuangan);
    
    // Response dengan OCR info
    $message = 'Pembayaran berhasil diajukan!';
    if ($auto_approved) {
        $message = '✅ Pembayaran OTOMATIS DISETUJUI oleh sistem AI OCR!';
    } elseif ($ocr_decision === 'reject') {
        $message = '❌ Pembayaran DITOLAK oleh sistem AI OCR. Silakan upload bukti yang lebih jelas.';
    } elseif ($ocr_decision === 'review') {
        $message = '⚠️ Pembayaran perlu REVIEW MANUAL oleh admin.';
    } else {
        $message = 'Pembayaran berhasil diajukan! Menunggu validasi admin.';
    }
    
    $response = [
        'success' => true,
        'message' => $message,
        'pembayaran_id' => $pembayaran_id,
        'auto_validated' => $auto_approved,
        'ocr_decision' => $ocr_decision,
        'ocr_validation_id' => $ocr_validation_id,
        'status' => $status ?? 'menunggu'
    ];
    
    ob_end_clean();
    echo json_encode($response);
    exit;
} else {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan pembayaran: ' . $conn->error
    ]);
    exit;
}
$conn->close();
?>
