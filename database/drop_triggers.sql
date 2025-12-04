-- =============================================
-- DROP TRIGGERS: Hapus trigger yang menyebabkan konflik saat delete cascade
-- Deskripsi: Trigger after_pembayaran_delete menyebabkan error saat menghapus data siswa
-- karena mencoba UPDATE tabel keuangan yang juga sedang dihapus
-- =============================================

USE dbsekolah;

-- Drop semua trigger terkait auto-update total_bayar
DROP TRIGGER IF EXISTS after_pembayaran_insert;
DROP TRIGGER IF EXISTS after_pembayaran_update;
DROP TRIGGER IF EXISTS after_pembayaran_delete;

-- Catatan: 
-- Total bayar sekarang akan diupdate secara manual di aplikasi PHP
-- Lihat file: public/api/process_payment_student.php
-- Lihat file: public/api/manual_approve_payment.php
-- Lihat file: public/api/manual_reject_payment.php
