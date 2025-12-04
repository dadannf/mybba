-- ========================================
-- STUDENT PORTAL - Test Accounts Setup
-- ========================================

-- Step 1: Create Admin Account
-- Password: admin123 (hashed with bcrypt)
INSERT INTO users (username, password, role, email) 
VALUES (
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'admin', 
    'admin@sekolah.com'
);

-- Step 2: Check existing students' NIS
-- You need to register student accounts with username = NIS
SELECT nis, nama, kelas, jurusan FROM siswa LIMIT 10;

-- Step 3: Create Student Accounts (Example)
-- IMPORTANT: Replace '12345' with actual NIS from siswa table
-- Password Format: bba#[4 angka terakhir NIS]
-- Example: NIS 12345 → password: bba#2345
--          NIS 67890 → password: bba#7890

-- Example 1: Student with NIS 12345
-- Password: bba#2345
INSERT INTO users (username, password, role, email) 
VALUES (
    '12345',  -- NIS siswa
    '$2y$10$[HASH_FOR_bba#2345]',  -- Generate hash via create_student_accounts.php
    'siswa', 
    'siswa12345@email.com'
);

-- Example 2: Student with NIS 67890
-- Password: bba#7890
INSERT INTO users (username, password, role, email) 
VALUES (
    '67890',  -- NIS siswa
    '$2y$10$[HASH_FOR_bba#7890]',  -- Generate hash via create_student_accounts.php
    'siswa', 
    'siswa67890@email.com'
);

-- ========================================
-- RECOMMENDED: Use create_student_accounts.php
-- ========================================
-- Instead of manual insert, use the PHP script to auto-generate
-- student accounts with correct password format:
-- 
-- Access: http://localhost/mybba/public/create_student_accounts.php
-- 
-- Script will:
-- 1. Read all NIS from siswa table
-- 2. Generate password: bba#[last 4 digits]
-- 3. Hash password with bcrypt
-- 4. Insert into users table
-- 5. Skip if username already exists

-- ========================================
-- Verify Accounts Created
-- ========================================
SELECT user_id, username, role, email 
FROM users 
ORDER BY role, username;

-- ========================================
-- Test Login Credentials
-- ========================================
-- Admin Account:
--   Username: admin
--   Password: admin123
--
-- Student Account Format:
--   Username: [NIS siswa]
--   Password: bba#[4 angka terakhir NIS]
--   
--   Example:
--   - NIS: 12345 → Username: 12345, Password: bba#2345
--   - NIS: 67890 → Username: 67890, Password: bba#7890
--   - NIS: 001234 → Username: 001234, Password: bba#1234
--
-- ========================================
-- Password Hash Generation (PHP)
-- ========================================
-- To generate password hash for student:
-- <?php
-- $nis = '12345';
-- $last4 = substr($nis, -4);
-- $password = 'bba#' . $last4;
-- echo password_hash($password, PASSWORD_DEFAULT);
-- ?>
--
-- Common admin hash for testing:
-- admin123  = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- ========================================
-- Check Student-Keuangan Relationship
-- ========================================
-- Pastikan siswa memiliki data keuangan
SELECT 
    s.nis,
    s.nama,
    s.kelas,
    COUNT(k.keuangan_id) as total_keuangan,
    SUM(k.total_tagihan) as total_tagihan,
    SUM(k.total_bayar) as total_dibayar
FROM siswa s
LEFT JOIN keuangan k ON s.nis = k.nis
GROUP BY s.nis, s.nama, s.kelas
ORDER BY s.nama
LIMIT 10;

-- ========================================
-- Create Sample Keuangan for Testing
-- ========================================
-- If student doesn't have keuangan record, create one:
-- Replace '12345' with actual NIS

INSERT INTO keuangan (
    nis, 
    tahun, 
    total_tagihan, 
    total_bayar, 
    sisa_tagihan, 
    status_lunas
) VALUES (
    '12345',           -- Student NIS
    '2024/2025',       -- Current academic year
    1200000,           -- 12 months x Rp 100,000
    0,                 -- Belum bayar
    1200000,           -- Sisa = total
    'belum'            -- Status
);

-- ========================================
-- Verify Setup Complete
-- ========================================
-- 1. Check users created
SELECT 'Users Table' as check_point, COUNT(*) as total, role 
FROM users 
GROUP BY role;

-- 2. Check students with keuangan
SELECT 'Students with Keuangan' as check_point, COUNT(*) as total 
FROM siswa s 
INNER JOIN keuangan k ON s.nis = k.nis;

-- 3. Check pending payments
SELECT 'Pending Payments' as check_point, COUNT(*) as total 
FROM pembayaran 
WHERE status_bayar = 'menunggu';

-- ========================================
-- Clean Up (If needed)
-- ========================================
-- Delete test accounts
-- DELETE FROM users WHERE username IN ('admin', '12345', '67890');

-- Reset keuangan for testing
-- UPDATE keuangan SET total_bayar = 0, sisa_tagihan = total_tagihan, status_lunas = 'belum' WHERE nis = '12345';

-- Delete all payments for re-testing
-- DELETE FROM pembayaran WHERE keuangan_id = [specific_id];
