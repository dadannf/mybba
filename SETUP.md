# ⚡ Quick Setup Guide - MyBBA

## 🚀 Setup dalam 5 Menit

### 1️⃣ Import Database
```bash
# Via MySQL Command Line
mysql -u root -e "CREATE DATABASE IF NOT EXISTS dbsekolah"
mysql -u root dbsekolah < database/backups/dbsekolah.sql

# Via phpMyAdmin
# 1. Buka http://localhost/phpmyadmin
# 2. Buat database 'dbsekolah'
# 3. Import file database/backups/dbsekolah.sql
```

### 2️⃣ Konfigurasi Database
Edit `public/config.php` jika perlu:
```php
$host = 'localhost';
$dbname = 'dbsekolah';
$username = 'root';
$password = ''; // Sesuaikan dengan password MySQL Anda
```

### 3️⃣ Set Permissions (Linux/Mac)
```bash
chmod -R 755 public/uploads
```

### 4️⃣ Jalankan Server

**Opsi A: Via Laragon (Recommended)**
```
Akses: http://localhost/mybba
atau: http://mybba.test
```

**Opsi B: Via PHP Built-in Server**
```bash
php -S localhost:8000 -t public
# Akses: http://localhost:8000
```

### 5️⃣ Login
```
Admin:
- Username: admin
- Password: admin123

Siswa:
- Username: siswa001
- Password: siswa123
```

## ✅ Checklist

- [ ] Database 'dbsekolah' sudah dibuat
- [ ] File SQL sudah diimport
- [ ] Config.php sudah disesuaikan
- [ ] Folder uploads bisa diakses
- [ ] Berhasil login sebagai admin
- [ ] Berhasil login sebagai siswa

## 🔧 Troubleshooting

**Q: Error "Connection refused"**
- Pastikan MySQL service running
- Cek username/password di config.php

**Q: Error "Permission denied" saat upload**
- Jalankan: `chmod -R 755 public/uploads`

**Q: Halaman blank/error 500**
- Cek PHP error log
- Pastikan PHP version >= 8.2

**Q: Session error**
- Hapus cookies browser
- Restart browser

## 🎯 Next Steps

1. ✅ Login sebagai admin
2. ✅ Tambah data siswa baru
3. ✅ Buat tagihan keuangan
4. ✅ Test upload bukti bayar sebagai siswa
5. ✅ Verifikasi pembayaran sebagai admin

---

**Need help?** Check DOCS.md for full documentation
