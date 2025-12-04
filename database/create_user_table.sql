-- =============================================
-- SQL Script: Create User Table
-- Database: dbsekolah
-- Deskripsi: Membuat tabel user untuk sistem login
-- =============================================

-- Gunakan database
USE dbsekolah;

-- Buat tabel users jika belum ada
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','siswa') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user
-- Username: admin
-- Password: admin123
INSERT INTO `users` (`username`, `password`, `role`, `email`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL)
ON DUPLICATE KEY UPDATE username=username;

-- Password hash untuk 'admin123' sudah di-generate menggunakan password_hash()
-- Untuk membuat user baru dengan password custom, gunakan:
-- SELECT PASSWORD('your_password'); di MySQL
-- atau gunakan halaman register.php

-- =============================================
-- CATATAN PENTING:
-- =============================================
-- 1. Password default admin adalah: admin123
-- 2. Silakan ganti password setelah login pertama kali
-- 3. Gunakan halaman register.php untuk membuat user baru
-- 4. Email bersifat opsional (bisa NULL)
-- 5. Role hanya bisa 'admin' atau 'siswa'
-- =============================================
