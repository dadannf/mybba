# 📥 Panduan Import Data Siswa dari CSV

## 🎯 Overview

Fitur import CSV memungkinkan admin untuk menambahkan banyak data siswa sekaligus dari file CSV/Excel.

---

## 📍 Lokasi Fitur

**URL:** `http://localhost/mybba/admin/students/index.php`

**Tombol:** Hijau dengan icon "Import" di pojok kanan atas

---

## 🚀 Cara Menggunakan

### Step 1: Download Template

1. Klik tombol **"Import"**
2. Di modal yang muncul, klik **"Download"** untuk download template
3. File `template_import_siswa.csv` akan terdownload
4. Template sudah include 3 contoh data

### Step 2: Isi Data di Excel

1. Buka file template dengan **Microsoft Excel** atau **Google Sheets**
2. Isi data siswa mulai dari baris ke-5 (setelah contoh data)
3. **Jangan ubah header** (baris pertama)
4. **Jangan hapus contoh data** jika ingin melihat format

**Contoh:**
```csv
nis,nisn,nik,nama,tempat_lahir,tanggal_lahir,jk,kelas,jurusan,...
222111001,0001234567,3201010101010001,Ahmad Rizki,Bandung,2007-05-15,L,10,RPL,...
222111002,0001234568,3201010202020002,Siti Nurhaliza,Jakarta,2007-08-20,P,10,TKJ,...
```

### Step 3: Save as CSV

**Di Excel:**
1. File → Save As
2. Pilih format: **CSV UTF-8 (Comma delimited) (*.csv)**
3. Save

**Di Google Sheets:**
1. File → Download → Comma Separated Values (.csv)

### Step 4: Upload & Import

1. Kembali ke halaman Data Siswa
2. Klik tombol **"Import"**
3. Klik **"Pilih File"** dan pilih CSV yang sudah diisi
4. Centang **"Skip data duplikat"** jika ingin skip NIS yang sudah ada
5. Klik **"Upload & Import"**
6. Tunggu proses selesai (beberapa detik)
7. Halaman akan refresh otomatis jika berhasil

---

## 📋 Format Data

### Kolom REQUIRED (Wajib Diisi)

| Kolom | Deskripsi | Format | Contoh |
|-------|-----------|--------|--------|
| `nis` | Nomor Induk Siswa | Angka, unique | 222111001 |
| `nama` | Nama lengkap siswa | Text | Ahmad Rizki Maulana |
| `jk` | Jenis kelamin | L atau P | L |
| `kelas` | Kelas siswa | 10, 11, atau 12 | 10 |
| `jurusan` | Jurusan | RPL, TKJ, MM, dll | RPL |

### Kolom OPTIONAL (Boleh Kosong)

| Kolom | Deskripsi | Format | Contoh |
|-------|-----------|--------|--------|
| `nisn` | NISN | Angka 10 digit | 0001234567 |
| `nik` | NIK | Angka 16 digit | 3201010101010001 |
| `tempat_lahir` | Tempat lahir | Text | Bandung |
| `tanggal_lahir` | Tanggal lahir | YYYY-MM-DD | 2007-05-15 |
| `agama` | Agama | Text | Islam |
| `alamat` | Alamat lengkap | Text | Jl. Merdeka No. 123 |
| `email` | Email siswa | Email format | ahmad@email.com |
| `no_hp` | No HP siswa | Angka | 081234567890 |
| `nama_ayah` | Nama ayah | Text | Budi Santoso |
| `nama_ibu` | Nama ibu | Text | Siti Aminah |
| `pekerjaan_ayah` | Pekerjaan ayah | Text | Wiraswasta |
| `pekerjaan_ibu` | Pekerjaan ibu | Text | Ibu Rumah Tangga |
| `no_hp_ortu` | No HP orang tua | Angka | 081234567891 |
| `status_siswa` | Status | aktif/lulus/keluar | aktif |
| `tahun_masuk` | Tahun masuk | YYYY | 2023 |

---

## 🔐 Auto-Generated Data

Sistem akan otomatis membuat:

### 1. **User Account**
- **Username:** NIS siswa
- **Password:** `bba#[4 digit terakhir NIS]`
- **Role:** siswa

**Contoh:**
- NIS: 222111001
- Username: 222111001
- Password: bba#1001

### 2. **Data Keuangan**
- **Tahun ajaran:** Tahun sekarang (2025/2026)
- **Total tagihan:** Rp 1.000.000 (default)
- **Total bayar:** Rp 0

---

## ✅ Validasi & Error Handling

