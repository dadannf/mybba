<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Get informasi ID from request
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ID'
    ]);
    exit;
}

// Query untuk mendapatkan detail informasi
// CONVERT untuk mengatasi perbedaan collation
$sql = "SELECT i.informasi_id, i.judul, i.isi, i.foto, i.created_at, i.created_by, u.username as penulis 
        FROM informasi i
        LEFT JOIN users u ON i.created_by COLLATE utf8mb4_general_ci = u.username COLLATE utf8mb4_general_ci
        WHERE i.informasi_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Informasi tidak ditemukan'
    ]);
}

$stmt->close();
$conn->close();
?>
