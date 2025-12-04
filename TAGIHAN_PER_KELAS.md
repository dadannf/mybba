# Sistem Tagihan Berdasarkan Kelas

## Ketentuan Tagihan Per Bulan

| Kelas | Tagihan/Bulan | Total/Tahun (12 bulan) |
|-------|---------------|------------------------|
| Kelas 10 | Rp 200.000 | Rp 2.400.000 |
| Kelas 11 | Rp 190.000 | Rp 2.280.000 |
| Kelas 12 | Rp 190.000 | Rp 2.280.000 |

## Implementasi

### Fungsi Utama

```php
function getTagihanPerBulan($kelas) {
    // Ambil angka kelas dari string (misal: "10 IPA 1" -> 10)
    preg_match('/^(\d+)/', $kelas, $matches);
    $tingkatKelas = isset($matches[1]) ? intval($matches[1]) : 10;
    
    // Kelas 10: Rp 200.000, Kelas 11-12: Rp 190.000
    if ($tingkatKelas == 10) {
        return 200000;
    } else {
        return 190000; // Kelas 11 dan 12
    }
}
```

### File yang Diupdate

1. **`public/student/finance.php`**
   - Menampilkan tagihan siswa berdasarkan kelasnya
   - Fungsi `getTagihanPerBulan()` ditambahkan
   - Perhitungan total tagihan disesuaikan

2. **`public/admin/finance/detail.php`**
   - Detail pembayaran siswa dengan tagihan dinamis
   - Admin melihat tagihan sesuai kelas siswa

3. **`public/admin/finance/print.php`**
   - Rekap pembayaran dengan perhitungan per kelas
   - Grand total dihitung berdasarkan kelas masing-masing siswa

4. **`public/admin/students/create.php`**
   - Saat membuat siswa baru, auto-create keuangan dengan tagihan sesuai kelas
   - Kelas 10: Rp 2.400.000/tahun
   - Kelas 11-12: Rp 2.280.000/tahun

5. **`public/admin/students/process_import.php`**
   - Import siswa dari Excel/CSV
   - Auto-create keuangan dengan tagihan sesuai kelas

## Cara Kerja

1. **Deteksi Kelas:**
   - Sistem mengambil angka di awal string kelas
   - Contoh: "10 IPA 1" → 10, "11 RPL 2" → 11, "12 MIPA" → 12

2. **Perhitungan Otomatis:**
   - Total tagihan per tahun = `tagihanPerBulan × 12`
   - Kelas 10: 200.000 × 12 = 2.400.000
   - Kelas 11-12: 190.000 × 12 = 2.280.000

3. **Integrasi OCR:**
   - Sistem OCR tetap bekerja normal
   - Expected amount disesuaikan dengan tagihan per bulan
   - Validasi otomatis berdasarkan nominal yang benar

## Catatan Penting

- **Siswa yang sudah ada:** Tagihan lama tidak berubah otomatis. Update manual jika perlu.
- **Siswa baru:** Otomatis menggunakan tarif baru sesuai kelas
- **Naik kelas:** Jika siswa naik kelas, buat record keuangan baru untuk tahun ajaran berikutnya

## Testing

### Test Case 1: Siswa Kelas 10
```
Kelas: 10 IPA 1
Tagihan/Bulan: Rp 200.000
Total/Tahun: Rp 2.400.000
```

### Test Case 2: Siswa Kelas 11
```
Kelas: 11 RPL 2
Tagihan/Bulan: Rp 190.000
Total/Tahun: Rp 2.280.000
```

### Test Case 3: Siswa Kelas 12
```
Kelas: 12 MIPA 1
Tagihan/Bulan: Rp 190.000
Total/Tahun: Rp 2.280.000
```

## Update untuk Data Existing

Jika ingin update tagihan untuk siswa yang sudah ada:

```sql
-- Update kelas 10
UPDATE keuangan k
INNER JOIN siswa s ON k.nis = s.nis
SET k.total_tagihan = 2400000
WHERE s.kelas LIKE '10%';

-- Update kelas 11-12
UPDATE keuangan k
INNER JOIN siswa s ON k.nis = s.nis
SET k.total_tagihan = 2280000
WHERE s.kelas LIKE '11%' OR s.kelas LIKE '12%';
```

---

**Tanggal Implementasi:** 1 Desember 2025  
**Developer:** AI Assistant  
**Status:** ✅ Selesai
