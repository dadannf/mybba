# 📝 Template Konfigurasi - Auto Deploy cPanel

## Template GitHub Secrets

### FTP_SERVER
```
ftp.namadomain.com
```
atau
```
123.456.789.10
```

### FTP_USERNAME
```
username_cpanel_anda
```
atau
```
user@namadomain.com
```

### FTP_PASSWORD
```
password_ftp_anda_yang_kuat
```

---

## Template Database Configuration

### File: `/config/database.php`

```php
<?php
// Database Configuration for cPanel Hosting

define('DB_HOST', 'localhost');
define('DB_NAME', 'cpanelusername_dbname');  // Format: username_databasename
define('DB_USER', 'cpanelusername_dbuser');  // Format: username_dbusername
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Create connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection error. Please contact administrator.");
}
?>
```

---

## Contoh Server Directory

### Default (Website utama)
```yaml
server-dir: /public_html/
```

### Subdomain
```yaml
server-dir: /public_html/subdomain/
```

### Addon Domain
```yaml
server-dir: /home/username/namadomain.com/
```

---

## Customisasi Workflow

### Deploy ke Branch Lain (Selain main)

Edit `.github/workflows/cpanel-deploy.yml`:

```yaml
on:
  push:
    branches:
      - production  # Ganti 'main' dengan nama branch Anda
```

### Deploy Multiple Branches

```yaml
on:
  push:
    branches:
      - main
      - staging
      - production
```

### Exclude File Tambahan

Tambahkan di bagian `exclude:`:

```yaml
exclude: |
  **/.git*
  **/.git*/**
  **/node_modules/**
  **/vendor/**
  **/.env
  **/tests/**           # Exclude folder tests
  **/temp/**            # Exclude folder temp
  **/*.md               # Exclude semua file markdown
  **/.vscode/**         # Exclude VSCode config
  **/.idea/**           # Exclude PHPStorm config
```

### Deploy Hanya Folder Tertentu

Jika ingin deploy hanya folder `public/`:

```yaml
- name: 🚀 FTP Deploy to cPanel
  uses: SamKirkland/FTP-Deploy-Action@v4.3.4
  with:
    server: ${{ secrets.FTP_SERVER }}
    username: ${{ secrets.FTP_USERNAME }}
    password: ${{ secrets.FTP_PASSWORD }}
    local-dir: ./public/      # Deploy hanya folder public
    server-dir: /public_html/
```

---

## Contoh .htaccess untuk cPanel

File ini biasanya sudah ada, tapi jika perlu:

### File: `/public_html/.htaccess`

```apache
# Apache Configuration
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect to HTTPS (Opsional)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Route semua request ke index.php kecuali file/folder yang ada
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>

# PHP Settings
<IfModule mod_php8.c>
    php_value upload_max_filesize 20M
    php_value post_max_size 25M
    php_value max_execution_time 300
    php_value memory_limit 256M
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Prevent Directory Listing
Options -Indexes

# Protect sensitive files
<FilesMatch "(^#.*#|\.(bak|config|dist|fla|inc|ini|log|psd|sh|sql|sw[op])|~)$">
    Require all denied
</FilesMatch>
```

---

## Troubleshooting Commands

### Cek FTP Connection dari Terminal

```bash
# Test FTP connection
ftp ftp.namadomain.com

# Login dengan username dan password
# Jika berhasil connect, FTP server OK
```

### Cek File Permission di cPanel

```bash
# Via SSH (jika ada akses)
ls -la /public_html/
chmod 755 /public_html/
chown username:username /public_html/
```

### Clear Cache Browser

```bash
# Chrome/Firefox
Ctrl + Shift + R  (Windows/Linux)
Cmd + Shift + R   (Mac)

# Hard Refresh
Ctrl + F5  (Windows/Linux)
```

---

## Monitoring & Logs

### GitHub Actions Logs

1. Buka repository di GitHub
2. Tab **Actions**
3. Klik workflow yang sedang/sudah berjalan
4. Expand setiap step untuk lihat detail log

### cPanel Error Logs

1. Login ke cPanel
2. Buka **"Errors"** atau **"Error Log"**
3. Lihat error terbaru dari website

### PHP Error Logs

Edit `php.ini` atau `.htaccess`:

```ini
# Enable error reporting (development only!)
php_flag display_errors on
php_value error_reporting E_ALL

# Log errors to file
php_flag log_errors on
php_value error_log /home/username/public_html/php_errors.log
```

**⚠️ PENTING**: Matikan `display_errors` di production!

---

## Checklist Post-Deployment

Setelah deployment berhasil:

- [ ] ✅ Akses website di browser
- [ ] ✅ Test halaman login
- [ ] ✅ Test fitur upload file
- [ ] ✅ Test koneksi database
- [ ] ✅ Cek error logs (tidak ada error)
- [ ] ✅ Test responsive (mobile view)
- [ ] ✅ Test di browser berbeda (Chrome, Firefox, Safari)
- [ ] ✅ Verifikasi file permissions (folders: 755, files: 644)
- [ ] ✅ Setup SSL certificate (HTTPS)
- [ ] ✅ Configure email (jika ada fitur email)
- [ ] ✅ Backup database secara rutin

---

## Best Practices

### Security

1. **Jangan commit file `.env`** dengan credentials
2. **Gunakan strong password** untuk FTP dan database
3. **Enable HTTPS/SSL** di cPanel
4. **Update PHP version** ke versi terbaru yang didukung
5. **Regular backup** database dan files
6. **Disable directory listing** via .htaccess
7. **Protect admin pages** dengan IP whitelist atau password

### Performance

1. **Enable caching** di cPanel (PHP OPcache)
2. **Optimize images** sebelum upload
3. **Minify CSS/JS** files
4. **Use CDN** untuk static assets (opsional)
5. **Database optimization** secara berkala

### Maintenance

1. **Monitor disk usage** di cPanel
2. **Check error logs** secara rutin
3. **Update dependencies** (jika ada)
4. **Test before deploy** di local environment
5. **Keep backup** before major changes

---

📖 **Kembali ke**: [DEPLOY.md](DEPLOY.md) | [DEPLOY-QUICK.md](DEPLOY-QUICK.md)
