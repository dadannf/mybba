# 📋 Import Data Siswa - Format CSV

## Format File CSV

### Delimiter
- **Semicolon (`;`)** - WAJIB menggunakan semicolon sebagai pemisah kolom

### Struktur Kolom

| No | Nama Kolom | Wajib | Format | Contoh | Keterangan |
|----|------------|-------|--------|--------|------------|
| 1 | nis | ✅ Ya | Angka/Text | 222111001 | Nomor Induk Siswa (Primary Key) |
| 2 | nisn | ❌ Tidak | Angka | 1234567 | Nomor Induk Siswa Nasional |
| 3 | nik | ❌ Tidak | Angka | 3201010101010001 | NIK sesuai KTP |
| 4 | nama | ✅ Ya | Text | Ahmad Rizki Maulana | Nama lengkap siswa |
| 5 | tempat_lahir | ❌ Tidak | Text | Bandung | Tempat lahir |
| 6 | tanggal_lahir | ❌ Tidak | DD/MM/YYYY | 15/05/2007 | Format tanggal Indonesia |
| 7 | jk | ✅ Ya | L atau P | L | Jenis kelamin (L=Laki-laki, P=Perempuan) |
| 8 | kelas | ✅ Ya | Angka | 10 | Tingkat kelas (10, 11, atau 12) |
| 9 | jurusan | ✅ Ya | Text | RPL | Kode jurusan (RPL, TKJ, MM, TSM) |
| 10 | agama | ❌ Tidak | Text | Islam | Agama siswa |
| 11 | alamat | ❌ Tidak | Text | Jl. Merdeka No. 123 | Alamat lengkap |
| 12 | email | ❌ Tidak | Email | ahmad@email.com | Email siswa |
| 13 | no_hp | ❌ Tidak | Angka | 81234567890 | Nomor HP siswa (tanpa 0 di depan) |
| 14 | nama_ayah | ❌ Tidak | Text | Budi Santoso | Nama ayah kandung |
| 15 | nama_ibu | ❌ Tidak | Text | Siti Aminah | Nama ibu kandung |
| 16 | pekerjaan_ayah | ❌ Tidak | Text | Wiraswasta | Pekerjaan ayah |
| 17 | pekerjaan_ibu | ❌ Tidak | Text | Ibu Rumah Tangga | Pekerjaan ibu |
| 18 | no_hp_ortu | ❌ Tidak | Angka | 81234567891 | Nomor HP orang tua |
| 19 | status_siswa | ❌ Tidak | Text | aktif | Status: aktif/lulus/keluar/pindah |
| 20 | tahun_masuk | ✅ Ya | YYYY | 2023 | Tahun masuk sekolah (untuk generate keuangan) |

---

## Contoh Data CSV

```csv
nis;nisn;nik;nama;tempat_lahir;tanggal_lahir;jk;kelas;jurusan;agama;alamat;email;no_hp;nama_ayah;nama_ibu;pekerjaan_ayah;pekerjaan_ibu;no_hp_ortu;status_siswa;tahun_masuk
222111001;1234567;3201010101010001;Ahmad Rizki Maulana;Bandung;15/05/2007;L;10;RPL;Islam;Jl. Merdeka No. 123;ahmad.rizki@email.com;81234567890;Budi Santoso;Siti Aminah;Wiraswasta;Ibu Rumah Tangga;81234567891;aktif;2023
222111002;1234568;3201010202020002;Siti Nurhaliza;Jakarta;20/08/2007;P;10;TKJ;Islam;Jl. Kenangan No. 456;siti.nur@email.com;81234567892;Hendra Wijaya;Rina Kusuma;Pegawai Negeri;Guru;81234567893;aktif;2023
222111003;1234569;3201010303030003;Budi Pratama;Surabaya;10/03/2007;L;11;MM;Kristen;Jl. Pahlawan No. 789;budi.pratama@email.com;81234567894;Agus Setiawan;Linda Sari;Dokter;Perawat;81234567895;aktif;2022
```

---

## Proses Import

### 1. Generate User Account
Untuk setiap siswa, sistem akan otomatis membuat akun login:
- **Username**: NIS siswa
- **Password**: `bba#[4 digit terakhir NIS]`
- **Role**: siswa

**Contoh:**
- NIS: `222111001` → Username: `222111001`, Password: `bba#1001`
- NIS: `222111002` → Username: `222111002`, Password: `bba#1002`

### 2. Generate Data Keuangan

Sistem akan generate data keuangan **otomatis** berdasarkan:
- **Tahun masuk** (dari kolom `tahun_masuk`)
- **Kelas saat ini** (dari kolom `kelas`)

#### Logika Generate Keuangan:

**Contoh 1: Siswa masuk tahun 2023 di kelas 10, sekarang tahun 2025 (kelas 12)**

