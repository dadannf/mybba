# 🗄️ PANDUAN IMPORT DATABASE KE HOSTING

## ❌ MASALAH YANG TERJADI

**Error:**
```
ERROR: Unknown collation: 'utf8mb4_0900_ai_ci'
```

**Penyebab:**
- Database backup dari MySQL 8.0 (Laragon lokal)
- Hosting menggunakan MySQL 5.7 atau MariaDB
- Collation `utf8mb4_0900_ai_ci` hanya ada di MySQL 8.0+

---

## ✅ SOLUSI - FILE LENGKAP SUDAH SIAP

File baru **`dbsekolah_complete.sql`** sudah dibuat dengan:
- ✅ Collation diganti: `utf8mb4_0900_ai_ci` → `utf8mb4_general_ci`
- ✅ Kompatibel dengan MySQL 5.7, 5.6, MariaDB
- ✅ **SEMUA 7 TABEL** termasuk (users, siswa, keuangan, pembayaran, informasi, notifikasi, ocr_validations)
- ✅ Data lengkap dengan indexes dan foreign keys

**Lokasi File:**
```
database/backups/dbsekolah_complete.sql (RECOMMENDED - USE THIS)
database/backups/dbsekolah_compatible.sql (Old - missing 2 tables)
```

---

## 📝 LANGKAH IMPORT KE HOSTING

### **Metode 1: Via phpMyAdmin (RECOMMENDED)**

**Step 1: Login ke cPanel/phpMyAdmin**
```
1. Login ke cPanel hosting Anda
2. Klik phpMyAdmin
3. Pilih database: xhpvlcgh_dbsekolah
```

**Step 2: Import File**
```
1. Klik tab "Import" di phpMyAdmin
2. Klik "Choose File" / "Browse"
3. Pilih file: dbsekolah_complete.sql ⚠️ USE THIS (not _compatible)
4. Pastikan:
   - Format: SQL
   - Character set: utf8mb4
5. Klik "Go" / "Execute"
```

**Step 3: Verifikasi**
```
1. Setelah import selesai, cek SEMUA 7 tabel:
   - users ✅
   - siswa ✅
   - keuangan ✅
   - pembayaran ✅
   - informasi ✅
   - notifikasi ✅ (NEW)
   - ocr_validations ✅ (NEW)
2. Pastikan ada data di tabel (kecuali notifikasi & ocr_validations boleh kosong)
3. Check total rows sesuai dengan lokal
```

---

### **Metode 2: Via MySQL Command Line**

Jika phpMyAdmin timeout karena file terlalu besar:

```bash
# Login ke server via SSH
ssh user@your-hosting.com

# Navigate ke folder
cd /path/to/database

# Import database
mysql -u xhpvlcgh_user -p xhpvlcgh_dbsekolah < dbsekolah_compatible.sql

# Atau jika di cPanel
mysql -u xhpvlcgh_user -p xhpvlcgh_dbsekolah < /home/username/dbsekolah_compatible.sql
```

---

## 🔧 KONFIGURASI APLIKASI

Setelah database berhasil di-import, update file `.env` atau `config.php`:

### **Update config.php:**
```php
// File: public/config.php

$host = 'localhost';  // atau IP dari hosting
$dbname = 'xhpvlcgh_dbsekolah';  // ⚠️ GANTI NAMA DATABASE
$username = 'xhpvlcgh_user';     // ⚠️ GANTI USERNAME
$password = 'password_anda';      // ⚠️ GANTI PASSWORD
```

### **Atau update .env (untuk Railway/production):**
```env
DB_HOST=localhost
DB_DATABASE=xhpvlcgh_dbsekolah
DB_USERNAME=xhpvlcgh_user
DB_PASSWORD=your_password_here
```

---

## 🛠️ TROUBLESHOOTING

### **Problem 1: Import Timeout**
**Error:** "Script timeout" atau "Maximum execution time exceeded"

**Solution:**
```
Method A - Split file:
1. Gunakan tool split SQL online
2. Import per bagian (split ke 2-3 file)

Method B - Increase timeout:
1. Edit php.ini (jika ada akses):
   max_execution_time = 600
   upload_max_filesize = 100M
   post_max_size = 100M
2. Restart web server
```

---

### **Problem 2: Access Denied**
**Error:** "Access denied for user"

**Solution:**
```
1. Pastikan username & password benar
2. Pastikan user punya privilege:
   - SELECT, INSERT, UPDATE, DELETE
   - CREATE, DROP, ALTER
   - INDEX, REFERENCES
3. Grant privilege via cPanel/phpMyAdmin:
   GRANT ALL PRIVILEGES ON xhpvlcgh_dbsekolah.* 
   TO 'xhpvlcgh_user'@'localhost';
   FLUSH PRIVILEGES;
```

---

### **Problem 3: Table Already Exists**
**Error:** "Table 'users' already exists"

