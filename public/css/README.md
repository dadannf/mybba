# DOKUMENTASI CSS - SISTEM INFORMASI BBA

## 📁 Struktur File CSS

```
public/css/
├── app.css                  # Master CSS dengan variabel & utilities
├── dashboard.css            # Layout dashboard (sidebar, topbar, content)
├── custom-components.css    # Komponen custom (dropdown, cards, badges)
├── print-styles.css         # Styling untuk print/cetak
├── admin-portal.css         # Styling khusus portal admin
├── siswa-portal.css         # Styling khusus portal siswa
└── auth-pages.css           # Styling halaman login & register
```

## 🎨 CSS Variables (Variabel Global)

File `app.css` menyediakan variabel CSS yang dapat digunakan di seluruh aplikasi:

### Colors
```css
--primary-blue: #0b63a8
--dark-blue: #064b7a
--purple-start: #667eea
--purple-end: #764ba2
--success-color: #28a745
--warning-color: #ffc107
--danger-color: #dc3545
--info-color: #0dcaf0
```

### Spacing
```css
--spacing-xs: 0.25rem   /* 4px */
--spacing-sm: 0.5rem    /* 8px */
--spacing-md: 1rem      /* 16px */
--spacing-lg: 1.5rem    /* 24px */
--spacing-xl: 2rem      /* 32px */
```

### Border Radius
```css
--radius-sm: 0.375rem
--radius-md: 0.5rem
--radius-lg: 0.75rem
--radius-xl: 1rem
```

## 📖 Cara Penggunaan

### 1. Halaman Admin (Portal Admin)

```php
<head>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS Custom -->
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/custom-components.css">
  <link rel="stylesheet" href="/css/admin-portal.css">
  <link rel="stylesheet" href="/css/print-styles.css"> <!-- Jika ada fitur print -->
</head>
```

### 2. Halaman Siswa (Portal Siswa)

```php
<head>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS Custom -->
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/custom-components.css">
  <link rel="stylesheet" href="/css/siswa-portal.css">
</head>
```

### 3. Halaman Login/Register

```php
<head>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS Custom -->
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/auth-pages.css">
</head>
```

## 🏷️ Class Naming Convention

### Prefix Berdasarkan Modul
- **admin-** : Khusus portal admin
- **siswa-** : Khusus portal siswa
- **auth-** : Khusus halaman authentication
- **print-** : Khusus untuk print

### Contoh Penggunaan

#### Admin Portal
```html
<!-- Stats Cards -->
<div class="admin-stats-card">
  <div class="card-body">
    <h6>Total Siswa</h6>
    <h3>150</h3>
  </div>
</div>

<!-- Data Table -->
<div class="admin-data-table">
  <table class="table">...</table>
</div>

<!-- Filter Section -->
<div class="admin-filter-section">
  <form>...</form>
</div>
```

#### Siswa Portal
```html
<!-- Summary Cards -->
<div class="siswa-keuangan-summary">
  <div class="summary-card">...</div>
</div>

<!-- Payment Table -->
<div class="siswa-payment-table">
  <table>...</table>
</div>

<!-- Profile Section -->
<div class="siswa-profile-section">
  <div class="profile-avatar-large">...</div>
</div>
```

#### Auth Pages
```html
<!-- Login/Register Wrapper -->
<div class="auth-wrapper">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-card-header">
        <h3>Login</h3>
      </div>
      <div class="auth-card-body">
        <form class="auth-form">...</form>
      </div>
    </div>
  </div>
</div>
```

## 🎯 Component Classes

### Buttons
```html
<!-- Custom Button -->
<button class="btn-custom btn-primary">Submit</button>

<!-- Auth Button -->
<button class="auth-btn auth-btn-primary">Login</button>
```

### Cards
```html
<!-- Gradient Card -->
<div class="card card-gradient-purple">...</div>

<!-- Info Box -->
<div class="info-box">...</div>

<!-- Welcome Card -->
<div class="welcome-card">...</div>
```

