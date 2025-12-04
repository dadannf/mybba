<?php
/**
 * Manual Payment Approval API
 * Endpoint untuk validasi manual pembayaran oleh admin
 */

error_reporting(E_ALL);
ini_set('display_errors', '0'); // Production mode
header('Content-Type: application/json');

// Include config
require_once '../config.php';

// Start session
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Hanya admin yang dapat memvalidasi pembayaran.'
    ]);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['pembayaran_id']) || empty($data['pembayaran_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID pembayaran tidak ditemukan'
    ]);
    exit;
}

$pembayaran_id = intval($data['pembayaran_id']);
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['username'] ?? 'Admin';

try {
    // Start transaction
    $conn->begin_transaction();

    // Get pembayaran details
    $query = "SELECT p.*, k.tahun, k.total_tagihan, k.nis as keuangan_nis,
              s.nama as nama_siswa, s.nis
              FROM pembayaran p
              JOIN keuangan k ON p.keuangan_id = k.keuangan_id
              JOIN siswa s ON k.nis = s.nis
              WHERE p.pembayaran_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pembayaran_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Pembayaran tidak ditemukan");
    }
    
    $pembayaran = $result->fetch_assoc();
    $stmt->close();

    // Check if already approved
    if ($pembayaran['status'] === 'valid') {
        echo json_encode([
            'success' => true,
            'message' => 'Pembayaran sudah divalidasi sebelumnya',
            'status' => 'Valid',
            'already_approved' => true
        ]);
        $conn->commit();
        exit;
    }

    // Update pembayaran status to VALID
    $updateQuery = "UPDATE pembayaran 
                    SET status = 'valid',
                        tanggal_bayar = NOW()
                    WHERE pembayaran_id = ?";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $pembayaran_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal mengupdate status pembayaran: " . $stmt->error);
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Pembayaran berhasil divalidasi',
        'status' => 'Valid',
        'pembayaran_id' => $pembayaran_id,
        'nominal' => $pembayaran['nominal_bayar'],
        'nama_siswa' => $pembayaran['nama_siswa'],
        'bulan_untuk' => $pembayaran['bulan_untuk'],
        'validated_by' => $admin_name,
        'validated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    error_log("Manual approve error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
