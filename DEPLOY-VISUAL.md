# 📸 Visual Guide - Setup Auto Deploy (Step-by-Step)

Panduan visual step-by-step untuk setup auto-deployment dari GitHub ke cPanel.

---

## 🎯 Overview

Tujuan: Menghubungkan GitHub repository dengan cPanel hosting untuk deployment otomatis.

**Total waktu setup**: ~15 menit (one-time setup)

---

## Part 1️⃣: Dapatkan FTP Credentials dari cPanel

### Step 1: Login ke cPanel
1. Buka browser, akses cPanel Anda (biasanya: `https://namadomain.com:2083` atau `https://namadomain.com/cpanel`)
2. Masukkan username dan password cPanel
3. Klik **Login**

### Step 2: Buka FTP Accounts
1. Di dashboard cPanel, scroll ke section **"Files"**
2. Klik menu **"FTP Accounts"**

### Step 3: Lihat FTP Credentials
Anda akan melihat informasi FTP di halaman ini:

**FTP Server/Hostname**:
- Cari di bagian atas halaman
- Atau di "Account Information" di sidebar
- Format: `ftp.namadomain.com` atau `123.456.789.10`

**FTP Username**:
- Lihat di tabel "FTP Accounts"
- Atau sama dengan username cPanel Anda

**FTP Password**:
- Jika lupa password, Anda bisa reset atau membuat FTP account baru
- Klik **"Change Password"** untuk FTP user yang ada
- Atau klik **"Create FTP Account"** untuk membuat baru

### Step 4: Catat Credentials
Copy dan simpan di notepad:
```
FTP Server: ftp.namadomain.com
FTP Username: username_anda
FTP Password: password_anda
FTP Directory: /public_html/
```

**✅ Checklist Part 1:**
- [ ] Login ke cPanel berhasil
- [ ] FTP Server sudah dicatat
- [ ] FTP Username sudah dicatat
- [ ] FTP Password sudah dicatat

---

## Part 2️⃣: Setup GitHub Secrets

### Step 1: Buka Repository GitHub
1. Buka browser
2. Login ke GitHub
3. Akses repository: `https://github.com/dadannf/mybba`

### Step 2: Masuk ke Settings
1. Klik tab **"Settings"** (paling kanan, di atas)
2. Pastikan Anda punya akses admin/owner (jika tidak ada tab Settings, hubungi owner)

### Step 3: Buka Secrets Menu
1. Di sidebar kiri, scroll ke section **"Security"**
2. Klik **"Secrets and variables"**
3. Pilih **"Actions"**
4. Anda akan melihat halaman "Actions secrets and variables"

### Step 4: Tambahkan Secret #1 - FTP_SERVER
1. Klik tombol **"New repository secret"** (hijau, di kanan atas)
2. Di field **"Name"**, ketik: `FTP_SERVER` (huruf besar semua)
3. Di field **"Secret"**, paste FTP Server Anda (contoh: `ftp.namadomain.com`)
4. Klik **"Add secret"**

### Step 5: Tambahkan Secret #2 - FTP_USERNAME
1. Klik **"New repository secret"** lagi
2. **Name**: `FTP_USERNAME`
3. **Secret**: Paste FTP Username Anda
4. Klik **"Add secret"**

### Step 6: Tambahkan Secret #3 - FTP_PASSWORD
1. Klik **"New repository secret"** lagi
2. **Name**: `FTP_PASSWORD`
3. **Secret**: Paste FTP Password Anda
4. Klik **"Add secret"**

### Step 7: Verifikasi Secrets
Setelah selesai, Anda harus melihat 3 secrets di halaman:
```
FTP_SERVER      Updated now by you
FTP_USERNAME    Updated now by you
FTP_PASSWORD    Updated now by you
```

**⚠️ Catatan**: Anda tidak bisa melihat isi secret setelah dibuat (untuk keamanan). Hanya bisa delete atau update.

**✅ Checklist Part 2:**
- [ ] Masuk ke Settings → Secrets and variables → Actions
- [ ] Secret FTP_SERVER sudah dibuat
- [ ] Secret FTP_USERNAME sudah dibuat
- [ ] Secret FTP_PASSWORD sudah dibuat
- [ ] Total ada 3 secrets

