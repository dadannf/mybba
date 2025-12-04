# 🔧 Troubleshooting Guide - MyBBA

Panduan mengatasi masalah umum saat deployment dan penggunaan MyBBA.

## 📋 Daftar Isi

1. [Database Issues](#database-issues)
2. [Web Server Issues](#web-server-issues)
3. [PHP Issues](#php-issues)
4. [Permission Issues](#permission-issues)
5. [Docker Issues](#docker-issues)
6. [SSL/HTTPS Issues](#sslhttps-issues)
7. [OCR System Issues](#ocr-system-issues)
8. [Performance Issues](#performance-issues)

---

## 🗄️ Database Issues

### Error: "Connection refused" atau "Can't connect to MySQL"

**Penyebab:** MySQL service tidak running atau config salah

**Solusi:**
```bash
# Check MySQL status
sudo systemctl status mysql

# Start MySQL jika tidak running
sudo systemctl start mysql

# Check config di public/config.php
cat public/config.php | grep DB_

# Test connection manual
mysql -u root -p -h localhost
```

### Error: "Access denied for user"

**Penyebab:** Username atau password salah

**Solusi:**
```bash
# Reset password MySQL user
mysql -u root -p
mysql> ALTER USER 'mybba_user'@'localhost' IDENTIFIED BY 'new_password';
mysql> FLUSH PRIVILEGES;

# Update config.php dengan password baru
nano public/config.php
```

### Error: "Database doesn't exist"

**Penyebab:** Database belum dibuat atau tidak ter-import

**Solusi:**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE dbsekolah CHARACTER SET utf8mb4"

# Import SQL
mysql -u root -p dbsekolah < database/backups/dbsekolah.sql

# Verify
mysql -u root -p -e "USE dbsekolah; SHOW TABLES;"
```

### Error: "Table doesn't exist"

**Penyebab:** Import database tidak lengkap

**Solusi:**
```bash
# Re-import database
mysql -u root -p dbsekolah < database/backups/dbsekolah.sql

# Check tables
mysql -u root -p dbsekolah -e "SHOW TABLES"
```

---

## 🌐 Web Server Issues

### Nginx: "502 Bad Gateway"

**Penyebab:** PHP-FPM tidak running

**Solusi:**
```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Start PHP-FPM
sudo systemctl start php8.2-fpm

# Check logs
sudo tail -f /var/log/nginx/mybba_error.log
sudo tail -f /var/log/php8.2-fpm.log
```

### Nginx: "404 Not Found"

**Penyebab:** Document root salah atau file tidak ada

**Solusi:**
```bash
# Check nginx config
sudo nginx -t

# Check document root
ls -la /var/www/mybba/public

# Check nginx site config
cat /etc/nginx/sites-available/mybba

# Restart nginx
sudo systemctl restart nginx
```

### Apache: "Internal Server Error"

**Penyebab:** .htaccess error atau PHP error

**Solusi:**
```bash
# Check Apache error log
sudo tail -f /var/log/apache2/error.log

# Check PHP error log
sudo tail -f /var/log/php8.2-fpm.log

# Test PHP
php -v
php -m  # Check loaded modules
```

### Website tidak bisa diakses dari luar

**Penyebab:** Firewall blocking atau domain not pointing

**Solusi:**
```bash
# Check firewall
sudo ufw status

# Allow HTTP & HTTPS
sudo ufw allow 'Nginx Full'
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Check if service listening
netstat -tulpn | grep :80

# Test dari luar
curl http://YOUR_SERVER_IP
```

---

## 🐘 PHP Issues

### Error: "Call to undefined function"

**Penyebab:** PHP extension tidak terinstall

**Solusi:**
```bash
# Check installed extensions
php -m

# Install missing extensions
sudo apt install php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-gd

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Error: "Maximum execution time exceeded"

**Penyebab:** Script timeout

**Solusi:**
```bash
# Edit PHP config
sudo nano /etc/php/8.2/fpm/php.ini

# Update values:
max_execution_time = 300
max_input_time = 300

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Error: "Memory limit exceeded"

**Penyebab:** PHP memory tidak cukup

**Solusi:**
```bash
# Edit PHP config
sudo nano /etc/php/8.2/fpm/php.ini

# Update:
memory_limit = 512M

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Upload file error

**Penyebab:** Upload size limit

**Solusi:**
```bash
# Edit PHP config
sudo nano /etc/php/8.2/fpm/php.ini

# Update:
upload_max_filesize = 10M
post_max_size = 10M

# Edit Nginx config
sudo nano /etc/nginx/sites-available/mybba

# Add:
client_max_body_size 10M;

# Restart services
sudo systemctl restart php8.2-fpm nginx
```

---

## 🔐 Permission Issues

### Error: "Permission denied" saat upload

**Penyebab:** Folder tidak writable

**Solusi:**
```bash
# Set correct permissions
sudo chown -R www-data:www-data /var/www/mybba
sudo chmod -R 755 /var/www/mybba
sudo chmod -R 775 /var/www/mybba/public/uploads

# Check permissions
ls -la /var/www/mybba/public/uploads
```

### Error: Session error atau "Cannot write session"

**Penyebab:** Session directory tidak writable

**Solusi:**
```bash
# Check session path
php -i | grep session.save_path

# Set permissions (biasanya /var/lib/php/sessions)
sudo chmod 1733 /var/lib/php/sessions
sudo chown root:root /var/lib/php/sessions
```

### Error: "Failed to write to log"

**Penyebab:** Log directory tidak writable

**Solusi:**
```bash
# Create and set permissions
sudo mkdir -p /var/log/mybba
sudo chown -R www-data:www-data /var/log/mybba
sudo chmod -R 755 /var/log/mybba
```

---

## 🐳 Docker Issues

### Container tidak start

**Penyebab:** Port conflict atau config error

**Solusi:**
```bash
# Check logs
docker-compose logs

# Check if port in use
netstat -tulpn | grep :8080

# Change port in docker-compose.yml
# Rebuild
docker-compose down
docker-compose up -d
```

### Database connection error in Docker

**Penyebab:** Container networking issue

**Solusi:**
```bash
# Check network
docker network ls
docker network inspect mybba_mybba-network

# Check if MySQL container running
docker-compose ps

# Check MySQL logs
docker-compose logs mysql

# Recreate containers
docker-compose down -v
docker-compose up -d
```

### "Permission denied" in Docker

**Penyebab:** Volume permission issue

**Solusi:**
```bash
# Fix permissions
docker-compose exec web chown -R www-data:www-data /var/www/mybba
docker-compose exec web chmod -R 775 /var/www/mybba/public/uploads
```

### Docker out of disk space

**Penyebab:** Docker images/volumes menggunakan banyak space

**Solusi:**
```bash
# Check disk usage
docker system df

# Clean up
docker system prune -a
docker volume prune

# Remove unused images
docker image prune -a
```

---

## 🔒 SSL/HTTPS Issues

### Certbot error: "Challenge failed"

**Penyebab:** Domain tidak pointing ke server

**Solusi:**
```bash
# Check DNS
nslookup yourdomain.com
dig yourdomain.com

# Verify domain pointing to server IP
# Wait 15-30 minutes for DNS propagation

# Check port 80 accessible
curl -I http://yourdomain.com
```

### SSL certificate expired

**Penyebab:** Auto-renewal gagal

**Solusi:**
```bash
# Manual renew
sudo certbot renew

# Check renewal timer
sudo systemctl status certbot.timer

# Enable auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer

# Test renewal
sudo certbot renew --dry-run
```

### Mixed content error (HTTP/HTTPS)

**Penyebab:** Assets loading via HTTP di HTTPS page

**Solusi:**
```bash
# Check .htaccess or nginx config
# Add header:
add_header Content-Security-Policy "upgrade-insecure-requests";

# Or force HTTPS redirect
# Nginx:
return 301 https://$server_name$request_uri;
```

---

## 🤖 OCR System Issues

### OCR service not accessible

**Penyebab:** Service tidak running atau firewall blocking

**Solusi:**
```bash
# Check if OCR running
curl http://localhost:8000/health

# Start OCR service
cd /var/www/mybba/ocr_system
python3 main.py

# Or with systemd
sudo systemctl start mybba-ocr
sudo systemctl status mybba-ocr

# Check firewall
sudo ufw allow 8000/tcp
```

### CORS error

**Penyebab:** CORS policy blocking

**Solusi:**
```python
# Edit ocr_system/main.py
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Or specific domain
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Restart service
sudo systemctl restart mybba-ocr
```

### OCR accuracy poor

**Penyebab:** Image quality rendah atau model needs tuning

**Solusi:**
```bash
# Improve image quality before upload
# Check image resolution (min 1000px width recommended)

# Update OCR confidence threshold
# Edit ocr_system config
```

---

## ⚡ Performance Issues

### Website lambat

**Penyebab:** Resource tidak cukup atau query inefficient

**Solusi:**
```bash
# Check server resources
htop
df -h
free -m

# Optimize MySQL
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add:
innodb_buffer_pool_size = 512M
query_cache_size = 32M
query_cache_limit = 2M

sudo systemctl restart mysql

# Enable PHP OpCache
sudo nano /etc/php/8.2/fpm/php.ini

# Add:
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000

sudo systemctl restart php8.2-fpm
```

### High CPU usage

**Penyebab:** Too many processes atau inefficient code

**Solusi:**
```bash
# Check processes
top
ps aux --sort=-%cpu | head

# Limit PHP-FPM processes
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Update:
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10

sudo systemctl restart php8.2-fpm
```

### High memory usage

**Penyebab:** Memory leak atau cache terlalu besar

**Solusi:**
```bash
# Check memory
free -m
ps aux --sort=-%mem | head

# Clear cache
sync; echo 3 > /proc/sys/vm/drop_caches

# Add swap if needed
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
```

### Database queries slow

**Penyebab:** Missing indexes atau query inefficient

**Solusi:**
```bash
# Enable slow query log
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add:
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2

# Check slow queries
sudo tail -f /var/log/mysql/slow-query.log

# Add indexes if needed
mysql -u root -p dbsekolah
mysql> SHOW INDEXES FROM tablename;
mysql> CREATE INDEX idx_column ON tablename(column);
```

---

## 🆘 Getting Help

### Check Logs

```bash
# Nginx logs
sudo tail -f /var/log/nginx/mybba_error.log
sudo tail -f /var/log/nginx/mybba_access.log

# PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# MySQL logs
sudo tail -f /var/log/mysql/error.log

# System logs
sudo journalctl -u nginx -f
sudo journalctl -u php8.2-fpm -f
sudo journalctl -u mysql -f
```

### Diagnostic Commands

```bash
# System info
uname -a
lsb_release -a

# Check services
sudo systemctl status nginx php8.2-fpm mysql

# Check listening ports
netstat -tulpn

# Check disk space
df -h

# Check memory
free -m

# Check PHP info
php -i

# Check MySQL status
mysql -u root -p -e "SHOW VARIABLES LIKE '%version%'"
```

### Community Support

- 📖 Documentation: [README.md](README.md)
- 🐛 Report Issues: [GitHub Issues](https://github.com/dadannf/mybba/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/dadannf/mybba/discussions)
- 📧 Email: support@mybba.com (if available)

---

**💡 Tip:** Selalu backup data sebelum melakukan perubahan besar!

*Last Updated: December 2025*
