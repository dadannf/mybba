# 🔗 Panduan Integrasi OCR dengan Sistem Pembayaran BBA

## Overview

Dokumen ini menjelaskan cara mengintegrasikan OCR System dengan payment flow yang sudah ada di sistem BBA.

---

## 📋 Payment Flow dengan OCR

### Before OCR:
```
Siswa upload bukti → Admin review manual → Approve/Reject
```

### After OCR:
```
Siswa upload bukti → OCR auto-validate → 
  ├─ Auto Accept (85%+) → Langsung approve
  ├─ Auto Reject (<50%) → Langsung reject
  └─ Need Review (50-85%) → Admin review manual
```

---

## 🔧 Integrasi Step-by-Step

### Step 1: Update proses_pembayaran_siswa.php

File: `public/api/process_payment_student.php`

**Tambahkan di bagian atas:**
```php
require_once __DIR__ . '/../includes/ocr_helper.php';
```

**Setelah upload bukti berhasil, tambahkan OCR validation:**

```php
// Existing code: Upload bukti bayar berhasil
$bukti_bayar = 'uploads/bukti_pembayaran/' . $fileName;

// NEW: OCR Validation
$ocr_result = processTransferWithOCR(
    __DIR__ . '/../' . $bukti_bayar,
    'siswa',
    $nis,
    $nominal_bayar,
    $nis,
    $nama_siswa,
    $keuangan_id
);

// Determine status based on OCR result
if ($ocr_result['success'] && $ocr_result['use_ocr']) {
    $validation_id_ocr = $ocr_result['validation_id'];
    
    if ($ocr_result['decision'] === 'accept' && $ocr_result['validation_score'] >= 90) {
        // Auto approve dengan confidence tinggi
        $status = 'sudah bayar';
        $keterangan = 'Pembayaran tervalidasi otomatis oleh sistem OCR (Score: ' . 
                      round($ocr_result['validation_score'], 1) . '/100)';
    } elseif ($ocr_result['decision'] === 'reject') {
        // Auto reject
        $status = 'ditolak';
        $keterangan = 'Bukti transfer tidak valid: ' . $ocr_result['decision_reason'];
    } else {
        // Need manual review
        $status = 'pending';
        $keterangan = 'Menunggu verifikasi admin. OCR Score: ' . 
                      round($ocr_result['validation_score'], 1) . '/100';
    }
} else {
    // OCR not available or failed, fallback to manual
    $status = 'pending';
    $keterangan = 'Menunggu verifikasi admin';
}

// Existing code: Insert pembayaran
$sql = "INSERT INTO pembayaran (..., status, keterangan, ocr_validation_id) 
        VALUES (..., '$status', '$keterangan', " . 
        ($validation_id_ocr ?? 'NULL') . ")";
```

**Update schema pembayaran table (optional):**
```sql
ALTER TABLE pembayaran 
ADD COLUMN ocr_validation_id INT(11) DEFAULT NULL COMMENT 'FK ke ocr_validations',
ADD INDEX idx_ocr_validation (ocr_validation_id);
```

---

### Step 2: Update Detail Pembayaran Page

File: `public/admin/finance/detail.php` atau `public/student/payment_detail.php`

**Tambahkan di query:**
```php
// Include OCR helper
require_once __DIR__ . '/../../includes/ocr_helper.php';

// Query pembayaran dengan OCR validation
$sql = "SELECT p.*, o.* 
        FROM pembayaran p
        LEFT JOIN ocr_validations o ON p.ocr_validation_id = o.id
        WHERE p.pembayaran_id = '$pembayaran_id'";
```

