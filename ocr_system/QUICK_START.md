# 🚀 Quick Start Guide - OCR System

## Panduan Cepat Instalasi dan Penggunaan Sistem OCR

---

## ⚡ Quick Install (5 Langkah)

### 1️⃣ Masuk ke Folder OCR System

```powershell
cd F:\laragon\www\mybba\ocr_system
```

### 2️⃣ Install Python Dependencies

```powershell
# Install semua package yang dibutuhkan
pip install -r requirements.txt
```

**Waktu install:** ~5-10 menit (tergantung internet)

### 3️⃣ Setup Environment Variables

```powershell
# Copy file example
copy .env.example .env

# Edit .env dengan text editor
notepad .env
```

**Edit minimal:**
```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=dbsekolah
```

### 4️⃣ Create Database Table

**Opsi A - Via MySQL Command:**
```powershell
mysql -u root dbsekolah < ..\database\create_ocr_validations_table.sql
```

**Opsi B - Via Python:**
```powershell
python -c "from app.core.database import init_db; init_db()"
```

### 5️⃣ Start Server

```powershell
python main.py
```

**Output yang diharapkan:**
```
INFO:     Started server process
INFO:     Uvicorn running on http://0.0.0.0:8000
```

✅ **Server siap digunakan!**

---

## 🧪 Test Installation

### Test 1: Health Check

Buka browser: `http://localhost:8000/`

**Expected response:**
```json
{
  "status": "running",
  "timestamp": "2025-11-13T...",
  "version": "1.0.0",
  "ocr_ready": true,
  "database_connected": true
}
```

### Test 2: API Documentation

Buka: `http://localhost:8000/docs`

Anda akan melihat interactive API documentation (Swagger UI)

---

## 💻 Cara Menggunakan dari PHP

### 1. Upload & Validate Transfer

```php
<?php
require_once 'includes/ocr_helper.php';

// Contoh: setelah upload bukti transfer
$bukti_path = 'uploads/bukti_pembayaran/transfer_12345.jpg';

$ocr_result = processTransferWithOCR(
    $bukti_path,
    'siswa',           // uploader_type: 'admin' atau 'siswa'
    '22211161',        // uploader_id: NIS atau username
    500000,            // expected_amount
    '22211161',        // expected_nis
    'Bagas Pratama',   // expected_nama
    123                // keuangan_id (optional)
);

if ($ocr_result['success'] && $ocr_result['use_ocr']) {
    echo "✅ OCR berhasil!<br>";
    echo "Decision: " . $ocr_result['decision'] . "<br>";
    echo "Score: " . $ocr_result['validation_score'] . "/100<br>";
    
    // Auto decision
    if ($ocr_result['decision'] === 'accept') {
        // Otomatis approve
        $status = 'sudah bayar';
    } elseif ($ocr_result['decision'] === 'reject') {
        // Otomatis reject
        $status = 'ditolak';
    } else {
        // Perlu review manual
        $status = 'pending';
    }
} else {
    echo "❌ OCR service tidak tersedia, fallback ke manual<br>";
    $status = 'pending';
}
?>
```

### 2. Display OCR Result

```php
<!-- Tampilkan badge dan detail OCR -->
<?= getValidationBadgeHTML($ocr_result) ?>
<?= getOCRDetailsHTML($ocr_result) ?>
```

### 3. Manual Approval (Admin)

```php
<?php
// Approve atau reject manual
$result = approveOCRValidation(
    $validation_id,
    'approved',        // atau 'rejected'
    $_SESSION['username'],
    'Bukti valid setelah review manual'
);
?>
```

---

## 📊 Contoh Output OCR

### Input Image:
```
┌─────────────────────────────────┐
│         BCA Mobile              │
│                                 │
│  Transfer Berhasil              │
│  ─────────────────              │
│  Penerima: BAGAS PRATAMA        │
│  No. Rek: 1234567890            │
│  Nominal: Rp 500.000            │
│  Tanggal: 13 Nov 2025 10:30     │
│  Ref: TRX202511130001           │
└─────────────────────────────────┘
```

