# Railway Deployment Script for MyBBA
# PowerShell version for Windows users

$ErrorActionPreference = "Stop"

Write-Host "🚀 Railway Deployment Started..." -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Check if Railway CLI is installed
try {
    railway --version | Out-Null
    Write-Host "✅ Railway CLI found" -ForegroundColor Green
} catch {
    Write-Host "❌ Railway CLI not found!" -ForegroundColor Red
    Write-Host "📥 Install with: npm i -g @railway/cli" -ForegroundColor Yellow
    Write-Host "   Or download from: https://railway.app/cli" -ForegroundColor Yellow
    exit 1
}

# Check required files
Write-Host ""
Write-Host "🔍 Checking required files..." -ForegroundColor Cyan
$requiredFiles = @("Dockerfile", "composer.json", "composer.lock", "railway.toml")
$allFilesExist = $true

foreach ($file in $requiredFiles) {
    if (Test-Path $file) {
        Write-Host "  ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $file not found!" -ForegroundColor Red
        $allFilesExist = $false
    }
}

if (-not $allFilesExist) {
    exit 1
}

# Login to Railway
Write-Host ""
Write-Host "🔐 Logging in to Railway..." -ForegroundColor Cyan
railway login

# Link or create project
Write-Host ""
Write-Host "📂 Railway Project Setup..." -ForegroundColor Cyan
Write-Host "   Choose one:" -ForegroundColor Yellow
Write-Host "   1) Link existing project" -ForegroundColor White
Write-Host "   2) Create new project" -ForegroundColor White
$choice = Read-Host "   Enter choice (1/2)"

if ($choice -eq "1") {
    railway link
} elseif ($choice -eq "2") {
    railway init
} else {
    Write-Host "❌ Invalid choice" -ForegroundColor Red
    exit 1
}

# Check environment variables
Write-Host ""
Write-Host "🔧 Environment Variables Check..." -ForegroundColor Cyan
Write-Host "   Make sure these are set in Railway Dashboard:" -ForegroundColor Yellow
Write-Host "   - DB_HOST" -ForegroundColor White
Write-Host "   - DB_DATABASE" -ForegroundColor White
Write-Host "   - DB_USERNAME" -ForegroundColor White
Write-Host "   - DB_PASSWORD" -ForegroundColor White
Write-Host "   - APP_ENV=production" -ForegroundColor White
Write-Host "   - APP_DEBUG=false" -ForegroundColor White
Write-Host ""
$envReady = Read-Host "   Environment variables configured? (y/n)"

if ($envReady -ne "y") {
    Write-Host "⚠️  Please configure environment variables first" -ForegroundColor Yellow
    Write-Host "   Railway Dashboard → Your Project → Variables" -ForegroundColor White
    exit 1
}

# Deploy
Write-Host ""
Write-Host "🚀 Deploying to Railway..." -ForegroundColor Cyan
railway up

Write-Host ""
Write-Host "✅ Deployment Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📊 Next steps:" -ForegroundColor Cyan
Write-Host "   1. Check logs: railway logs" -ForegroundColor White
Write-Host "   2. Open app: railway open" -ForegroundColor White
Write-Host "   3. Import database:" -ForegroundColor White
Write-Host "      railway run bash" -ForegroundColor Gray
Write-Host "      mysql -h `$DB_HOST -u `$DB_USERNAME -p`$DB_PASSWORD `$DB_DATABASE < database/backups/dbsekolah.sql" -ForegroundColor Gray
Write-Host ""
Write-Host "🎉 Done!" -ForegroundColor Green
