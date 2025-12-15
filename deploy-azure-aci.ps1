# ========================================
# Azure Container Instances Deployment Script
# Project: MyBBA School Payment System
# Date: December 8, 2025
# ========================================

param(
    [Parameter(Mandatory=$false)]
    [string]$ResourceGroup = "mybba-rg",
    
    [Parameter(Mandatory=$false)]
    [string]$Location = "southeastasia",
    
    [Parameter(Mandatory=$false)]
    [string]$ACRName = "mybbaregistry",
    
    [Parameter(Mandatory=$false)]
    [string]$MySQLServer = "mybba-mysql-server",
    
    [Parameter(Mandatory=$false)]
    [string]$DBUser = "mybbaadmin",
    
    [Parameter(Mandatory=$true)]
    [string]$DBPassword
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🚀 MyBBA Azure Container Instances Deployment" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Azure CLI is installed
Write-Host "📋 Checking prerequisites..." -ForegroundColor Yellow
try {
    $azVersion = az --version 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Azure CLI not found"
    }
    Write-Host "✅ Azure CLI installed" -ForegroundColor Green
} catch {
    Write-Host "❌ Azure CLI not installed!" -ForegroundColor Red
    Write-Host "   Install from: https://aka.ms/installazurecliwindows" -ForegroundColor Yellow
    exit 1
}

# Check if Docker is installed
try {
    $dockerVersion = docker --version 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Docker not found"
    }
    Write-Host "✅ Docker installed" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker not installed!" -ForegroundColor Red
    Write-Host "   Install from: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# Login to Azure
Write-Host "🔐 Logging in to Azure..." -ForegroundColor Yellow
az login --only-show-errors
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Azure login failed!" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Logged in to Azure" -ForegroundColor Green
Write-Host ""

# Create Resource Group
Write-Host "📦 Creating resource group: $ResourceGroup" -ForegroundColor Yellow
az group create `
    --name $ResourceGroup `
    --location $Location `
    --output none

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Resource group created" -ForegroundColor Green
} else {
    Write-Host "⚠️  Resource group already exists or creation failed" -ForegroundColor Yellow
}
Write-Host ""

# Create Azure Container Registry
Write-Host "🐳 Creating Azure Container Registry: $ACRName" -ForegroundColor Yellow
az acr create `
    --name $ACRName `
    --resource-group $ResourceGroup `
    --sku Basic `
    --admin-enabled true `
    --output none

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Container Registry created" -ForegroundColor Green
} else {
    Write-Host "⚠️  Container Registry already exists" -ForegroundColor Yellow
}

# Get ACR credentials
Write-Host "🔑 Getting ACR credentials..." -ForegroundColor Yellow
$ACR_USERNAME = az acr credential show --name $ACRName --query username -o tsv
$ACR_PASSWORD = az acr credential show --name $ACRName --query "passwords[0].value" -o tsv
$ACR_LOGIN_SERVER = az acr show --name $ACRName --query loginServer -o tsv

Write-Host "   Registry: $ACR_LOGIN_SERVER" -ForegroundColor Cyan
Write-Host "   Username: $ACR_USERNAME" -ForegroundColor Cyan
Write-Host "✅ ACR credentials retrieved" -ForegroundColor Green
Write-Host ""

# Login to ACR
Write-Host "🔐 Logging in to Container Registry..." -ForegroundColor Yellow
az acr login --name $ACRName --output none
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Logged in to ACR" -ForegroundColor Green
} else {
    Write-Host "❌ ACR login failed!" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Build and push Web image
Write-Host "🏗️  Building Web container image..." -ForegroundColor Yellow
Write-Host "   This may take 5-10 minutes..." -ForegroundColor Cyan

docker build -t "${ACR_LOGIN_SERVER}/mybba-web:latest" .
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Web image build failed!" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Web image built successfully" -ForegroundColor Green

Write-Host "📤 Pushing Web image to ACR..." -ForegroundColor Yellow
docker push "${ACR_LOGIN_SERVER}/mybba-web:latest"
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Web image push failed!" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Web image pushed to ACR" -ForegroundColor Green
Write-Host ""

# Build and push OCR image
Write-Host "🤖 Building OCR container image..." -ForegroundColor Yellow
Write-Host "   This may take 10-15 minutes (downloading PaddleOCR models)..." -ForegroundColor Cyan

Push-Location ocr_system
docker build -t "${ACR_LOGIN_SERVER}/mybba-ocr:latest" .
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    Write-Host "❌ OCR image build failed!" -ForegroundColor Red
    exit 1
}
Write-Host "✅ OCR image built successfully" -ForegroundColor Green

