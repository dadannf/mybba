-- Add catatan column to pembayaran table
-- For storing payment notes, especially for Tunai payments (penerima kasir, dll)

USE dbsekolah;

-- Add catatan column (MySQL 8.0 compatible syntax)
ALTER TABLE pembayaran 
ADD COLUMN catatan TEXT NULL 
AFTER status;

-- Verify the change
DESCRIBE pembayaran;
