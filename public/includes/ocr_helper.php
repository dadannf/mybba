<?php
/**
 * =============================================
 * OCR Validation Handler untuk Sistem BBA
 * Process bukti transfer dengan OCR
 * =============================================
 */

require_once __DIR__ . '/ocr_api_client.php';
require_once __DIR__ . '/../config.php';

/**
 * Process bukti transfer menggunakan OCR
 */
function processTransferWithOCR($file_path, $uploader_type, $uploader_id, $expected_amount, $nis, $nama, $keuangan_id = null) {
    // Initialize OCR client
    $ocr_client = new OCRAPIClient('http://localhost:8000');
    
    // Check if OCR service is running
    $health = $ocr_client->healthCheck();
    if (!isset($health['status']) || $health['status'] !== 'running') {
        return [
            'success' => false,
            'error' => 'OCR service is not available',
            'use_ocr' => false
        ];
    }
    
    // Prepare parameters
    $params = [
        'uploader_type' => $uploader_type,
        'uploader_id' => $uploader_id,
        'expected_amount' => $expected_amount,
        'expected_nis' => $nis,
        'expected_nama' => $nama,
    ];
    
    if ($keuangan_id) {
        $params['keuangan_id'] = $keuangan_id;
    }
    
    // Call OCR API
    $result = $ocr_client->validateTransfer($file_path, $params);
    
    if (!$result['success']) {
        return [
            'success' => false,
            'error' => $result['error'] ?? 'OCR validation failed',
            'use_ocr' => false
        ];
    }
    
    $data = $result['data'];
    
    return [
        'success' => true,
        'use_ocr' => true,
        'validation_id' => $data['validation_id'],
        'decision' => $data['decision'],
        'validation_score' => $data['validation_score'],
        'validation_status' => $data['validation_status'],
        'auto_decision' => $data['decision'],
        'decision_reason' => $data['decision_reason'],
        'requires_manual_review' => $data['requires_manual_review'],
        'confidence_scores' => $data['confidence_scores'],
        'validation_checks' => $data['validation_checks'],
        'extracted_data' => [
            'amount' => $data['parsed_data']['transfer_amount'],
            'bank' => $data['parsed_data']['bank_name'],
            'account_name' => $data['parsed_data']['account_name'],
            'date' => $data['parsed_data']['transfer_date'],
        ],
        'ocr_result' => [
            'raw_text' => $data['ocr_result']['raw_text'],
            'overall_confidence' => $data['ocr_result']['overall_confidence'],
        ]
    ];
}

/**
 * Get OCR validation detail
 */
function getOCRValidationDetail($validation_id) {
    $ocr_client = new OCRAPIClient('http://localhost:8000');
    return $ocr_client->getValidationDetail($validation_id);
}

/**
 * Approve or reject OCR validation
 */
function approveOCRValidation($validation_id, $status, $validated_by, $notes = null) {
    $ocr_client = new OCRAPIClient('http://localhost:8000');
    return $ocr_client->manualApproval($validation_id, $status, $validated_by, $notes);
}

/**
 * Get validation status badge HTML
 */
