# ❓ FAQ - Frequently Asked Questions

## 📱 Deployment & Access

### Q: Bagaimana cara membuat aplikasi ini bisa diakses oleh orang lain?

**A:** Ada beberapa cara:

1. **Untuk Testing/Demo (Gratis):**
   - Gunakan **Ngrok** - lihat [SETUP_NGROK.md](SETUP_NGROK.md)
   - Setup waktu: ~5 menit
   - URL berubah setiap restart

2. **Untuk Production dengan Domain:**
   - Deploy ke VPS (DigitalOcean, AWS, dll)
   - Lihat panduan lengkap: [DEPLOYMENT.md](DEPLOYMENT.md)
   - Setup waktu: ~30 menit
   - Biaya: mulai dari $5/bulan

3. **Menggunakan Docker (Mudah):**
   - Lihat [DOCKER_GUIDE.md](DOCKER_GUIDE.md)
   - Setup waktu: ~10 menit

### Q: Apakah bisa menggunakan domain sendiri?

**A:** Ya! Ada 2 cara:

1. **Domain + VPS:**
   - Beli domain (Niagahoster, Namecheap, dll)
   - Sewa VPS ($5/bulan)
   - Pointing domain ke IP VPS
   - Install SSL gratis dengan Let's Encrypt
   - Panduan: [DEPLOYMENT.md](DEPLOYMENT.md) bagian "Konfigurasi Domain"

2. **Domain + Shared Hosting:**
   - Beli paket hosting + domain (~100k/tahun)
   - Upload via cPanel
   - SSL sudah included
   - Panduan: [DEPLOYMENT.md](DEPLOYMENT.md) bagian "Shared Hosting"

### Q: Berapa biaya untuk deploy ke production?

**A:** Tergantung pilihan:

| Option | Biaya | Kelebihan | Kekurangan |
|--------|-------|-----------|------------|
| Ngrok | Gratis | Cepat, mudah | URL berubah, tidak untuk production |
| Shared Hosting | 10k-50k/bulan | Mudah, support bagus | Performance terbatas |
| VPS | 75k-150k/bulan | Kontrol penuh, scalable | Perlu setup manual |
| Cloud (Railway, Render) | Gratis-$10/bulan | Auto-deploy, mudah | Limited free tier |

### Q: Bisa diakses tanpa domain, hanya dengan IP?

**A:** Ya! Setelah deploy ke VPS:
```
http://123.45.67.89  # Ganti dengan IP VPS Anda
```

Tapi lebih baik gunakan domain untuk profesionalisme dan SEO.

---

## 🔐 Security & Login

### Q: Bagaimana cara mengubah password default?

**A:** Login sebagai admin, masuk ke profile/settings, ubah password. Atau via database:

```sql
mysql -u root -p dbsekolah
UPDATE users SET password = MD5('password_baru') WHERE username = 'admin';
```

### Q: Apakah aman untuk production?

**A:** Ya, JIKA Anda:
- ✅ Ubah password default admin & database
- ✅ Gunakan HTTPS/SSL
- ✅ Set permission file dengan benar
- ✅ Update sistem & software secara rutin
- ✅ Backup database berkala

Lihat checklist security di [DEPLOYMENT.md](DEPLOYMENT.md)

### Q: Bagaimana cara backup data?

**A:** 
```bash
# Backup database
mysqldump -u root -p dbsekolah > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf mybba_files_$(date +%Y%m%d).tar.gz public/uploads/

# Setup auto backup (crontab)
crontab -e
# Add: 0 2 * * * /path/to/backup-script.sh
```

---

## 🛠️ Technical Issues

### Q: Website tidak bisa diakses setelah deploy

**A:** Check:
1. Service running: `sudo systemctl status nginx php8.2-fpm mysql`
2. Firewall: `sudo ufw allow 'Nginx Full'`
3. DNS pointing ke IP yang benar
4. Logs: `sudo tail -f /var/log/nginx/mybba_error.log`

Lihat [TROUBLESHOOTING.md](TROUBLESHOOTING.md) untuk solusi lengkap.

### Q: Upload file tidak berfungsi

**A:** 
```bash
# Fix permissions
sudo chmod -R 775 /var/www/mybba/public/uploads
sudo chown -R www-data:www-data /var/www/mybba/public/uploads

# Increase upload limit
# Edit /etc/php/8.2/fpm/php.ini:
upload_max_filesize = 10M
post_max_size = 10M

sudo systemctl restart php8.2-fpm nginx
```

### Q: Database connection error

**A:**
```bash
# Check MySQL running
sudo systemctl status mysql

# Test connection
mysql -u mybba_user -p -h localhost dbsekolah

# Check config.php credentials
cat /var/www/mybba/public/config.php | grep DB_
```

---

## 🐳 Docker Questions

### Q: Kenapa pilih Docker?

**A:** Kelebihan Docker:
- ✅ Setup konsisten (works everywhere)
- ✅ Isolasi environment
- ✅ Mudah di-scale
- ✅ Mudah rollback
- ✅ Deployment cepat

### Q: Bagaimana cara update aplikasi di Docker?

**A:**
```bash
cd /path/to/mybba
git pull
docker-compose up -d --build
```

### Q: Docker vs Manual deployment, mana lebih baik?

**A:**

**Gunakan Docker jika:**
- Ingin deployment mudah dan cepat
- Perlu environment yang konsisten
- Planning untuk scale up
- Familiar dengan Docker

**Gunakan Manual jika:**
- Perlu kontrol penuh server
- VPS resource terbatas
- Tidak familiar dengan Docker
- Shared hosting

---

## 💰 Cost & Scaling

### Q: Berapa biaya server untuk 100 siswa?

