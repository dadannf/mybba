# 🐳 Docker Quick Start Guide

Panduan cepat deploy MyBBA menggunakan Docker.

## Prerequisites

- Docker installed
- Docker Compose installed
- 2GB RAM minimum
- 10GB disk space

## Quick Start (5 Menit)

### 1. Clone Repository

```bash
git clone https://github.com/dadannf/mybba.git
cd mybba
```

### 2. Setup Environment

```bash
# Copy environment file
cp .env.production .env

# Edit sesuai kebutuhan (opsional)
nano .env
```

### 3. Start Services

```bash
# Build dan start semua services
docker-compose up -d

# Check status
docker-compose ps
```

### 4. Import Database

```bash
# Wait for MySQL to be ready (30 seconds)
sleep 30

# Import database
docker-compose exec mysql mysql -u root -proot_password dbsekolah < database/backups/dbsekolah.sql
```

### 5. Access Application

```
🌐 Web Application:  http://localhost:8080
📊 phpMyAdmin:       http://localhost:8081
🤖 OCR API:          http://localhost:8000
```

**Default Login:**
- Admin: `admin` / `admin123`
- Siswa: `siswa001` / `siswa123`

## Services

### Web Application (Port 8080)
- PHP 8.2 + Nginx
- Auto-restart enabled
- Volumes mounted for hot reload

### MySQL Database (Port 3306)
- MySQL 8.0
- Data persisted in volume
- Auto-backup enabled

### OCR Service (Port 8000)
- Python FastAPI
- PaddleOCR integration
- Optional service

### phpMyAdmin (Port 8081)
- Database management UI
- Connected to MySQL automatically

## Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f
docker-compose logs -f web       # Web app logs only
docker-compose logs -f mysql     # MySQL logs only

# Restart services
docker-compose restart

# Rebuild after code changes
docker-compose up -d --build

# Check service status
docker-compose ps

# Execute command in container
docker-compose exec web sh
docker-compose exec mysql mysql -u root -p

# Remove all containers and volumes
docker-compose down -v
```

## Configuration

### Environment Variables

Edit `.env` file:

```env
# Database
DB_HOST=mysql
DB_USER=mybba_user
DB_PASS=mybba_password
DB_NAME=dbsekolah

# OCR API
OCR_API_URL=http://ocr:8000

# Application
APP_ENV=production
```

### Port Mapping

Edit `docker-compose.yml` untuk ubah port:

```yaml
services:
  web:
    ports:
      - "8080:80"  # Change 8080 to your preferred port
```

## Production Deployment

### With Custom Domain

```bash
# Update docker-compose.yml
services:
  web:
    ports:
      - "80:80"
    environment:
      - VIRTUAL_HOST=yourdomain.com
```

### With SSL (Nginx Proxy + Let's Encrypt)

```bash
# Install nginx-proxy
docker run -d -p 80:80 -p 443:443 \
  --name nginx-proxy \
  -v /var/run/docker.sock:/tmp/docker.sock:ro \
  nginxproxy/nginx-proxy

# Install Let's Encrypt companion
docker run -d \
  --name nginx-proxy-letsencrypt \
  --volumes-from nginx-proxy \
  -v /var/run/docker.sock:/var/run/docker.sock:ro \
  nginxproxy/acme-companion

# Update docker-compose.yml
services:
  web:
    environment:
      - VIRTUAL_HOST=yourdomain.com
      - LETSENCRYPT_HOST=yourdomain.com
      - LETSENCRYPT_EMAIL=your-email@example.com
```

## Troubleshooting

### Container tidak start

```bash
# Check logs
docker-compose logs

# Check if ports are available
netstat -tulpn | grep :8080

# Remove and recreate
docker-compose down
docker-compose up -d
```

### Database connection error

```bash
# Check MySQL status
docker-compose exec mysql mysql -u root -proot_password -e "SELECT 1"

# Recreate database
docker-compose down -v
docker-compose up -d
```

### Permission denied on uploads

```bash
# Fix permissions
docker-compose exec web chmod -R 775 /var/www/mybba/public/uploads
docker-compose exec web chown -R www-data:www-data /var/www/mybba/public/uploads
```

### Out of disk space

```bash
# Clean up unused images
docker system prune -a

# Remove old volumes
docker volume prune
```

## Backup & Restore

### Backup Database

```bash
# Create backup
docker-compose exec mysql mysqldump -u root -proot_password dbsekolah > backup_$(date +%Y%m%d).sql

# Or use docker-compose
docker-compose exec -T mysql mysqldump -u root -proot_password dbsekolah > backup.sql
```

### Restore Database

```bash
# Restore from backup
docker-compose exec -T mysql mysql -u root -proot_password dbsekolah < backup.sql
```

### Backup Files

```bash
# Backup uploads folder
tar -czf uploads_backup.tar.gz public/uploads/

# Restore uploads
tar -xzf uploads_backup.tar.gz
```

## Performance Tuning

### Increase PHP Memory

Edit `docker/nginx.conf`:

```nginx
location ~ \.php$ {
    fastcgi_param PHP_VALUE "memory_limit=512M";
    ...
}
```

### Increase Upload Size

Edit `docker/nginx.conf`:

```nginx
client_max_body_size 50M;
```

### MySQL Performance

Edit `docker-compose.yml`:

```yaml
services:
  mysql:
    command: --default-authentication-plugin=mysql_native_password
             --max-connections=200
             --innodb-buffer-pool-size=512M
```

## Monitoring

### Resource Usage

```bash
# View container stats
docker stats

# View disk usage
docker system df
```

### Health Checks

```bash
# Check web service
curl http://localhost:8080

# Check OCR service
curl http://localhost:8000/health

# Check MySQL
docker-compose exec mysql mysql -u root -proot_password -e "SELECT 1"
```

## Updates

```bash
# Pull latest code
git pull

# Rebuild and restart
docker-compose up -d --build

# Check if update successful
docker-compose ps
docker-compose logs -f web
```

## Security

### Change Default Passwords

```bash
# Update docker-compose.yml
services:
  mysql:
    environment:
      - MYSQL_ROOT_PASSWORD=new_strong_password
      - MYSQL_PASSWORD=new_user_password
```

### Enable Firewall

```bash
# Allow only specific ports
ufw allow 22/tcp      # SSH
ufw allow 80/tcp      # HTTP
ufw allow 443/tcp     # HTTPS
ufw enable
```

## Support

- 📖 Full Documentation: [DEPLOYMENT.md](DEPLOYMENT.md)
- 🐛 Issues: [GitHub Issues](https://github.com/dadannf/mybba/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/dadannf/mybba/discussions)

---

**Happy Deploying! 🚀**
