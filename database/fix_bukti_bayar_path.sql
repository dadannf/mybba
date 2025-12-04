-- =============================================
-- Script: Fix Bukti Bayar Path
-- Deskripsi: Perbaiki path bukti_bayar yang salah di database
-- Tanggal: 2025-11-06
-- =============================================

-- 1. Cek data yang perlu diperbaiki
SELECT 
    pembayaran_id,
    keuangan_id,
    bukti_bayar AS path_lama,
    CASE 
        WHEN bukti_bayar LIKE '/uploads/bukti_pembayaran/%' THEN 
            CONCAT('uploads/bukti_bayar/', SUBSTRING_INDEX(bukti_bayar, '/', -1))
        WHEN bukti_bayar LIKE 'uploads/bukti_pembayaran/%' THEN 
            CONCAT('uploads/bukti_bayar/', SUBSTRING_INDEX(bukti_bayar, '/', -1))
        ELSE bukti_bayar
    END AS path_baru
FROM pembayaran 
WHERE bukti_bayar IS NOT NULL 
    AND bukti_bayar != ''
    AND bukti_bayar LIKE '%bukti_pembayaran%';

-- 2. Update path yang salah (HATI-HATI: Backup dulu sebelum jalankan!)
-- UPDATE pembayaran 
-- SET bukti_bayar = CONCAT('uploads/bukti_bayar/', SUBSTRING_INDEX(bukti_bayar, '/', -1))
-- WHERE bukti_bayar IS NOT NULL 
--     AND bukti_bayar != ''
--     AND bukti_bayar LIKE '%bukti_pembayaran%';

-- 3. Verifikasi hasil update
-- SELECT pembayaran_id, keuangan_id, bukti_bayar 
-- FROM pembayaran 
-- WHERE bukti_bayar IS NOT NULL AND bukti_bayar != '';
