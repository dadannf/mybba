# =============================================
# Test OCR dengan Gambar DANA
# =============================================

param(
    [Parameter(Mandatory=$false)]
    [string]$ImagePath = ""
)

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   TEST OCR SYSTEM - GAMBAR DANA" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Jika tidak ada argument, tanya user
if ([string]::IsNullOrEmpty($ImagePath)) {
    Write-Host "CARA PAKAI:" -ForegroundColor Yellow
    Write-Host "  .\test_dana.ps1 'C:\path\to\dana_screenshot.jpg'" -ForegroundColor White
    Write-Host ""
    Write-Host "Atau drag & drop file gambar ke PowerShell dan enter" -ForegroundColor Cyan
    Write-Host ""
    
    $ImagePath = Read-Host "Masukkan path gambar DANA"
    
    # Remove quotes if any
    $ImagePath = $ImagePath.Trim('"', "'")
}

# Validasi file exists
if (-not (Test-Path $ImagePath)) {
    Write-Host ""
    Write-Host "ERROR: File tidak ditemukan!" -ForegroundColor Red
    Write-Host "Path: $ImagePath" -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

# Get file info
$fileInfo = Get-Item $ImagePath
$fileSizeMB = [math]::Round($fileInfo.Length / 1MB, 2)

Write-Host "File Details:" -ForegroundColor Green
Write-Host "  Path: $($fileInfo.FullName)" -ForegroundColor White
Write-Host "  Size: $fileSizeMB MB" -ForegroundColor White
Write-Host "  Type: $($fileInfo.Extension)" -ForegroundColor White
Write-Host ""

# Check file size (max 10MB)
if ($fileInfo.Length -gt 10MB) {
    Write-Host "WARNING: File terlalu besar (>10MB)!" -ForegroundColor Yellow
    Write-Host "OCR mungkin akan timeout atau gagal." -ForegroundColor Yellow
    Write-Host ""
}

Write-Host "Mengirim request ke OCR server..." -ForegroundColor Cyan
Write-Host "Endpoint: http://localhost:8000/api/v1/validate-transfer" -ForegroundColor Gray
Write-Host ""

# Expected data dari screenshot DANA
$expectedData = @{
    expected_amount = '200000'
    expected_nis = '22211161'
    expected_nama = 'AHMAD HILMI FAUZAN'
    uploader_type = 'admin'
    uploader_id = 'admin'
}

Write-Host "Expected Data:" -ForegroundColor Green
Write-Host "  Amount: Rp $($expectedData.expected_amount)" -ForegroundColor White
Write-Host "  Name: $($expectedData.expected_nama)" -ForegroundColor White
Write-Host "  NIS: $($expectedData.expected_nis)" -ForegroundColor White
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

try {
    # Prepare multipart form data
    $boundary = [System.Guid]::NewGuid().ToString()
    $LF = "`r`n"
    
    # Read file content
    $fileContent = [System.IO.File]::ReadAllBytes($ImagePath)
    $fileName = [System.IO.Path]::GetFileName($ImagePath)
    
    # Build multipart body
    $bodyLines = @(
        "--$boundary",
        "Content-Disposition: form-data; name=`"file`"; filename=`"$fileName`"",
        "Content-Type: application/octet-stream$LF",
        [System.Text.Encoding]::GetEncoding("iso-8859-1").GetString($fileContent),
        "--$boundary",
        "Content-Disposition: form-data; name=`"expected_amount`"$LF",
        $expectedData.expected_amount,
        "--$boundary",
        "Content-Disposition: form-data; name=`"expected_nis`"$LF",
        $expectedData.expected_nis,
        "--$boundary",
        "Content-Disposition: form-data; name=`"expected_nama`"$LF",
        $expectedData.expected_nama,
        "--$boundary",
        "Content-Disposition: form-data; name=`"uploader_type`"$LF",
        $expectedData.uploader_type,
        "--$boundary",
        "Content-Disposition: form-data; name=`"uploader_id`"$LF",
        $expectedData.uploader_id,
        "--$boundary--$LF"
    )
    
    $body = $bodyLines -join $LF
    
    # Send request
    $response = Invoke-RestMethod `
        -Uri "http://localhost:8000/api/v1/validate-transfer" `
        -Method Post `
        -ContentType "multipart/form-data; boundary=$boundary" `
        -Body $body `
        -TimeoutSec 60
    
    # Display results
    Write-Host "RESPONSE RECEIVED!" -ForegroundColor Green
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host ""
    
    if ($response.success) {
        $data = $response.data
        
        Write-Host "✅ OCR BERHASIL!" -ForegroundColor Green -BackgroundColor DarkGreen
        Write-Host ""
        
        # Parsed Data
        Write-Host "📋 DATA TERDETEKSI:" -ForegroundColor Yellow
        Write-Host "  🏦 Bank        : $($data.parsed_data.bank_name)" -ForegroundColor White
        Write-Host "  💳 Akun        : $($data.parsed_data.account_number)" -ForegroundColor White
        Write-Host "  👤 Nama        : $($data.parsed_data.account_name)" -ForegroundColor White
        Write-Host "  💰 Nominal     : Rp $([math]::Round($data.parsed_data.transfer_amount, 0).ToString('N0'))" -ForegroundColor White
        Write-Host "  📅 Tanggal     : $($data.parsed_data.transfer_date)" -ForegroundColor White
        Write-Host "  🔖 Referensi   : $($data.parsed_data.reference_number)" -ForegroundColor White
        Write-Host ""
        
        # Confidence Scores
        Write-Host "📊 CONFIDENCE SCORES:" -ForegroundColor Yellow
        $ocrScore = [math]::Round($data.confidence_scores.overall_ocr * 100, 2)
        $detectionScore = [math]::Round($data.confidence_scores.detection * 100, 2)
        $recognitionScore = [math]::Round($data.confidence_scores.recognition * 100, 2)
        $amountScore = [math]::Round($data.confidence_scores.amount_match * 100, 2)
        
        Write-Host "  Overall OCR    : $ocrScore%" -ForegroundColor White
        Write-Host "  Detection      : $detectionScore%" -ForegroundColor White
        Write-Host "  Recognition    : $recognitionScore%" -ForegroundColor White
        Write-Host "  Amount Match   : $amountScore%" -ForegroundColor White
        Write-Host ""
        
        # Decision
        $validationScore = [math]::Round($data.validation_score, 1)
        Write-Host "🎯 DECISION: " -ForegroundColor Yellow -NoNewline
        
        switch ($data.decision) {
            "accept" {
                Write-Host "✅ AUTO APPROVED ($validationScore%)" -ForegroundColor Green
            }
            "reject" {
                Write-Host "❌ AUTO REJECTED ($validationScore%)" -ForegroundColor Red
            }
            default {
                Write-Host "⚠️  NEED REVIEW ($validationScore%)" -ForegroundColor Yellow
            }
        }
        
        Write-Host "💬 Reason: $($data.decision_reason)" -ForegroundColor Gray
        Write-Host ""
        
        # Raw Text Preview
        if ($data.raw_text) {
            Write-Host "📝 RAW TEXT (first 500 chars):" -ForegroundColor Yellow
            Write-Host "--------------------------------------------" -ForegroundColor Gray
            $preview = $data.raw_text.Substring(0, [Math]::Min(500, $data.raw_text.Length))
            Write-Host $preview -ForegroundColor White
            if ($data.raw_text.Length -gt 500) {
                Write-Host "... (+ $($data.raw_text.Length - 500) characters)" -ForegroundColor Gray
            }
            Write-Host ""
        }
        
        Write-Host "============================================" -ForegroundColor Cyan
        Write-Host "✅ TEST SELESAI - OCR BERHASIL" -ForegroundColor Green
        Write-Host "============================================" -ForegroundColor Cyan
        
    } else {
        Write-Host "❌ OCR GAGAL!" -ForegroundColor Red
        Write-Host "Message: $($response.message)" -ForegroundColor Yellow
    }
    
} catch {
    Write-Host ""
    Write-Host "❌ ERROR!" -ForegroundColor Red
    Write-Host "--------------------------------------------" -ForegroundColor Gray
    
    if ($_.Exception.Message -match "Unable to connect") {
        Write-Host "Tidak dapat terhubung ke OCR server!" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Pastikan server berjalan di http://localhost:8000" -ForegroundColor White
        Write-Host ""
        Write-Host "Jalankan di terminal lain:" -ForegroundColor Cyan
        Write-Host "  cd F:\laragon\www\mybba\ocr_system" -ForegroundColor White
        Write-Host "  python -m uvicorn main:app --reload" -ForegroundColor White
    } elseif ($_.Exception.Message -match "timeout") {
        Write-Host "Request timeout (>60 detik)!" -ForegroundColor Yellow
        Write-Host "Server mungkin sibuk atau gambar terlalu besar." -ForegroundColor White
    } else {
        Write-Host $_.Exception.Message -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Detail:" -ForegroundColor Gray
        Write-Host $_.Exception -ForegroundColor White
    }
    
    Write-Host ""
    exit 1
}

Write-Host ""
