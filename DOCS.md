# 📚 Dokumentasi MyBBA - Sistem Informasi Sekolah

## 🎯 Overview
Aplikasi web manajemen sekolah untuk data siswa, keuangan, pembayaran, dan informasi.

## 🏗️ Arsitektur
- **Backend:** Pure PHP 8.2+ (no framework)
- **Database:** MySQL (dbsekolah)
- **Frontend:** Bootstrap 5 + Vanilla JS
- **Server:** Laragon/Apache

## 📁 Struktur Folder
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

## 🗄️ Database Schema

### Tabel `users`
- `user_id` (PK), `username`, `password`, `role` (admin/siswa)

### Tabel `siswa`
- `nis` (PK), `user_id` (FK)
- Data pribadi: nama, TTL, jenis_kelamin, alamat, kontak
- Data sekolah: kelas, jurusan, foto
- Data ortu: nama_ortu, no_hp_ortu

### Tabel `keuangan`
- `keuangan_id` (PK), `nis` (FK)
- `tahun_ajaran`, `total_tagihan`, `total_bayar`

### Tabel `pembayaran`
- `pembayaran_id` (PK), `keuangan_id` (FK)
- `bulan_untuk`, `tanggal_bayar`, `nominal_bayar`
- `metode`, `tempat_bayar`, `bukti_bayar`, `status`

### Tabel `informasi`
- `informasi_id` (PK)
- `judul`, `isi`, `foto`, `created_at`, `created_by`

## 🚀 Setup

### 1. Database
```sql
-- Import database
mysql -u root dbsekolah < database/backups/dbsekolah.sql
```

### 2. Konfigurasi
Edit `public/config.php`:
```php
$host = 'localhost';
$dbname = 'dbsekolah';
$username = 'root';
$password = '';
```

### 3. Jalankan Server
```bash
# Via Laragon
http://localhost/mybba

# Via PHP Built-in
php -S localhost:8000 -t public
```

## 🔐 Login Credentials

**Admin:**
- Username: `admin`
- Password: `admin123`

**Siswa:**
- Username: `siswa001` / Password: `siswa123`

## 🎨 Fitur

### Admin
- ✅ Dashboard dengan statistik real-time
- ✅ CRUD siswa dengan foto profil
- ✅ CRUD keuangan & tagihan
- ✅ Verifikasi pembayaran (approve/reject)
- ✅ CRUD informasi/pengumuman
- ✅ Export laporan keuangan

### Siswa
- ✅ Dashboard pribadi
- ✅ Lihat tagihan & riwayat pembayaran
- ✅ Upload bukti transfer
- ✅ Lihat informasi sekolah
- ✅ Update profil

## 🔧 Development

### Struktur Shared Components
```php
// Include auth check
include __DIR__ . '/../../shared/middleware/auth_check.php';

// Include helpers
include __DIR__ . '/../../shared/helpers/functions.php';

// Use layout template
$pageTitle = 'Dashboard';
$sidebarType = 'admin'; // or 'student'
$contentFile = __DIR__ . '/content.php';
include __DIR__ . '/../../shared/layouts/main.php';
```

### Helper Functions
```php
sanitize($data)              // Sanitize input
formatRupiah($amount)        // Format to IDR
formatTanggal($date)         // Format to Indonesian date
isLoggedIn()                 // Check login status
hasRole($role)               // Check user role
redirect($url)               // Redirect helper
setFlash($type, $message)    // Set flash message
getFlash()                   // Get flash message
uploadFile($file, $dir)      // Upload file helper
```

## 🐛 Troubleshooting

**Session error:**
```php
// Clear session
session_destroy();
```

**Database connection error:**
```php
// Check config.php credentials
// Restart MySQL service
```

**Upload error:**
```bash
# Check folder permissions
chmod 755 public/uploads
```

## 📝 Changelog

**v2.0 (Current)**
- ✅ Refactored to modular structure
- ✅ Created shared components
- ✅ Eliminated code duplication
- ✅ Added helper functions
- ✅ Improved maintainability

**v1.0**
- Initial release with basic CRUD

---

**Developer:** MyBBA Team  
**Last Updated:** November 2025