function getValidationBadgeHTML($ocr_result) {
    if (!$ocr_result || !isset($ocr_result['use_ocr']) || !$ocr_result['use_ocr']) {
        return '';
    }
    
    $decision = $ocr_result['decision'];
    $score = $ocr_result['validation_score'];
    
    $badge_class = '';
    $badge_text = '';
    $badge_icon = '';
    
    switch ($decision) {
        case 'accept':
            $badge_class = 'success';
            $badge_text = 'OCR: Auto Approved';
            $badge_icon = 'check-circle-fill';
            break;
        case 'reject':
            $badge_class = 'danger';
            $badge_text = 'OCR: Auto Rejected';
            $badge_icon = 'x-circle-fill';
            break;
        case 'review':
            $badge_class = 'warning';
            $badge_text = 'OCR: Needs Review';
            $badge_icon = 'exclamation-triangle-fill';
            break;
    }
    
    $html = '<div class="ocr-validation-badge mb-3">';
    $html .= '<span class="badge bg-' . $badge_class . ' p-2">';
    $html .= '<i class="bi bi-' . $badge_icon . ' me-1"></i>';
    $html .= $badge_text . ' (Score: ' . round($score, 1) . '/100)';
    $html .= '</span>';
    
    if ($ocr_result['requires_manual_review']) {
        $html .= '<span class="badge bg-info p-2 ms-2">';
        $html .= '<i class="bi bi-info-circle-fill me-1"></i>Manual Review Required';
        $html .= '</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get OCR result details HTML
 */
function getOCRDetailsHTML($ocr_result) {
    if (!$ocr_result || !isset($ocr_result['use_ocr']) || !$ocr_result['use_ocr']) {
        return '';
    }
    
    $extracted = $ocr_result['extracted_data'];
    $scores = $ocr_result['confidence_scores'];
    $checks = $ocr_result['validation_checks'];
    
    $html = '<div class="card mb-3">';
    $html .= '<div class="card-header bg-primary text-white">';
    $html .= '<i class="bi bi-robot me-2"></i>OCR Validation Results';
    $html .= '</div>';
    $html .= '<div class="card-body">';
    
    // Extracted Information
    $html .= '<h6 class="fw-bold mb-3">Informasi Terdeteksi:</h6>';
    $html .= '<div class="row mb-3">';
    $html .= '<div class="col-md-6"><strong>Bank:</strong> ' . ($extracted['bank'] ?? 'Not detected') . '</div>';
    $html .= '<div class="col-md-6"><strong>Nominal:</strong> Rp ' . number_format($extracted['amount'] ?? 0, 0, ',', '.') . '</div>';
    $html .= '<div class="col-md-6"><strong>Nama Pengirim:</strong> ' . ($extracted['account_name'] ?? 'Not detected') . '</div>';
    $html .= '<div class="col-md-6"><strong>Tanggal:</strong> ' . ($extracted['date'] ?? 'Not detected') . '</div>';
    $html .= '</div>';
    
    // Confidence Scores
    $html .= '<h6 class="fw-bold mb-3">Confidence Scores:</h6>';
    $html .= '<div class="row mb-3">';
    $html .= '<div class="col-md-6">';
    $html .= '<small>OCR Quality:</small><br>';
    $html .= getProgressBar($scores['overall_ocr'] * 100, 'primary');
    $html .= '</div>';
    $html .= '<div class="col-md-6">';
    $html .= '<small>Amount Match:</small><br>';
    $html .= getProgressBar($scores['amount_match'] * 100, getScoreColor($scores['amount_match'] * 100));
    $html .= '</div>';
    $html .= '<div class="col-md-6">';
    $html .= '<small>Name Match:</small><br>';
    $html .= getProgressBar($scores['name_match'] * 100, getScoreColor($scores['name_match'] * 100));
    $html .= '</div>';
    $html .= '</div>';
    
    // Validation Checks
    $html .= '<h6 class="fw-bold mb-3">Validation Checks:</h6>';
    $html .= '<div class="row">';
    $html .= '<div class="col-md-6">' . getCheckIcon($checks['ocr_quality']) . ' OCR Quality</div>';
    $html .= '<div class="col-md-6">' . getCheckIcon($checks['amount_valid']) . ' Amount Valid</div>';
    $html .= '<div class="col-md-6">' . getCheckIcon($checks['name_valid']) . ' Name Match</div>';
    $html .= '<div class="col-md-6">' . getCheckIcon($checks['bank_detected']) . ' Bank Detected</div>';
    $html .= '</div>';
    
    // Decision Reason
    $html .= '<div class="mt-3 p-3 bg-light rounded">';
    $html .= '<strong>Alasan Decision:</strong><br>';
    $html .= '<small>' . htmlspecialchars($ocr_result['decision_reason']) . '</small>';
    $html .= '</div>';
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

function getProgressBar($value, $color = 'primary') {
    return '<div class="progress" style="height: 20px;">
        <div class="progress-bar bg-' . $color . '" role="progressbar" 
             style="width: ' . round($value) . '%;" 
             aria-valuenow="' . round($value) . '" aria-valuemin="0" aria-valuemax="100">
            ' . round($value, 1) . '%
        </div>
    </div>';
}

function getScoreColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'warning';
    return 'danger';
}

function getCheckIcon($passed) {
    if ($passed) {
        return '<i class="bi bi-check-circle-fill text-success me-1"></i>';
    }
    return '<i class="bi bi-x-circle-fill text-danger me-1"></i>';
}