**A:** 
- VPS 1GB RAM: ~75k/bulan (cukup untuk 100-500 concurrent users)
- Database space: ~500MB untuk 1000 siswa
- Bandwidth: ~10GB/bulan untuk usage normal

**Recommended:**
- Start: VPS $5/bulan (1GB RAM)
- Medium (500 siswa): VPS $10/bulan (2GB RAM)
- Large (1000+ siswa): VPS $20/bulan (4GB RAM)

### Q: Bagaimana cara scale untuk banyak user?

**A:** 
1. **Vertical scaling** (upgrade VPS):
   - Tambah RAM & CPU
   - Lebih mudah, tapi ada limit

2. **Horizontal scaling** (multiple servers):
   - Load balancer + multiple web servers
   - Separate database server
   - CDN untuk static files
   - Lebih kompleks, unlimited scale

3. **Optimization**:
   - Enable caching (Redis, Memcached)
   - Optimize database queries
   - Use CDN untuk images
   - Enable gzip compression

---

## 🔄 Updates & Maintenance

### Q: Bagaimana cara update aplikasi?

**A:**

**Manual deployment:**
```bash
cd /var/www/mybba
git pull origin main
sudo systemctl restart nginx
```

**Docker:**
```bash
git pull
docker-compose up -d --build
```

### Q: Berapa sering perlu maintenance?

**A:**
- **Backup database:** Harian (otomatis)
- **Update sistem:** Mingguan
- **Update aplikasi:** Saat ada update baru
- **Check logs:** Weekly
- **Security audit:** Monthly

### Q: Bagaimana cara monitoring uptime?

**A:** Gunakan service monitoring:
- **Gratis:** UptimeRobot, StatusCake
- **Berbayar:** Pingdom, New Relic

Setup alert via email/SMS jika website down.

---

## 🎓 Learning & Support

### Q: Saya tidak paham technical, bisa deploy sendiri?

**A:** Ya! Pilihan mudah:

1. **Shared Hosting** - Paling mudah
   - Tinggal upload via cPanel
   - Support 24/7 dari hosting
   - Panduan lengkap di [DEPLOYMENT.md](DEPLOYMENT.md)

2. **Managed VPS** - Lebih mudah dari VPS biasa
   - Server maintenance di-handle provider
   - Contoh: DigitalOcean + Cloudways

3. **Hire Developer** - Bayar sekali untuk setup
   - Budget: 500k-1jt untuk full setup
   - Setelah itu Anda bisa maintain sendiri

### Q: Dimana bisa belajar lebih lanjut?

**A:** Resources:
- 📖 [DOCS.md](DOCS.md) - Technical documentation
- 📖 [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide
- 🐳 [DOCKER_GUIDE.md](DOCKER_GUIDE.md) - Docker tutorial
- 🔧 [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Problem solving
- 🎥 YouTube: Search "deploy php mysql to vps"
- 📚 DigitalOcean Tutorials
- 💬 Community: GitHub Discussions

### Q: Bisa minta bantuan setup?

**A:** Ya! Beberapa opsi:

1. **Community Support (Gratis):**
   - Buat issue di GitHub
   - Join grup/forum

2. **Paid Support:**
   - Hire freelancer di Fiverr/Upwork
   - Contact developer langsung

3. **Managed Service:**
   - Bayar hosting yang include setup
   - Contoh: Managed WordPress hosting (tapi perlu adaptasi)

---

## 🌐 Fitur & Customization

### Q: Bisa ditambah fitur baru?

**A:** Ya! Project ini open source. Anda bisa:
- Fork repository
- Tambah fitur sendiri
- Submit pull request untuk kontribusi
- Hire developer untuk custom features

### Q: Bisa multi-sekolah (multi-tenant)?

**A:** Tidak by default, tapi bisa dimodifikasi:
1. Tambah tabel `schools`
2. Update semua query dengan filter school_id
3. Update authentication & authorization
4. Update UI untuk pilih sekolah

Atau deploy multiple instances (1 server per sekolah).

### Q: Ada mobile app?

**A:** Belum, tapi:
- Website sudah responsive (mobile-friendly)
- Bisa diakses via mobile browser
- Bisa wrap dengan Cordova/React Native jika perlu

---

## 🆘 Emergency

### Q: Website down, apa yang harus dilakukan?

**A:** Quick checklist:
```bash
# 1. Check services
sudo systemctl status nginx php8.2-fpm mysql

# 2. Restart services
sudo systemctl restart nginx php8.2-fpm mysql

# 3. Check logs
sudo tail -100 /var/log/nginx/mybba_error.log

# 4. Check disk space
df -h

# 5. Check memory
free -m
```

Jika masih error, lihat [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

### Q: Data hilang, ada backup?

**A:** 
1. Check backup folder: `/backups/` atau lokasi backup Anda
2. Restore dari backup terakhir
3. Jika tidak ada backup, coba MySQL recovery
4. Contact hosting support

**Prevention:** Setup auto-backup SEKARANG!

---

## 💡 Best Practices

### Q: Apa yang harus dilakukan setelah deploy?

**A:** Security checklist:
- [ ] Ubah password admin default
- [ ] Ubah password database
- [ ] Setup SSL/HTTPS
- [ ] Setup auto-backup
- [ ] Setup monitoring
- [ ] Update DNS records
- [ ] Test semua fitur
- [ ] Setup email notifications
- [ ] Document credentials (simpan aman!)
- [ ] Create recovery plan

---

**Masih ada pertanyaan?**

- 🐛 [Report Issue](https://github.com/dadannf/mybba/issues)
- 💬 [Discussions](https://github.com/dadannf/mybba/discussions)
- 📧 Email: support@mybba.com (if available)

*Last Updated: December 2025*
