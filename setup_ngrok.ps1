# MyBBA Ngrok Setup Script
# Run this script to setup environment for ngrok deployment

Write-Host "================================" -ForegroundColor Cyan
Write-Host "  MyBBA Ngrok Setup Assistant  " -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Function to test if ngrok is installed
function Test-Ngrok {
    try {
        $null = Get-Command ngrok -ErrorAction Stop
        return $true
    } catch {
        return $false
    }
}

# Function to test if Python is running
function Test-PythonServer {
    param([int]$Port)
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:$Port/health" -UseBasicParsing -TimeoutSec 3
        return $response.StatusCode -eq 200
    } catch {
        return $false
    }
}

# Check prerequisites
Write-Host "🔍 Checking prerequisites..." -ForegroundColor Yellow

if (-not (Test-Ngrok)) {
    Write-Host "❌ Ngrok not found!" -ForegroundColor Red
    Write-Host "   Download from: https://ngrok.com/download" -ForegroundColor Yellow
    Write-Host "   Or install via: choco install ngrok" -ForegroundColor Yellow
    exit 1
}
Write-Host "✅ Ngrok installed" -ForegroundColor Green

# Check if OCR server is running
Write-Host ""
Write-Host "🔍 Checking OCR server..." -ForegroundColor Yellow
if (Test-PythonServer -Port 8000) {
    Write-Host "✅ OCR Server is running on port 8000" -ForegroundColor Green
} else {
    Write-Host "⚠️  OCR Server not detected on port 8000" -ForegroundColor Yellow
    Write-Host "   Please start it first:" -ForegroundColor Yellow
    Write-Host "   cd F:\laragon\www\mybba\ocr_system" -ForegroundColor Cyan
    Write-Host "   python main.py" -ForegroundColor Cyan
    Write-Host ""
    $continue = Read-Host "Continue anyway? (y/n)"
    if ($continue -ne "y") {
        exit 0
    }
}

# Setup ngrok for OCR
Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "  STEP 1: Expose OCR Server" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Starting ngrok tunnel for OCR server (port 8000)..." -ForegroundColor Yellow
Write-Host "Please wait..." -ForegroundColor Yellow
Write-Host ""

# Start ngrok in background job
$ocrJob = Start-Job -ScriptBlock {
    ngrok http 8000 --log=stdout
}

# Wait for ngrok to start
Start-Sleep -Seconds 3

# Get ngrok URL from API
try {
    $tunnels = Invoke-RestMethod -Uri "http://localhost:4040/api/tunnels" -UseBasicParsing
    $ocrUrl = $tunnels.tunnels[0].public_url
    
    if ($ocrUrl) {
        Write-Host "✅ OCR Server exposed!" -ForegroundColor Green
        Write-Host "   Ngrok URL: $ocrUrl" -ForegroundColor Cyan
        Write-Host ""
        
        # Set environment variable
        Write-Host "Setting environment variable..." -ForegroundColor Yellow
        [System.Environment]::SetEnvironmentVariable("OCR_API_URL", $ocrUrl, "Process")
        $env:OCR_API_URL = $ocrUrl
        
        Write-Host "✅ Environment variable set: OCR_API_URL=$ocrUrl" -ForegroundColor Green
        Write-Host ""
        
        # Save to file for persistence
        $configPath = "F:\laragon\www\mybba\.env.ngrok"
        "OCR_API_URL=$ocrUrl" | Out-File -FilePath $configPath -Encoding UTF8
        Write-Host "✅ Saved to: $configPath" -ForegroundColor Green
        
    } else {
        throw "Could not get ngrok URL"
    }
} catch {
    Write-Host "❌ Failed to get ngrok URL" -ForegroundColor Red
    Write-Host "   Error: $_" -ForegroundColor Red
    Write-Host "   Please check ngrok manually at: http://localhost:4040" -ForegroundColor Yellow
    Stop-Job -Job $ocrJob
    Remove-Job -Job $ocrJob
    exit 1
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "  STEP 2: Restart Apache" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "⚠️  IMPORTANT: You need to restart Apache/Laragon" -ForegroundColor Yellow
Write-Host "   to apply the environment variable" -ForegroundColor Yellow
Write-Host ""
Write-Host "Options:" -ForegroundColor Cyan
Write-Host "  1. Restart Laragon (Recommended)" -ForegroundColor White
Write-Host "  2. Restart Apache service" -ForegroundColor White
Write-Host ""

$restart = Read-Host "Have you restarted Apache/Laragon? (y/n)"
if ($restart -ne "y") {
    Write-Host ""
    Write-Host "Please restart Apache/Laragon, then run this script again" -ForegroundColor Yellow
    Write-Host "or manually expose web app with:" -ForegroundColor Yellow
    Write-Host "  ngrok http 80" -ForegroundColor Cyan
    exit 0
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "  STEP 3: Expose Web App" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Now exposing web app (port 80)..." -ForegroundColor Yellow
Write-Host ""
Write-Host "⚠️  A new terminal will open for the web tunnel" -ForegroundColor Yellow
Write-Host "   Keep both terminals running!" -ForegroundColor Yellow
Write-Host ""

Start-Sleep -Seconds 2

# Start second ngrok in new terminal
Start-Process powershell -ArgumentList "-NoExit", "-Command", "ngrok http 80"

Write-Host ""
Write-Host "✅ Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Cyan
Write-Host "  1. Check the new terminal for Web App URL" -ForegroundColor White
Write-Host "  2. Access your app at: https://[your-url].ngrok-free.app/mybba" -ForegroundColor White
Write-Host "  3. Test upload bukti transfer" -ForegroundColor White
Write-Host ""
Write-Host "🔗 Useful URLs:" -ForegroundColor Cyan
Write-Host "  OCR Server: $ocrUrl" -ForegroundColor White
Write-Host "  Ngrok Dashboard: http://localhost:4040" -ForegroundColor White
Write-Host ""
Write-Host "⚠️  Keep both PowerShell windows open!" -ForegroundColor Yellow
Write-Host "   Closing them will stop the tunnels" -ForegroundColor Yellow
Write-Host ""

# Keep this window open
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# Cleanup
Stop-Job -Job $ocrJob -ErrorAction SilentlyContinue
Remove-Job -Job $ocrJob -ErrorAction SilentlyContinue
