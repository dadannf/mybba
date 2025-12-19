# 📚 Deployment Documentation Index

## 🎯 Mulai dari Sini

**Tujuan**: Menghubungkan GitHub repository dengan cPanel hosting untuk automatic deployment.

**Status**: ✅ Ready to use  
**Setup Time**: ~15 menit (one-time)  
**Deployment Time**: 1-2 menit (automatic)

---

## 📖 Pilih Panduan Sesuai Kebutuhan

### 🆕 First Time Setup (Belum Pernah Setup)

**👉 Mulai dengan: [DEPLOY-VISUAL.md](DEPLOY-VISUAL.md)**

Panduan step-by-step dengan checklist untuk setiap tahap:
- ✅ Part 1: Dapatkan FTP credentials dari cPanel
- ✅ Part 2: Setup GitHub Secrets
- ✅ Part 3: Test Deployment
- ✅ Part 4: Verifikasi di cPanel
- ✅ Part 5: Setup Database
- ✅ Part 6: Test Website Live

**Waktu**: 15 menit  
**Difficulty**: Easy (ikuti step-by-step)

---

### 📖 Ingin Memahami Detail Lengkap

**👉 Baca: [DEPLOY.md](DEPLOY.md)**

Panduan lengkap mencakup:
- 🔄 Diagram alur kerja
- 🔧 Cara mendapatkan FTP credentials
- 🔐 Setup GitHub Secrets
- 🧪 Test deployment (auto & manual)
- 🗄️ Setup database
- 🔍 Troubleshooting lengkap
- 🔐 Tips keamanan
- ✅ Checklist verifikasi

**Waktu**: 20-30 menit baca  
**For**: Yang suka detail dan understanding

---

### ⚡ Sudah Pernah Setup (Quick Reference)

**👉 Lihat: [DEPLOY-QUICK.md](DEPLOY-QUICK.md)**

Quick reference berisi:
- 🎯 Ringkasan setup
- 📊 Tabel troubleshooting
- 🔗 Command cheat sheet
- ⚡ Fast lookup

**Waktu**: 2 menit  
**For**: Yang sudah familiar dengan process

---

### ⚙️ Ingin Customisasi & Advanced Setup

**👉 Buka: [DEPLOY-CONFIG.md](DEPLOY-CONFIG.md)**

Template dan customisasi:
- 📝 Template GitHub Secrets
- 🗄️ Template database config
- 📄 Contoh .htaccess
- 🎨 Customisasi workflow
- 📊 Monitoring & logging
- 🔐 Security best practices
- ✅ Post-deployment checklist

**Waktu**: 10-15 menit  
**For**: Advanced users, multiple environments

---

### ❓ Ada Pertanyaan atau Masalah

**👉 Cek: [DEPLOY-FAQ.md](DEPLOY-FAQ.md)**

30+ FAQ mencakup:
- 🔐 Keamanan & Privacy
- ⚙️ Setup & Configuration
- 🚨 Troubleshooting
- 💰 Biaya & Performa
- 🔄 Workflow Behavior
- 📁 File & Folder
- 🔧 Advanced Questions
- 📊 Monitoring & Logs

**Waktu**: Sesuai kebutuhan  
**For**: Solving specific problems

---

## 🚀 Quick Start (TL;DR)

Jika Anda sudah familiar dengan GitHub Actions dan FTP:

1. **Setup GitHub Secrets** (3 secrets):
   - `FTP_SERVER` → ftp.domain.com
   - `FTP_USERNAME` → username_ftp
   - `FTP_PASSWORD` → password_ftp

2. **Push ke main branch**:
   ```bash
   git push origin main
   ```

3. **Monitor** di GitHub Actions tab

4. **Done!** Website live di cPanel 🎉

Detail: Lihat [DEPLOY-QUICK.md](DEPLOY-QUICK.md)

---

## 🎓 Learning Path

### Path 1: Beginner (Pertama Kali)
```
DEPLOY-VISUAL.md → Test Deployment → DEPLOY-FAQ.md (jika ada masalah)
```

### Path 2: Intermediate (Ada Experience)
```
DEPLOY.md → Setup Secrets → Test → DEPLOY-CONFIG.md (customize)
```

### Path 3: Advanced (Experienced)
```
DEPLOY-QUICK.md → DEPLOY-CONFIG.md → Custom Workflow
```

