<?php
// =============================================
// Test OCR System - Demo Upload & Validasi
// =============================================

require_once 'config.php';
require_once 'includes/ocr_api_client.php';
require_once 'includes/ocr_helper.php';

session_start();

// Simple auth check
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$result = null;
$error = null;

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_transfer'])) {
    $file = $_FILES['bukti_transfer'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Save uploaded file
        $uploadDir = __DIR__ . '/uploads/test_ocr/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = 'test_' . time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Test data untuk validasi
            $expected_amount = isset($_POST['expected_amount']) ? floatval($_POST['expected_amount']) : 500000;
            $expected_nis = isset($_POST['expected_nis']) ? $_POST['expected_nis'] : '22211161';
            $expected_nama = isset($_POST['expected_nama']) ? $_POST['expected_nama'] : 'Bagas Pratama';
            
            // Call OCR API
            $result = processTransferWithOCR(
                $targetPath,
                'admin',  // uploader_type
                $_SESSION['username'],  // uploader_id
                $expected_amount,
                $expected_nis,
                $expected_nama,
                null  // keuangan_id (optional)
            );
        } else {
            $error = 'Gagal upload file!';
        }
    } else {
        $error = 'Error upload: ' . $file['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test OCR System - BBA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9ff;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #764ba2;
            background: #f0f1ff;
        }
        .upload-area i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        .result-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .ocr-detail {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid #e0e0e0;
        }
        .confidence-bar {
            height: 8px;
            border-radius: 4px;
            background: #e0e0e0;
            overflow: hidden;
        }
        .confidence-fill {
            height: 100%;
            transition: width 0.5s;
        }
        .badge-large {
            font-size: 16px;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Header -->
                <div class="text-center text-white mb-4">
                    <h1><i class="fas fa-robot"></i> Test OCR System</h1>
                    <p class="lead">Upload bukti transfer untuk test validasi otomatis</p>
                </div>

                <!-- Upload Form -->
                <div class="test-card">
                    <h4 class="mb-4"><i class="fas fa-upload text-primary"></i> Upload Bukti Transfer</h4>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="upload-area mb-4" onclick="document.getElementById('file-input').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h5>Klik untuk Upload File</h5>
                            <p class="text-muted mb-0">Atau drag & drop file disini</p>
                            <small class="text-muted">Format: JPG, PNG, PDF (Max 10MB)</small>
                            <input type="file" id="file-input" name="bukti_transfer" accept="image/*,application/pdf" style="display: none" required onchange="this.form.querySelector('.upload-area h5').textContent = this.files[0].name">
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-money-bill-wave"></i> Expected Amount</label>
                                <input type="number" name="expected_amount" class="form-control" value="500000" step="1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-id-card"></i> Expected NIS</label>
                                <input type="text" name="expected_nis" class="form-control" value="22211161">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-user"></i> Expected Nama</label>
                                <input type="text" name="expected_nama" class="form-control" value="Bagas Pratama">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search"></i> Validasi Dengan OCR
                        </button>
                    </form>
                </div>

                <!-- Results -->
                <?php if ($error): ?>
                <div class="test-card">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($result): ?>
                <div class="test-card">
                    <h4 class="mb-4">
                        <i class="fas fa-check-circle text-success"></i> Hasil Validasi OCR
                    </h4>

                    <?php if ($result['success']): ?>
                        <!-- Decision Badge -->
                        <div class="text-center mb-4">
                            <?= getValidationBadgeHTML($result) ?>
                        </div>

                        <!-- OCR Details -->
                        <div class="ocr-detail">
                            <h5 class="mb-3"><i class="fas fa-info-circle text-info"></i> Detail Hasil OCR</h5>
                            <?= getOCRDetailsHTML($result) ?>
                        </div>

                        <!-- Raw Data -->
                        <div class="mt-4">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rawData">
                                <i class="fas fa-code"></i> Show Raw JSON
                            </button>
                            <div class="collapse mt-3" id="rawData">
                                <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code><?= json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></code></pre>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 d-flex gap-2">
                            <?php if ($result['decision'] === 'accept'): ?>
                                <button class="btn btn-success flex-fill">
                                    <i class="fas fa-check"></i> Auto Approve
                                </button>
                            <?php elseif ($result['decision'] === 'reject'): ?>
                                <button class="btn btn-danger flex-fill">
                                    <i class="fas fa-times"></i> Auto Reject
                                </button>
                            <?php else: ?>
                                <button class="btn btn-warning flex-fill">
                                    <i class="fas fa-eye"></i> Need Review
                                </button>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                    <i class="fas fa-thumbs-up"></i> Manual Approve
                                </button>
                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="fas fa-thumbs-down"></i> Manual Reject
                                </button>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>OCR Failed:</strong> <?= htmlspecialchars($result['message'] ?? 'Unknown error') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Info -->
                <div class="test-card">
                    <h5><i class="fas fa-lightbulb text-warning"></i> Cara Kerja Sistem OCR</h5>
                    <ol class="mb-0">
                        <li>Upload bukti transfer (foto/scan)</li>
                        <li>PaddleOCR akan deteksi dan baca teks dari gambar</li>
                        <li>Sistem ekstrak informasi: Bank, Nominal, Nama, Tanggal</li>
                        <li>Validasi otomatis dengan expected data</li>
                        <li>Decision: <span class="badge bg-success">ACCEPT</span> (score ≥85%), <span class="badge bg-danger">REJECT</span> (score <50%), atau <span class="badge bg-warning">REVIEW</span> (borderline)</li>
                    </ol>
                </div>

                <div class="text-center">
                    <a href="index.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
