-- Add diterima_oleh column to pembayaran table
-- For storing the name of cashier/admin who received the payment

USE dbsekolah;

-- Add diterima_oleh column
ALTER TABLE pembayaran 
ADD COLUMN diterima_oleh VARCHAR(100) NULL 
AFTER catatan;

-- Verify the change
DESCRIBE pembayaran;