---

## 📊 Documentation Overview

| File | Size | Lines | Purpose |
|------|------|-------|---------|
| **DEPLOY.md** | 9.7 KB | 318 | 📖 Panduan lengkap |
| **DEPLOY-QUICK.md** | 1.9 KB | 66 | ⚡ Quick reference |
| **DEPLOY-CONFIG.md** | 6.1 KB | 237 | ⚙️ Templates & config |
| **DEPLOY-VISUAL.md** | 10 KB | 431 | 📸 Step-by-step visual |
| **DEPLOY-FAQ.md** | 9.4 KB | 326 | ❓ Troubleshooting |
| **cpanel-deploy.yml** | 1.1 KB | 42 | 🔧 GitHub Actions workflow |
| **Total** | **37 KB** | **1,420** | **Complete guide** |

---

## 🔄 Workflow Process

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
└─────────────────────────┘
```

---

## ✅ Setup Checklist

### One-Time Setup:
- [ ] Baca dokumentasi yang sesuai
- [ ] Dapatkan FTP credentials dari cPanel
- [ ] Setup 3 GitHub Secrets
- [ ] Test deployment pertama
- [ ] Verifikasi file di cPanel
- [ ] Setup database MySQL
- [ ] Test website live

### After Setup (Routine):
- [ ] Edit code di local
- [ ] Commit & push ke main
- [ ] Monitor GitHub Actions (optional)
- [ ] Verify changes di website

---

## 🎯 Common Use Cases

### Use Case 1: First Deployment
→ Follow [DEPLOY-VISUAL.md](DEPLOY-VISUAL.md) Part 1-6

### Use Case 2: Update Code
```bash
git add .
git commit -m "Update feature"
git push origin main
```
→ Auto deploy! (1-2 menit)

### Use Case 3: Deploy ke Staging
→ See [DEPLOY-CONFIG.md](DEPLOY-CONFIG.md) - Multiple environments

### Use Case 4: Troubleshooting Error
→ Check [DEPLOY-FAQ.md](DEPLOY-FAQ.md) - Common errors

### Use Case 5: Custom Configuration
→ Follow [DEPLOY-CONFIG.md](DEPLOY-CONFIG.md) - Customization

---

## 🔍 Quick Find

**Mencari...**

- **Setup GitHub Secrets?** → [DEPLOY-VISUAL.md](DEPLOY-VISUAL.md) Part 2
- **FTP credentials?** → [DEPLOY-VISUAL.md](DEPLOY-VISUAL.md) Part 1
- **Setup database?** → [DEPLOY-VISUAL.md](DEPLOY-VISUAL.md) Part 5
- **Error "Login incorrect"?** → [DEPLOY-FAQ.md](DEPLOY-FAQ.md)
- **Customisasi workflow?** → [DEPLOY-CONFIG.md](DEPLOY-CONFIG.md)
- **Template .htaccess?** → [DEPLOY-CONFIG.md](DEPLOY-CONFIG.md)
- **Multiple branches?** → [DEPLOY-CONFIG.md](DEPLOY-CONFIG.md)
- **Rollback deployment?** → [DEPLOY-FAQ.md](DEPLOY-FAQ.md)

---

## 💡 Tips

- ✅ Selalu backup sebelum deployment
- ✅ Test di local sebelum push
- ✅ Monitor GitHub Actions tab
- ✅ Gunakan descriptive commit messages
- ✅ Keep FTP credentials secure
- ✅ Setup SSL/HTTPS di cPanel

---

## 🆘 Need Help?

1. **Cek dokumentasi** sesuai kategori di atas
2. **Cari di FAQ** ([DEPLOY-FAQ.md](DEPLOY-FAQ.md))
3. **Lihat GitHub Actions logs** untuk error details
4. **Contact hosting support** untuk masalah cPanel/FTP
5. **Post GitHub Issue** jika butuh bantuan

---

## 🎉 Ready to Deploy?

**Belum setup?**  
→ Mulai dengan [DEPLOY-VISUAL.md](DEPLOY-VISUAL.md)

**Sudah setup?**  
→ Just `git push origin main` dan enjoy! 🚀

---

**Last Updated**: December 2025  
**Version**: 1.0  
**Status**: Production Ready ✅
