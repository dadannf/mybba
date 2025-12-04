# 🏫 MyBBA - Sistem Informasi Manajemen Sekolah

Aplikasi web untuk manajemen data siswa, keuangan, pembayaran, dan informasi sekolah.

## ⚡ Quick Start

```bash
# 1. Import database
mysql -u root dbsekolah < database/backups/dbsekolah.sql

# 2. Jalankan server
php -S localhost:8000 -t public
# atau via Laragon: http://localhost/mybba

# 3. Login
Admin: admin / admin123
Siswa: siswa001 / siswa123
```

## 🎯 Fitur

### 👨‍💼 Admin
- Dashboard dengan statistik real-time
- CRUD siswa dengan foto profil
- CRUD keuangan & tagihan per tahun ajaran
- Verifikasi pembayaran (approve/reject)
- CRUD informasi/pengumuman
- Export laporan keuangan

### 👨‍🎓 Siswa
- Dashboard pribadi
- Lihat tagihan & riwayat pembayaran
- Upload bukti transfer
- Lihat informasi sekolah
- Update profil

## 🏗️ Tech Stack

- **Backend:** Pure PHP 8.2+ (no framework)
- **Database:** MySQL (dbsekolah)
- **Frontend:** Bootstrap 5 + Vanilla JS
- **Server:** Laragon/Apache

## 📁 Struktur

```
public/
├── admin/              → Portal admin
│   ├── finance/        → Manajemen keuangan
│   ├── students/       → Manajemen siswa
│   └── information/    → Manajemen informasi
├── student/            → Portal siswa
├── auth/               → Login/register
├── api/                → AJAX endpoints
├── shared/             → Komponen reusable
│   ├── components/     → Sidebar, navbar, modals
│   ├── helpers/        → Helper functions
│   ├── middleware/     → Auth check
│   └── layouts/        → Template layouts
├── css/                → Stylesheets
├── js/                 → JavaScript files
└── uploads/            → File uploads
```

## 📚 Dokumentasi

### Development
- **[SETUP.md](SETUP.md)** - Panduan instalasi lengkap
- **[DOCS.md](DOCS.md)** - Dokumentasi teknis & development
- **[API.md](API.md)** - API endpoints reference

### Deployment & Production
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - 🚀 Panduan deploy ke production dengan domain
- **[DOCKER_GUIDE.md](DOCKER_GUIDE.md)** - 🐳 Deploy dengan Docker
- **[SETUP_NGROK.md](SETUP_NGROK.md)** - 🌐 Testing dengan ngrok

## 🔐 Default Login

**Admin:**
- Username: `admin`
- Password: `admin123`

**Siswa:**
- Username: `siswa001`
- Password: `siswa123`

## 🗄️ Database

Database: **dbsekolah**

**Tabel:**
- `users` - Data login
- `siswa` - Data siswa
- `keuangan` - Tagihan per tahun ajaran
- `pembayaran` - History pembayaran
- `informasi` - Pengumuman/berita

## 🚀 Development

### Struktur Modular
```php
// Include auth check
include __DIR__ . '/../../shared/middleware/auth_check.php';

// Include helpers
include __DIR__ . '/../../shared/helpers/functions.php';

// Use layout template
$pageTitle = 'Dashboard';
$sidebarType = 'admin';
$contentFile = __DIR__ . '/content.php';
include __DIR__ . '/../../shared/layouts/main.php';
```

### Helper Functions
```php
sanitize($data)              // Sanitize input
formatRupiah($amount)        // Format to IDR
formatTanggal($date)         // Format date
isLoggedIn()                 // Check login
hasRole($role)               // Check role
setFlash($type, $message)    // Flash message
uploadFile($file, $dir)      // Upload helper
```

## 🎨 Features Highlight

✅ **Modular Structure** - Shared components, no duplication  
✅ **Real-time Validation** - AJAX duplicate check  
✅ **Responsive Design** - Mobile-friendly Bootstrap 5  
✅ **Flash Messages** - Session-based notifications  
✅ **File Upload** - Foto siswa & bukti pembayaran  
✅ **Role-based Access** - Admin & siswa separation  
✅ **Progress Tracking** - Visual payment progress  

## 🔧 Requirements

- PHP >= 8.2
- MySQL >= 8.0
- Apache/Nginx (atau PHP built-in server)
- Browser modern (Chrome, Firefox, Edge)

## 🚀 Deployment

Ingin membuat aplikasi ini bisa diakses orang lain dengan domain sendiri?

### Quick Options:

**1. Testing/Demo (Gratis - 5 menit)** ⚡
```bash
# Lihat panduan: SETUP_NGROK.md
ngrok http 80
```

**2. Production dengan Docker (Mudah - 10 menit)** 🐳
```bash
# Lihat panduan lengkap: DOCKER_GUIDE.md
docker-compose up -d
```

**3. Deploy ke VPS dengan Domain Custom** 🌐
```bash
# Lihat panduan lengkap: DEPLOYMENT.md
sudo bash deploy.sh
```

**📖 Dokumentasi Lengkap:**
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Panduan lengkap deployment production
- **[DOCKER_GUIDE.md](DOCKER_GUIDE.md)** - Deploy dengan Docker
- **[SETUP_NGROK.md](SETUP_NGROK.md)** - Quick testing dengan ngrok

## 📝 Changelog

**v2.0 (Current)**
- ✅ Refactored to modular structure
- ✅ Created shared components
- ✅ Eliminated code duplication (~90%)
- ✅ Added helper functions
- ✅ Improved maintainability

**v1.0**
- Initial release

---

**Developer:** MyBBA Team  
**Version:** 2.0  
**Last Updated:** November 2025

<p align="center">Built with ❤️ using Pure PHP</p>
