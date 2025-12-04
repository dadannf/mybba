<?php
/**
 * API: Notifikasi
 * Endpoint untuk mengelola notifikasi admin
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

// Cek autentikasi admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

try {
    switch ($action) {
        case 'get':
            // Ambil daftar notifikasi
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            $sql = "SELECT * FROM notifikasi ORDER BY created_at DESC LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $notifications
            ]);
            break;

        case 'count':
            // Hitung notifikasi yang belum dibaca
            $sql = "SELECT COUNT(*) as total FROM notifikasi WHERE is_read = 0";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'count' => (int)$row['total']
            ]);
            break;

        case 'mark_read':
            // Tandai satu notifikasi sebagai sudah dibaca
            $notifId = $_POST['notifikasi_id'] ?? 0;
            
            $sql = "UPDATE notifikasi SET is_read = 1 WHERE notifikasi_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $notifId);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notifikasi ditandai sebagai sudah dibaca'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal memperbarui notifikasi'
                ]);
            }
            break;

        case 'mark_all_read':
            // Tandai semua notifikasi sebagai sudah dibaca
            $sql = "UPDATE notifikasi SET is_read = 1 WHERE is_read = 0";
            
            if ($conn->query($sql)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Semua notifikasi ditandai sebagai sudah dibaca'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal memperbarui notifikasi'
                ]);
            }
            break;

        case 'delete':
            // Hapus notifikasi
            $notifId = $_POST['notifikasi_id'] ?? 0;
            
            $sql = "DELETE FROM notifikasi WHERE notifikasi_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $notifId);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notifikasi berhasil dihapus'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menghapus notifikasi'
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
