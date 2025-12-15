# 🔵 Azure Deployment Guide untuk MyBBA

## ⚠️ PENTING: Mengapa Azure Static Web Apps GAGAL?

### ❌ **Azure Static Web Apps TIDAK COCOK untuk MyBBA**

**Alasan:**
1. Static Web Apps hanya untuk **static content** (HTML, CSS, JS)
2. Tidak support **PHP runtime**
3. Tidak include **MySQL database**
4. Tidak support **custom backend services** (OCR server)

**Stack MyBBA:**
```
❌ PHP 8.2        → Butuh PHP runtime
❌ MySQL          → Butuh database server
❌ FastAPI OCR    → Butuh Python runtime
✅ HTML/CSS/JS    → Hanya ini yang supported
```

---

## ✅ SOLUSI: 3 Options untuk Deploy MyBBA ke Azure

### **Option 1: Azure App Service + Azure Database (EASIEST)**

**Kelebihan:**
- ✅ Native PHP support
- ✅ Easy deployment
- ✅ Managed database
- ✅ Auto-scaling

**Kekurangan:**
- ⚠️ OCR server perlu deploy terpisah
- 💰 Cost: ~$50-100/month

**Architecture:**
```
┌─────────────────────────────────────────────────┐
│ Azure App Service (PHP 8.2)                     │
│ - MyBBA Web Application                         │
│ - Port: 80/443                                  │
└────────┬────────────────────────────────────────┘
         │
         ├─────> Azure Database for MySQL
         │       - dbsekolah database
         │
         └─────> Azure Container Instance (OCR)
                 - FastAPI + PaddleOCR
```

**Setup Steps:**

1. **Create Azure Database for MySQL:**
   ```bash
   az mysql flexible-server create \
     --name mybba-mysql \
     --resource-group mybba-rg \
     --location eastus \
     --admin-user mybbaadmin \
     --admin-password <strong-password> \
     --sku-name Standard_B1ms \
     --tier Burstable \
     --storage-size 32
   ```

2. **Create Azure App Service:**
   ```bash
   az webapp create \
     --name mybba-app \
     --resource-group mybba-rg \
     --plan mybba-plan \
     --runtime "PHP:8.2"
   ```

3. **Configure App Settings:**
   ```bash
   az webapp config appsettings set \
     --name mybba-app \
     --resource-group mybba-rg \
     --settings \
       DB_HOST=mybba-mysql.mysql.database.azure.com \
       DB_USER=mybbaadmin \
       DB_PASSWORD=<password> \
       DB_NAME=dbsekolah \
       OCR_API_URL=http://mybba-ocr.eastus.azurecontainer.io:8000
   ```

4. **Deploy via GitHub Actions:**
   - Use workflow: `.github/workflows/azure-app-service.yml`
   - Add secret: `AZURE_WEBAPP_PUBLISH_PROFILE`

---

### **Option 2: Azure Container Instances (RECOMMENDED)**

**Kelebihan:**
- ✅ Full Docker support
- ✅ Deploy semua services sekaligus
- ✅ Consistent dengan development
- ✅ Easy migration

**Kekurangan:**
- ⚠️ Manual networking setup
- 💰 Cost: ~$40-80/month

**Architecture:**
```
┌─────────────────────────────────────────────────┐
│ Azure Container Instances                       │
│                                                 │
│ ┌─────────────┐ ┌─────────────┐ ┌────────────┐ │
│ │ mybba-web   │ │ mybba-ocr   │ │ mybba-db   │ │
│ │ PHP App     │ │ FastAPI     │ │ MySQL 8.0  │ │
│ └─────────────┘ └─────────────┘ └────────────┘ │
└─────────────────────────────────────────────────┘
```

**Setup Steps:**

1. **Create Azure Container Registry:**
   ```bash
   az acr create \
     --name mybbaregistry \
     --resource-group mybba-rg \
     --sku Basic \
     --admin-enabled true
   ```