### Validasi Otomatis:

✅ **File Format**
- Hanya CSV yang didukung
- Excel (.xlsx/.xls) harus diconvert ke CSV dulu

✅ **File Size**
- Maksimal 2MB

✅ **Required Fields**
- NIS, nama, jk, kelas, jurusan harus diisi
- Baris dengan data tidak lengkap akan di-skip

✅ **Duplicate Check**
- NIS yang sudah ada akan di-skip (jika opsi dicentang)
- Atau akan error jika opsi tidak dicentang

✅ **Data Integrity**
- Menggunakan database transaction
- Jika ada error, semua data di-rollback

---

## 📊 Import Result

Setelah import selesai, akan muncul summary:

```
Import berhasil! 
- 45 data ditambahkan
- 3 dilewati (duplikat)
- 2 gagal (data tidak lengkap)
```

**Keterangan:**
- **Ditambahkan:** Data berhasil diimport
- **Dilewati:** NIS sudah ada (jika skip duplicate dicentang)
- **Gagal:** Data tidak valid atau error

---

## ⚠️ Common Issues & Solutions

### Issue 1: "Format file tidak didukung"

**Penyebab:** File bukan CSV atau format salah

**Solusi:**
1. Pastikan save as **CSV UTF-8**
2. Jangan gunakan Excel (.xlsx) langsung
3. Convert Excel → CSV dulu

### Issue 2: "Kolom required tidak ditemukan"

**Penyebab:** Header CSV tidak sesuai template

**Solusi:**
1. Download template baru
2. Jangan ubah nama kolom di baris pertama
3. Copy-paste data ke template baru

### Issue 3: "Data required tidak lengkap"

**Penyebab:** Ada kolom wajib yang kosong

**Solusi:**
1. Pastikan kolom: nis, nama, jk, kelas, jurusan terisi
2. Check baris yang error di summary
3. Perbaiki dan upload ulang

### Issue 4: "NIS sudah terdaftar"

**Penyebab:** NIS duplikat dengan data yang sudah ada

**Solusi:**
1. Centang opsi "Skip data duplikat"
2. Atau ganti NIS dengan yang unik
3. Check database untuk NIS yang sudah ada

### Issue 5: "Ukuran file terlalu besar"

**Penyebab:** File > 2MB

**Solusi:**
1. Split file menjadi beberapa bagian
2. Import per batch (max 500 siswa per file)
3. Hapus kolom yang tidak perlu

---

## 💡 Tips & Best Practices

### 1. **Persiapan Data**

✅ **Gunakan Template**
- Selalu mulai dari template yang didownload
- Jangan ubah struktur header

✅ **Validasi Manual**
- Check data di Excel sebelum upload
- Pastikan format tanggal: YYYY-MM-DD
- Pastikan jk: L atau P (huruf kapital)

✅ **Backup Data**
- Backup database sebelum import besar
- Simpan file CSV sebagai backup

### 2. **Import Bertahap**

✅ **Batch Import**
- Import max 100-200 siswa per batch
- Lebih mudah track error
- Lebih cepat proses

✅ **Test Import**
- Test dengan 5-10 data dulu
- Pastikan format sudah benar
- Baru import semua data

### 3. **Setelah Import**

✅ **Verifikasi Data**
- Check jumlah siswa bertambah
- Check beberapa data random
- Test login dengan akun siswa baru

✅ **Update Data**
- Upload foto siswa manual
- Update data keuangan jika perlu
- Inform siswa tentang username & password

---

## 🔧 Advanced: Bulk Password Reset

Jika perlu reset password semua siswa hasil import:

```sql
-- Reset password semua siswa ke format default
UPDATE users u
JOIN siswa s ON u.user_id = s.user_id
SET u.password = PASSWORD('bba#' + RIGHT(s.nis, 4))
WHERE u.role = 'siswa';
```

---

## 📞 Support

**Jika mengalami masalah:**

1. Check error message di modal import
2. Verify format CSV sesuai template
3. Check database logs
4. Contact admin system

---

## 📝 Changelog

**v1.0 (Current)**
- ✅ Import CSV basic
- ✅ Auto create user account
- ✅ Auto create keuangan
- ✅ Skip duplicate option
- ✅ Transaction safety

**v2.0 (Planned)**
- 🔄 Support Excel (.xlsx/.xls)
- 🔄 Real-time progress bar
- 🔄 Detailed error report per row
- 🔄 Preview before import
- 🔄 Update existing data option

---

**Last Updated:** November 26, 2025  
**Version:** 1.0.0
