<?php
// =============================================
// File: process_import.php
// Deskripsi: Memproses import data siswa dari CSV
// =============================================

// Disable error display, only log errors
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Fatal Error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});

try {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../auth_check.php';
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load dependencies: ' . $e->getMessage()
    ]);
    exit;
}

// Set header FIRST before any output
header('Content-Type: application/json; charset=utf-8');

// Helper functions (defined ONCE, outside loops to avoid memory issues)
function convertDate($dateStr) {
    if (empty($dateStr)) return null;
    
    $dateStr = trim($dateStr);
    
    // Try DD/MM/YYYY format
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        
        // Validate date
        if (checkdate((int)$month, (int)$day, (int)$year)) {
            return "$year-$month-$day";
        }
    }
    
    // Already in YYYY-MM-DD format
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dateStr, $matches)) {
        $year = $matches[1];
        $month = $matches[2];
        $day = $matches[3];
        
        // Validate date
        if (checkdate((int)$month, (int)$day, (int)$year)) {
            return $dateStr;
        }
    }
    
    // Invalid date format - return null instead of partial data
    return null;
}

function cleanPhoneNumber($phone) {
    if (empty($phone)) return null;
    
    // Remove scientific notation (e.g., "6.28E+11" -> "628123456789")
    if (stripos($phone, 'E') !== false) {
        $phone = sprintf("%.0f", $phone);
    }
    
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add leading 0 if missing
    if (!empty($phone) && $phone[0] !== '0') {
        $phone = '0' . $phone;
    }
    
    return $phone;
}

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

// Handle UTF-8 BOM (EF BB BF) - Read once and position correctly
$position = 0;
$bom = fread($handle, 3);

if ($bom === chr(0xEF) . chr(0xBB) . chr(0xBF)) {
    // BOM exists, file pointer now at position 3 (correct position)
    $position = 3;
} else {
    // No BOM, rewind to start
    rewind($handle);
    $position = 0;
}

// Now read first line to detect delimiter (file pointer is at correct position)
$firstLine = fgets($handle);
if ($firstLine === false) {
    echo json_encode(['success' => false, 'message' => 'File CSV kosong']);
    fclose($handle);
    exit;
}

// Detect delimiter
$delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

// Go back to correct position (after BOM if exists, or start if no BOM)
rewind($handle);
if ($position === 3) {
    fread($handle, 3); // Skip BOM
}

// Now read headers with correct delimiter
$headers = fgetcsv($handle, 0, $delimiter);
if (!$headers) {
    echo json_encode(['success' => false, 'message' => 'File CSV kosong atau format tidak valid']);
    fclose($handle);
    exit;
}

// Trim whitespace from headers
$headers = array_map('trim', $headers);

// Debug: Validate header count
if (count($headers) < 5) {
    echo json_encode([
        'success' => false, 
        'message' => 'Format CSV tidak valid. Hanya ' . count($headers) . ' kolom terdeteksi. Pastikan delimiter semicolon (;) digunakan.',
        'debug' => [
            'detected_delimiter' => $delimiter,
            'header_count' => count($headers),
            'first_headers' => array_slice($headers, 0, 3)
        ]
    ]);
    fclose($handle);
    exit;
}
// error_log("Delimiter used: " . $delimiter);

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

// Validate database connection before starting transaction
if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: Connection not available'
    ]);
    fclose($handle);
    exit;
}

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    fclose($handle);
    exit;
}

// Start transaction
if (!$conn->begin_transaction()) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to start transaction: ' . $conn->error
    ]);
    fclose($handle);
    exit;
}