Write-Host "📤 Pushing OCR image to ACR..." -ForegroundColor Yellow
docker push "${ACR_LOGIN_SERVER}/mybba-ocr:latest"
if ($LASTEXITCODE -ne 0) {
    Pop-Location
    Write-Host "❌ OCR image push failed!" -ForegroundColor Red
    exit 1
}
Write-Host "✅ OCR image pushed to ACR" -ForegroundColor Green
Pop-Location
Write-Host ""

# Create Azure Database for MySQL
Write-Host "🗄️  Creating Azure Database for MySQL..." -ForegroundColor Yellow
Write-Host "   This may take 5-10 minutes..." -ForegroundColor Cyan

az mysql flexible-server create `
    --name $MySQLServer `
    --resource-group $ResourceGroup `
    --location $Location `
    --admin-user $DBUser `
    --admin-password $DBPassword `
    --sku-name Standard_B1ms `
    --tier Burstable `
    --storage-size 32 `
    --version 8.0 `
    --public-access 0.0.0.0 `
    --output none

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ MySQL database created" -ForegroundColor Green
} else {
    Write-Host "⚠️  MySQL database already exists or creation failed" -ForegroundColor Yellow
}

# Create database
Write-Host "📊 Creating database: dbsekolah..." -ForegroundColor Yellow
az mysql flexible-server db create `
    --resource-group $ResourceGroup `
    --server-name $MySQLServer `
    --database-name dbsekolah `
    --output none

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Database created" -ForegroundColor Green
} else {
    Write-Host "⚠️  Database already exists" -ForegroundColor Yellow
}

# Configure firewall to allow Azure services
Write-Host "🔒 Configuring firewall rules..." -ForegroundColor Yellow
az mysql flexible-server firewall-rule create `
    --resource-group $ResourceGroup `
    --name $MySQLServer `
    --rule-name AllowAzureServices `
    --start-ip-address 0.0.0.0 `
    --end-ip-address 0.0.0.0 `
    --output none

Write-Host "✅ Firewall configured" -ForegroundColor Green

$DB_HOST = "$MySQLServer.mysql.database.azure.com"
Write-Host "   Database Host: $DB_HOST" -ForegroundColor Cyan
Write-Host ""

# Deploy OCR Container
Write-Host "🚀 Deploying OCR container..." -ForegroundColor Yellow
Write-Host "   This may take 3-5 minutes..." -ForegroundColor Cyan

az container create `
    --resource-group $ResourceGroup `
    --name mybba-ocr `
    --image "${ACR_LOGIN_SERVER}/mybba-ocr:latest" `
    --registry-login-server $ACR_LOGIN_SERVER `
    --registry-username $ACR_USERNAME `
    --registry-password $ACR_PASSWORD `
    --dns-name-label mybba-ocr-$((Get-Random -Minimum 1000 -Maximum 9999)) `
    --ports 8000 `
    --cpu 2 `
    --memory 4 `
    --restart-policy Always `
    --output none

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ OCR container deployed" -ForegroundColor Green
} else {
    Write-Host "❌ OCR container deployment failed!" -ForegroundColor Red
    exit 1
}

# Get OCR container FQDN
$OCR_FQDN = az container show `
    --resource-group $ResourceGroup `
    --name mybba-ocr `
    --query ipAddress.fqdn `
    -o tsv

$OCR_URL = "http://${OCR_FQDN}:8000"
Write-Host "   OCR URL: $OCR_URL" -ForegroundColor Cyan
Write-Host ""

# Wait for OCR container to be ready
Write-Host "⏳ Waiting for OCR container to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

$maxAttempts = 10
$attempt = 0
$ocrReady = $false

while ($attempt -lt $maxAttempts -and -not $ocrReady) {
    $attempt++
    Write-Host "   Attempt $attempt/$maxAttempts..." -ForegroundColor Cyan
    
    try {
        $response = Invoke-WebRequest -Uri "$OCR_URL/health" -TimeoutSec 5 -UseBasicParsing -ErrorAction SilentlyContinue
        if ($response.StatusCode -eq 200) {
            $ocrReady = $true
            Write-Host "✅ OCR container is ready!" -ForegroundColor Green
        }
    } catch {
        Start-Sleep -Seconds 10
    }
}

if (-not $ocrReady) {
    Write-Host "⚠️  OCR container might not be fully ready yet. Continuing anyway..." -ForegroundColor Yellow
}
Write-Host ""

# Deploy Web Container
Write-Host "🌐 Deploying Web container..." -ForegroundColor Yellow
Write-Host "   This may take 3-5 minutes..." -ForegroundColor Cyan

az container create `
    --resource-group $ResourceGroup `
    --name mybba-web `
    --image "${ACR_LOGIN_SERVER}/mybba-web:latest" `
    --registry-login-server $ACR_LOGIN_SERVER `
    --registry-username $ACR_USERNAME `
    --registry-password $ACR_PASSWORD `
    --dns-name-label mybba-app-$((Get-Random -Minimum 1000 -Maximum 9999)) `
    --ports 80 `
    --cpu 1 `
    --memory 1.5 `
    --restart-policy Always `
    --environment-variables `
        DB_HOST=$DB_HOST `
        DB_USER=$DBUser `
        DB_PASSWORD=$DBPassword `
        DB_NAME=dbsekolah `
        OCR_API_URL=$OCR_URL `
    --output none

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Web container deployed" -ForegroundColor Green
} else {
    Write-Host "❌ Web container deployment failed!" -ForegroundColor Red
    exit 1
}

# Get Web container FQDN
$WEB_FQDN = az container show `
    --resource-group $ResourceGroup `
    --name mybba-web `
    --query ipAddress.fqdn `
    -o tsv

$WEB_URL = "http://${WEB_FQDN}"
Write-Host ""

# Summary
Write-Host "========================================" -ForegroundColor Green
Write-Host "✅ DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 MyBBA Application URL:" -ForegroundColor Cyan
Write-Host "   $WEB_URL" -ForegroundColor White
Write-Host ""
Write-Host "🤖 OCR Service URL:" -ForegroundColor Cyan
Write-Host "   $OCR_URL" -ForegroundColor White
Write-Host ""
Write-Host "🗄️  Database Connection:" -ForegroundColor Cyan
Write-Host "   Host: $DB_HOST" -ForegroundColor White
Write-Host "   Database: dbsekolah" -ForegroundColor White
Write-Host "   User: $DBUser" -ForegroundColor White
Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Yellow
Write-Host "   1. Import your database schema to Azure MySQL" -ForegroundColor White
Write-Host "      mysql -h $DB_HOST -u $DBUser -p dbsekolah < database/backups/dbsekolah.sql" -ForegroundColor Gray
Write-Host ""
Write-Host "   2. Test your application:" -ForegroundColor White
Write-Host "      Open: $WEB_URL" -ForegroundColor Gray
Write-Host ""
Write-Host "   3. Monitor containers:" -ForegroundColor White
Write-Host "      az container logs --resource-group $ResourceGroup --name mybba-web" -ForegroundColor Gray
Write-Host "      az container logs --resource-group $ResourceGroup --name mybba-ocr" -ForegroundColor Gray
Write-Host ""
Write-Host "💰 Estimated Monthly Cost: ~$115 USD" -ForegroundColor Yellow
Write-Host ""
Write-Host "========================================" -ForegroundColor Green

# Save deployment info to file
$deploymentInfo = @"
MyBBA Azure Container Instances Deployment
===========================================
Deployment Date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

Web Application URL: $WEB_URL
OCR Service URL: $OCR_URL

Database Host: $DB_HOST
Database Name: dbsekolah
Database User: $DBUser

Resource Group: $ResourceGroup
Location: $Location
Container Registry: $ACR_LOGIN_SERVER

Container Names:
- mybba-web
- mybba-ocr

Useful Commands:
- View web logs: az container logs --resource-group $ResourceGroup --name mybba-web
- View OCR logs: az container logs --resource-group $ResourceGroup --name mybba-ocr
- Restart web: az container restart --resource-group $ResourceGroup --name mybba-web
- Restart OCR: az container restart --resource-group $ResourceGroup --name mybba-ocr
- Delete deployment: az group delete --name $ResourceGroup --yes

"@

$deploymentInfo | Out-File -FilePath "azure-deployment-info.txt" -Encoding UTF8
Write-Host "📄 Deployment info saved to: azure-deployment-info.txt" -ForegroundColor Cyan
Write-Host ""