**Solution:**
```
Option A - Drop tables first:
1. Di phpMyAdmin, pilih database
2. Check semua tabel
3. Klik "Drop" untuk delete
4. Import ulang

Option B - Drop database & create ulang:
DROP DATABASE xhpvlcgh_dbsekolah;
CREATE DATABASE xhpvlcgh_dbsekolah 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;
```

---

### **Problem 4: Data Tidak Muncul**
**Solution:**
```
1. Check apakah import complete (no errors)
2. Check jumlah rows:
   SELECT COUNT(*) FROM siswa;
   SELECT COUNT(*) FROM users;
3. Check struktur tabel:
   DESCRIBE siswa;
4. Check data:
   SELECT * FROM users LIMIT 5;
```

---

## 📊 VERIFIKASI IMPORT BERHASIL

### **Checklist:**

### **Database Structure:**
- [ ] Table `users` exists (5 columns)
- [ ] Table `siswa` exists (17+ columns)
- [ ] Table `keuangan` exists (6 columns)
- [ ] Table `pembayaran` exists (12 columns)
- [ ] Table `informasi` exists (7 columns)
- [ ] Table `notifikasi` exists (9 columns) ✨ NEW
- [ ] Table `ocr_validations` exists (28 columns) ✨ NEW

**Data Check:**
- [ ] `users` table punya min 2 rows (admin + siswa)
- [ ] `siswa` table punya data siswa
- [ ] `keuangan` table punya data keuangan
- [ ] `pembayaran` table punya riwayat pembayaran (jika ada)
- [ ] `informasi` table punya pengumuman (jika ada)
- [ ] `notifikasi` table boleh kosong (akan terisi otomatis)
- [ ] `ocr_validations` table boleh kosong (akan terisi saat OCR)

**Login Test:**
- [ ] Login sebagai admin (username: admin, password: admin123)
- [ ] Login sebagai siswa (username: siswa001, password: siswa123)
- [ ] Dashboard muncul dengan benar
- [ ] Data siswa muncul di list

---

## 🔄 REGENERATE FILE COMPATIBLE (jika perlu)

Jika Anda update database lokal dan perlu export ulang:

```powershell
# Run di PowerShell (Windows)
$inputFile = "f:\laragon\www\mybba\database\backups\dbsekolah.sql"
$outputFile = "f:\laragon\www\mybba\database\backups\dbsekolah_compatible.sql"
$content = Get-Content $inputFile -Raw
$content = $content -replace "utf8mb4_0900_ai_ci", "utf8mb4_general_ci"
$content | Out-File -FilePath $outputFile -Encoding UTF8
Write-Host "✅ Conversion complete!"
```

Atau via bash/Linux:
```bash
sed 's/utf8mb4_0900_ai_ci/utf8mb4_general_ci/g' \
  database/backups/dbsekolah.sql > \
  database/backups/dbsekolah_compatible.sql
```

---

## 📚 COLLATION COMPARISON

| Collation | MySQL Version | Bahasa | Performance | Akurasi |
|-----------|---------------|--------|-------------|---------|
| `utf8mb4_0900_ai_ci` | 8.0+ | Modern | ⚡⚡⚡ | ✅✅✅ |
| `utf8mb4_general_ci` | 5.5+ | Legacy | ⚡⚡ | ✅✅ |
| `utf8mb4_unicode_ci` | 5.5+ | Modern | ⚡ | ✅✅✅ |

**Kesimpulan:**
- `utf8mb4_general_ci` = **BEST untuk compatibility** (works di semua MySQL 5.5+)
- `utf8mb4_0900_ai_ci` = Best untuk MySQL 8.0+ (lebih akurat, lebih cepat)
- `utf8mb4_unicode_ci` = Best untuk accuracy tapi slower

---

## 🎯 NEXT STEPS

Setelah database berhasil di-import:

1. **Update Config:**
   - Update `public/config.php` dengan credentials hosting

2. **Test Koneksi:**
   - Buka aplikasi di browser
   - Check apakah bisa connect ke database

3. **Upload Files:**
   - Upload semua file aplikasi via FTP/cPanel
   - Upload folder `public/`
   - Upload folder `ocr_system/` (jika perlu)

4. **Set Permissions:**
   - Folder `uploads/`: 755 atau 777
   - Folder `uploads/bukti_pembayaran/`: 755
   - Folder `uploads/informasi/`: 755

5. **Test Aplikasi:**
   - Login admin
   - Check semua menu
   - Test CRUD operations

---

## ✅ STATUS

- [x] File compatible sudah dibuat
- [x] Collation sudah diubah ke `utf8mb4_general_ci`
- [x] Ready untuk import ke hosting
- [ ] Import ke database hosting
- [ ] Update config aplikasi
- [ ] Test koneksi & login

---

**File Ready:** `database/backups/dbsekolah_compatible.sql`  
**Database Target:** `xhpvlcgh_dbsekolah`  
**Compatible With:** MySQL 5.5+, 5.6, 5.7, 8.0, MariaDB 10.x

---

_Last Updated: December 17, 2025_  
_Issue: MySQL collation compatibility_  
_Status: RESOLVED ✅_