try {
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        // CRITICAL: Validate column count matches headers
        if (count($row) !== count($headers)) {
            $errors++;
            $errorDetails[] = "Baris " . ($imported + $skipped + $errors) . ": Jumlah kolom tidak sesuai. Expected: " . count($headers) . ", Got: " . count($row);
            
            // DEBUG: Show first mismatched row
            if ($errors === 1) {
                $errorDetails[] = "DEBUG - Delimiter detected: '$delimiter'";
                $errorDetails[] = "DEBUG - Headers count: " . count($headers);
                $errorDetails[] = "DEBUG - First 5 headers: " . implode(', ', array_slice($headers, 0, 5));
                $errorDetails[] = "DEBUG - Row data count: " . count($row);
                $errorDetails[] = "DEBUG - First 5 row values: " . implode(', ', array_slice($row, 0, 5));
                $errorDetails[] = "DEBUG - Column map tanggal_lahir index: " . ($columnMap['tanggal_lahir'] ?? 'NOT FOUND');
                if (isset($columnMap['tanggal_lahir'])) {
                    $errorDetails[] = "DEBUG - Value at tanggal_lahir index: " . ($row[$columnMap['tanggal_lahir']] ?? 'EMPTY');
                }
            }
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
        
        // Get optional fields (use helper functions defined at top)
        $nisn = isset($columnMap['nisn']) && isset($row[$columnMap['nisn']]) ? trim($row[$columnMap['nisn']]) : '';
        $nik = isset($columnMap['nik']) && isset($row[$columnMap['nik']]) ? trim($row[$columnMap['nik']]) : '';
        // Clean NIK from scientific notation
        if (!empty($nik)) {
            $nik = preg_replace('/[^0-9]/', '', $nik);
        }
        
        $tempat_lahir = isset($columnMap['tempat_lahir']) && isset($row[$columnMap['tempat_lahir']]) ? trim($row[$columnMap['tempat_lahir']]) : '';
        $tanggal_lahir_raw = isset($columnMap['tanggal_lahir']) && isset($row[$columnMap['tanggal_lahir']]) ? trim($row[$columnMap['tanggal_lahir']]) : '';
        $tanggal_lahir = convertDate($tanggal_lahir_raw);
        
        // Debug: Log if date conversion failed
        if (!empty($tanggal_lahir_raw) && empty($tanggal_lahir)) {
            $errors++;
            $errorDetails[] = "NIS $nis: Format tanggal lahir tidak valid: '$tanggal_lahir_raw' (gunakan DD/MM/YYYY)";
            
            // Debug info for first error
            if ($errors === 1) {
                $errorDetails[] = "DEBUG - Total kolom di row: " . count($row) . " | Expected: " . count($headers);
                $errorDetails[] = "DEBUG - Delimiter: '$delimiter' | Headers: " . implode(', ', array_slice($headers, 0, 5));
            }
            continue;
        }
        
        // Kolom yang sesuai dengan struktur database
        $ayah = isset($columnMap['ayah']) && isset($row[$columnMap['ayah']]) ? trim($row[$columnMap['ayah']]) : '';
        $ibu = isset($columnMap['ibu']) && isset($row[$columnMap['ibu']]) ? trim($row[$columnMap['ibu']]) : '';
        $alamat = isset($columnMap['alamat']) && isset($row[$columnMap['alamat']]) ? trim($row[$columnMap['alamat']]) : '';
        $email = isset($columnMap['email']) && isset($row[$columnMap['email']]) ? trim($row[$columnMap['email']]) : '';
        $no_hp_raw = isset($columnMap['no_hp']) && isset($row[$columnMap['no_hp']]) ? trim($row[$columnMap['no_hp']]) : '';
        $no_hp = cleanPhoneNumber($no_hp_raw);
        
        $status_siswa = isset($columnMap['status_siswa']) && isset($row[$columnMap['status_siswa']]) ? trim($row[$columnMap['status_siswa']]) : 'aktif';
        
        // DEBUG FIRST ROW: Log all values before insert
        if ($imported + $skipped + $errors === 0) {
            $debugMsg = "FIRST ROW DEBUG:";
            $debugMsg .= " | nis=$nis";
            $debugMsg .= " | nama=$nama";
            $debugMsg .= " | tanggal_lahir_raw='$tanggal_lahir_raw'";
            $debugMsg .= " | tanggal_lahir='$tanggal_lahir'";
            $debugMsg .= " | tempat_lahir=$tempat_lahir";
            $debugMsg .= " | columnMap[tanggal_lahir]=" . ($columnMap['tanggal_lahir'] ?? 'NOT SET');
            $errorDetails[] = $debugMsg;
        }
        
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
            $errorDetails[] = "NIS $nis: Gagal membuat akun user - " . $conn->error;
            continue;
        }
        
        $user_id = $conn->insert_id;
        $stmtUser->close();
        
        // 2. Insert siswa data (sesuai dengan struktur tabel di database)
        $stmtSiswa = $conn->prepare("INSERT INTO siswa 
            (nis, nisn, nik, user_id, nama, tempat_lahir, tanggal_lahir, jk, kelas, jurusan, 
             ayah, ibu, alamat, email, no_hp, status_siswa, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)");
        
        // s=string, i=integer
        // Count: 16 parameters total
        // nis, nisn, nik, user_id(i), nama, tempat_lahir, tanggal_lahir(DATE=string!), jk, kelas, jurusan, ayah, ibu, alamat, email, no_hp, status_siswa
        $stmtSiswa->bind_param(
            "sssissssssssssss",
            $nis, $nisn, $nik, $user_id, $nama, $tempat_lahir, $tanggal_lahir, $jk, $kelas, $jurusan,
            $ayah, $ibu, $alamat, $email, $no_hp, $status_siswa
        );
        
        // CRITICAL DEBUG: Check tanggal_lahir value before execute
        if ($tanggal_lahir === null || strlen($tanggal_lahir) < 10 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_lahir)) {
            $stmtSiswa->close();
            $conn->query("DELETE FROM users WHERE user_id = $user_id");
            $errors++;
            $errorDetails[] = "NIS $nis: Invalid date value before insert. Raw: '$tanggal_lahir_raw', Converted: '$tanggal_lahir', Length: " . strlen($tanggal_lahir ?? '') . ", Pattern match: " . (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_lahir ?? '') ? 'YES' : 'NO');
            continue;
        }
        
        if (!$stmtSiswa->execute()) {
            $stmtSiswa->close();
            // Rollback user creation
            $conn->query("DELETE FROM users WHERE user_id = $user_id");
            $errors++;
            
            // Enhanced error message with debug info
            $errorMsg = "NIS $nis: Gagal menyimpan data siswa - " . $conn->error;
            $errorMsg .= " | Raw date: '$tanggal_lahir_raw' -> Converted: '" . ($tanggal_lahir ?? 'NULL') . "'";
            $errorMsg .= " | Row columns: " . count($row) . " | Expected: " . count($headers);
            
            $errorDetails[] = $errorMsg;
            continue;
        }
        $stmtSiswa->close();
        
        // 3. Generate keuangan record untuk tahun ajaran aktif saja
        // Logic: Hanya buat data keuangan untuk tahun ajaran saat ini
        // Format tahun: YYYY/YYYY+1 (konsisten dengan create.php)
        
        $currentYear = intval(date('Y'));
        $tahunAjaran = $currentYear . '/' . ($currentYear + 1);
        
        // Extract tingkat kelas dari string kelas (contoh: "12" dari "12 TKJ")
        preg_match('/^(\d+)/', $kelas, $matches);
        $tingkatKelas = isset($matches[1]) ? intval($matches[1]) : 10;
        
        // Hitung tagihan berdasarkan kelas (kelas 10 = 200k/bulan, kelas 11-12 = 190k/bulan)
        if ($tingkatKelas == 10) {
            $tagihanPerBulan = 200000;
        } else {
            $tagihanPerBulan = 190000;
        }
        
        $totalTagihan = $tagihanPerBulan * 12; // Total 12 bulan
        
        // Check if record already exists (UNIQUE INDEX akan mencegah duplikasi di level database)
        $checkKeuanganStmt = $conn->prepare("SELECT keuangan_id FROM keuangan WHERE nis = ? AND tahun = ?");
        $checkKeuanganStmt->bind_param("ss", $nis, $tahunAjaran);
        $checkKeuanganStmt->execute();
        $checkKeuanganResult = $checkKeuanganStmt->get_result();
        
        if ($checkKeuanganResult->num_rows == 0) {
            // Insert new keuangan record
            $stmtKeuangan = $conn->prepare("INSERT INTO keuangan (nis, tahun, total_tagihan, total_bayar) VALUES (?, ?, ?, 0)");
            $stmtKeuangan->bind_param("ssd", $nis, $tahunAjaran, $totalTagihan);
            $stmtKeuangan->execute();
            $stmtKeuangan->close();
        }
        
        $checkKeuanganStmt->close();
        
        // Success - increment counter
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
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

if (isset($handle) && $handle) {
    fclose($handle);
}
if (isset($conn)) {
    $conn->close();
}
