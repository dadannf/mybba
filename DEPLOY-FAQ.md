# ❓ FAQ - Auto Deploy GitHub ke cPanel

Pertanyaan yang sering ditanyakan (Frequently Asked Questions) tentang auto-deployment.

---

## 🔐 Keamanan & Privacy

### Q: Apakah aman menyimpan password FTP di GitHub Secrets?

**A:** Ya, sangat aman! GitHub Secrets di-encrypt dan tidak bisa dilihat oleh siapapun setelah disimpan. Bahkan owner repository tidak bisa melihat isi secret. Secret hanya bisa digunakan oleh GitHub Actions workflow dan tidak pernah terexpose di log.

### Q: Apakah file .env ikut ter-deploy ke cPanel?

**A:** Tidak. File `.env` sudah di-exclude di workflow configuration. File ini tidak akan di-upload ke cPanel untuk keamanan. Anda perlu membuat file `.env` atau konfigurasi database langsung di cPanel.

### Q: Bagaimana cara mengamankan deployment saya?

**A:** 
1. Gunakan password FTP yang kuat
2. Jangan commit file .env atau credentials ke Git
3. Enable HTTPS/SSL di cPanel
4. Restrict FTP user hanya ke folder yang diperlukan
5. Rotate password FTP secara berkala
6. Monitor GitHub Actions logs

---

## ⚙️ Setup & Configuration

### Q: Apakah saya harus punya cPanel hosting?

**A:** Ya. Workflow ini dirancang khusus untuk cPanel hosting dengan FTP access. Jika hosting Anda tidak support FTP atau bukan cPanel, Anda perlu modifikasi workflow atau gunakan metode deployment lain.

### Q: Berapa lama waktu deployment?

**A:** Biasanya 1-2 menit dari push hingga file live di server. Tergantung ukuran file dan kecepatan koneksi FTP.

### Q: Apakah bisa deploy ke subdomain atau addon domain?

**A:** Ya! Edit `server-dir` di workflow file:
- Subdomain: `/public_html/subdomain/`
- Addon domain: `/home/username/namadomain.com/`

### Q: Apakah bisa deploy ke multiple environments (staging, production)?

**A:** Ya! Buat workflow terpisah untuk setiap environment dengan branch dan FTP credentials berbeda. Lihat [DEPLOY-CONFIG.md](DEPLOY-CONFIG.md) untuk contoh.

### Q: Apakah workflow ini otomatis berjalan?

**A:** Ya, setiap kali Anda push ke branch `main`. Anda juga bisa trigger manual via GitHub Actions UI.

---

## 🚨 Troubleshooting

### Q: Workflow saya failed dengan error "Login incorrect", apa yang salah?

**A:** 
1. Verifikasi FTP username dan password di cPanel
2. Cek typo saat copy-paste ke GitHub Secrets
3. Pastikan tidak ada spasi tambahan
4. Update GitHub Secrets dengan credentials yang benar
5. Re-run workflow

### Q: Error "Could not connect to server", solusinya?

**A:**
1. Cek FTP server address (bisa coba IP atau domain)
2. Test FTP connection via FTP client (FileZilla)
3. Pastikan hosting aktif dan tidak maintenance
4. Hubungi provider hosting untuk konfirmasi
5. Cek firewall atau IP blocking

### Q: File tidak terupdate di cPanel setelah deployment, kenapa?

**A:**
1. Clear browser cache (Ctrl+F5)
2. Cek timestamp file di cPanel File Manager
3. Verifikasi workflow status = Success
4. Tunggu 2-3 menit (mungkin cache server)
5. Cek apakah file di-exclude di workflow

### Q: Workflow sukses tapi website error 500, apa masalahnya?

**A:** Error 500 biasanya masalah server-side:
1. Cek PHP error log di cPanel
2. Verify PHP version compatibility
3. Check file permissions (folders: 755, files: 644)
4. Verify database configuration
5. Check .htaccess syntax

### Q: Database tidak connect setelah deployment?

**A:**
1. Pastikan database credentials benar di config file
2. Format database name: `cpanelusername_dbname`
3. Verify user has privileges via phpMyAdmin
4. Test connection langsung dari phpMyAdmin
5. Cek DB_HOST (biasanya `localhost`)

---

## 💰 Biaya & Performa

### Q: Apakah menggunakan GitHub Actions berbayar?

**A:** GitHub Actions gratis untuk public repositories dengan limit 2000 menit/bulan. Untuk private repositories, free tier dapat 2000 menit/bulan. Workflow deployment ini hanya pakai ~1-2 menit per deployment, jadi sangat cukup.

### Q: Apakah deployment ini lambat?

**A:** Tidak. Upload via FTP biasanya cepat (1-2 menit) untuk project ukuran normal. Jika terlalu lambat:
1. Check exclude list (jangan upload file besar yang tidak perlu)
2. Gunakan FTP server terdekat
3. Compress assets jika memungkinkan

### Q: Berapa besar file yang bisa di-deploy?

**A:** Tergantung limit FTP hosting Anda. Umumnya tidak ada masalah untuk project PHP normal. Jika ada file >100MB, pertimbangkan exclude atau upload manual.

---

## 🔄 Workflow Behavior

### Q: Apakah workflow menghapus file yang sudah ada di server?

**A:** Tidak secara default. FTP-Deploy-Action menggunakan mode sync yang:
- Upload file baru
- Update file yang berubah
- **Tidak** delete file yang tidak ada di repository

Jika ingin auto-delete, tambahkan option `dangerous-clean-slate: true` (hati-hati!).

### Q: Bagaimana cara deploy hanya file tertentu?

