-- =============================================
-- TRIGGER: Auto-update total_bayar di tabel keuangan
-- Deskripsi: Otomatis menghitung ulang total_bayar ketika ada perubahan di tabel pembayaran
-- =============================================

DELIMITER $$

-- Trigger setelah INSERT pembayaran
DROP TRIGGER IF EXISTS after_pembayaran_insert$$
CREATE TRIGGER after_pembayaran_insert
AFTER INSERT ON pembayaran
FOR EACH ROW
BEGIN
    UPDATE keuangan 
    SET total_bayar = (
        SELECT COALESCE(SUM(nominal_bayar), 0) 
        FROM pembayaran 
        WHERE keuangan_id = NEW.keuangan_id 
        AND status = 'valid'
    )
    WHERE keuangan_id = NEW.keuangan_id;
END$$

-- Trigger setelah UPDATE pembayaran
DROP TRIGGER IF EXISTS after_pembayaran_update$$
CREATE TRIGGER after_pembayaran_update
AFTER UPDATE ON pembayaran
FOR EACH ROW
BEGIN
    -- Update untuk keuangan lama (jika keuangan_id berubah)
    IF OLD.keuangan_id != NEW.keuangan_id THEN
        UPDATE keuangan 
        SET total_bayar = (
            SELECT COALESCE(SUM(nominal_bayar), 0) 
            FROM pembayaran 
            WHERE keuangan_id = OLD.keuangan_id 
            AND status = 'valid'
        )
        WHERE keuangan_id = OLD.keuangan_id;
    END IF;
    
    -- Update untuk keuangan baru
    UPDATE keuangan 
    SET total_bayar = (
        SELECT COALESCE(SUM(nominal_bayar), 0) 
        FROM pembayaran 
        WHERE keuangan_id = NEW.keuangan_id 
        AND status = 'valid'
    )
    WHERE keuangan_id = NEW.keuangan_id;
END$$

-- Trigger setelah DELETE pembayaran
DROP TRIGGER IF EXISTS after_pembayaran_delete$$
CREATE TRIGGER after_pembayaran_delete
AFTER DELETE ON pembayaran
FOR EACH ROW
BEGIN
    UPDATE keuangan 
    SET total_bayar = (
        SELECT COALESCE(SUM(nominal_bayar), 0) 
        FROM pembayaran 
        WHERE keuangan_id = OLD.keuangan_id 
        AND status = 'valid'
    )
    WHERE keuangan_id = OLD.keuangan_id;
END$$

DELIMITER ;

-- Test trigger dengan mengupdate existing data
UPDATE keuangan k
SET k.total_bayar = (
    SELECT COALESCE(SUM(p.nominal_bayar), 0)
    FROM pembayaran p
    WHERE p.keuangan_id = k.keuangan_id AND p.status = 'valid'
);

-- Verify
SELECT 
    k.keuangan_id,
    k.nis,
    k.tahun,
    k.total_tagihan,
    k.total_bayar,
    k.progress,
    (SELECT COUNT(*) FROM pembayaran WHERE keuangan_id = k.keuangan_id AND status = 'valid') as jumlah_valid
FROM keuangan k
ORDER BY k.keuangan_id;
