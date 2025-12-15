-- =============================================
-- FIX: Mencegah Duplikasi Data Keuangan
-- Deskripsi: Menambahkan UNIQUE constraint pada kolom (nis, tahun)
--            untuk mencegah duplikasi saat import siswa dari CSV
-- Tanggal: 15 Desember 2025
-- =============================================

-- STEP 1: Hapus duplikasi yang sudah ada (jika ada)
-- Simpan record yang akan dihapus untuk backup
CREATE TABLE IF NOT EXISTS keuangan_duplicates_backup_20251215 AS
SELECT * FROM keuangan WHERE keuangan_id IN (
    SELECT k2.keuangan_id 
    FROM keuangan k1
    INNER JOIN keuangan k2 ON k1.nis = k2.nis AND k1.tahun = k2.tahun
    WHERE k1.keuangan_id < k2.keuangan_id
);

-- Hapus duplikasi (keep yang keuangan_id terkecil)
DELETE FROM keuangan WHERE keuangan_id IN (
    SELECT keuangan_id FROM keuangan_duplicates_backup_20251215
);

-- STEP 2: Tambahkan UNIQUE INDEX untuk mencegah duplikasi di masa depan
ALTER TABLE keuangan 
ADD UNIQUE INDEX idx_nis_tahun (nis, tahun);

-- STEP 3: Verifikasi
SELECT 
    COUNT(*) as total_records,
    COUNT(DISTINCT nis, tahun) as unique_combinations,
    COUNT(*) - COUNT(DISTINCT nis, tahun) as duplicates
FROM keuangan;

-- Jika duplicates = 0, berarti berhasil!
