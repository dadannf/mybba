# 🐳 MyBBA Docker Deployment Guide

## 📋 Daftar Isi
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Arsitektur Docker](#arsitektur-docker)
- [Struktur Container](#struktur-container)
- [Konfigurasi](#konfigurasi)
- [Deployment Steps](#deployment-steps)
- [Ngrok Integration](#ngrok-integration)
- [Troubleshooting](#troubleshooting)
- [Commands Reference](#commands-reference)

---

## 📦 Prerequisites

### Required Software
1. **Docker Desktop** (Windows/Mac) atau **Docker Engine** (Linux)
   - Download: https://www.docker.com/products/docker-desktop
   - Version: 20.10+

2. **Docker Compose**
   - Usually included with Docker Desktop
   - Version: 2.0+

3. **PowerShell** (Windows) atau **Bash** (Linux/Mac)

### System Requirements
- RAM: Minimum 4GB (Recommended 8GB)
- Disk: 10GB free space
- CPU: 2 cores minimum

---

## 🚀 Quick Start

### Option 1: Automated Setup (RECOMMENDED)

```powershell
# 1. Navigate to project directory
cd F:\laragon\www\mybba

# 2. Run setup script
.\docker-setup.ps1

# 3. Access application
# http://localhost:8080/mybba
```

### Option 2: Manual Setup

```powershell
# 1. Build images
docker-compose build

# 2. Start containers
docker-compose up -d

# 3. Check status
docker-compose ps

# 4. View logs
docker-compose logs -f
```

---

## 🏗️ Arsitektur Docker

```
┌─────────────────────────────────────────────────────────┐
│                    Docker Network                        │
│                   (mybba-network)                        │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   MySQL DB   │  │  OCR Server  │  │   PHP Web    │  │
│  │   Port 3306  │  │  Port 8000   │  │   Port 80    │  │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘  │
│         │                  │                  │          │
└─────────┼──────────────────┼──────────────────┼──────────┘
          │                  │                  │
     Port 3307           Port 8000         Port 8080
          │                  │                  │
    ┌─────▼──────────────────▼──────────────────▼─────┐
    │              Host Machine (Windows)              │
    │                                                   │
    │  Browser ──> http://localhost:8080/mybba         │
    │  API Test ─> http://localhost:8000/health        │
    │  DB Client -> localhost:3307                     │
    └───────────────────────────────────────────────────┘
```

---

## 📦 Struktur Container

### 1. MySQL Container (`mybba-mysql`)
- **Image**: mysql:8.0
- **Port**: 3307 (host) → 3306 (container)
- **Database**: dbsekolah
- **Credentials**:
  - Root Password: `root`
  - User: `mybba`
  - Password: `mybba123`
- **Volume**: `mysql_data` (persistent storage)
- **Init**: Auto-import dari `database/backups/dbsekolah.sql`

### 2. OCR Server Container (`mybba-ocr`)
- **Image**: Custom (Python 3.10 + FastAPI + PaddleOCR)
- **Port**: 8000 (host) → 8000 (container)
- **Framework**: FastAPI + Uvicorn
- **OCR Engine**: PaddleOCR
- **Volumes**:
  - `./ocr_system/logs` → `/app/logs`
  - `./ocr_system/uploads` → `/app/uploads`
- **Health Check**: `http://localhost:8000/health`

### 3. Web Application Container (`mybba-web`)
- **Image**: Custom (PHP 8.2 + Apache)
- **Port**: 8080 (host) → 80 (container)
- **Framework**: Pure PHP (no framework)
- **Volumes**:
  - `./public` → `/var/www/html`
  - `./uploads` → `/var/www/html/uploads`
- **Environment**:
  - `OCR_API_URL=http://ocr:8000` (internal Docker network)

---

## ⚙️ Konfigurasi

### Environment Variables

#### Docker Compose (`.env` file - optional)
```env
# MySQL Configuration
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=dbsekolah
MYSQL_USER=mybba
MYSQL_PASSWORD=mybba123

# OCR Configuration
OCR_DEBUG=false

# Ports (optional - already in docker-compose.yml)
WEB_PORT=8080
OCR_PORT=8000
MYSQL_PORT=3307
```

#### PHP Application (`config.docker.php`)
```php
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_USER', getenv('DB_USER') ?: 'mybba');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'mybba123');
define('DB_NAME', getenv('DB_NAME') ?: 'dbsekolah');
define('OCR_API_URL', getenv('OCR_API_URL') ?: 'http://ocr:8000');
```

---

## 📖 Deployment Steps

### Step 1: Prepare Files

```powershell
# Ensure all Docker files exist
ls Dockerfile
ls docker-compose.yml
ls ocr_system/Dockerfile
ls public/config.docker.php
```

### Step 2: Prepare Database

```powershell
# Ensure database dump exists
ls database/backups/dbsekolah.sql

# If not exists, create it from running MySQL
mysqldump -u root -p dbsekolah > database/backups/dbsekolah.sql
```

### Step 3: Build Images

```powershell
# Build all images
docker-compose build

# Or build specific service
docker-compose build web
docker-compose build ocr
```

**Note**: First build takes 10-15 minutes (downloading base images, Python packages, OCR models)

### Step 4: Start Services

```powershell
# Start all containers in background
docker-compose up -d

# Or start with logs (foreground)
docker-compose up

# Start specific service
docker-compose up -d mysql
```

### Step 5: Verify Deployment

```powershell
# Check container status
docker-compose ps

# Check logs
docker-compose logs -f web
docker-compose logs -f ocr
docker-compose logs -f mysql

# Test web app
curl http://localhost:8080/mybba

# Test OCR API
curl http://localhost:8000/health

# Test database connection
docker-compose exec mysql mysql -u mybba -pmybba123 -e "SHOW DATABASES;"
```

### Step 6: Access Application

1. **Web Application**: http://localhost:8080/mybba
2. **Login Credentials**: (your existing admin/student accounts)
3. **Test OCR**: Upload bukti transfer → should validate automatically

---

## 🌐 Ngrok Integration

### Keuntungan Docker + Ngrok

✅ **Hanya butuh 1 ngrok tunnel** (FREE plan compatible!)  
✅ **OCR internal** - web app connect via Docker network  
✅ **Lebih cepat** - no external HTTP calls for OCR  
✅ **Lebih aman** - OCR tidak exposed ke internet  

### Setup dengan Ngrok

```powershell
# Run automated script
.\setup_docker_ngrok.ps1

# Or manual
ngrok http 8080
```

### Architecture dengan Ngrok

```
Internet
    │
    ▼
Ngrok Tunnel (https://xyz.ngrok-free.app)
    │
    ▼
Docker Web Container (port 8080)
    │
    ├─> MySQL Container (internal network)
    └─> OCR Container (internal network: http://ocr:8000)
```

**Advantage**: OCR API calls dari web app menggunakan internal Docker network `http://ocr:8000`, bukan external URL. Ini berarti:
- ✅ Tidak perlu ngrok tunnel kedua untuk OCR
- ✅ Lebih cepat (no internet round-trip)
- ✅ Lebih aman (OCR tidak exposed)

---

## 🔧 Troubleshooting

### Issue 1: Port Already in Use

**Error**: `Bind for 0.0.0.0:8080 failed: port is already allocated`

**Solution**:
```powershell
# Find process using port
netstat -ano | findstr :8080

# Kill process
taskkill /PID <PID> /F

# Or change port in docker-compose.yml
ports:
  - "8081:80"  # Change 8080 to 8081
```

### Issue 2: MySQL Connection Refused

**Error**: `Connection refused` atau `Can't connect to MySQL`

**Solution**:
```powershell
# Wait for MySQL to fully start (30-60 seconds)
docker-compose logs -f mysql

# Check health
docker-compose exec mysql mysqladmin ping -h localhost -u root -proot

# Restart MySQL
docker-compose restart mysql
```

### Issue 3: OCR Server Not Responding

**Error**: `Failed to fetch http://ocr:8000`

**Solution**:
```powershell
# Check OCR logs
docker-compose logs -f ocr

# Restart OCR
docker-compose restart ocr

# Rebuild OCR image
docker-compose build --no-cache ocr
docker-compose up -d ocr
```

### Issue 4: Permission Denied on Uploads

**Error**: `Permission denied` when uploading files

**Solution**:
```powershell
# Fix permissions in container
docker-compose exec web chown -R www-data:www-data /var/www/html/uploads
docker-compose exec web chmod -R 755 /var/www/html/uploads
```

### Issue 5: Database Not Initialized

**Error**: Database empty, no tables

**Solution**:
```powershell
# Stop containers
docker-compose down

# Remove volume
docker volume rm mybba_mysql_data

# Restart (will re-import SQL)
docker-compose up -d
```

### Issue 6: Build Failed - Out of Memory

**Error**: `Killed` or `Out of memory` during build

**Solution**:
1. Increase Docker memory limit (Docker Desktop → Settings → Resources → Memory)
2. Build services one by one:
   ```powershell
   docker-compose build mysql
   docker-compose build ocr
   docker-compose build web
   ```

---

## 📝 Commands Reference

### Basic Commands

```powershell
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart all services
docker-compose restart

# Stop services but keep data
docker-compose stop

# Start stopped services
docker-compose start
```

### View Logs

```powershell
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f web
docker-compose logs -f ocr
docker-compose logs -f mysql

# Last 100 lines
docker-compose logs --tail=100 web
```

### Container Management

```powershell
# List containers
docker-compose ps

# Execute command in container
docker-compose exec web bash
docker-compose exec mysql mysql -u root -p
docker-compose exec ocr python -c "print('Hello')"

# Restart specific service
docker-compose restart web

# Rebuild and restart
docker-compose up -d --build web
```

### Clean Up

```powershell
# Stop and remove containers
docker-compose down

# Remove containers and volumes
docker-compose down -v

# Remove everything including images
docker-compose down -v --rmi all

# Clean unused Docker resources
docker system prune -a
```

### Database Operations

```powershell
# Backup database
docker-compose exec mysql mysqldump -u mybba -pmybba123 dbsekolah > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u mybba -pmybba123 dbsekolah < backup.sql

# Access MySQL CLI
docker-compose exec mysql mysql -u mybba -pmybba123 dbsekolah
```

### Debugging

```powershell
# Check container resource usage
docker stats

# Inspect container
docker inspect mybba-web

# View container processes
docker-compose top

# Execute bash in container
docker-compose exec web bash
docker-compose exec ocr sh
```

---

## 🚀 Production Deployment

### Option 1: VPS dengan Docker

```bash
# Install Docker on Ubuntu
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Clone repository
git clone <your-repo> mybba
cd mybba

# Run setup
./docker-setup.ps1

# Setup reverse proxy (Nginx)
# See nginx.conf example below
```

### Option 2: Cloud Container Services

#### Google Cloud Run
```bash
# Build image
gcloud builds submit --tag gcr.io/PROJECT-ID/mybba-web

# Deploy
gcloud run deploy mybba --image gcr.io/PROJECT-ID/mybba-web
```

#### AWS ECS / Azure Container Instances
- Upload images to registry
- Create task definitions
- Deploy services

---

## 📊 Performance Tips

1. **MySQL**: Increase `innodb_buffer_pool_size` in production
2. **OCR**: Use `--workers 4` in Uvicorn for better performance
3. **PHP**: Enable OPcache in production
4. **Docker**: Allocate sufficient memory (4GB+)

---

## 🔐 Security Considerations

### For Production:

1. **Change Default Passwords**:
   ```yaml
   MYSQL_ROOT_PASSWORD: <strong-password>
   MYSQL_PASSWORD: <strong-password>
   ```

2. **Don't Expose Unnecessary Ports**:
   ```yaml
   # Remove port mapping for MySQL
   # ports:
   #   - "3307:3306"
   ```

3. **Use Environment Variables**:
   - Never commit secrets to git
   - Use `.env` file (add to `.gitignore`)

4. **CORS Configuration**:
   ```python
   # main.py - restrict origins
   allow_origins=["https://yourdomain.com"]
   ```

5. **HTTPS Only**:
   - Use reverse proxy (Nginx) with SSL
   - Never expose containers directly

---

## ✅ Checklist Deployment

- [ ] Docker Desktop installed and running
- [ ] Database dump available (`database/backups/dbsekolah.sql`)
- [ ] All Docker files present (Dockerfile, docker-compose.yml)
- [ ] Run `.\docker-setup.ps1`
- [ ] Verify all containers running (`docker-compose ps`)
- [ ] Test web access: http://localhost:8080/mybba
- [ ] Test OCR: http://localhost:8000/health
- [ ] Test file upload with OCR validation
- [ ] (Optional) Setup ngrok: `.\setup_docker_ngrok.ps1`

---

**Created**: December 4, 2025  
**Author**: AI Assistant  
**Version**: 1.0
