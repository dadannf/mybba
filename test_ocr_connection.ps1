# Quick Test OCR Connection
# Run this to verify OCR server is accessible

Write-Host "Testing OCR Server Connection..." -ForegroundColor Cyan
Write-Host ""

# Get OCR URL from environment
$ocrUrl = $env:OCR_API_URL
if (-not $ocrUrl) {
    $ocrUrl = "http://localhost:8000"
    Write-Host "⚠️  No OCR_API_URL environment variable found" -ForegroundColor Yellow
    Write-Host "   Using default: $ocrUrl" -ForegroundColor Yellow
}

Write-Host "Testing: $ocrUrl/health" -ForegroundColor Cyan
Write-Host ""

try {
    $response = Invoke-RestMethod -Uri "$ocrUrl/health" -Method Get -TimeoutSec 5
    
    Write-Host "✅ SUCCESS!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Response:" -ForegroundColor Cyan
    $response | ConvertTo-Json -Depth 3 | Write-Host
    Write-Host ""
    
    # Test validate endpoint
    Write-Host "Testing validate endpoint..." -ForegroundColor Cyan
    $testUrl = "$ocrUrl/api/v1/validate-transfer"
    
    try {
        # Just test if endpoint exists (will fail without proper data, that's ok)
        $null = Invoke-WebRequest -Uri $testUrl -Method Post -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    } catch {
        if ($_.Exception.Response.StatusCode -eq 422) {
            Write-Host "✅ Endpoint exists (422 = missing required fields, that's expected)" -ForegroundColor Green
        } else {
            Write-Host "⚠️  Endpoint status: $($_.Exception.Response.StatusCode)" -ForegroundColor Yellow
        }
    }
    
    Write-Host ""
    Write-Host "🎉 OCR Server is accessible!" -ForegroundColor Green
    Write-Host ""
    
} catch {
    Write-Host "❌ FAILED!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    
    if ($ocrUrl -like "*localhost*") {
        Write-Host "💡 Troubleshooting:" -ForegroundColor Yellow
        Write-Host "   1. Is OCR server running?" -ForegroundColor White
        Write-Host "      cd F:\laragon\www\mybba\ocr_system" -ForegroundColor Cyan
        Write-Host "      python main.py" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "   2. Check port 8000:" -ForegroundColor White
        Write-Host "      netstat -ano | findstr :8000" -ForegroundColor Cyan
    } else {
        Write-Host "💡 Troubleshooting:" -ForegroundColor Yellow
        Write-Host "   1. Is ngrok tunnel active?" -ForegroundColor White
        Write-Host "      Check: http://localhost:4040" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "   2. Is OCR server running locally?" -ForegroundColor White
        Write-Host "      Test: http://localhost:8000/health" -ForegroundColor Cyan
    }
    Write-Host ""
    exit 1
}
