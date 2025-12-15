-- =============================================
-- FIX: Normalisasi Format Tahun di Tabel Keuangan
-- Deskripsi: Ubah semua format YYYY menjadi YYYY/YYYY+1
-- =============================================

-- Backup data sebelum update
CREATE TABLE IF NOT EXISTS keuangan_backup_20251215 AS SELECT * FROM keuangan;

-- Update format tahun dari YYYY ke YYYY/YYYY+1
-- Hanya untuk yang masih format YYYY saja
UPDATE keuangan 
SET tahun = CONCAT(tahun, '/', CAST(tahun AS UNSIGNED) + 1)
WHERE tahun REGEXP '^[0-9]{4}$';

-- Verifikasi hasil
SELECT 
    tahun,
    COUNT(*) as jumlah,
    CASE 
        WHEN tahun LIKE '%/%' THEN '✅ Format YYYY/YYYY+1'
        ELSE '❌ Format Lain'
    END as status
FROM keuangan
GROUP BY tahun
ORDER BY tahun DESC;

SELECT CONCAT('✅ Total records diupdate: ', ROW_COUNT()) as result;
