-- =============================================
-- Create OCR Validations Table
-- Table untuk menyimpan hasil validasi OCR bukti transfer
-- =============================================

CREATE TABLE IF NOT EXISTS `ocr_validations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  
  -- Upload Information
  `filename` VARCHAR(255) NOT NULL COMMENT 'Nama file yang diupload',
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Path lengkap file',
  `upload_timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu upload',
  
  -- Uploader Information
  `uploader_type` ENUM('admin', 'siswa') NOT NULL COMMENT 'Tipe uploader',
  `uploader_id` VARCHAR(50) NOT NULL COMMENT 'Username admin atau NIS siswa',
  
  -- OCR Results
  `raw_text` TEXT DEFAULT NULL COMMENT 'Hasil OCR raw text',
  `detected_boxes` TEXT DEFAULT NULL COMMENT 'JSON bounding boxes',
  
  -- Parsed Information
  `bank_name` VARCHAR(100) DEFAULT NULL COMMENT 'Nama bank terdeteksi',
  `account_number` VARCHAR(50) DEFAULT NULL COMMENT 'Nomor rekening',
  `account_name` VARCHAR(200) DEFAULT NULL COMMENT 'Nama pemilik rekening',
  `transfer_amount` DECIMAL(15,2) DEFAULT NULL COMMENT 'Nominal transfer terdeteksi',
  `transfer_date` DATETIME DEFAULT NULL COMMENT 'Tanggal transfer',
  `reference_number` VARCHAR(100) DEFAULT NULL COMMENT 'Nomor referensi transfer',
  
  -- Expected Information (untuk validasi)
  `expected_amount` DECIMAL(15,2) DEFAULT NULL COMMENT 'Nominal yang diharapkan',
  `expected_nis` VARCHAR(20) DEFAULT NULL COMMENT 'NIS yang diharapkan',
  `expected_nama` VARCHAR(200) DEFAULT NULL COMMENT 'Nama yang diharapkan',
  `keuangan_id` INT(11) DEFAULT NULL COMMENT 'FK ke tabel keuangan',
  
  -- Confidence Scores
  `overall_confidence` DECIMAL(5,4) DEFAULT NULL COMMENT 'Overall OCR confidence (0-1)',
  `detection_confidence` DECIMAL(5,4) DEFAULT NULL COMMENT 'Text detection confidence',
  `recognition_confidence` DECIMAL(5,4) DEFAULT NULL COMMENT 'Text recognition confidence',
  `amount_match_score` DECIMAL(5,4) DEFAULT NULL COMMENT 'Amount match score (0-1)',
  
  -- Validation Result
  `validation_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' COMMENT 'Status validasi final',
  `validation_message` TEXT DEFAULT NULL COMMENT 'Pesan validasi/alasan',
  `auto_decision` VARCHAR(20) DEFAULT NULL COMMENT 'Decision otomatis: accept/reject/review',
  
  -- Processing Information
  `processing_time_ms` DECIMAL(10,2) DEFAULT NULL COMMENT 'Waktu proses dalam milliseconds',
  `error_message` TEXT DEFAULT NULL COMMENT 'Error message jika ada',
  
  -- Timestamps
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `validated_at` DATETIME DEFAULT NULL COMMENT 'Waktu manual validation',
  `validated_by` VARCHAR(50) DEFAULT NULL COMMENT 'Admin yang validasi',
  
  PRIMARY KEY (`id`),
  KEY `idx_uploader` (`uploader_type`, `uploader_id`),
  KEY `idx_status` (`validation_status`),
  KEY `idx_keuangan` (`keuangan_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel untuk menyimpan hasil validasi OCR bukti transfer';

-- Create indexes for better query performance
CREATE INDEX idx_auto_decision ON ocr_validations(auto_decision);
CREATE INDEX idx_expected_nis ON ocr_validations(expected_nis);
CREATE INDEX idx_upload_timestamp ON ocr_validations(upload_timestamp);

-- Insert sample comment
INSERT INTO ocr_validations 
(filename, file_path, uploader_type, uploader_id, expected_amount, expected_nis, expected_nama, 
 validation_message, auto_decision, validation_status, processing_time_ms)
VALUES
('sample_receipt.jpg', '/uploads/sample_receipt.jpg', 'admin', 'admin', 500000.00, '22211161', 'Bagas', 
 'Sample data - OCR system ready', 'review', 'pending', 0.0)
ON DUPLICATE KEY UPDATE id=id;
