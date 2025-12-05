# MyBBA Docker Deployment Checklist

## 📋 Pre-Deployment Checklist

### System Requirements
- [ ] Docker Desktop installed (version 20.10+)
- [ ] Docker Compose available (version 2.0+)
- [ ] Minimum 4GB RAM available
- [ ] Minimum 10GB disk space
- [ ] Windows PowerShell 5.1+ or PowerShell Core 7+

### Files Verification
- [ ] `Dockerfile` exists in project root
- [ ] `docker-compose.yml` exists in project root
- [ ] `ocr_system/Dockerfile` exists
- [ ] `public/config.docker.php` exists
- [ ] `.dockerignore` exists
- [ ] Database dump: `database/backups/dbsekolah.sql` exists

### Docker Status
- [ ] Docker Desktop is running
- [ ] Docker daemon accessible: `docker ps` works
- [ ] Docker Compose accessible: `docker-compose version` works
- [ ] No port conflicts:
  - [ ] Port 8080 available
  - [ ] Port 8000 available
  - [ ] Port 3307 available

---

## 🚀 Deployment Steps

### Step 1: Preparation
- [ ] Navigate to project directory: `cd F:\laragon\www\mybba`
- [ ] Verify all files present: `ls Dockerfile, docker-compose.yml`
- [ ] Check database dump size: `ls database/backups/dbsekolah.sql`

### Step 2: Build Phase
- [ ] Run: `docker-compose build` or `.\docker-setup.ps1`
- [ ] Wait for build completion (10-15 minutes first time)
- [ ] Verify no build errors in output
- [ ] Check images created: `docker images | findstr mybba`

### Step 3: Start Services
- [ ] Run: `docker-compose up -d`
- [ ] Wait for containers to start (30-60 seconds)
- [ ] Check status: `docker-compose ps`
- [ ] All containers show "Up" status

### Step 4: Verification

#### Container Status
- [ ] MySQL container running: `mybba-mysql`
- [ ] OCR container running: `mybba-ocr`
- [ ] Web container running: `mybba-web`

#### Service Health
- [ ] MySQL responding: `docker-compose exec mysql mysqladmin ping -h localhost -u root -proot`
- [ ] OCR health check: `curl http://localhost:8000/health`
- [ ] Web responding: `curl http://localhost:8080/mybba`

#### Log Verification
- [ ] MySQL logs show "ready for connections"
- [ ] OCR logs show "Uvicorn running"
- [ ] Web logs show no errors

### Step 5: Functional Testing

#### Database
- [ ] Connect to MySQL: Host=localhost, Port=3307, User=mybba, Pass=mybba123
- [ ] Verify database exists: `SHOW DATABASES;` includes `dbsekolah`
- [ ] Check tables imported: `SHOW TABLES;` returns table list
- [ ] Sample query works: `SELECT * FROM users LIMIT 1;`

#### Web Application
- [ ] Access http://localhost:8080/mybba
- [ ] Login page loads correctly
- [ ] CSS/JS loaded (no 404 errors in browser console)
- [ ] Login with existing credentials works
- [ ] Dashboard displays correctly

#### OCR System
- [ ] OCR API accessible: http://localhost:8000
- [ ] Health endpoint works: http://localhost:8000/health
- [ ] API docs accessible: http://localhost:8000/docs
- [ ] Upload bukti transfer test works
- [ ] OCR validation triggered and returns result

#### Internal Communication
- [ ] Web app can reach MySQL (no database errors)
- [ ] Web app can reach OCR (no connection errors in console)
- [ ] OCR can reach MySQL (no database connection errors)

### Step 6: Performance Check
- [ ] Resource usage acceptable: `docker stats`
- [ ] Memory usage < 3GB total
- [ ] CPU usage stable after startup
- [ ] No container restarts: `docker-compose ps` shows healthy uptime

---

## 🌐 Ngrok Deployment Checklist (Optional)

### Prerequisites
- [ ] Docker containers running successfully
- [ ] Ngrok installed and authenticated
- [ ] Ngrok account active

### Deployment
- [ ] Run: `.\setup_docker_ngrok.ps1`
- [ ] Ngrok tunnel started successfully
- [ ] Copy public URL from ngrok window
- [ ] Access app via ngrok URL
- [ ] Test upload with OCR via ngrok

### Verification
- [ ] External users can access via ngrok URL
- [ ] OCR works via internal Docker network (http://ocr:8000)
- [ ] No CORS errors in browser console
- [ ] Upload and OCR validation works from external network

---

## 🔍 Post-Deployment Verification

### Security
- [ ] Default passwords changed (if production)
- [ ] Unnecessary ports not exposed
- [ ] `.env` file not committed to git
- [ ] File upload permissions correct

### Backup
- [ ] Database backup working: `docker-compose exec mysql mysqldump ...`
- [ ] Uploads volume persisted correctly
- [ ] Logs accessible and rotated

### Monitoring
- [ ] Container logs accessible: `docker-compose logs -f`
- [ ] Health checks responding
- [ ] No error logs accumulating

### Documentation
- [ ] Team knows how to access application
- [ ] Credentials documented securely
- [ ] Restart procedure documented
- [ ] Backup procedure documented

---

## 🆘 Rollback Plan

If deployment fails:

- [ ] Stop containers: `docker-compose down`
- [ ] Check logs: `docker-compose logs`
- [ ] Identify error in logs
- [ ] Fix configuration
- [ ] Rebuild if needed: `docker-compose build --no-cache`
- [ ] Retry deployment

If critical failure:
- [ ] Restore original config.php: `cp public/config.php.backup public/config.php`
- [ ] Use Laragon setup instead
- [ ] Document issue for troubleshooting

---

## ✅ Sign-off

Deployment completed by: __________________

Date: __________________

Verification notes:
_______________________________________________________
_______________________________________________________
_______________________________________________________

Issues encountered:
_______________________________________________________
_______________________________________________________
_______________________________________________________

---

## 📞 Support Contacts

- Documentation: `DOCKER_DEPLOYMENT.md`
- Quick Start: `DOCKER_QUICK_START.txt`
- Troubleshooting: See "Troubleshooting" section in docs
- Scripts: `docker-setup.ps1`, `setup_docker_ngrok.ps1`