**Display OCR result:**
```php
<?php if ($row['ocr_validation_id']): ?>
    <div class="card mt-3">
        <div class="card-header bg-info text-white">
            <i class="bi bi-robot me-2"></i>Hasil Validasi OCR
        </div>
        <div class="card-body">
            <?php
            $ocr_data = [
                'use_ocr' => true,
                'decision' => $row['auto_decision'],
                'validation_score' => $row['overall_confidence'] * 100,
                'decision_reason' => $row['validation_message'],
                'requires_manual_review' => $row['auto_decision'] === 'review',
                'extracted_data' => [
                    'bank' => $row['bank_name'],
                    'amount' => $row['transfer_amount'],
                    'account_name' => $row['account_name'],
                    'date' => $row['transfer_date'],
                ],
                'confidence_scores' => [
                    'overall_ocr' => $row['overall_confidence'],
                    'amount_match' => $row['amount_match_score'],
                    'name_match' => 0, // Calculate if stored
                ],
                'validation_checks' => [
                    'ocr_quality' => $row['overall_confidence'] >= 0.7,
                    'amount_valid' => $row['amount_match_score'] >= 0.95,
                    'name_valid' => true, // Calculate if stored
                    'bank_detected' => !empty($row['bank_name']),
                    'date_detected' => !empty($row['transfer_date']),
                ]
            ];
            
            echo getValidationBadgeHTML($ocr_data);
            echo getOCRDetailsHTML($ocr_data);
            ?>
            
            <!-- Manual Override Buttons (Admin only) -->
            <?php if ($_SESSION['role'] === 'admin' && $row['validation_status'] === 'pending'): ?>
                <div class="mt-3">
                    <h6 class="fw-bold">Manual Review:</h6>
                    <button class="btn btn-success" onclick="approveOCR(<?= $row['ocr_validation_id'] ?>)">
                        <i class="bi bi-check-circle me-1"></i>Approve Transfer
                    </button>
                    <button class="btn btn-danger" onclick="rejectOCR(<?= $row['ocr_validation_id'] ?>)">
                        <i class="bi bi-x-circle me-1"></i>Reject Transfer
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
function approveOCR(validationId) {
    if (!confirm('Approve transfer ini?')) return;
    
    fetch('process_ocr_approval.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            validation_id: validationId,
            status: 'approved',
            notes: 'Manual approval by admin'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Transfer approved!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function rejectOCR(validationId) {
    const reason = prompt('Alasan reject:');
    if (!reason) return;
    
    fetch('process_ocr_approval.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            validation_id: validationId,
            status: 'rejected',
            notes: reason
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Transfer rejected!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}
</script>
```

---

### Step 3: Create OCR Approval Handler

File: `public/admin/finance/process_ocr_approval.php`

```php
<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';
require_once __DIR__ . '/../../includes/ocr_helper.php';

// Only admin
if ($userRole !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$validation_id = $input['validation_id'] ?? null;
$status = $input['status'] ?? null; // 'approved' or 'rejected'
$notes = $input['notes'] ?? '';

if (!$validation_id || !$status) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Call OCR API to update status
$result = approveOCRValidation(
    $validation_id,
    $status,
    $_SESSION['username'],
    $notes
);

if ($result['success']) {
    // Update pembayaran status
    $new_status = ($status === 'approved') ? 'sudah bayar' : 'ditolak';
    
    $sql = "UPDATE pembayaran 
            SET status = '$new_status',
                keterangan = CONCAT(keterangan, ' - Manual review: $notes')
            WHERE ocr_validation_id = $validation_id";
    
    $conn->query($sql);
    
    echo json_encode([
        'success' => true,
        'message' => 'Validation updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Failed to update validation'
    ]);
}
```

---

### Step 4: Add Dashboard Widget untuk Review Queue

File: `public/admin/index.php`

**Tambahkan query untuk pending reviews:**
```php
// Query OCR validations yang butuh review
$sql_ocr_review = "SELECT COUNT(*) as total 
                   FROM ocr_validations 
                   WHERE auto_decision = 'review' 
                   AND validation_status = 'pending'";
$result_ocr = $conn->query($sql_ocr_review);
$ocr_review_count = $result_ocr->fetch_assoc()['total'];
```

**Display widget:**
```php
<div class="col-md-6 col-lg-3">
    <div class="card stat-card stat-warning">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="stat-title">OCR Review Queue</h6>
                    <h2 class="stat-value"><?= $ocr_review_count ?></h2>
                    <p class="stat-desc">
                        <a href="finance/ocr_review_queue.php" class="text-decoration-none">
                            Perlu Review Manual →
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

### Step 5: Create Review Queue Page

File: `public/admin/finance/ocr_review_queue.php`

```php
<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';
require_once __DIR__ . '/../../includes/ocr_helper.php';

