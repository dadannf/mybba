# 🔌 API Documentation - MyBBA

## Overview
Internal AJAX API endpoints untuk operasi real-time.

## Base URL
```
http://localhost/mybba/api/
```

## Authentication
Semua endpoint memerlukan session yang valid (user harus login).

---

## 📋 Endpoints

### 1. Check Duplicate NIS/NISN
**Endpoint:** `/api/check_duplicate.php`  
**Method:** GET  
**Purpose:** Validasi duplikasi NIS/NISN saat input siswa

**Parameters:**
- `field` (string): 'nis' atau 'nisn'
- `value` (string): Nilai yang akan dicek
- `exclude_nis` (string, optional): NIS yang dikecualikan (untuk edit)

**Response:**
```json
{
  "exists": true,
  "message": "NIS sudah terdaftar"
}
```

**Example:**
```javascript
fetch('/api/check_duplicate.php?field=nis&value=12345')
  .then(res => res.json())
  .then(data => console.log(data.exists));
```

---

### 2. Check Keuangan Duplicate
**Endpoint:** `/api/check_keuangan_duplicate.php`  
**Method:** GET  
**Purpose:** Cek duplikasi data keuangan per tahun ajaran

**Parameters:**
- `nis` (string): NIS siswa
- `tahun_ajaran` (string): Tahun ajaran (format: 2024/2025)
- `exclude_id` (int, optional): ID keuangan yang dikecualikan

**Response:**
```json
{
  "exists": false
}
```

---

### 3. Get Keuangan Progress
**Endpoint:** `/api/get_keuangan_progress.php`  
**Method:** GET  
**Purpose:** Ambil progress pembayaran siswa

**Parameters:**
- `keuangan_id` (int): ID keuangan

**Response:**
```json
{
  "success": true,
  "data": {
    "total_tagihan": 5000000,
    "total_bayar": 3000000,
    "sisa": 2000000,
    "progress": 60,
    "pembayaran": [
      {
        "bulan": "Januari",
        "nominal": 500000,
        "tanggal": "2024-01-15",
        "status": "verified"
      }
    ]
  }
}
```

---

### 4. List Pembayaran
**Endpoint:** `/api/list_pembayaran.php`  
**Method:** GET  
**Purpose:** Daftar pembayaran untuk admin

**Parameters:**
- `status` (string, optional): 'pending', 'verified', 'rejected'
- `limit` (int, optional): Jumlah data per halaman

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "pembayaran_id": 1,
      "nama_siswa": "John Doe",
      "nis": "12345",
      "bulan": "Januari",
      "nominal": 500000,
      "tanggal": "2024-01-15",
      "status": "pending",
      "bukti_bayar": "uploads/bukti_bayar/xxx.jpg"
    }
  ],
  "total": 10
}
```

---

### 5. Update Payment Status
**Endpoint:** `/api/update_payment_status.php`  
**Method:** POST  
**Purpose:** Approve/reject pembayaran

**Parameters:**
```json
{
  "pembayaran_id": 1,
  "action": "approve", // or "reject"
  "catatan": "Pembayaran valid"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Pembayaran berhasil diverifikasi"
}
```

---

### 6. Process Payment (Student)
**Endpoint:** `/api/process_payment_student.php`  
**Method:** POST  
**Purpose:** Upload bukti pembayaran oleh siswa

**Parameters (FormData):**
- `keuangan_id` (int)
- `bulan_untuk` (string)
- `nominal_bayar` (int)
- `metode` (string): 'transfer', 'tunai', 'va'
- `tempat_bayar` (string)
- `bukti_bayar` (file): Image file

**Response:**
```json
{
  "success": true,
  "message": "Bukti pembayaran berhasil diupload",
  "pembayaran_id": 123
}
```

---

### 7. Get Informasi Detail
**Endpoint:** `/api/get_informasi_detail.php`  
**Method:** GET  
**Purpose:** Detail informasi/pengumuman

**Parameters:**
- `id` (int): ID informasi

**Response:**
```json
{
  "success": true,
  "data": {
    "informasi_id": 1,
    "judul": "Libur Semester",
    "isi": "Pengumuman libur...",
    "foto": "uploads/informasi/xxx.jpg",
    "created_at": "2024-01-15 10:00:00",
    "created_by": "Admin"
  }
}
```

---

### 8. Notifikasi
**Endpoint:** `/api/notifikasi.php`  
**Method:** GET  
**Purpose:** Ambil notifikasi user

**Response:**
```json
{
  "success": true,
  "unread_count": 3,
  "notifications": [
    {
      "id": 1,
      "title": "Pembayaran Diverifikasi",
      "message": "Pembayaran Januari telah diverifikasi",
      "type": "success",
      "created_at": "2024-01-15 10:00:00",
      "is_read": false
    }
  ]
}
```

---

## Error Handling

Semua endpoint menggunakan format error yang konsisten:

```json
{
  "success": false,
  "message": "Error message here",
  "error_code": "VALIDATION_ERROR"
}
```

**Common Error Codes:**
- `AUTH_REQUIRED` - User belum login
- `VALIDATION_ERROR` - Input tidak valid
- `NOT_FOUND` - Data tidak ditemukan
- `PERMISSION_DENIED` - Tidak punya akses
- `SERVER_ERROR` - Internal server error

---

## Rate Limiting
Tidak ada rate limiting saat ini (internal use only).

## CORS
CORS disabled (same-origin only).

---

**Last Updated:** November 2025
