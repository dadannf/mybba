# MyBBA - Railway Deployment Guide

## 🚀 Quick Deploy ke Railway

### Prerequisites:
- Akun Railway (gratis: https://railway.app)
- Repository Git yang sudah push ke GitHub/GitLab

### Langkah Deployment:

#### 1️⃣ **Setup Railway Project**
```bash
# Login ke Railway
railway login

# Link project
railway link

# Atau buat project baru
railway init
```

#### 2️⃣ **Set Environment Variables**
Di Railway Dashboard → Variables, tambahkan:

```env
# Database (gunakan Railway MySQL)
DB_HOST=containers-us-west-xxx.railway.app
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=xxxxx

# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxxx
APP_URL=https://your-app.railway.app

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

#### 3️⃣ **Add MySQL Database**
- Di Railway Dashboard → New → Database → MySQL
- Copy credentials ke Environment Variables (step 2)

#### 4️⃣ **Deploy**
```bash
# Deploy via CLI
railway up

# Atau push ke GitHub (auto deploy)
git push origin main
```

#### 5️⃣ **Import Database**
```bash
# Connect ke Railway MySQL
railway run mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < database/backups/dbsekolah.sql

# Atau via Railway CLI
railway run bash
mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < database/backups/dbsekolah.sql
```

---

## 📋 File Konfigurasi

### **Dockerfile** ✅
- Install dependencies via Composer di container
- TIDAK copy folder `vendor/` (akan di-build di Railway)
- Optimized untuk production

### **railway.toml** ✅
- Konfigurasi build & deploy
- Dockerfile path
- Start command: `apache2-foreground`

### **.dockerignore** ✅
- Exclude file yang tidak perlu di-copy
- `vendor/` di-ignore (akan di-install via composer)

---

## 🔧 Troubleshooting

### **Error: "/vendor": not found**
✅ **SOLVED** - Dockerfile sekarang install dependencies via `composer install`

### **Error: Database connection failed**
- Pastikan Environment Variables DB_* sudah benar
- Cek Railway MySQL service sudah running
- Test koneksi: `railway run php public/test_db.php`

### **Error: Permission denied on uploads/**
- Railway filesystem read-only, gunakan S3/CloudFlare R2 untuk uploads
- Atau mount persistent volume di Railway

### **Port Error**
Railway auto-detect port 80 dari Dockerfile EXPOSE. Tidak perlu ubah.

---

## 📊 Monitoring

### **View Logs:**
```bash
# Via CLI
railway logs

# Atau di Railway Dashboard → Deployments → View Logs
```

### **Check Build:**
```bash
# Build logs
railway logs --build

# Deployment logs
railway logs --deployment
```

---

## 🎯 Production Checklist

- [ ] Environment variables set di Railway
- [ ] MySQL database created & connected
- [ ] Database imported (dbsekolah.sql)
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Custom domain (optional)
- [ ] SSL/HTTPS enabled (auto by Railway)

---

## 💡 Tips

1. **Free Tier Limits:**
   - $5 credit/month
   - 500 hours/month execution
   - Cukup untuk development/testing

2. **Custom Domain:**
   - Railway Dashboard → Settings → Domain
   - Add CNAME record ke DNS

3. **Auto Deploy:**
   - Setiap `git push` ke branch main = auto deploy
   - Bisa disable di Settings jika mau manual

4. **Database Backup:**
   ```bash
   # Export dari Railway MySQL
   railway run mysqldump -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > backup.sql
   ```

---

## 🔗 Resources

- Railway Docs: https://docs.railway.app
- Railway Templates: https://railway.app/templates
- Support: https://help.railway.app

---

**Status:** ✅ Ready to Deploy
**Last Updated:** December 15, 2025
