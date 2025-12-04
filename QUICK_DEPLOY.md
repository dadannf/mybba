# 🚀 Quick Deployment Cheat Sheet

## Option 1: Ngrok (Testing - 5 menit)
```bash
# Terminal 1: Start app
php -S localhost:8000 -t public

# Terminal 2: Expose via ngrok
ngrok http 8000

# Share URL: https://xxx.ngrok-free.app
```

## Option 2: Docker (Production - 10 menit)
```bash
git clone https://github.com/dadannf/mybba.git
cd mybba
docker-compose up -d
# Access: http://localhost:8080
```

## Option 3: VPS Manual (Production - 30 menit)
```bash
# 1. SSH ke VPS
ssh root@YOUR_SERVER_IP

# 2. Run deploy script
curl -o deploy.sh https://raw.githubusercontent.com/dadannf/mybba/main/deploy.sh
sudo bash deploy.sh

# 3. Setup SSL (optional)
sudo certbot --nginx -d yourdomain.com
```

## Quick Commands

### Start Services
```bash
sudo systemctl start nginx php8.2-fpm mysql
```

### Check Status
```bash
sudo systemctl status nginx php8.2-fpm mysql
```

### View Logs
```bash
sudo tail -f /var/log/nginx/mybba_error.log
```

### Backup Database
```bash
mysqldump -u root -p dbsekolah > backup.sql
```

### Update Application
```bash
cd /var/www/mybba
git pull
sudo systemctl restart nginx
```

## Default Access
- Admin: `admin` / `admin123`
- Siswa: `siswa001` / `siswa123`

⚠️ **PENTING:** Ubah password default setelah deployment!

## Full Documentation
- [DEPLOYMENT.md](DEPLOYMENT.md) - Complete guide
- [DOCKER_GUIDE.md](DOCKER_GUIDE.md) - Docker details
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Problem solving