2. **Build and Push Images:**
   ```bash
   # Login to ACR
   az acr login --name mybbaregistry
   
   # Build and push web image
   docker build -t mybbaregistry.azurecr.io/mybba-web:latest .
   docker push mybbaregistry.azurecr.io/mybba-web:latest
   
   # Build and push OCR image
   cd ocr_system
   docker build -t mybbaregistry.azurecr.io/mybba-ocr:latest .
   docker push mybbaregistry.azurecr.io/mybba-ocr:latest
   ```

3. **Deploy Container Group:**
   ```bash
   az container create \
     --resource-group mybba-rg \
     --name mybba-containers \
     --image mybbaregistry.azurecr.io/mybba-web:latest \
     --registry-login-server mybbaregistry.azurecr.io \
     --registry-username <username> \
     --registry-password <password> \
     --dns-name-label mybba-app \
     --ports 80
   ```

4. **Deploy via GitHub Actions:**
   - Use workflow: `.github/workflows/azure-container.yml`

---

### **Option 3: Azure Kubernetes Service (AKS) - For Scale**

**Kelebihan:**
- ✅ Production-grade
- ✅ Auto-scaling
- ✅ High availability
- ✅ Full control

**Kekurangan:**
- ⚠️ Complex setup
- ⚠️ Requires K8s knowledge
- 💰 Cost: ~$100-200/month

**Best for:** Large scale deployment (100+ users)

---

## 📋 COMPARISON TABLE

| Feature | App Service | Container Instances | AKS |
|---------|-------------|---------------------|-----|
| **Ease of Setup** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐ |
| **Cost (Monthly)** | $50-100 | $40-80 | $100-200 |
| **PHP Support** | Native ✅ | Via Docker | Via Docker |
| **OCR Support** | Separate deploy | Included ✅ | Included ✅ |
| **Database** | Managed MySQL | Separate deploy | Separate deploy |
| **Scaling** | Auto ✅ | Manual | Auto ✅ |
| **Best For** | Small-Medium | Small-Medium | Enterprise |

---

## 🚀 RECOMMENDED DEPLOYMENT PATH

### **For Your Case (Testing/Small Production):**

**Use: Azure Container Instances**

**Why?**
1. ✅ You already have Docker setup
2. ✅ Deploy all services together
3. ✅ Cost-effective
4. ✅ Easy to maintain

**Steps:**

```powershell
# 1. Create Azure resources
az group create --name mybba-rg --location eastus

# 2. Create Container Registry
az acr create --name mybbaregistry --resource-group mybba-rg --sku Basic

# 3. Push your Docker images (already built locally)
docker tag mybba-web mybbaregistry.azurecr.io/mybba-web:latest
docker tag mybba-ocr mybbaregistry.azurecr.io/mybba-ocr:latest
docker push mybbaregistry.azurecr.io/mybba-web:latest
docker push mybbaregistry.azurecr.io/mybba-ocr:latest

# 4. Deploy using docker-compose.azure.yml (see below)
```

---

## 📝 SETUP FILES

### **1. GitHub Actions Workflow**

Already created at: `.github/workflows/azure-container.yml`

### **2. Azure CLI Commands Script**

