-- =============================================
-- Tabel: informasi
-- Deskripsi: Menyimpan informasi dan pengumuman sekolah
-- =============================================
-- Catatan: Tabel ini sudah ada di database Anda
-- Script ini hanya untuk referensi struktur

-- Struktur tabel yang sudah ada:
-- CREATE TABLE IF NOT EXISTS `informasi` (
--   `informasi_id` INT(11) NOT NULL AUTO_INCREMENT,
--   `judul` VARCHAR(255) NOT NULL,
--   `isi` TEXT NOT NULL,
--   `foto` VARCHAR(500) DEFAULT NULL,
--   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
--   `created_by` VARCHAR(100) DEFAULT NULL,
--   PRIMARY KEY (`informasi_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jika tabel belum ada created_by, jalankan query ini:
ALTER TABLE `informasi` 
ADD COLUMN IF NOT EXISTS `created_by` VARCHAR(100) DEFAULT NULL AFTER `updated_at`;

-- Contoh data untuk testing (opsional):
-- INSERT INTO `informasi` (`judul`, `isi`, `created_by`) VALUES
-- ('Pembayaran SPP Januari 2025', 'Diberitahukan kepada seluruh siswa SMK BIT Bina Aulia untuk segera melakukan pembayaran SPP bulan Januari 2025 paling lambat tanggal 10 Januari 2025.\n\nPembayaran dapat dilakukan melalui:\n1. Transfer Bank BCA 1234567890 a.n. SMK BIT Bina Aulia\n2. Langsung ke Tata Usaha sekolah\n\nBukti pembayaran harap diupload melalui sistem.\n\nTerima kasih.', 'admin'),
-- ('Ujian Tengah Semester Genap 2024/2025', 'Jadwal Ujian Tengah Semester (UTS) Genap Tahun Pelajaran 2024/2025:\n\nTanggal: 10-14 Februari 2025\nWaktu: 07.30 - selesai\n\nSiswa diwajibkan:\n- Hadir 15 menit sebelum ujian dimulai\n- Membawa kartu peserta ujian\n- Menggunakan seragam lengkap\n- Membawa alat tulis sendiri\n\nJadwal detail per kelas akan diinformasikan lebih lanjut.', 'admin');
