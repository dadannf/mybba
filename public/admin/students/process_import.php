<?php
// =============================================
// File: process_import.php
// Deskripsi: Memproses import data siswa dari CSV
// =============================================

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';

header('Content-Type: application/json');

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if file uploaded
if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan atau error saat upload']);
    exit;
}

$file = $_FILES['csvFile'];
$skipDuplicate = isset($_POST['skipDuplicate']) && $_POST['skipDuplicate'] == '1';

// Validate file type
$allowedExtensions = ['csv', 'xlsx', 'xls'];
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan .csv, .xlsx, atau .xls']);
    exit;
}

// Handle Excel files (convert to CSV)
$csvFile = $file['tmp_name'];
if ($fileExtension === 'xlsx' || $fileExtension === 'xls') {
    // Untuk Excel, kita perlu library tambahan atau convert manual
    // Untuk sementara, hanya support CSV
    echo json_encode(['success' => false, 'message' => 'Saat ini hanya mendukung format CSV. Silakan export Excel ke CSV terlebih dahulu.']);
    exit;
}

// Read CSV file
$handle = fopen($csvFile, 'r');
if (!$handle) {
    echo json_encode(['success' => false, 'message' => 'Gagal membaca file CSV']);
    exit;
}

// Skip BOM if exists
$bom = fread($handle, 3);
if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
    rewind($handle);
}

// Read header
$headers = fgetcsv($handle);
if (!$headers) {
    echo json_encode(['success' => false, 'message' => 'File CSV kosong atau format tidak valid']);
    fclose($handle);
    exit;
}

// Validate required columns
$requiredColumns = ['nis', 'nama', 'jk', 'kelas', 'jurusan'];
$missingColumns = array_diff($requiredColumns, $headers);
if (!empty($missingColumns)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Kolom required tidak ditemukan: ' . implode(', ', $missingColumns)
    ]);
    fclose($handle);
    exit;
}

// Map header indices
$columnMap = array_flip($headers);

// Statistics
$imported = 0;
$skipped = 0;
$errors = 0;
$errorDetails = [];

// Start transaction
$conn->begin_transaction();