### Alerts
```html
<!-- Custom Alert -->
<div class="alert-custom alert-info">...</div>

<!-- Siswa Info Alert -->
<div class="siswa-info-alert">...</div>

<!-- Admin Alert -->
<div class="admin-alert">...</div>
```

### Badges
```html
<!-- Status Badges -->
<span class="badge badge-lunas">Lunas</span>
<span class="badge badge-menunggu">Menunggu</span>
<span class="badge badge-ditolak">Ditolak</span>
<span class="badge badge-belum">Belum Bayar</span>
```

### Tables
```html
<!-- Custom Table -->
<div class="table-custom">
  <table class="table">...</table>
</div>

<!-- Admin Monthly Table -->
<div class="admin-monthly-table">
  <table>...</table>
</div>
```

## 🖨️ Print Styling

Untuk halaman yang memiliki fitur print, tambahkan class `no-print` pada elemen yang tidak ingin dicetak:

```html
<!-- Element yang tidak akan muncul saat print -->
<button class="btn no-print">Edit</button>
<div class="sidebar no-print">...</div>

<!-- Element khusus untuk print (hidden di layar) -->
<div class="print-header">
  <h2>LAPORAN KEUANGAN</h2>
</div>

<div class="print-info">
  <strong>Filter:</strong> Tahun 2024/2025
</div>

<div class="stats-print">
  <div class="stat-box">...</div>
</div>

<div class="print-footer">
  <p>Dicetak pada: 5 November 2025</p>
</div>
```

## 📱 Responsive Classes

Semua CSS sudah responsive dengan breakpoint:
- **xs**: < 576px (Mobile Portrait)
- **sm**: ≥ 576px (Mobile Landscape)
- **md**: ≥ 768px (Tablet)
- **lg**: ≥ 992px (Desktop)
- **xl**: ≥ 1200px (Wide Desktop)

## ⚡ Utility Classes

```html
<!-- Gradient Text -->
<h1 class="text-gradient">Welcome</h1>

<!-- Gradient Background -->
<div class="bg-gradient-purple">...</div>
<div class="bg-gradient-blue">...</div>

<!-- Shadow -->
<div class="shadow-custom">...</div>

<!-- Rounded -->
<div class="rounded-custom">...</div>

<!-- Transition -->
<button class="transition-all">Hover Me</button>
```

## 🔧 Customization

Untuk mengubah warna atau spacing global, edit variabel di `app.css`:

```css
:root {
    --primary-blue: #your-color;
    --spacing-md: 1.5rem; /* ubah spacing default */
}
```

## 📝 Best Practices

1. **Gunakan variabel CSS** untuk konsistensi warna dan spacing
2. **Gunakan prefix** untuk menghindari konflik class
3. **Tambahkan class `no-print`** pada elemen yang tidak perlu dicetak
4. **Gunakan utility classes** untuk styling cepat
5. **Ikuti naming convention** yang sudah ditentukan
6. **Test responsiveness** di berbagai ukuran layar

## 🚀 Migration dari Inline Style

Jika ada inline style di file PHP, pindahkan ke CSS file dengan prefix yang sesuai:

**Before:**
```php
<style>
  .my-card { background: purple; }
</style>
```

**After:**
```php
<!-- Hapus tag <style>, tambahkan class yang sesuai -->
<div class="admin-stats-card">...</div>
```

Kemudian definisikan di file CSS yang sesuai (`admin-portal.css`, `siswa-portal.css`, dll).

## ⚠️ Important Notes

1. **Jangan menghapus file `dashboard.css`** - masih digunakan untuk layout utama
2. **Load CSS dengan urutan yang benar** - `app.css` → `dashboard.css` → module CSS
3. **Gunakan Bootstrap classes** untuk layout dasar (grid, spacing, dll)
4. **Custom classes** hanya untuk styling spesifik yang tidak ada di Bootstrap

## 📚 References

- Bootstrap 5.3: https://getbootstrap.com/docs/5.3/
- Bootstrap Icons: https://icons.getbootstrap.com/
- CSS Variables: https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties
