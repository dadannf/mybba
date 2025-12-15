-- =============================================
-- VERIFIKASI: Testing Format Tahun dan UNIQUE Constraint
-- Deskripsi: Cek konsistensi format tahun di tabel keuangan
-- =============================================

-- 1. Cek format tahun yang ada di database
SELECT 
    tahun,
    COUNT(*) as jumlah,
    CASE 
        WHEN tahun LIKE '%/%' THEN 'Format YYYY/YYYY+1'
        WHEN tahun REGEXP '^[0-9]{4}$' THEN 'Format YYYY'
        ELSE 'Format Lain'
    END as format_type
FROM keuangan
GROUP BY tahun
ORDER BY tahun DESC;

-- 2. Cek apakah ada duplikasi (nis + tahun)
SELECT 
    nis, 
    tahun, 
    COUNT(*) as jumlah_duplikat
FROM keuangan
GROUP BY nis, tahun
HAVING COUNT(*) > 1;

-- 3. Cek UNIQUE INDEX sudah terpasang
SHOW INDEX FROM keuangan WHERE Key_name = 'idx_nis_tahun';

-- 4. Test Insert Duplicate (harus GAGAL dengan error)
-- INSERT INTO keuangan (nis, tahun, total_tagihan, total_bayar) 
-- VALUES ('222111003', '2025/2026', 2280000, 0);
-- ^ Jika sudah ada, akan error: Duplicate entry

SELECT '✅ Verifikasi selesai! Jika no duplikasi dan index ada, sistem sudah aman.' as status;
