# Docker + Ngrok Setup Script
# Deploy MyBBA with Docker and expose via ngrok

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║         MyBBA Docker + Ngrok Deployment                   ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Check if containers are running
$containers = docker-compose ps -q
if (-not $containers) {
    Write-Host "⚠️  Docker containers not running!" -ForegroundColor Yellow
    Write-Host "   Please run .\docker-setup.ps1 first" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ Docker containers are running" -ForegroundColor Green
Write-Host ""

# Check ngrok
if (-not (Get-Command ngrok -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Ngrok not found!" -ForegroundColor Red
    Write-Host "   Download from: https://ngrok.com/download" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ Ngrok installed" -ForegroundColor Green
Write-Host ""

Write-Host "🌐 Starting ngrok tunnel for web application..." -ForegroundColor Yellow
Write-Host ""

# Start ngrok for web app (port 8080 - Docker mapped port)
Start-Process powershell -ArgumentList "-NoExit", "-Command", "ngrok http 8080" -WindowStyle Normal

Write-Host "✅ Ngrok tunnel started!" -ForegroundColor Green
Write-Host ""

Write-Host "📋 Configuration:" -ForegroundColor Cyan
Write-Host "   Docker Web:  http://localhost:8080" -ForegroundColor White
Write-Host "   Docker OCR:  http://localhost:8000" -ForegroundColor White
Write-Host "   OCR in App:  http://ocr:8000 (internal Docker network)" -ForegroundColor White
Write-Host ""

Write-Host "🔗 Ngrok URLs:" -ForegroundColor Cyan
Write-Host "   Check ngrok window for public URL" -ForegroundColor White
Write-Host "   Dashboard: http://localhost:4040" -ForegroundColor White
Write-Host ""

Write-Host "✨ Advantages of Docker + Internal Network:" -ForegroundColor Green
Write-Host "   ✅ No need for separate OCR ngrok tunnel" -ForegroundColor White
Write-Host "   ✅ Web app connects to OCR via internal Docker network" -ForegroundColor White
Write-Host "   ✅ Only 1 ngrok tunnel needed (FREE plan compatible)" -ForegroundColor White
Write-Host "   ✅ Faster communication between services" -ForegroundColor White
Write-Host "   ✅ More secure (OCR not exposed to internet)" -ForegroundColor White
Write-Host ""

Write-Host "📝 Access your app at the ngrok URL shown in the new window!" -ForegroundColor Yellow
Write-Host ""
