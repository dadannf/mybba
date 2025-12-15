# FORMAT IMPORT DATA SISWA

## ⚠️ PENTING: Format CSV Harus Sesuai Struktur Database

### ✅ Format CSV Yang BENAR (15 Kolom):

```
nis;nisn;nik;nama;tempat_lahir;tanggal_lahir;jk;kelas;jurusan;ayah;ibu;alamat;email;no_hp;status_siswa
```

### 📋 Penjelasan Kolom:

| No | Kolom | Tipe | Required | Contoh | Keterangan |
|----|-------|------|----------|--------|------------|
| 1 | nis | String | ✅ Yes | 222111001 | Nomor Induk Siswa (unique) |
| 2 | nisn | String | ❌ No | 1234567 | Nomor Induk Siswa Nasional |
| 3 | nik | String | ❌ No | 3201010101010001 | NIK (16 digit) |
| 4 | nama | String | ✅ Yes | Ahmad Rizki | Nama lengkap siswa |
| 5 | tempat_lahir | String | ❌ No | Bandung | Tempat lahir |
| 6 | tanggal_lahir | Date | ❌ No | 15/05/2007 | Format: DD/MM/YYYY |
| 7 | jk | Char | ✅ Yes | L | L (Laki-laki) atau P (Perempuan) |
| 8 | kelas | String | ✅ Yes | 10 | Tingkat kelas (10, 11, 12) |
| 9 | jurusan | String | ✅ Yes | RPL | RPL, TKJ, MM, TSM, dll |
| 10 | ayah | String | ❌ No | Budi Santoso | Nama ayah |
| 11 | ibu | String | ❌ No | Siti Aminah | Nama ibu |
| 12 | alamat | Text | ❌ No | Jl. Merdeka No. 123 | Alamat lengkap |
| 13 | email | String | ❌ No | ahmad@email.com | Email siswa |
| 14 | no_hp | String | ❌ No | 81234567890 | No HP (tanpa +62 atau 0) |
| 15 | status_siswa | Enum | ❌ No | aktif | aktif/lulus/keluar/pindah |

### ❌ Kolom Yang TIDAK ADA di Database (JANGAN gunakan):

- ❌ `agama` - TIDAK ADA di tabel siswa
- ❌ `nama_ayah` - Gunakan `ayah` saja
- ❌ `nama_ibu` - Gunakan `ibu` saja
- ❌ `pekerjaan_ayah` - TIDAK ADA di tabel siswa
- ❌ `pekerjaan_ibu` - TIDAK ADA di tabel siswa
- ❌ `no_hp_ortu` - TIDAK ADA di tabel siswa
- ❌ `tahun_masuk` - TIDAK ADA di tabel siswa

## 📝 Contoh Data CSV:

```csv
nis;nisn;nik;nama;tempat_lahir;tanggal_lahir;jk;kelas;jurusan;ayah;ibu;alamat;email;no_hp;status_siswa
222111001;1234567;3201010101010001;Ahmad Rizki;Bandung;15/05/2007;L;10;RPL;Budi Santoso;Siti Aminah;Jl. Merdeka No. 123;ahmad.rizki@email.com;81234567890;aktif
222111002;1234568;3201010202020002;Siti Nurhaliza;Jakarta;20/08/2007;P;10;TKJ;Hendra Wijaya;Rina Kusuma;Jl. Kenangan No. 456;siti.nur@email.com;81234567892;aktif
```

## 🔧 Catatan Teknis:

1. **Delimiter**: Gunakan semicolon (`;`) BUKAN comma (`,`)
2. **Encoding**: UTF-8 (dengan atau tanpa BOM)
3. **Format Tanggal**: `DD/MM/YYYY` (contoh: 15/05/2007)
4. **Header**: Baris pertama HARUS berisi nama kolom
5. **Password**: Auto-generate `bba#[4 digit terakhir NIS]` (contoh: NIS 222111001 → password: bba#1001)

## 💰 Generate Keuangan Otomatis:

System akan **otomatis generate data keuangan** berdasarkan kelas siswa:

**Logika Perhitungan Tahun Masuk:**
- Kelas 10 → Baru masuk tahun ini (2025)
- Kelas 11 → Masuk tahun lalu (2024)
- Kelas 12 → Masuk 2 tahun lalu (2023)

**Tarif Per Tahun:**
- Kelas 10: Rp 200.000/bulan × 12 = Rp 2.400.000/tahun
- Kelas 11: Rp 190.000/bulan × 12 = Rp 2.280.000/tahun
- Kelas 12: Rp 190.000/bulan × 12 = Rp 2.280.000/tahun

**Contoh:**
- Import siswa **Kelas 10** → Generate 1 record keuangan (tahun 2025)
- Import siswa **Kelas 11** → Generate 2 record keuangan (tahun 2024 & 2025)
- Import siswa **Kelas 12** → Generate 3 record keuangan (tahun 2023, 2024 & 2025)

## ⚙️ Cara Download Template:

1. Login sebagai Admin
2. Menu: **Data Siswa**
3. Klik tombol **Import Data**
4. Klik **Download Template CSV**
5. Edit template dengan data Anda
6. Upload file CSV

## 🐛 Troubleshooting:

### Error: "Incorrect date value: '2007'"
**Penyebab**: File CSV masih menggunakan format lama (20 kolom) yang include `tahun_masuk`  
**Solusi**: Download template baru dan gunakan format 15 kolom

### Error: "Jumlah kolom tidak sesuai"
**Penyebab**: Delimiter salah atau ada karakter special di data  
**Solusi**: Pastikan menggunakan semicolon (`;`) sebagai delimiter

### Error: "Format tanggal lahir tidak valid"
**Penyebab**: Format tanggal bukan DD/MM/YYYY  
**Solusi**: Ubah format tanggal menjadi DD/MM/YYYY (contoh: 15/05/2007)

## 📊 File Yang Tersedia:

- ✅ `test_import_fix.csv` - File test dengan format BENAR (15 kolom)
- ✅ `data_siswa_fix.csv` - Data Anda yang sudah dikonversi ke format BENAR
- ❌ `data_siswa.csv` - Format LAMA (20 kolom) - JANGAN GUNAKAN!
