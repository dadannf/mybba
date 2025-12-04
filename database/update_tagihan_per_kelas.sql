-- =============================================
-- UPDATE TAGIHAN KEUANGAN BERDASARKAN KELAS
-- Gunakan script ini untuk update tagihan siswa yang sudah ada
-- =============================================

-- Lihat distribusi kelas saat ini
SELECT 
    SUBSTRING(kelas, 1, 2) as tingkat_kelas,
    COUNT(*) as jumlah_siswa,
    kelas
FROM siswa
GROUP BY kelas
ORDER BY kelas;

-- Preview tagihan yang akan diupdate
SELECT 
    s.nis,
    s.nama,
    s.kelas,
    k.tahun,
    k.total_tagihan as tagihan_lama,
    CASE 
        WHEN s.kelas LIKE '10%' THEN 2400000
        ELSE 2280000
    END as tagihan_baru,
    CASE 
        WHEN s.kelas LIKE '10%' THEN 2400000 - k.total_tagihan
        ELSE 2280000 - k.total_tagihan
    END as selisih
FROM siswa s
INNER JOIN keuangan k ON s.nis = k.nis
ORDER BY s.kelas, s.nama;

-- =============================================
-- EKSEKUSI UPDATE (Hapus komentar untuk menjalankan)
-- =============================================

-- Update tagihan kelas 10 menjadi Rp 2.400.000 per tahun
-- UPDATE keuangan k
-- INNER JOIN siswa s ON k.nis = s.nis
-- SET k.total_tagihan = 2400000,
--     k.sisa_tagihan = 2400000 - k.total_bayar
-- WHERE s.kelas LIKE '10%';

-- Update tagihan kelas 11 dan 12 menjadi Rp 2.280.000 per tahun
-- UPDATE keuangan k
-- INNER JOIN siswa s ON k.nis = s.nis
-- SET k.total_tagihan = 2280000,
--     k.sisa_tagihan = 2280000 - k.total_bayar
-- WHERE s.kelas LIKE '11%' OR s.kelas LIKE '12%';

-- =============================================
-- VERIFIKASI HASIL UPDATE
-- =============================================

-- Cek hasil update
SELECT 
    s.kelas,
    COUNT(*) as jumlah_siswa,
    MIN(k.total_tagihan) as tagihan_min,
    MAX(k.total_tagihan) as tagihan_max,
    AVG(k.total_tagihan) as tagihan_rata2
FROM siswa s
INNER JOIN keuangan k ON s.nis = k.nis
GROUP BY SUBSTRING(s.kelas, 1, 2)
ORDER BY s.kelas;

-- Cek total tagihan seluruh sekolah
SELECT 
    COUNT(DISTINCT s.nis) as total_siswa,
    SUM(k.total_tagihan) as total_tagihan_sekolah,
    SUM(k.total_bayar) as total_terbayar,
    SUM(k.total_tagihan - k.total_bayar) as total_sisa_tagihan,
    ROUND((SUM(k.total_bayar) / SUM(k.total_tagihan) * 100), 2) as persentase_terbayar
FROM siswa s
INNER JOIN keuangan k ON s.nis = k.nis
WHERE k.tahun = '2024/2025'; -- Sesuaikan tahun ajaran

-- =============================================
-- BACKUP (Jalankan sebelum update)
-- =============================================

-- Backup tabel keuangan
-- CREATE TABLE keuangan_backup_20251201 AS SELECT * FROM keuangan;

-- Restore jika ada masalah
-- DELETE FROM keuangan;
-- INSERT INTO keuangan SELECT * FROM keuangan_backup_20251201;
