# Docker Setup Script for MyBBA System
# This script automates the entire Docker deployment process

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║           MyBBA Docker Deployment Setup                  ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Function to check if command exists
function Test-Command {
    param([string]$Command)
    try {
        $null = Get-Command $Command -ErrorAction Stop
        return $true
    } catch {
        return $false
    }
}

# Step 1: Check prerequisites
Write-Host "🔍 Step 1: Checking prerequisites..." -ForegroundColor Yellow
Write-Host ""

if (-not (Test-Command docker)) {
    Write-Host "❌ Docker not found!" -ForegroundColor Red
    Write-Host "   Please install Docker Desktop from: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}
Write-Host "✅ Docker installed" -ForegroundColor Green

if (-not (Test-Command docker-compose)) {
    Write-Host "❌ Docker Compose not found!" -ForegroundColor Red
    Write-Host "   Please install Docker Compose" -ForegroundColor Yellow
    exit 1
}
Write-Host "✅ Docker Compose installed" -ForegroundColor Green

# Check if Docker is running
try {
    docker ps | Out-Null
    Write-Host "✅ Docker daemon is running" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker daemon is not running!" -ForegroundColor Red
    Write-Host "   Please start Docker Desktop" -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# Step 2: Prepare config file
Write-Host "🔧 Step 2: Preparing configuration..." -ForegroundColor Yellow
Write-Host ""

# Backup original config
if (Test-Path "public\config.php") {
    if (-not (Test-Path "public\config.php.backup")) {
        Copy-Item "public\config.php" "public\config.php.backup"
        Write-Host "✅ Backed up original config.php" -ForegroundColor Green
    }
}

# Use Docker config
Copy-Item "public\config.docker.php" "public\config.php" -Force
Write-Host "✅ Applied Docker configuration" -ForegroundColor Green
Write-Host ""

# Step 3: Check database dump
Write-Host "🗄️  Step 3: Checking database dump..." -ForegroundColor Yellow
Write-Host ""

$dbDump = "database\backups\dbsekolah.sql"
if (Test-Path $dbDump) {
    $fileSize = (Get-Item $dbDump).Length / 1MB
    Write-Host "✅ Database dump found ($([math]::Round($fileSize, 2)) MB)" -ForegroundColor Green
} else {
    Write-Host "⚠️  Database dump not found at: $dbDump" -ForegroundColor Yellow
    Write-Host "   Container will start with empty database" -ForegroundColor Yellow
}
Write-Host ""

# Step 4: Build Docker images
Write-Host "🐳 Step 4: Building Docker images..." -ForegroundColor Yellow
Write-Host "   This may take 10-15 minutes on first build..." -ForegroundColor Gray
Write-Host ""

docker-compose build --progress=plain

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Docker build failed!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "✅ Docker images built successfully" -ForegroundColor Green
Write-Host ""

# Step 5: Start containers
Write-Host "🚀 Step 5: Starting containers..." -ForegroundColor Yellow
Write-Host ""

docker-compose up -d

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to start containers!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "✅ All containers started" -ForegroundColor Green
Write-Host ""

# Step 6: Wait for services to be ready
Write-Host "⏳ Step 6: Waiting for services to be ready..." -ForegroundColor Yellow
Write-Host ""

Write-Host "   Waiting for MySQL..." -NoNewline
$maxRetries = 30
$retry = 0
while ($retry -lt $maxRetries) {
    try {
        $result = docker-compose exec -T mysql mysqladmin ping -h localhost -u root -proot 2>&1
        if ($result -match "mysqld is alive") {
            Write-Host " ✅" -ForegroundColor Green
            break
        }
    } catch {}
    Start-Sleep -Seconds 2
    Write-Host "." -NoNewline
    $retry++
}

if ($retry -eq $maxRetries) {
    Write-Host " ❌ Timeout" -ForegroundColor Red
}

Write-Host "   Waiting for OCR Server..." -NoNewline
$retry = 0
while ($retry -lt $maxRetries) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8000/health" -UseBasicParsing -TimeoutSec 2 -ErrorAction SilentlyContinue
        if ($response.StatusCode -eq 200) {
            Write-Host " ✅" -ForegroundColor Green
            break
        }
    } catch {}
    Start-Sleep -Seconds 2
    Write-Host "." -NoNewline
    $retry++
}

if ($retry -eq $maxRetries) {
    Write-Host " ⚠️  Still starting..." -ForegroundColor Yellow
}

Write-Host "   Waiting for Web App..." -NoNewline
$retry = 0
while ($retry -lt $maxRetries) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8080" -UseBasicParsing -TimeoutSec 2 -ErrorAction SilentlyContinue
        if ($response.StatusCode -eq 200 -or $response.StatusCode -eq 302) {
            Write-Host " ✅" -ForegroundColor Green
            break
        }
    } catch {}
    Start-Sleep -Seconds 2
    Write-Host "." -NoNewline
    $retry++
}

if ($retry -eq $maxRetries) {
    Write-Host " ⚠️  Still starting..." -ForegroundColor Yellow
}

Write-Host ""

# Step 7: Show status
Write-Host "📊 Step 7: Container Status" -ForegroundColor Yellow
Write-Host ""

docker-compose ps

Write-Host ""

# Step 8: Success message
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║              🎉 DEPLOYMENT SUCCESSFUL! 🎉                 ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Host "🌐 Access URLs:" -ForegroundColor Cyan
Write-Host "   Web Application:  http://localhost:8080/mybba" -ForegroundColor White
Write-Host "   OCR API:          http://localhost:8000" -ForegroundColor White
Write-Host "   OCR Health Check: http://localhost:8000/health" -ForegroundColor White
Write-Host "   OCR API Docs:     http://localhost:8000/docs" -ForegroundColor White
Write-Host ""

Write-Host "📦 Database Connection:" -ForegroundColor Cyan
Write-Host "   Host:     localhost" -ForegroundColor White
Write-Host "   Port:     3307" -ForegroundColor White
Write-Host "   User:     mybba" -ForegroundColor White
Write-Host "   Password: mybba123" -ForegroundColor White
Write-Host "   Database: dbsekolah" -ForegroundColor White
Write-Host ""

Write-Host "🔧 Useful Commands:" -ForegroundColor Cyan
Write-Host "   View logs:        docker-compose logs -f" -ForegroundColor White
Write-Host "   Stop containers:  docker-compose down" -ForegroundColor White
Write-Host "   Restart:          docker-compose restart" -ForegroundColor White
Write-Host "   Rebuild:          docker-compose up -d --build" -ForegroundColor White
Write-Host ""

Write-Host "📝 Next Steps:" -ForegroundColor Cyan
Write-Host "   1. Access http://localhost:8080/mybba" -ForegroundColor White
Write-Host "   2. Login with your credentials" -ForegroundColor White
Write-Host "   3. Test upload bukti transfer (OCR should work automatically)" -ForegroundColor White
Write-Host ""

Write-Host "💡 For ngrok deployment:" -ForegroundColor Yellow
Write-Host "   Use: .\setup_docker_ngrok.ps1" -ForegroundColor White
Write-Host ""

# Open browser
$openBrowser = Read-Host "Open browser now? (y/n)"
if ($openBrowser -eq "y") {
    Start-Process "http://localhost:8080/mybba"
}

Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