try {
    while (($row = fgetcsv($handle)) !== false) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Get data from row
        $nis = isset($row[$columnMap['nis']]) ? trim($row[$columnMap['nis']]) : '';
        $nama = isset($row[$columnMap['nama']]) ? trim($row[$columnMap['nama']]) : '';
        $jk = isset($row[$columnMap['jk']]) ? trim($row[$columnMap['jk']]) : '';
        $kelas = isset($row[$columnMap['kelas']]) ? trim($row[$columnMap['kelas']]) : '';
        $jurusan = isset($row[$columnMap['jurusan']]) ? trim($row[$columnMap['jurusan']]) : '';
        
        // Validate required fields
        if (empty($nis) || empty($nama) || empty($jk) || empty($kelas) || empty($jurusan)) {
            $errors++;
            $errorDetails[] = "Baris " . ($imported + $skipped + $errors + 1) . ": Data required tidak lengkap";
            continue;
        }
        
        // Check if NIS already exists
        $checkStmt = $conn->prepare("SELECT nis FROM siswa WHERE nis = ?");
        $checkStmt->bind_param("s", $nis);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $checkStmt->close();
            if ($skipDuplicate) {
                $skipped++;
                continue;
            } else {
                $errors++;
                $errorDetails[] = "NIS $nis sudah terdaftar";
                continue;
            }
        }
        $checkStmt->close();
        
        // Get optional fields
        $nisn = isset($columnMap['nisn']) && isset($row[$columnMap['nisn']]) ? trim($row[$columnMap['nisn']]) : '';
        $nik = isset($columnMap['nik']) && isset($row[$columnMap['nik']]) ? trim($row[$columnMap['nik']]) : '';
        $tempat_lahir = isset($columnMap['tempat_lahir']) && isset($row[$columnMap['tempat_lahir']]) ? trim($row[$columnMap['tempat_lahir']]) : '';
        $tanggal_lahir = isset($columnMap['tanggal_lahir']) && isset($row[$columnMap['tanggal_lahir']]) ? trim($row[$columnMap['tanggal_lahir']]) : null;
        $agama = isset($columnMap['agama']) && isset($row[$columnMap['agama']]) ? trim($row[$columnMap['agama']]) : '';
        $alamat = isset($columnMap['alamat']) && isset($row[$columnMap['alamat']]) ? trim($row[$columnMap['alamat']]) : '';
        $email = isset($columnMap['email']) && isset($row[$columnMap['email']]) ? trim($row[$columnMap['email']]) : '';
        $no_hp = isset($columnMap['no_hp']) && isset($row[$columnMap['no_hp']]) ? trim($row[$columnMap['no_hp']]) : '';
        $nama_ayah = isset($columnMap['nama_ayah']) && isset($row[$columnMap['nama_ayah']]) ? trim($row[$columnMap['nama_ayah']]) : '';
        $nama_ibu = isset($columnMap['nama_ibu']) && isset($row[$columnMap['nama_ibu']]) ? trim($row[$columnMap['nama_ibu']]) : '';
        $pekerjaan_ayah = isset($columnMap['pekerjaan_ayah']) && isset($row[$columnMap['pekerjaan_ayah']]) ? trim($row[$columnMap['pekerjaan_ayah']]) : '';
        $pekerjaan_ibu = isset($columnMap['pekerjaan_ibu']) && isset($row[$columnMap['pekerjaan_ibu']]) ? trim($row[$columnMap['pekerjaan_ibu']]) : '';
        $no_hp_ortu = isset($columnMap['no_hp_ortu']) && isset($row[$columnMap['no_hp_ortu']]) ? trim($row[$columnMap['no_hp_ortu']]) : '';
        $status_siswa = isset($columnMap['status_siswa']) && isset($row[$columnMap['status_siswa']]) ? trim($row[$columnMap['status_siswa']]) : 'aktif';
        $tahun_masuk = isset($columnMap['tahun_masuk']) && isset($row[$columnMap['tahun_masuk']]) ? trim($row[$columnMap['tahun_masuk']]) : date('Y');
        
        // Generate password: bba#[4 digit terakhir NIS]
        $last4Digits = substr($nis, -4);
        $plainPassword = 'bba#' . $last4Digits;
        $password = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        // 1. Insert user account
        $stmtUser = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, 'siswa', ?)");
        $stmtUser->bind_param("sss", $nis, $password, $email);
        
        if (!$stmtUser->execute()) {
            $stmtUser->close();
            $errors++;
            $errorDetails[] = "NIS $nis: Gagal membuat akun user";
            continue;
        }
        
        $user_id = $conn->insert_id;
        $stmtUser->close();
        
        // 2. Insert siswa data
        $stmtSiswa = $conn->prepare("INSERT INTO siswa 
            (nis, nisn, nik, user_id, nama, tempat_lahir, tanggal_lahir, jk, kelas, jurusan, 
             agama, alamat, email, no_hp, nama_ayah, nama_ibu, pekerjaan_ayah, pekerjaan_ibu, 
             no_hp_ortu, status_siswa, tahun_masuk, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)");
        
        $stmtSiswa->bind_param(
            "sssissssssssssssssss",
            $nis, $nisn, $nik, $user_id, $nama, $tempat_lahir, $tanggal_lahir, $jk, $kelas, $jurusan,
            $agama, $alamat, $email, $no_hp, $nama_ayah, $nama_ibu, $pekerjaan_ayah, $pekerjaan_ibu,
            $no_hp_ortu, $status_siswa, $tahun_masuk
        );
        
        if (!$stmtSiswa->execute()) {
            $stmtSiswa->close();
            // Rollback user creation
            $conn->query("DELETE FROM users WHERE user_id = $user_id");
            $errors++;
            $errorDetails[] = "NIS $nis: Gagal menyimpan data siswa";
            continue;
        }
        $stmtSiswa->close();
        
        // 3. Create default keuangan record
        $currentYear = date('Y');
        $tahunAjaran = $currentYear . '/' . ($currentYear + 1);
        
        // Hitung total tagihan berdasarkan kelas (12 bulan)
        preg_match('/^(\d+)/', $kelas, $matches);
        $tingkatKelas = isset($matches[1]) ? intval($matches[1]) : 10;
        $tagihanPerBulan = ($tingkatKelas == 10) ? 200000 : 190000;
        $totalTagihan = $tagihanPerBulan * 12; // Total 12 bulan
        
        $stmtKeuangan = $conn->prepare("INSERT INTO keuangan (nis, tahun, total_tagihan, total_bayar) VALUES (?, ?, ?, 0)");
        $stmtKeuangan->bind_param("ssd", $nis, $tahunAjaran, $totalTagihan);
        $stmtKeuangan->execute();
        $stmtKeuangan->close();
        
        $imported++;
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'imported' => $imported,
        'skipped' => $skipped,
        'errors' => $errors,
        'errorDetails' => $errorDetails
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

fclose($handle);
$conn->close();
