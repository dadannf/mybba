# ========================================
# Database Import Script for Azure MySQL
# ========================================

param(
    [Parameter(Mandatory=$false)]
    [string]$MySQLServer = "mybba-mysql-server",
    
    [Parameter(Mandatory=$false)]
    [string]$DBUser = "mybbaadmin",
    
    [Parameter(Mandatory=$true)]
    [string]$DBPassword,
    
    [Parameter(Mandatory=$false)]
    [string]$SQLFile = "database/backups/dbsekolah.sql"
)

$DBHost = "$MySQLServer.mysql.database.azure.com"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "📊 Importing Database to Azure MySQL" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if SQL file exists
if (-not (Test-Path $SQLFile)) {
    Write-Host "❌ SQL file not found: $SQLFile" -ForegroundColor Red
    exit 1
}

Write-Host "✅ SQL file found: $SQLFile" -ForegroundColor Green
Write-Host ""

# Check if mysql client is installed
Write-Host "📋 Checking for MySQL client..." -ForegroundColor Yellow
try {
    $mysqlVersion = mysql --version 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "MySQL client not found"
    }
    Write-Host "✅ MySQL client installed" -ForegroundColor Green
} catch {
    Write-Host "❌ MySQL client not installed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "📥 Please install MySQL client:" -ForegroundColor Yellow
    Write-Host "   Option 1: Install MySQL Server from https://dev.mysql.com/downloads/mysql/" -ForegroundColor White
    Write-Host "   Option 2: Use Azure Cloud Shell (bash)" -ForegroundColor White
    Write-Host ""
    Write-Host "Alternative: Import via Azure Portal" -ForegroundColor Yellow
    Write-Host "   1. Go to Azure Portal → Your MySQL server" -ForegroundColor White
    Write-Host "   2. Click 'Import' in the left menu" -ForegroundColor White
    Write-Host "   3. Upload your SQL file" -ForegroundColor White
    exit 1
}
Write-Host ""

# Test connection
Write-Host "🔌 Testing database connection..." -ForegroundColor Yellow
$env:MYSQL_PWD = $DBPassword

$testQuery = "SELECT 1;"
$testResult = echo $testQuery | mysql -h $DBHost -u $DBUser -P 3306 --ssl-mode=REQUIRED dbsekolah 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Database connection successful" -ForegroundColor Green
} else {
    Write-Host "❌ Database connection failed!" -ForegroundColor Red
    Write-Host "   Error: $testResult" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "💡 Troubleshooting:" -ForegroundColor Yellow
    Write-Host "   1. Check if database server is running" -ForegroundColor White
    Write-Host "   2. Verify firewall rules allow your IP" -ForegroundColor White
    Write-Host "   3. Confirm credentials are correct" -ForegroundColor White
    exit 1
}
Write-Host ""

# Import database
Write-Host "📤 Importing database schema and data..." -ForegroundColor Yellow
Write-Host "   This may take 1-5 minutes depending on file size..." -ForegroundColor Cyan
Write-Host ""

mysql -h $DBHost -u $DBUser -P 3306 --ssl-mode=REQUIRED dbsekolah < $SQLFile 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ Database imported successfully!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "❌ Database import failed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "💡 Common issues:" -ForegroundColor Yellow
    Write-Host "   1. SQL file contains errors" -ForegroundColor White
    Write-Host "   2. Incompatible MySQL version" -ForegroundColor White
    Write-Host "   3. Insufficient permissions" -ForegroundColor White
    exit 1
}

# Verify import
Write-Host ""
Write-Host "🔍 Verifying imported data..." -ForegroundColor Yellow

$tables = @"
SHOW TABLES;
"@ | mysql -h $DBHost -u $DBUser -P 3306 --ssl-mode=REQUIRED dbsekolah 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Database verification successful" -ForegroundColor Green
    Write-Host ""
    Write-Host "📊 Tables in database:" -ForegroundColor Cyan
    Write-Host $tables -ForegroundColor White
} else {
    Write-Host "⚠️  Could not verify database tables" -ForegroundColor Yellow
}

# Clean up environment variable
Remove-Item Env:\MYSQL_PWD

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "✅ DATABASE IMPORT COMPLETE!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
