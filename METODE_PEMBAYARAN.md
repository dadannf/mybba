# Sistem Metode Pembayaran Sederhana

## Ketentuan Metode Pembayaran

### **Metode yang Tersedia:**

1. **Transfer Bank**
   - Bank: **BRI (Bank Rakyat Indonesia)** ONLY
   - Siswa/Admin harus transfer ke rekening BRI sekolah
   - Sistem OCR akan validasi bukti transfer BRI

2. **Tunai**
   - Pembayaran langsung ke Kas Sekolah
   - Tidak perlu upload bukti transfer
   - Tempat bayar otomatis: "Kas Sekolah"

---

## Perubahan yang Dilakukan

### **1. Frontend (Form Pembayaran)**

#### File: `public/student/finance.php`
- ✅ Dropdown metode: Transfer Bank, Tunai
- ✅ Dropdown bank: Hanya BRI
- ✅ Auto-hide bank selection jika pilih Tunai
- ✅ JavaScript toggle: `toggleTempatBayar()`

#### File: `public/admin/finance/detail.php`
- ✅ Dropdown metode: Transfer Bank, Tunai
- ✅ Dropdown bank: Hanya BRI
- ✅ Auto-hide bank selection jika pilih Tunai
- ✅ JavaScript toggle: `toggleTempatBayarAdmin()`

#### File: `public/student/payment_detail.php`
- ✅ Dropdown metode: Transfer Bank, Tunai
- ✅ Dropdown bank: Hanya BRI
- ✅ JavaScript toggle: `toggleBankPayment()`

---

### **2. Backend (OCR Parser)**

#### File: `ocr_system/app/utils/text_parser.py`
- ✅ BANK_PATTERNS: Hanya BRI yang aktif
- ✅ E-wallet patterns tetap ada untuk backward compatibility
- ❌ BCA, Mandiri, BNI, dll dihapus dari validasi

---

## Logika Sistem

### **Transfer Bank:**
```
User pilih "Transfer Bank"
  → Auto-select bank: BRI
  → Wajib upload bukti transfer
  → OCR validasi (cek bank = BRI, nominal, nama)
  → Sistem approve/reject otomatis
```

### **Tunai:**
```
User pilih "Tunai"
  → Bank selection hidden
  → Tempat bayar: "Kas Sekolah"
  → Tidak perlu upload bukti (optional)
  → Admin approve manual
```

---

## JavaScript Toggle Logic

```javascript
function toggleTempatBayar(metode) {
    if (metode === 'Transfer') {
        // Tampilkan bank selection
        // Auto-select BRI
        tempatBayarSelect.value = 'BRI';
        tempatBayarSelect.required = true;
    } else if (metode === 'Tunai') {
        // Sembunyikan bank selection
        // Set tempat = Kas Sekolah
        tempatBayarSelect.value = 'Kas Sekolah';
        tempatBayarSelect.required = false;
    }
}
```

---

## Validasi OCR

### **Bank yang Diterima:**
- ✅ **BRI** (Bank Rakyat Indonesia)
- ✅ E-wallet: DANA, GoPay, OVO (untuk legacy/backward compatibility)
- ❌ BCA, Mandiri, BNI, dll → **REJECT**

### **Contoh Response OCR:**

**Transfer BRI (Valid):**
```json
{
  "bank_name": "BRI",
  "transfer_amount": 200000,
  "decision": "accept",
  "validation_score": 95.5
}
```

**Transfer BCA (Invalid):**
```json
{
  "bank_name": "BCA",
  "decision": "reject",
  "decision_reason": "Bank tidak didukung. Hanya transfer BRI yang diterima."
}
```

---

## Testing

### **Test Case 1: Transfer BRI**
1. Pilih metode: Transfer Bank
2. Bank auto-select: BRI
3. Upload bukti transfer BRI
4. Expected: AUTO APPROVED (jika nominal cocok)

### **Test Case 2: Tunai**
1. Pilih metode: Tunai
2. Bank selection hidden
3. Submit tanpa upload bukti
4. Expected: Pending (Admin approve manual)

### **Test Case 3: Upload Bukti BCA (Should Reject)**
1. Pilih metode: Transfer Bank
2. Upload bukti transfer BCA
3. Expected: AUTO REJECTED (Bank tidak didukung)

---

## Database Schema (Tidak Berubah)

Table: `pembayaran`
```sql
- metode: VARCHAR (Transfer/Tunai)
- tempat_bayar: VARCHAR (BRI/Kas Sekolah)
- bukti_bayar: VARCHAR (path file)
- status: ENUM (valid/pending/tolak)
```

---

## Info Rekening BRI

**Informasi yang harus ditampilkan di form:**
```
Bank: BRI (Bank Rakyat Indonesia)
No. Rekening: [NOMOR_REKENING_SEKOLAH]
Atas Nama: [NAMA_SEKOLAH]
```

> ⚠️ **Catatan:** Update nomor rekening BRI sekolah di form pembayaran!

---

## Migrasi Data Lama

Jika ada data pembayaran lama dengan bank selain BRI:

```sql
-- Lihat pembayaran bank selain BRI/Tunai
SELECT 
    pembayaran_id,
    metode,
    tempat_bayar,
    status
FROM pembayaran
WHERE tempat_bayar NOT IN ('BRI', 'Kas Sekolah')
  AND metode = 'Transfer';

-- Update ke BRI (jika diperlukan)
-- UPDATE pembayaran
-- SET tempat_bayar = 'BRI'
-- WHERE tempat_bayar IN ('BCA', 'MANDIRI', 'BNI', 'BTN');
```

---

**Tanggal Implementasi:** 1 Desember 2025  
**Status:** ✅ Selesai  
**Berlaku untuk:** Admin & Siswa