| Tahun Ajaran | Kelas | Tagihan/Bulan | Total Tagihan (12 bulan) |
|--------------|-------|---------------|--------------------------|
| 2023 | 10 | Rp 200.000 | Rp 2.400.000 |
| 2024 | 11 | Rp 190.000 | Rp 2.280.000 |
| 2025 | 12 | Rp 190.000 | Rp 2.280.000 |

**Total tagihan kumulatif**: Rp 6.960.000

**Contoh 2: Siswa masuk tahun 2024 di kelas 10, sekarang tahun 2025 (kelas 11)**

| Tahun Ajaran | Kelas | Tagihan/Bulan | Total Tagihan (12 bulan) |
|--------------|-------|---------------|--------------------------|
| 2024 | 10 | Rp 200.000 | Rp 2.400.000 |
| 2025 | 11 | Rp 190.000 | Rp 2.280.000 |

**Total tagihan kumulatif**: Rp 4.680.000

### 3. Tarif SPP

| Kelas | Tarif per Bulan |
|-------|-----------------|
| Kelas 10 | Rp 200.000 |
| Kelas 11 | Rp 190.000 |
| Kelas 12 | Rp 190.000 |

---

## Validasi & Error Handling

### Validasi yang Dilakukan:

1. ✅ **NIS unik** - NIS tidak boleh duplikat
2. ✅ **Kolom wajib** - nis, nama, jk, kelas, jurusan, tahun_masuk harus diisi
3. ✅ **Format tanggal** - DD/MM/YYYY atau YYYY-MM-DD
4. ✅ **Nomor HP** - Otomatis clean scientific notation dan tambah prefix 0
5. ✅ **NIK** - Clean dari scientific notation Excel

### Skip Duplikat

Jika checkbox "Skip data duplikat" diaktifkan:
- Siswa dengan NIS yang sudah ada akan **dilewati**
- Tidak ada error, hanya skip dan lanjut ke data berikutnya

### Response Import

Setelah import selesai, sistem akan menampilkan:
```json
{
  "success": true,
  "imported": 45,    // Jumlah siswa berhasil diimport
  "skipped": 3,      // Jumlah siswa di-skip (duplikat)
  "errors": 2,       // Jumlah siswa gagal
  "errorDetails": [  // Detail error
    "NIS 222111999 sudah terdaftar",
    "Baris 50: Data required tidak lengkap"
  ]
}
```

---

## Tips & Best Practices

### 1. Format Excel ke CSV
Jika data Anda di Excel:
1. Buka file Excel
2. File → Save As → CSV (Comma delimited) `*.csv`
3. Di Excel, ganti delimiter comma (`,`) ke semicolon (`;`):
   - Windows: Control Panel → Region → Additional Settings → List Separator → ubah ke `;`
   - Atau gunakan Find & Replace setelah save

### 2. Hindari Scientific Notation
Excel sering convert angka panjang (NIK, no HP) ke scientific notation:
- **Contoh**: `3201010101010001` → `3.20101E+15`

**Solusi:**
1. Format cell sebagai **Text** sebelum input data
2. Tambahkan `'` (apostrophe) di depan angka: `'3201010101010001`
3. Sistem sudah handle clean scientific notation otomatis

### 3. Format Tanggal Konsisten
Gunakan format: **DD/MM/YYYY**
- ✅ Benar: `15/05/2007`
- ❌ Salah: `15-05-2007`, `2007/05/15`, `May 15, 2007`

### 4. Nomor HP
Bisa dengan atau tanpa prefix `0`:
- ✅ `081234567890` → otomatis jadi `081234567890`
- ✅ `81234567890` → otomatis jadi `081234567890`

### 5. Tahun Masuk Wajib Benar
Tahun masuk sangat penting untuk kalkulasi keuangan:
- Jika siswa sekarang kelas 12 tahun 2025, tahun masuk seharusnya **2023**
- Jika siswa sekarang kelas 11 tahun 2025, tahun masuk seharusnya **2024**

---

## Troubleshooting

### Error: "Format file tidak didukung"
- Pastikan file extension adalah `.csv`
- Jangan upload file `.xlsx` atau `.xls` langsung

### Error: "Kolom required tidak ditemukan"
- Pastikan header CSV memiliki kolom: `nis`, `nama`, `jk`, `kelas`, `jurusan`, `tahun_masuk`
- Header harus ada di baris pertama

### Error: "Data required tidak lengkap"
- Ada baris yang kolom wajib kosong
- Check setiap baris memiliki: NIS, nama, jk, kelas, jurusan, tahun_masuk

### NIK atau No HP salah/terpotong
- Format cell sebagai Text di Excel
- Atau tambah `'` di depan angka

### Import berhasil tapi keuangan tidak muncul
- Check kolom `tahun_masuk` sudah diisi dengan benar
- Check format tahun adalah angka 4 digit (contoh: 2023)

---

## Download Template

Klik tombol **Download Template** di modal import untuk mendapatkan file CSV dengan format yang sudah benar dan contoh data.

---

**Update**: December 9, 2025