**A:** Edit workflow, gunakan `local-dir` parameter:
```yaml
local-dir: ./public/  # Deploy hanya folder public
```

### Q: Apakah bisa deploy dari branch selain main?

**A:** Ya! Edit workflow file, bagian `branches`:
```yaml
on:
  push:
    branches:
      - production  # Ganti dengan nama branch Anda
```

### Q: Bagaimana cara pause auto-deployment sementara?

**A:** 
1. Buka repository Settings
2. Actions → General
3. Disable workflows
Atau delete/rename workflow file sementara.

---

## 📁 File & Folder

### Q: File apa saja yang di-deploy?

**A:** Semua file di repository KECUALI yang di-exclude:
- ✅ Deploy: `/public/`, `/config/`, `/database/`, `/uploads/`
- ❌ Exclude: `.git/`, `node_modules/`, `.env`, docs, dll

Lihat file workflow untuk daftar lengkap exclude.

### Q: Bagaimana cara exclude file tambahan?

**A:** Edit workflow file, tambahkan di bagian `exclude:`:
```yaml
exclude: |
  **/.git*
  **/tests/**      # Exclude folder tests
  **/*.md          # Exclude markdown files
```

### Q: Apakah vendor/ atau node_modules/ ikut ter-deploy?

**A:** Tidak, sudah di-exclude. Jika perlu, install dependencies langsung di server atau remove dari exclude list.

---

## 🔧 Advanced Questions

### Q: Bagaimana cara run composer install atau npm install setelah deploy?

**A:** Ada 2 cara:
1. **Run di workflow** (tambahkan step sebelum deploy)
2. **Run di cPanel** via SSH atau cron job

Untuk option 1, tambahkan di workflow:
```yaml
- name: Install dependencies
  run: |
    composer install --no-dev
    npm install
```

### Q: Bagaimana cara automatic database migration?

**A:** Tidak recommended untuk production. Lebih baik:
1. Run migration manual di cPanel
2. Atau gunakan script PHP yang di-trigger manual
3. Backup database sebelum migration

### Q: Apakah bisa deploy ke multiple servers sekaligus?

**A:** Ya! Duplicate step deployment dengan FTP credentials berbeda:
```yaml
- name: Deploy to Server 1
  uses: SamKirkland/FTP-Deploy-Action@v4.3.4
  with:
    server: ${{ secrets.FTP_SERVER_1 }}
    # ...

- name: Deploy to Server 2
  uses: SamKirkland/FTP-Deploy-Action@v4.3.4
  with:
    server: ${{ secrets.FTP_SERVER_2 }}
    # ...
```

### Q: Bagaimana cara rollback jika deployment salah?

**A:** 
1. **Via Git**: Revert commit, push lagi
2. **Via cPanel**: Restore dari backup
3. **Via GitHub**: Re-run workflow dari commit sebelumnya

Best practice: Selalu backup sebelum major changes!

---

## 📊 Monitoring & Logs

### Q: Bagaimana cara melihat log deployment?

**A:** 
1. GitHub: Actions tab → Click workflow → View logs
2. cPanel: Error Log menu → Lihat PHP errors

### Q: Apakah ada notifikasi jika deployment failed?

**A:** Ya! GitHub otomatis send email notification jika workflow failed (jika enabled di Settings).

### Q: Bagaimana cara monitoring uptime website?

**A:** Gunakan tools seperti:
- UptimeRobot (free)
- Pingdom
- StatusCake
- cPanel built-in monitoring (jika ada)

---

## 🆘 Getting Help

### Q: Workflow saya tidak berjalan sama sekali?

**A:** Check:
1. Workflow file ada di `.github/workflows/` folder?
2. Push ke branch yang benar (main)?
3. GitHub Actions enabled di repository settings?
4. Ada syntax error di YAML file?

### Q: Dimana saya bisa minta bantuan?

**A:**
1. **GitHub Issues**: Post issue di repository
2. **Hosting Support**: Contact provider untuk masalah cPanel/FTP
3. **Documentation**: Baca DEPLOY.md, DEPLOY-CONFIG.md
4. **Community**: Stack Overflow atau forum GitHub

---

## 🎓 Learning Resources

### Q: Dimana saya bisa belajar lebih lanjut tentang GitHub Actions?

**A:**
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [FTP-Deploy-Action Repo](https://github.com/SamKirkland/FTP-Deploy-Action)

### Q: Apakah ada alternative untuk FTP deployment?

**A:** Ya:
1. **Git Version Control** (built-in cPanel, jika support)
2. **SSH/SFTP** (lebih aman tapi perlu setup)
3. **Rsync** (lebih cepat untuk large files)
4. **CI/CD tools** (Jenkins, GitLab CI, dll)

---

## ✅ Best Practices

### Q: Apa yang harus saya lakukan sebelum setiap deployment?

**A:** Checklist:
- [ ] Test di local environment
- [ ] Commit dengan descriptive message
- [ ] Review changes sebelum push
- [ ] Backup database jika ada schema changes
- [ ] Monitor Actions tab setelah push
- [ ] Verify website setelah deployment success

### Q: Berapa sering saya harus backup?

**A:**
- **Database**: Daily (automatic via cPanel cron)
- **Files**: Weekly atau sebelum major changes
- **Full backup**: Monthly

---

## 📝 Summary

**Setup time**: 15 menit (one-time)  
**Deployment time**: 1-2 menit (automatic)  
**Cost**: Free (GitHub Actions free tier cukup)  
**Difficulty**: Easy (ikuti DEPLOY.md step-by-step)

---

**Masih ada pertanyaan?** Buka [GitHub Issues](https://github.com/dadannf/mybba/issues) atau baca dokumentasi lengkap di [DEPLOY.md](DEPLOY.md).
