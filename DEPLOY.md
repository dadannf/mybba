# 🚀 Panduan Auto-Deploy GitHub ke cPanel

Panduan ini menjelaskan cara menghubungkan repository GitHub dengan hosting cPanel agar dapat melakukan deployment otomatis setiap kali ada perubahan kode.

## 🔄 Cara Kerja Auto-Deploy

```
┌─────────────────┐
│   Developer     │
│   (Anda)        │
└────────┬────────┘
         │ git push origin main
         ▼
┌─────────────────────────┐
│   GitHub Repository     │
│   (dadannf/mybba)       │
└────────┬────────────────┘
         │ Trigger
         ▼
┌─────────────────────────┐
│   GitHub Actions        │
│   (cpanel-deploy.yml)   │
└────────┬────────────────┘
         │ FTP Upload
         ▼
┌─────────────────────────┐
│   cPanel Hosting        │
│   (/public_html/)       │
└─────────────────────────┘
         │
         ▼
┌─────────────────────────┐
│   Website Live! 🎉      │
│   (namadomain.com)      │
└─────────────────────────┘
```

**Alur Kerja:**
1. Anda edit code di komputer lokal
2. Commit & push ke GitHub (branch main)
3. GitHub Actions otomatis terdeteksi ada push baru
4. Workflow `cpanel-deploy.yml` dijalankan
5. File di-upload via FTP ke cPanel hosting
6. Website otomatis update!

⏱️ **Durasi**: Sekitar 1-2 menit dari push hingga live

## 📋 Prasyarat

1. **Akun cPanel hosting** yang sudah aktif
2. **Akses FTP/SFTP** ke cPanel hosting Anda
3. **Repository GitHub** dengan akses admin/owner
4. **Domain** sudah terhubung ke cPanel

## 🔧 Langkah-Langkah Setup

### 1️⃣ Dapatkan Informasi FTP dari cPanel

Login ke **cPanel** Anda, kemudian:

1. Cari menu **"FTP Accounts"** atau **"Akun FTP"**
2. Jika belum ada akun FTP, buat akun baru atau gunakan akun FTP utama
3. Catat informasi berikut:
   - **FTP Server/Host**: biasanya `ftp.namadomain.com` atau IP server
   - **FTP Username**: username FTP Anda
   - **FTP Password**: password FTP Anda
   - **FTP Directory**: biasanya `/public_html/` untuk website utama

#### Tips Mencari Info FTP:
- **Server**: Bisa ditemukan di halaman utama cPanel (Shared IP atau Account Information)
- **Username**: Sama dengan username cPanel atau tertera di FTP Accounts
- **Password**: Password yang Anda buat saat setup FTP account

### 2️⃣ Setup GitHub Secrets

GitHub Secrets digunakan untuk menyimpan informasi sensitif seperti kredensial FTP dengan aman.

1. Buka repository GitHub Anda: `https://github.com/dadannf/mybba`
2. Klik tab **"Settings"** (pastikan Anda adalah owner/admin)
3. Di sidebar kiri, klik **"Secrets and variables"** → **"Actions"**
4. Klik tombol **"New repository secret"**
5. Tambahkan 3 secrets berikut satu per satu:

#### Secret 1: FTP_SERVER
- **Name**: `FTP_SERVER`
- **Value**: `ftp.namadomain.com` (ganti dengan FTP server Anda)
- Klik **"Add secret"**

#### Secret 2: FTP_USERNAME
- **Name**: `FTP_USERNAME`
- **Value**: `username_ftp_anda` (ganti dengan username FTP Anda)
- Klik **"Add secret"**

#### Secret 3: FTP_PASSWORD
- **Name**: `FTP_PASSWORD`
- **Value**: `password_ftp_anda` (ganti dengan password FTP Anda)
- Klik **"Add secret"**

### 3️⃣ Sesuaikan Server Directory (Opsional)

Jika folder deployment di cPanel Anda **BUKAN** `/public_html/`, edit file workflow:

1. Buka file `.github/workflows/cpanel-deploy.yml`
2. Cari baris `server-dir: /public_html/`
3. Ganti dengan path yang sesuai, contoh:
   - `/public_html/mybba/` - jika deploy ke subfolder
   - `/home/username/public_html/` - jika perlu full path
   - `/` - jika deploy ke root FTP

### 4️⃣ Test Deployment

#### Cara 1: Push ke Branch Main (Otomatis)
```bash
git add .
git commit -m "Test auto deploy"
git push origin main
```

#### Cara 2: Manual Trigger (Melalui GitHub)
1. Buka repository di GitHub
2. Klik tab **"Actions"**
3. Pilih workflow **"Deploy to cPanel"**
4. Klik tombol **"Run workflow"**
5. Pilih branch **"main"**
6. Klik **"Run workflow"**

### 5️⃣ Monitor Deployment

1. Buka tab **"Actions"** di repository GitHub
2. Anda akan melihat workflow yang sedang berjalan
3. Klik pada workflow untuk melihat detail log
4. Tunggu hingga status berubah menjadi ✅ (sukses) atau ❌ (gagal)

## 📊 Status Deployment

Setelah workflow selesai:
- ✅ **Success** (hijau): File berhasil di-deploy ke cPanel
- ❌ **Failure** (merah): Ada error, cek log untuk detail

## 🔍 Troubleshooting

### ❌ Error: "Login incorrect" atau "Authentication failed"
**Penyebab**: Username atau password FTP salah

**Solusi**:
1. Verifikasi username/password FTP di cPanel
2. Update GitHub Secrets dengan kredensial yang benar
3. Pastikan tidak ada spasi tambahan saat copy-paste

