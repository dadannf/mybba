# ⚡ Quick Reference - MyBBA v2.0

## 🚀 Instant Commands

```bash
# Start server
php -S localhost:8000 -t public

# Import database
mysql -u root dbsekolah < database/backups/dbsekolah.sql

# Access
http://localhost:8000
```

## 🔐 Login

```
Admin: admin / admin123
Siswa: siswa001 / siswa123
```

---

## 📁 File Locations

```
Config:          public/config.php
Auth Check:      public/shared/middleware/auth_check.php
Helpers:         public/shared/helpers/functions.php
Admin Sidebar:   public/shared/components/sidebar.php
Student Sidebar: public/shared/components/student_sidebar.php
Navbar:          public/shared/components/navbar.php
Layout:          public/shared/layouts/main.php
```

---

## 🔧 Common Code Snippets

### Include Config & Auth
```php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../shared/middleware/auth_check.php';
```

### Use Layout Template
```php
$pageTitle = 'Dashboard';
$sidebarType = 'admin'; // or 'student'
$contentFile = __DIR__ . '/content.php';
include __DIR__ . '/../../shared/layouts/main.php';
```

### Include Sidebar
```php
// Admin
include __DIR__ . '/../../shared/components/sidebar.php';

// Student
include __DIR__ . '/../../shared/components/student_sidebar.php';
```

### Flash Message
```php
setFlash('success', 'Berhasil!');
setFlash('danger', 'Error!');
redirect('/admin/students/index.php');
```

### Format Data
```php
formatRupiah(5000000);        // Rp 5.000.000
formatTanggal('2024-01-15');  // 15 Januari 2024
sanitize($_POST['input']);    // Clean input
```

### Check Auth
```php
if (isLoggedIn()) { }
if (hasRole('admin')) { }
```

### Upload File
```php
$result = uploadFile($_FILES['foto'], $targetDir, ['jpg', 'png']);
if ($result['success']) {
    $filename = $result['filename'];
}
```

---

## 🗄️ Database

```sql
-- Tables
users          → Login data
siswa          → Student data
keuangan       → Finance/billing
pembayaran     → Payment history
informasi      → Announcements

-- Common Queries
SELECT * FROM siswa WHERE nis = ?
SELECT * FROM keuangan WHERE nis = ?
SELECT * FROM pembayaran WHERE keuangan_id = ?
```

---

## 🎨 CSS Classes

```css
.sidebar              → Sidebar container
.topbar               → Top navbar
.main-content         → Main content area
.content-area         → Content wrapper
.has-sidebar          → Body with sidebar
.sidebar-collapsed    → Collapsed sidebar state
```

---

## 📡 API Endpoints

```
GET  /api/check_duplicate.php
GET  /api/get_keuangan_progress.php
GET  /api/list_pembayaran.php
POST /api/update_payment_status.php
POST /api/process_payment_student.php
GET  /api/notifikasi.php
```

---

## 🔍 Debugging

```php
// Enable errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug variable
echo '<pre>'; print_r($data); echo '</pre>';

// Check session
echo '<pre>'; print_r($_SESSION); echo '</pre>';

// Check database
if ($conn->error) {
    die('Error: ' . $conn->error);
}
```

---

## 📂 Folder Structure

```
public/
├── admin/
│   ├── finance/
│   ├── students/
│   └── information/
├── student/
├── auth/
├── api/
├── shared/
│   ├── components/
│   ├── helpers/
│   ├── middleware/
│   └── layouts/
├── css/
├── js/
└── uploads/
```

---

## 🛠️ Helper Functions

```php
sanitize($data)              // Clean input
formatRupiah($amount)        // Format currency
formatTanggal($date)         // Format date
isLoggedIn()                 // Check login
hasRole($role)               // Check role
redirect($url)               // Redirect
setFlash($type, $msg)        // Set flash
getFlash()                   // Get flash
uploadFile($file, $dir)      // Upload
generateRandomString($len)   // Random string
```

---

## 📖 Documentation

- **SETUP.md** → Installation guide
- **DOCS.md** → Technical docs
- **API.md** → API reference
- **EXAMPLES.md** → Code examples
- **CHANGELOG.md** → Version history

---

## 🐛 Common Issues

**Session error:**
```php
session_destroy();
// Clear browser cookies
```

**Database error:**
```php
// Check config.php
// Restart MySQL
```

**Upload error:**
```bash
chmod 755 public/uploads
```

**404 error:**
```
Check file path
Check .htaccess
```

---

## ⚡ Performance Tips

1. Use prepared statements
2. Cache database queries
3. Optimize images before upload
4. Use CDN for Bootstrap/jQuery
5. Enable gzip compression
6. Minimize database queries

---

## 🔒 Security Checklist

- [x] Use prepared statements
- [x] Sanitize all inputs
- [x] Validate file uploads
- [x] Check authentication
- [x] Use HTTPS (production)
- [x] Secure session config
- [x] Prevent SQL injection
- [x] Prevent XSS attacks

---

**Version:** 2.0.0  
**Last Updated:** November 26, 2025

---

<p align="center">
<strong>Need more details?</strong><br>
Check DOCS.md for full documentation
</p>
