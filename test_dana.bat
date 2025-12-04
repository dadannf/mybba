@echo off
REM =============================================
REM Quick Test OCR - DANA Screenshot
REM =============================================

echo ============================================
echo TEST OCR SYSTEM - GAMBAR DANA
echo ============================================
echo.

REM Cek apakah ada argument (path gambar)
if "%~1"=="" (
    echo ERROR: Path gambar tidak diberikan!
    echo.
    echo CARA PAKAI:
    echo   test_dana.bat "C:\path\to\dana_screenshot.jpg"
    echo.
    echo ATAU save gambar di:
    echo   public\uploads\test_ocr\dana.jpg
    echo Lalu jalankan:
    echo   test_dana.bat public\uploads\test_ocr\dana.jpg
    echo.
    pause
    exit /b 1
)

set IMAGE_PATH=%~1

REM Cek apakah file ada
if not exist "%IMAGE_PATH%" (
    echo ERROR: File tidak ditemukan!
    echo Path: %IMAGE_PATH%
    echo.
    pause
    exit /b 1
)

echo File ditemukan: %IMAGE_PATH%
echo.
echo Mengirim request ke OCR server...
echo ============================================
echo.

REM Test dengan curl (PowerShell version)
powershell -Command "& {$response = Invoke-RestMethod -Uri 'http://localhost:8000/api/v1/validate-transfer' -Method Post -Form @{file=Get-Item '%IMAGE_PATH%'; expected_amount='200000'; expected_nis='22211161'; expected_nama='AHMAD HILMI FAUZAN'; uploader_type='admin'; uploader_id='admin'}; $response | ConvertTo-Json -Depth 10}"

echo.
echo ============================================
echo TEST SELESAI
echo ============================================
pause