---

## Part 3️⃣: Test Deployment

### Option A: Automatic Deployment (via Push)

#### Step 1: Edit Code di Local
1. Buka project di text editor/IDE
2. Buat perubahan kecil (misal: edit README atau tambah comment)

#### Step 2: Commit & Push
```bash
git add .
git commit -m "Test auto deployment"
git push origin main
```

#### Step 3: Monitor di GitHub Actions
1. Buka repository di browser
2. Klik tab **"Actions"** (di samping Pull requests)
3. Anda akan melihat workflow berjalan dengan judul commit Anda
4. Klik workflow tersebut untuk melihat progress real-time

### Option B: Manual Deployment (via GitHub UI)

#### Step 1: Buka Actions Tab
1. Di repository GitHub
2. Klik tab **"Actions"**

#### Step 2: Pilih Workflow
1. Di sidebar kiri, klik **"Deploy to cPanel"**

#### Step 3: Run Workflow
1. Klik tombol **"Run workflow"** (dropdown, di kanan atas)
2. Pilih branch: **main**
3. Klik **"Run workflow"** (tombol hijau)

#### Step 4: Monitor Progress
1. Refresh halaman jika perlu
2. Klik pada workflow yang muncul
3. Lihat progress bar dan log details

### Step 5: Tunggu Sampai Selesai
- ⏳ Status **yellow (in progress)**: Sedang deploy (~1-2 menit)
- ✅ Status **green (success)**: Deploy berhasil!
- ❌ Status **red (failed)**: Ada error (klik untuk lihat log)

**✅ Checklist Part 3:**
- [ ] Workflow berhasil dijalankan (manual atau otomatis)
- [ ] Status workflow = Success (hijau)
- [ ] Tidak ada error di log

---

## Part 4️⃣: Verifikasi di cPanel

### Step 1: Cek File di cPanel
1. Login ke cPanel
2. Buka **"File Manager"**
3. Navigate ke `/public_html/`
4. Verifikasi file-file project sudah ada

### Step 2: Cek Timestamp
1. Lihat "Last Modified" date/time file
2. Seharusnya sama dengan waktu deployment
3. Jika tidak update, refresh browser atau re-run workflow

**✅ Checklist Part 4:**
- [ ] File Manager menampilkan file project
- [ ] Timestamp file sesuai dengan waktu deployment
- [ ] Struktur folder sesuai (public, config, database, dll)

---

## Part 5️⃣: Setup Database (One-time)

### Step 1: Buat Database MySQL
1. Di cPanel, buka **"MySQL Databases"**
2. Buat database baru:
   - **Database Name**: `dbsekolah` (atau nama lain)
   - Klik **"Create Database"**
   - Catat nama lengkap: `username_dbsekolah`

### Step 2: Buat User Database
1. Scroll ke **"MySQL Users"**
2. **Username**: `dbuser`
3. **Password**: (gunakan strong password)
4. Klik **"Create User"**
5. Catat: `username_dbuser` dan passwordnya

### Step 3: Assign User ke Database
1. Scroll ke **"Add User To Database"**
2. **User**: Pilih user yang baru dibuat
3. **Database**: Pilih database yang baru dibuat
4. Klik **"Add"**
5. Centang **"ALL PRIVILEGES"**
6. Klik **"Make Changes"**

### Step 4: Import Database
1. Buka **"phpMyAdmin"** dari cPanel
2. Di sidebar kiri, pilih database yang baru dibuat
3. Klik tab **"Import"**
4. Klik **"Choose File"**
5. Pilih file: `/database/backups/dbsekolah.sql` dari komputer lokal
   - Atau download dari GitHub repository dulu
6. Scroll ke bawah, klik **"Go"**
7. Tunggu hingga import selesai (hijau = sukses)

