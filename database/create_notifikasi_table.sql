-- =============================================
-- Tabel: notifikasi
-- Deskripsi: Menyimpan notifikasi aktivitas siswa
-- =============================================

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
  KEY `fk_siswa_nis` (`nis`),
  CONSTRAINT `fk_notifikasi_siswa` FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Index untuk performa query notifikasi belum dibaca
CREATE INDEX idx_unread_notifications ON notifikasi(is_read, created_at DESC);