### ❌ Error: "Could not connect to server"
**Penyebab**: Server FTP tidak bisa diakses atau salah

**Solusi**:
1. Cek FTP server address (bisa coba IP server atau domain)
2. Pastikan hosting aktif dan tidak down
3. Coba ping server: `ping ftp.namadomain.com`
4. Hubungi provider hosting untuk konfirmasi FTP server

### ❌ Error: "Permission denied" atau "550 error"
**Penyebab**: User FTP tidak punya akses write ke folder

**Solusi**:
1. Pastikan FTP user punya permission write
2. Cek ownership folder di cPanel File Manager
3. Gunakan FTP account utama (bukan sub-account)

### ❌ Workflow tidak jalan otomatis
**Penyebab**: Workflow hanya jalan di branch `main`

**Solusi**:
1. Pastikan Anda push ke branch `main`, bukan branch lain
2. Atau edit file workflow, ganti `main` dengan nama branch Anda
3. Cek tab Actions apakah workflow enabled

### ⚠️ File tidak terupdate di cPanel
**Penyebab**: Cache browser atau FTP sync issue

**Solusi**:
1. Clear browser cache (Ctrl+F5)
2. Cek file timestamp di cPanel File Manager
3. Re-run workflow secara manual
4. Cek exclude list di workflow file (mungkin file Anda di-exclude)

## 📁 File yang Di-Deploy

Workflow akan deploy **semua file** kecuali yang ada di exclude list:
- ✅ Folder `public/` → `/public_html/`
- ✅ Folder `config/` → `/public_html/config/`
- ✅ Folder `database/` → `/public_html/database/`
- ✅ Folder `uploads/` → `/public_html/uploads/`
- ❌ Folder `.git/`, `node_modules/`, `vendor/` (tidak di-upload)
- ❌ File `.env`, `README.md`, documentation files (tidak di-upload)

## 🔐 Keamanan

**PENTING - Jangan:**
- ❌ Commit file `.env` dengan data sensitif
- ❌ Share GitHub Secrets ke orang lain
- ❌ Screenshot atau publish password FTP

**Lakukan:**
- ✅ Gunakan password FTP yang kuat
- ✅ Simpan `.env` di `.gitignore`
- ✅ Buat file `.env` langsung di cPanel setelah deploy
- ✅ Rotate password FTP secara berkala

## 🎯 Setup Database di cPanel

Setelah file berhasil di-deploy, setup database:

1. **Buat Database di cPanel**:
   - Masuk ke **"MySQL Databases"**
   - Buat database baru (misal: `dbsekolah`)
   - Buat user dan set password
   - Assign user ke database dengan ALL PRIVILEGES

2. **Import Database**:
   - Masuk ke **"phpMyAdmin"**
   - Pilih database yang baru dibuat
   - Klik tab **"Import"**
   - Upload file `database/backups/dbsekolah.sql`
   - Klik **"Go"**

3. **Setup File .env di cPanel**:
   - Masuk ke **"File Manager"**
   - Navigate ke `/public_html/config/`
   - Create file baru `.env` atau edit `database.php`
   - Isi dengan koneksi database cPanel:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'username_dbsekolah');
   define('DB_USER', 'username_dbuser');
   define('DB_PASS', 'password_database');
   ```

## ✅ Verifikasi Deployment Berhasil

1. **Cek File**: Buka cPanel File Manager, cek folder `/public_html/` ada file-file project
2. **Akses Website**: Buka `https://namadomain.com` di browser
3. **Test Login**: Coba login dengan akun admin atau siswa
4. **Cek Database**: Pastikan koneksi database berhasil

## 🔄 Workflow Deployment

Setiap kali Anda:
1. Edit code di komputer lokal
2. `git add .` dan `git commit -m "pesan"`
3. `git push origin main`
4. ✨ **Otomatis**: GitHub Actions akan deploy ke cPanel
5. 🎉 Website langsung update (dalam 1-2 menit)

## 📞 Bantuan Lebih Lanjut

Jika masih ada masalah:
1. Cek log detail di tab **Actions** → Klik workflow → Lihat error message
2. Screenshot error dan konsultasi dengan provider hosting
3. Baca dokumentasi cPanel hosting Anda
4. Contact support hosting untuk bantuan FTP setup

## 🎓 Alternatif: Git Version Control di cPanel

Beberapa hosting cPanel modern support **Git Version Control** built-in:

1. Di cPanel, cari menu **"Git Version Control"**
2. Klik **"Create"**
3. Clone repository: `https://github.com/dadannf/mybba.git`
4. Set repository path ke `/public_html/`
5. Klik **"Create"**

Keuntungan:
- ✅ Bisa pull langsung dari cPanel
- ✅ Tidak perlu FTP credentials
- ✅ Lebih cepat untuk update

Kekurangan:
- ❌ Tidak otomatis (harus manual pull)
- ❌ Tidak semua hosting support

---

## 📝 Checklist Setup

- [ ] Dapatkan FTP credentials dari cPanel
- [ ] Tambahkan 3 GitHub Secrets (FTP_SERVER, FTP_USERNAME, FTP_PASSWORD)
- [ ] Sesuaikan `server-dir` jika perlu
- [ ] Test deployment (push ke main atau run workflow manual)
- [ ] Monitor workflow di tab Actions
- [ ] Verifikasi file sudah ada di cPanel File Manager
- [ ] Buat database di cPanel
- [ ] Import database SQL
- [ ] Setup file `.env` atau config database di cPanel
- [ ] Test akses website
- [ ] Test login dan fitur aplikasi

---

**🎉 Selamat! Sekarang GitHub dan cPanel Anda sudah terhubung untuk auto-deployment!**