### OCR Result:
```json
{
  "decision": "accept",
  "validation_score": 92.5,
  "extracted_data": {
    "bank": "BCA",
    "amount": 500000,
    "account_name": "BAGAS PRATAMA",
    "date": "2025-11-13T10:30:00"
  },
  "confidence_scores": {
    "overall_ocr": 0.89,
    "amount_match": 1.0,
    "name_match": 0.85
  }
}
```

---

## 🎯 Decision Logic

### ✅ AUTO ACCEPT
- Validation score >= 85
- Amount match >= 98%
- OCR quality tinggi
- Semua check passed

### ❌ AUTO REJECT
- Validation score < 50
- Amount tidak sesuai (>10% difference)
- OCR quality rendah
- Multiple critical failures

### ⚠️ NEEDS REVIEW
- Score 50-85
- Amount match 95-98%
- Nama tidak match
- Bank tidak terdeteksi

---

## 🔧 Troubleshooting

### Problem: Server tidak bisa start

**Solution:**
```powershell
# Check Python version (min 3.8)
python --version

# Re-install dependencies
pip install -r requirements.txt --force-reinstall

# Check port 8000
netstat -ano | findstr :8000
```

### Problem: Database connection error

**Solution:**
```powershell
# Test database connection
mysql -u root -e "SELECT 1"

# Edit .env file dengan credentials yang benar
notepad .env
```

### Problem: OCR accuracy rendah

**Solution:**
1. Pastikan gambar jelas (min 800px width)
2. Hindari gambar blur atau dark
3. Upload format JPG/PNG dengan kualitas baik
4. Pertimbangkan fine-tuning model

### Problem: Import error PaddleOCR

**Solution:**
```powershell
# Install PaddlePaddle terlebih dahulu
pip install paddlepaddle==2.5.2

# Lalu install PaddleOCR
pip install paddleocr==2.7.0.3
```

---

## 📈 Performance Tips

### 1. Gunakan GPU (Optional)

Edit `.env`:
```
OCR_USE_GPU=True
```

Install GPU version:
```powershell
pip install paddlepaddle-gpu==2.5.2
```

### 2. Adjust Thresholds

Edit `.env` untuk sensitivity:
```
MIN_CONFIDENCE_SCORE=0.70      # Lower = lebih lenient
MIN_AMOUNT_MATCH_THRESHOLD=0.95 # Lower = lebih toleran
```

### 3. Optimize Image Size

```
MAX_IMAGE_SIZE=2048  # Lower = faster, tapi accuracy berkurang
```

---

## 📝 Integration Checklist

- [ ] Install Python dependencies
- [ ] Setup `.env` file
- [ ] Create database table
- [ ] Start FastAPI server
- [ ] Test health check endpoint
- [ ] Include OCR helper di PHP
- [ ] Update upload payment handler
- [ ] Test dengan sample receipt
- [ ] Setup notification untuk review needed
- [ ] Train staff untuk manual review

---

## 🆘 Support

### Check Logs

```powershell
# View application logs
type logs\ocr_system.log
```

### Check Database

```sql
-- View latest validations
SELECT * FROM ocr_validations ORDER BY created_at DESC LIMIT 10;

-- View pending reviews
SELECT * FROM ocr_validations 
WHERE auto_decision = 'review' AND validation_status = 'pending';
```

### Re-run Setup

```powershell
python setup.py
```

---

## 🎓 Next Steps

1. **Test dengan real data** - Upload beberapa bukti transfer asli
2. **Monitor accuracy** - Check berapa % auto accept vs review
3. **Fine-tuning** - Jika accuracy kurang, collect data untuk training
4. **Integration** - Integrate ke semua payment flows
5. **Staff training** - Train admin untuk handle review cases

---

## 📚 Documentation

- Full documentation: `README_OCR.md`
- API docs: `http://localhost:8000/docs`
- Code examples: `tests/test_ocr_system.py`

---

**Developed for SMK BIT Bina Aulia - 2025**
