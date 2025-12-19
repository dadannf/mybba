# 🚀 Quick Reference - GitHub to cPanel Auto-Deploy

## Ringkasan Singkat Setup

### 1. Dapatkan Info FTP dari cPanel
- Server: `ftp.namadomain.com`
- Username: username FTP Anda
- Password: password FTP Anda

### 2. Setup GitHub Secrets
Masuk ke: **Repository → Settings → Secrets and variables → Actions → New repository secret**

Tambahkan 3 secrets:
```
FTP_SERVER = ftp.namadomain.com
FTP_USERNAME = your_ftp_username
FTP_PASSWORD = your_ftp_password
```

### 3. Push ke Branch Main
```bash
git add .
git commit -m "Your changes"
git push origin main
```

### 4. Monitor di GitHub Actions
**Repository → Actions tab** → Lihat workflow berjalan

---

## Troubleshooting Cepat

| Error | Solusi |
|-------|--------|
| Login incorrect | Cek username/password di GitHub Secrets |
| Could not connect | Verifikasi FTP server address di cPanel |
| Permission denied | Gunakan FTP account utama dengan write access |
| File tidak update | Clear cache browser (Ctrl+F5) |

---

## Struktur Deployment

```
GitHub Repository → GitHub Actions → FTP Upload → cPanel Hosting
```

File yang di-deploy:
- ✅ `/public/` → `/public_html/`
- ✅ `/config/` → `/public_html/config/`
- ✅ `/database/` → `/public_html/database/`
- ✅ `/uploads/` → `/public_html/uploads/`

File yang di-exclude:
- ❌ `.git/`, `node_modules/`, `vendor/`
- ❌ `.env`, documentation files

---

## Setup Database (One-time)

1. **cPanel → MySQL Databases** → Create database
2. **phpMyAdmin** → Import `database/backups/dbsekolah.sql`
3. **File Manager** → Edit `/config/database.php` dengan kredensial DB

---

## Manual Trigger Deployment

1. GitHub → **Actions** tab
2. Select **"Deploy to cPanel"** workflow
3. Click **"Run workflow"** → Select **main** branch → Run

---

📖 **Dokumentasi lengkap**: Lihat [DEPLOY.md](DEPLOY.md)
