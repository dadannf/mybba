# Start OCR Server Script
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Starting OCR Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Changing directory to ocr_system..." -ForegroundColor Yellow
Set-Location "F:\laragon\www\mybba\ocr_system"

Write-Host "Starting Uvicorn server..." -ForegroundColor Yellow
Write-Host "Server will be available at: http://localhost:8000" -ForegroundColor Green
Write-Host ""
Write-Host "⏳ Please wait ~20-30 seconds for PaddleOCR initialization..." -ForegroundColor Magenta
Write-Host "✅ Server is ready when you see: 'Application startup complete'" -ForegroundColor Green
Write-Host ""
Write-Host "Press CTRL+C to stop the server" -ForegroundColor Red
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

python -m uvicorn main:app --host 0.0.0.0 --port 8000 --reload