### Step 5: Edit Database Config
1. Buka **"File Manager"** di cPanel
2. Navigate ke `/public_html/config/`
3. Cari file `database.php` atau buat `.env`
4. Klik kanan → **"Edit"**
5. Update dengan credentials database:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'username_dbsekolah');
define('DB_USER', 'username_dbuser');
define('DB_PASS', 'password_yang_dibuat');
```
6. Klik **"Save Changes"**

**✅ Checklist Part 5:**
- [ ] Database MySQL dibuat
- [ ] Database user dibuat dan assigned
- [ ] Database berhasil di-import (tabel terlihat di phpMyAdmin)
- [ ] Config database sudah diupdate
- [ ] Tidak ada error saat akses website

---

## Part 6️⃣: Test Website Live

### Step 1: Akses Website
1. Buka browser
2. Akses: `https://namadomain.com` atau `http://namadomain.com`

### Step 2: Test Homepage
- Pastikan website terbuka dengan benar
- Tidak ada error 404 atau 500
- CSS dan layout terlihat benar

### Step 3: Test Login Admin
1. Klik tombol **Login** atau akses `/auth/login.php`
2. Username: `admin`
3. Password: `admin123`
4. Klik **Login**
5. Pastikan masuk ke dashboard admin

### Step 4: Test Login Siswa
1. Logout dari admin
2. Login dengan:
   - Username: `siswa001`
   - Password: `siswa123`
3. Pastikan masuk ke dashboard siswa

### Step 5: Test Fitur Utama
- ✅ Dashboard tampil data (statistik, dll)
- ✅ Menu navigasi berfungsi
- ✅ Database connection OK (data tampil)
- ✅ Upload file berfungsi (test upload foto)
- ✅ Responsive (test di mobile view)

**✅ Checklist Part 6:**
- [ ] Website dapat diakses
- [ ] Login admin berhasil
- [ ] Login siswa berhasil
- [ ] Fitur utama berfungsi normal
- [ ] Tidak ada PHP errors

---

## 🎉 Setup Complete!

### Workflow Selanjutnya:

1. **Development** (di komputer lokal):
   ```bash
   # Edit code
   git add .
   git commit -m "Feature/fix description"
   git push origin main
   ```

2. **Automatic Deployment**:
   - GitHub Actions otomatis terdeteksi push
   - Deploy ke cPanel via FTP (~1-2 menit)

3. **Verification**:
   - Akses website untuk verify changes
   - Check logs di GitHub Actions jika ada issue

### Tips:
- 💡 Selalu test di local sebelum push
- 💡 Monitor tab Actions setelah push
- 💡 Keep backup database secara berkala
- 💡 Use descriptive commit messages
- 💡 Test major changes di staging environment dulu

---

## 🔧 Common Issues & Solutions

### Issue: Workflow Failed (Red X)

**Langkah Debugging:**
1. Klik workflow yang failed
2. Lihat step mana yang error (expand untuk detail)
3. Baca error message

**Common Errors:**

| Error | Fix |
|-------|-----|
| "Login incorrect" | Re-check FTP username/password di Secrets |
| "Could not connect" | Verify FTP server address |
| "Permission denied" | Check FTP user has write permission |
| "Timeout" | Hosting mungkin down, coba lagi nanti |

### Issue: Website Tidak Update

**Possible Causes:**
1. Browser cache → Hard refresh (Ctrl+F5)
2. Workflow belum selesai → Check Actions tab
3. File wrong directory → Verify `server-dir` di workflow
4. cPanel cache → Wait 2-3 minutes

### Issue: Database Error

**Fixes:**
1. Verify database credentials di config file
2. Check database name format: `username_dbname`
3. Ensure user has ALL PRIVILEGES
4. Test connection via phpMyAdmin

---

## 📞 Need Help?

1. **Error di GitHub Actions**: 
   - Screenshot error log
   - Share di GitHub Issues

2. **Error di cPanel**:
   - Cek error_log di cPanel
   - Contact hosting support

3. **Database Issues**:
   - Test di phpMyAdmin dulu
   - Verify table structures

---

## 📚 Related Documentation

- [DEPLOY.md](DEPLOY.md) - Full deployment guide
- [DEPLOY-QUICK.md](DEPLOY-QUICK.md) - Quick reference
- [DEPLOY-CONFIG.md](DEPLOY-CONFIG.md) - Configuration templates

---

**Last Updated**: December 2025  
**Version**: 1.0
