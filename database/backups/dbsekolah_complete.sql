-- phpMyAdmin SQL Dump - COMPLETE VERSION
-- Database: xhpvlcgh_dbsekolah
-- Compatible with: MySQL 5.5+, MariaDB 10.x
-- Generated: 2025-12-17 20:04:34
-- 
-- Includes ALL tables:
-- 1. users
-- 2. siswa
-- 3. keuangan
-- 4. pembayaran
-- 5. informasi
-- 6. notifikasi (NEW)
-- 7. ocr_validations (NEW)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xhpvlcgh_dbsekolah`
--


-- --------------------------------------------------------

--
-- Table structure for table `informasi`
--

CREATE TABLE `informasi` (
  `informasi_id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `informasi`
--

INSERT INTO `informasi` (`informasi_id`, `judul`, `isi`, `foto`, `created_at`, `updated_at`, `created_by`) VALUES
(2, 'Pengumuman Pembayaran SPP', 'Pembayaran SPP dapat dilakukan melalui menu Keuangan. Silakan upload bukti pembayaran untuk verifikasi.', NULL, '2025-10-30 01:18:11', NULL, 'admin'),
(3, 'Info Kontak Sekolah', 'Untuk informasi lebih lanjut, silakan hubungi bagian administrasi sekolah.', NULL, '2025-10-30 01:18:11', NULL, 'admin'),
(4, 'INFO PENTING', 'Penting banget ini', 'uploads/informasi/info_1761765435_6902683b8fe71.jfif', '2025-10-30 02:17:15', NULL, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `keuangan`
--

CREATE TABLE `keuangan` (
  `keuangan_id` int NOT NULL,
  `nis` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `tahun` int NOT NULL,
  `total_tagihan` decimal(12,2) NOT NULL,
  `total_bayar` decimal(12,2) DEFAULT '0.00',
  `progress` decimal(5,2) GENERATED ALWAYS AS (((`total_bayar` / `total_tagihan`) * 100)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keuangan`
--

INSERT INTO `keuangan` (`keuangan_id`, `nis`, `tahun`, `total_tagihan`, `total_bayar`) VALUES
(2, '2222444', 2025, '1200000.00', '200000.00'),
(4, '2221', 2025, '1200000.00', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `pembayaran_id` int NOT NULL,
  `keuangan_id` int NOT NULL,
  `bulan_untuk` tinyint NOT NULL DEFAULT '1' COMMENT 'Bulan yang dibayar (1=Jan, 2=Feb, dst)',
  `tanggal_bayar` date NOT NULL,
  `nominal_bayar` decimal(12,2) NOT NULL,
  `metode` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tempat_bayar` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bukti_bayar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('valid','menunggu','tolak') COLLATE utf8mb4_general_ci DEFAULT 'menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`pembayaran_id`, `keuangan_id`, `bulan_untuk`, `tanggal_bayar`, `nominal_bayar`, `metode`, `tempat_bayar`, `bukti_bayar`, `status`) VALUES
(11, 2, 1, '2025-01-30', '100000.00', 'Tunai', '', 'uploads/bukti_bayar/bukti_2222444_1_1761811690.jfif', 'valid'),
(13, 2, 2, '2025-02-20', '100000.00', 'Transfer Bank', 'BRI', 'bukti_admin_2_2_1761813830.jpg', 'valid'),
(14, 2, 3, '2025-10-30', '100000.00', 'Transfer Bank', 'BRI', 'uploads/bukti_bayar/bukti_2222444_3_1761814381.jpg', 'menunggu'),
(15, 2, 3, '2025-10-30', '100000.00', 'Tunai', '', 'uploads/bukti_bayar/bukti_2222444_3_1761814432.jpeg', 'menunggu');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `nis` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jk` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `kelas` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jurusan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nik` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ayah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ibu` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_siswa` enum('aktif','lulus','keluar','pindah') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`nis`, `nisn`, `user_id`, `nama`, `tempat_lahir`, `tanggal_lahir`, `jk`, `kelas`, `jurusan`, `nik`, `ayah`, `ibu`, `alamat`, `email`, `no_hp`, `status_siswa`, `foto`) VALUES
('2221', '2009', 2, 'bagas', 'Bandung', '2025-10-07', 'L', '12', 'TSM', '2234', 'udin', 'jaenab', 'budi', 'budi@gmail.com', '0077292882', 'aktif', NULL),
('2222444', '2222', 1, 'dadan', 'Jakarta', '2024-01-07', 'L', '12', 'TKJ', '2234', 'udin', 'jaenab', 'disini', 'budi@gmail.com', '0077292882', 'aktif', 'siswa_2222444_1761580415.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','siswa') COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `email`) VALUES
(1, '2222444', '$2y$10$keh6ccrd/DU2p648n7M5B.7FHlSRagnMRCKGUqhbn3wogCnO2wExy', 'siswa', 'budi@gmail.com'),
(2, '2221', '$2y$10$GAlw5CrU/fXQtSudgg2uIONO93b9HPW0sMOHCDuJMhXm0NsFTZO8S', 'siswa', 'budi@gmail.com'),
(3, 'admin', '$2y$10$e8k3Ekb9U9BAjOzmAXSAA.9X/nECd/KR4.W3cfXKtPE2JefvSErW6', 'admin', 'dadannuhf@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `informasi`
--
ALTER TABLE `informasi`
  ADD PRIMARY KEY (`informasi_id`);

--
-- Indexes for table `keuangan`
--
ALTER TABLE `keuangan`
  ADD PRIMARY KEY (`keuangan_id`),
  ADD KEY `nis` (`nis`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`pembayaran_id`),
  ADD KEY `idx_keuangan_bulan` (`keuangan_id`,`bulan_untuk`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`nis`),
  ADD UNIQUE KEY `unique_nisn` (`nisn`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `informasi`
--
ALTER TABLE `informasi`
  MODIFY `informasi_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `keuangan`
--
ALTER TABLE `keuangan`
  MODIFY `keuangan_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `pembayaran_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `keuangan`
--
ALTER TABLE `keuangan`
  ADD CONSTRAINT `keuangan_ibfk_1` FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`keuangan_id`) REFERENCES `keuangan` (`keuangan_id`);

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE IF NOT EXISTS `notifikasi` (
  `notifikasi_id` int NOT NULL AUTO_INCREMENT,
  `tipe` enum('update_data','pembayaran') NOT NULL COMMENT 'Tipe notifikasi',
  `judul` varchar(200) NOT NULL COMMENT 'Judul notifikasi',
  `pesan` text NOT NULL COMMENT 'Isi pesan notifikasi',
  `nis` varchar(20) DEFAULT NULL COMMENT 'NIS siswa terkait',
  `nama_siswa` varchar(100) DEFAULT NULL COMMENT 'Nama siswa untuk kemudahan akses',
  `link` varchar(255) DEFAULT NULL COMMENT 'Link ke halaman detail',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=belum dibaca, 1=sudah dibaca',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notifikasi_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_tipe` (`tipe`),
  KEY `fk_siswa_nis` (`nis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `notifikasi`
--
CREATE INDEX idx_unread_notifications ON notifikasi(is_read, created_at DESC);


-- --------------------------------------------------------

--
-- Table structure for table `ocr_validations`
--

CREATE TABLE IF NOT EXISTS `ocr_validations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL COMMENT 'Nama file yang diupload',
  `file_path` varchar(500) NOT NULL COMMENT 'Path lengkap file',
  `upload_timestamp` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu upload',
  `uploader_type` enum('admin','siswa') NOT NULL COMMENT 'Tipe uploader',
  `uploader_id` varchar(50) NOT NULL COMMENT 'Username admin atau NIS siswa',
  `raw_text` text DEFAULT NULL COMMENT 'Hasil OCR raw text',
  `detected_boxes` text DEFAULT NULL COMMENT 'JSON bounding boxes',
  `bank_name` varchar(100) DEFAULT NULL COMMENT 'Nama bank terdeteksi',
  `account_number` varchar(50) DEFAULT NULL COMMENT 'Nomor rekening',
  `account_name` varchar(200) DEFAULT NULL COMMENT 'Nama pemilik rekening',
  `transfer_amount` decimal(15,2) DEFAULT NULL COMMENT 'Nominal transfer terdeteksi',
  `transfer_date` datetime DEFAULT NULL COMMENT 'Tanggal transfer',
  `reference_number` varchar(100) DEFAULT NULL COMMENT 'Nomor referensi transfer',
  `expected_amount` decimal(15,2) DEFAULT NULL COMMENT 'Nominal yang diharapkan',
  `expected_nis` varchar(20) DEFAULT NULL COMMENT 'NIS yang diharapkan',
  `expected_nama` varchar(200) DEFAULT NULL COMMENT 'Nama yang diharapkan',
  `keuangan_id` int DEFAULT NULL COMMENT 'FK ke tabel keuangan',
  `overall_confidence` decimal(5,4) DEFAULT NULL COMMENT 'Overall OCR confidence (0-1)',
  `detection_confidence` decimal(5,4) DEFAULT NULL COMMENT 'Text detection confidence',
  `recognition_confidence` decimal(5,4) DEFAULT NULL COMMENT 'Text recognition confidence',
  `amount_match_score` decimal(5,4) DEFAULT NULL COMMENT 'Amount match score (0-1)',
  `validation_status` enum('pending','approved','rejected') DEFAULT 'pending' COMMENT 'Status validasi final',
  `validation_message` text DEFAULT NULL COMMENT 'Pesan validasi/alasan',
  `auto_decision` varchar(20) DEFAULT NULL COMMENT 'Decision otomatis: accept/reject/review',
  `processing_time_ms` decimal(10,2) DEFAULT NULL COMMENT 'Waktu proses dalam milliseconds',
  `error_message` text DEFAULT NULL COMMENT 'Error message jika ada',
  `validated_by` varchar(50) DEFAULT NULL COMMENT 'Admin yang validasi manual',
  `validated_at` datetime DEFAULT NULL COMMENT 'Waktu validasi manual',
  PRIMARY KEY (`id`),
  KEY `idx_upload_timestamp` (`upload_timestamp`),
  KEY `idx_validation_status` (`validation_status`),
  KEY `idx_uploader` (`uploader_type`, `uploader_id`),
  KEY `idx_keuangan_id` (`keuangan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notifikasi_siswa` FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`) ON DELETE CASCADE;

--
-- Constraints for table `ocr_validations`
--
ALTER TABLE `ocr_validations`
  ADD CONSTRAINT `fk_ocr_keuangan` FOREIGN KEY (`keuangan_id`) REFERENCES `keuangan` (`keuangan_id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- 
--  COMPLETE DATABASE DUMP - ALL TABLES INCLUDED
-- Database ready for import to: xhpvlcgh_dbsekolah
-- Compatible with: MySQL 5.5+, 5.6, 5.7, 8.0, MariaDB 10.x
--