// Get OCR validations yang perlu review
$ocr_client = new OCRAPIClient('http://localhost:8000');
$validations = $ocr_client->getValidations([
    'status' => 'pending',
    'limit' => 50
]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>OCR Review Queue</title>
    <!-- Include Bootstrap & CSS -->
</head>
<body>
    <div class="container-fluid mt-4">
        <h2><i class="bi bi-robot me-2"></i>OCR Review Queue</h2>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Uploader</th>
                        <th>Expected Amount</th>
                        <th>Detected Amount</th>
                        <th>Score</th>
                        <th>Decision</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($validations['data'] as $val): ?>
                    <tr>
                        <td><?= $val['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($val['created_at'])) ?></td>
                        <td>
                            <?= $val['uploader_type'] ?>: <?= $val['uploader_id'] ?><br>
                            <small><?= $val['expected_nama'] ?></small>
                        </td>
                        <td>Rp <?= number_format($val['expected_amount'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($val['extracted_amount']): ?>
                                Rp <?= number_format($val['extracted_amount'], 0, ',', '.') ?>
                            <?php else: ?>
                                <span class="text-muted">Not detected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $score = $val['validation_score'] ?? 0;
                            $badge_class = $score >= 70 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                            ?>
                            <span class="badge bg-<?= $badge_class ?>">
                                <?= round($score, 1) ?>/100
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-warning">
                                <?= $val['auto_decision'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="ocr_review_detail.php?id=<?= $val['id'] ?>" 
                               class="btn btn-sm btn-primary">
                                Review
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
```

---

## 🔔 Notifikasi untuk Admin

Update `notification_helper.php`:

```php
function createOCRReviewNotification($conn, $validation_id, $nis, $nama, $score) {
    $judul = 'OCR: Perlu Review Manual';
    $pesan = "Transfer dari $nama ($nis) perlu review manual. OCR Score: " . round($score, 1) . "/100";
    $link = "/admin/finance/ocr_review_detail.php?id=$validation_id";
    
    createNotification($conn, 'ocr_review', $judul, $pesan, $nis, $nama, $link);
}
```

Call notification di `process_payment_student.php`:

```php
if ($ocr_result['requires_manual_review']) {
    createOCRReviewNotification(
        $conn, 
        $validation_id_ocr, 
        $nis, 
        $nama_siswa,
        $ocr_result['validation_score']
    );
}
```

---

## 📊 Monitoring & Analytics

### Query untuk statistik OCR:

```sql
-- Accuracy rate
SELECT 
    auto_decision,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM ocr_validations), 2) as percentage
FROM ocr_validations
GROUP BY auto_decision;

-- Average processing time
SELECT AVG(processing_time_ms) as avg_time_ms
FROM ocr_validations;

-- Success rate by bank
SELECT 
    bank_name,
    COUNT(*) as total,
    SUM(CASE WHEN auto_decision = 'accept' THEN 1 ELSE 0 END) as accepted
FROM ocr_validations
WHERE bank_name IS NOT NULL
GROUP BY bank_name;
```

---

## ✅ Integration Checklist

- [ ] Update process_payment_student.php dengan OCR call
- [ ] Update payment detail pages dengan OCR display
- [ ] Create OCR approval handler
- [ ] Add review queue dashboard widget
- [ ] Create review queue page
- [ ] Add OCR notifications
- [ ] Test full flow: upload → OCR → approve/reject
- [ ] Monitor accuracy dan adjust thresholds
- [ ] Train admin untuk handle review queue

---

## 🎓 Best Practices

1. **Always have fallback** - Jika OCR service down, fallback ke manual review
2. **Log everything** - Semua decision OCR harus ter-record di database
3. **Monitor accuracy** - Track berapa % auto accept vs manual review
4. **Adjust thresholds** - Fine-tune berdasarkan real data
5. **User feedback** - Collect feedback untuk improve model

---

**Integration Complete! 🎉**