```bash
#!/bin/bash
# deploy-to-azure.sh

# Configuration
RESOURCE_GROUP="mybba-rg"
LOCATION="eastus"
ACR_NAME="mybbaregistry"
MYSQL_SERVER="mybba-mysql"

# Create resource group
az group create --name $RESOURCE_GROUP --location $LOCATION

# Create Container Registry
az acr create \
  --name $ACR_NAME \
  --resource-group $RESOURCE_GROUP \
  --sku Basic \
  --admin-enabled true

# Get ACR credentials
ACR_USERNAME=$(az acr credential show --name $ACR_NAME --query username -o tsv)
ACR_PASSWORD=$(az acr credential show --name $ACR_NAME --query passwords[0].value -o tsv)

# Create MySQL Database
az mysql flexible-server create \
  --name $MYSQL_SERVER \
  --resource-group $RESOURCE_GROUP \
  --location $LOCATION \
  --admin-user mybbaadmin \
  --admin-password <YourStrongPassword> \
  --sku-name Standard_B1ms \
  --tier Burstable \
  --storage-size 32 \
  --version 8.0

# Import database
az mysql flexible-server db create \
  --resource-group $RESOURCE_GROUP \
  --server-name $MYSQL_SERVER \
  --database-name dbsekolah

# Deploy containers
az container create \
  --resource-group $RESOURCE_GROUP \
  --name mybba-app \
  --image $ACR_NAME.azurecr.io/mybba-web:latest \
  --registry-login-server $ACR_NAME.azurecr.io \
  --registry-username $ACR_USERNAME \
  --registry-password $ACR_PASSWORD \
  --dns-name-label mybba-app \
  --ports 80 443 \
  --environment-variables \
    DB_HOST=$MYSQL_SERVER.mysql.database.azure.com \
    DB_USER=mybbaadmin \
    DB_PASSWORD=<YourStrongPassword> \
    DB_NAME=dbsekolah \
    OCR_API_URL=http://mybba-ocr:8000

echo "Deployment complete!"
echo "Access your app at: http://mybba-app.$LOCATION.azurecontainer.io"
```

---

## 🔐 REQUIRED SECRETS (GitHub)

Add these to your GitHub repository secrets:

1. **AZURE_CREDENTIALS** - Service Principal JSON
2. **ACR_USERNAME** - Container Registry username
3. **ACR_PASSWORD** - Container Registry password
4. **DB_HOST** - MySQL server hostname
5. **DB_USER** - Database username
6. **DB_PASSWORD** - Database password
7. **DB_NAME** - Database name (dbsekolah)

---

## 💰 COST ESTIMATION

### **Azure Container Instances (Recommended):**

| Resource | Specs | Monthly Cost |
|----------|-------|--------------|
| Web Container | 1 vCPU, 1.5 GB RAM | ~$30 |
| OCR Container | 2 vCPU, 4 GB RAM | ~$60 |
| MySQL Flexible | Standard_B1ms | ~$20 |
| Storage | 32 GB | ~$5 |
| **TOTAL** | | **~$115/month** |

### **Azure App Service (Alternative):**

| Resource | Specs | Monthly Cost |
|----------|-------|--------------|
| App Service | B1 (1 core, 1.75 GB) | ~$13 |
| MySQL Database | B_Standard_B1ms | ~$20 |
| Container Instance (OCR) | 2 vCPU, 4 GB | ~$60 |
| **TOTAL** | | **~$93/month** |

---

## 🆘 TROUBLESHOOTING

### **Issue: "Deployment failed"**

**Check:**
```bash
# View container logs
az container logs --name mybba-app --resource-group mybba-rg

# Check container status
az container show --name mybba-app --resource-group mybba-rg
```

### **Issue: "Database connection refused"**

**Fix:**
```bash
# Add firewall rule
az mysql flexible-server firewall-rule create \
  --resource-group mybba-rg \
  --name mybba-mysql \
  --rule-name AllowAzureServices \
  --start-ip-address 0.0.0.0 \
  --end-ip-address 0.0.0.0
```

### **Issue: "OCR not accessible"**

**Fix:**
```bash
# Ensure containers are in same network
# Or use Azure Container Apps with built-in networking
```

---

## 📚 NEXT STEPS

1. **Delete Static Web Apps deployment** (not compatible)
2. **Choose deployment option** (Container Instances recommended)
3. **Setup Azure resources** (see commands above)
4. **Configure GitHub secrets**
5. **Push to trigger deployment**
6. **Test and monitor**

---

## 🔗 USEFUL LINKS

- [Azure Container Instances Docs](https://docs.microsoft.com/azure/container-instances/)
- [Azure Database for MySQL](https://docs.microsoft.com/azure/mysql/)
- [Azure Container Registry](https://docs.microsoft.com/azure/container-registry/)
- [GitHub Actions for Azure](https://github.com/Azure/actions)

---

**Created**: December 8, 2025
**Status**: Ready for deployment
