# 🚀 Panduan Deployment MyBBA

Panduan lengkap untuk melaunching aplikasi MyBBA agar dapat diakses oleh orang lain melalui internet dengan domain sendiri.

## 📋 Daftar Isi

1. [Pilihan Deployment](#pilihan-deployment)
2. [Persiapan Sebelum Deploy](#persiapan-sebelum-deploy)
3. [Deploy ke VPS/Cloud](#deploy-ke-vpscloud)
4. [Deploy dengan Docker](#deploy-dengan-docker)
5. [Konfigurasi Domain](#konfigurasi-domain)
6. [Setup SSL/HTTPS](#setup-sslhttps)
7. [Maintenance & Monitoring](#maintenance--monitoring)

---

## 🎯 Pilihan Deployment

### 1. **Ngrok (Testing/Demo - GRATIS)** ⚡
**Ideal untuk:** Testing cepat, demo ke klien, development  
**Kelebihan:** Setup cepat (5 menit), tidak perlu VPS  
**Kekurangan:** URL berubah setiap restart, tidak untuk production

👉 **Panduan:** Lihat [SETUP_NGROK.md](SETUP_NGROK.md)

### 2. **VPS/Cloud Server (Production - BERBAYAR)** 🏢
**Ideal untuk:** Production, custom domain, kontrol penuh  
**Provider:** DigitalOcean, AWS, Alibaba Cloud, Niagahoster, IDCloudHost  
**Biaya:** Mulai dari $5/bulan (±75.000 IDR)

👉 **Panduan:** Lihat bagian [Deploy ke VPS](#deploy-ke-vpscloud) di bawah

### 3. **Shared Hosting (Mudah - BERBAYAR)** 🏠
**Ideal untuk:** Pemula, budget terbatas  
**Provider:** Hostinger, Niagahoster, Rumahweb  
**Biaya:** Mulai dari 10.000 IDR/bulan

👉 **Panduan:** Lihat bagian [Shared Hosting](#deploy-ke-shared-hosting)

### 4. **Docker (Modern - GRATIS/BERBAYAR)** 🐳
**Ideal untuk:** Deployment konsisten, scalable  
**Platform:** Railway.app, Render.com, Fly.io, DigitalOcean App Platform

👉 **Panduan:** Lihat bagian [Deploy dengan Docker](#deploy-dengan-docker)

---

## 🔧 Persiapan Sebelum Deploy

### ✅ Checklist Persiapan

- [ ] **Database backup** sudah ada
- [ ] **Test local** berjalan lancar
- [ ] **Domain sudah dibeli** (opsional, bisa pakai IP)
- [ ] **VPS/Server sudah ready** (jika menggunakan VPS)
- [ ] **Ubah password default** admin dan database
- [ ] **Hapus data test** jika tidak diperlukan

### 🔐 Security Checklist

```php
// ⚠️ WAJIB DIUBAH SEBELUM PRODUCTION!

// 1. Ubah password admin di database
UPDATE users SET password = PASSWORD('password_baru_yang_kuat') WHERE username = 'admin';

// 2. Ubah password database di config.php
define('DB_PASS', 'password_database_yang_kuat');

// 3. Hapus atau nonaktifkan akun test
DELETE FROM users WHERE username LIKE 'siswa%' AND user_id > 1;
```

---

## 🖥️ Deploy ke VPS/Cloud

### Metode 1: Manual Setup (Recommended untuk Belajar)

#### Step 1: Sewa & Setup VPS

**Pilih Provider VPS:**
- **DigitalOcean** - $5/bulan, mudah untuk pemula
- **Vultr** - $2.50/bulan, lebih murah
- **IDCloudHost** - Indonesia, support Bahasa
- **Niagahoster** - VPS Indonesia

**Spesifikasi Minimum:**
- CPU: 1 Core
- RAM: 1GB
- Storage: 25GB SSD
- OS: Ubuntu 22.04 LTS

#### Step 2: Koneksi ke VPS

```bash
# Dari komputer lokal
ssh root@IP_VPS_ANDA

# Atau menggunakan PuTTY di Windows
```

#### Step 3: Install Dependencies

```bash
# Update sistem
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Install PHP 8.2 & Extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-gd -y

# Install MySQL
sudo apt install mysql-server -y

# Install Python untuk OCR (opsional)
sudo apt install python3 python3-pip -y

# Install Certbot untuk SSL
sudo apt install certbot python3-certbot-nginx -y
```

#### Step 4: Setup MySQL Database

```bash
# Amankan MySQL
sudo mysql_secure_installation

# Login ke MySQL
sudo mysql

# Buat database dan user
CREATE DATABASE dbsekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mybba_user'@'localhost' IDENTIFIED BY 'password_yang_kuat_123';
GRANT ALL PRIVILEGES ON dbsekolah.* TO 'mybba_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import database
mysql -u mybba_user -p dbsekolah < /path/to/database/backups/dbsekolah.sql
```

#### Step 5: Upload Aplikasi ke VPS

**Cara 1: Via Git (Recommended)**
```bash
# Install Git
sudo apt install git -y

# Clone repository
cd /var/www
sudo git clone https://github.com/dadannf/mybba.git
sudo chown -R www-data:www-data mybba
sudo chmod -R 755 mybba
```

**Cara 2: Via FTP**
```bash
# Install FileZilla di komputer lokal
# Connect ke VPS dengan SFTP
# Upload folder project ke /var/www/mybba
```

#### Step 6: Konfigurasi Aplikasi

```bash
# Edit config database
sudo nano /var/www/mybba/public/config.php
```

Update configuration:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'mybba_user');
define('DB_PASS', 'password_yang_kuat_123');
define('DB_NAME', 'dbsekolah');
```

Set permissions:
```bash
sudo chown -R www-data:www-data /var/www/mybba
sudo chmod -R 755 /var/www/mybba
sudo chmod -R 775 /var/www/mybba/public/uploads
```

#### Step 7: Konfigurasi Nginx

```bash
# Buat konfigurasi site
sudo nano /etc/nginx/sites-available/mybba
```

Paste konfigurasi berikut:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    # Atau gunakan IP: server_name 123.45.67.89;
    
    root /var/www/mybba/public;
    index index.php index.html;
    
    # Logs
    access_log /var/log/nginx/mybba_access.log;
    error_log /var/log/nginx/mybba_error.log;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Upload size limit
    client_max_body_size 10M;
    
    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/mybba /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Step 8: Test Akses

```bash
# Buka browser, akses:
http://IP_VPS_ANDA
# atau
http://yourdomain.com
```

---

## 🐳 Deploy dengan Docker

Docker memudahkan deployment dan memastikan konsistensi environment.

### Step 1: Install Docker di VPS

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt install docker-compose -y

# Verifikasi
docker --version
docker-compose --version
```

### Step 2: Setup Project dengan Docker

File Docker sudah disediakan di repository. Cukup:

```bash
# Clone repository
git clone https://github.com/dadannf/mybba.git
cd mybba

# Copy environment file
cp .env.example .env

# Edit .env sesuai kebutuhan
nano .env

# Build dan jalankan
docker-compose up -d

# Import database
docker-compose exec mysql mysql -u root -p dbsekolah < /docker-entrypoint-initdb.d/dbsekolah.sql
```

### Step 3: Akses Aplikasi

```
http://IP_VPS:8080
```

### Docker Commands Reference

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Restart services
docker-compose restart

# Update aplikasi
git pull
docker-compose up -d --build
```

---

## 🏠 Deploy ke Shared Hosting

Shared hosting adalah cara termudah dan termurah untuk deploy.

### Provider Recommended Indonesia:
- **Niagahoster** - Support bagus, ada live chat
- **Hostinger** - Murah, performa baik
- **Rumahweb** - Server Indonesia

### Step-by-Step:

#### 1. Beli Hosting
- Pilih paket yang support PHP 8.2+ dan MySQL
- Paket termurah (10k-30k/bulan) biasanya cukup

#### 2. Setup Domain
- Beli domain atau gunakan subdomain gratis dari hosting
- Arahkan domain ke hosting (biasanya otomatis)

#### 3. Upload Files via cPanel

```bash
# Compress dulu di lokal
zip -r mybba.zip mybba/

# Upload via cPanel File Manager ke public_html
# Extract zip file
```

#### 4. Import Database
- Buka phpMyAdmin di cPanel
- Create database baru
- Import file `database/backups/dbsekolah.sql`

#### 5. Update Config
Edit `public_html/public/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username_dari_cpanel');
define('DB_PASS', 'password_dari_cpanel');
define('DB_NAME', 'namadb_dari_cpanel');
```

#### 6. Set File Permissions
Via cPanel File Manager:
- `public/uploads` → 755

#### 7. Akses Website
```
http://yourdomain.com
```

---

## 🌐 Konfigurasi Domain

### Beli Domain

**Provider Domain Indonesia:**
- **Niagahoster** - .com mulai 100k/tahun
- **Domainesia** - .id mulai 150k/tahun
- **Rumahweb** - Banyak promo

**Provider Internasional:**
- **Namecheap** - Murah, interface bagus
- **Cloudflare** - Registrar harga wholesale
- **Google Domains** - Reliable

### Pointing Domain ke VPS

#### Via DNS A Record:
```
Type: A
Name: @
Value: IP_VPS_ANDA
TTL: 3600

Type: A
Name: www
Value: IP_VPS_ANDA
TTL: 3600
```

#### Via Cloudflare (Recommended):
1. Daftar di cloudflare.com
2. Add site → masukkan domain
3. Update nameserver di registrar domain
4. Add DNS records:
   - Type: A, Name: @, Value: IP_VPS
   - Type: A, Name: www, Value: IP_VPS
5. Enable proxy (ikon awan orange)

**Benefit Cloudflare:**
- ✅ SSL gratis otomatis
- ✅ CDN untuk website lebih cepat
- ✅ DDoS protection
- ✅ Cache & optimization

---

## 🔒 Setup SSL/HTTPS

### Metode 1: Let's Encrypt (GRATIS - Recommended)

```bash
# Pastikan domain sudah pointing ke VPS
# Install Certbot (jika belum)
sudo apt install certbot python3-certbot-nginx -y

# Generate SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Ikuti wizard, pilih redirect HTTP ke HTTPS

# Test auto-renewal
sudo certbot renew --dry-run
```

Certificate akan otomatis renew setiap 90 hari.

### Metode 2: Cloudflare SSL (GRATIS)

Jika menggunakan Cloudflare:
1. Di dashboard Cloudflare → SSL/TLS
2. Pilih "Full" atau "Full (strict)"
3. Enable "Always Use HTTPS"
4. Enable "Automatic HTTPS Rewrites"

Done! SSL aktif otomatis.

### Verify SSL

```bash
# Test SSL
https://www.ssllabs.com/ssltest/analyze.html?d=yourdomain.com
```

---

## 📊 Maintenance & Monitoring

### Backup Rutin

#### Backup Database
```bash
# Manual backup
mysqldump -u mybba_user -p dbsekolah > backup_$(date +%Y%m%d).sql

# Auto backup harian (crontab)
0 2 * * * mysqldump -u mybba_user -p'password' dbsekolah > /backups/db_$(date +\%Y\%m\%d).sql
```

#### Backup Files
```bash
# Compress dan backup
tar -czf mybba_backup_$(date +%Y%m%d).tar.gz /var/www/mybba

# Upload ke cloud storage
# Gunakan rclone atau aws s3 sync
```

### Monitoring

#### Check Server Status
```bash
# Check disk space
df -h

# Check memory
free -m

# Check CPU
top

# Check Nginx
sudo systemctl status nginx

# Check PHP-FPM
sudo systemctl status php8.2-fpm

# Check MySQL
sudo systemctl status mysql
```

#### Setup Monitoring (Opsional)

**Uptime Robot** (Gratis)
- Monitoring uptime 24/7
- Email alert jika website down
- Dashboard monitoring

**Google Analytics**
- Track pengunjung
- Analisis behavior

### Update & Security

```bash
# Update sistem rutin
sudo apt update && sudo apt upgrade -y

# Update aplikasi
cd /var/www/mybba
git pull origin main
sudo systemctl restart nginx
```

---

## 🚨 Troubleshooting

### 1. Website tidak bisa diakses
```bash
# Check Nginx status
sudo systemctl status nginx
sudo systemctl restart nginx

# Check error logs
sudo tail -f /var/log/nginx/mybba_error.log
```

### 2. Database connection error
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u mybba_user -p -h localhost dbsekolah
```

### 3. Permission denied upload
```bash
sudo chmod -R 775 /var/www/mybba/public/uploads
sudo chown -R www-data:www-data /var/www/mybba/public/uploads
```

### 4. PHP version wrong
```bash
# Check PHP version
php -v

# Update PHP
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2
```

---

## 📱 Deploy ke Platform Cloud Modern

### Railway.app (Gratis untuk mulai)
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Deploy
railway init
railway up
```

### Render.com (Gratis dengan limitasi)
1. Connect GitHub repository
2. New Web Service
3. Auto-detect build & deploy
4. Add database (PostgreSQL/MySQL)

### Fly.io (Gratis $5 credit/bulan)
```bash
# Install flyctl
curl -L https://fly.io/install.sh | sh

# Deploy
fly launch
fly deploy
```

---

## 📞 Dukungan & Resources

### Dokumentasi Project
- [README.md](README.md) - Overview & quick start
- [SETUP.md](SETUP.md) - Setup development
- [SETUP_NGROK.md](SETUP_NGROK.md) - Testing dengan ngrok
- [DOCS.md](DOCS.md) - Dokumentasi teknis

### Learning Resources
- [DigitalOcean Tutorials](https://www.digitalocean.com/community/tutorials)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [PHP Manual](https://www.php.net/manual/en/)

### Community
- Buat issue di GitHub jika ada masalah
- Diskusi di forum Laravel Indonesia
- StackOverflow untuk troubleshooting

---

## 🎉 Summary Checklist Deployment

### Untuk Testing (Cepat)
- [ ] Gunakan Ngrok sesuai [SETUP_NGROK.md](SETUP_NGROK.md)
- [ ] Share link ngrok ke user lain
- [ ] Test akses dari device lain

### Untuk Production (Domain Custom)
- [ ] Beli domain
- [ ] Sewa VPS/Cloud
- [ ] Install Nginx + PHP + MySQL
- [ ] Upload aplikasi
- [ ] Import database
- [ ] Setup domain DNS
- [ ] Install SSL certificate
- [ ] Test akses https://yourdomain.com
- [ ] Setup backup otomatis
- [ ] Setup monitoring

---

**🚀 Selamat! Aplikasi MyBBA Anda sekarang online dan bisa diakses siapa saja!**

**Questions?** Buat issue di GitHub atau hubungi developer.

---

*Last Updated: December 2025*
*Version: 2.0*
