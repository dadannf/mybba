# 🛠️ Utility Tools

Folder ini berisi script-script utility untuk setup, maintenance, dan troubleshooting.

## 📁 File dalam Folder Ini:

### 1. Setup Tools (Dijalankan sekali saat instalasi)

#### `create_first_user.php`
**Fungsi:** Create akun admin pertama kali  
**Kapan Digunakan:** Instalasi awal sistem  
**Akses:** `http://localhost/mybba/public/utils/create_first_user.php`  
**Output:** 
- Username: admin
- Password: admin123 (default)
- Role: admin

---

#### `create_student_accounts.php`
**Fungsi:** Auto-generate akun untuk SEMUA siswa sekaligus  
**Kapan Digunakan:** 
- Instalasi awal
- Batch create akun siswa baru
**Akses:** `http://localhost/mybba/public/utils/create_student_accounts.php`  
**Format Password:** `bba#[4 angka terakhir NIS]`  
**Output:** 
- List semua siswa yang berhasil dibuat
- Skip jika sudah ada
- Summary hasil

---

### 2. Maintenance Tools (Dijalankan saat ada masalah)

#### `reset_student_passwords.php`
**Fungsi:** Reset password SEMUA siswa ke format standar  
**Kapan Digunakan:**
- Password siswa tidak konsisten
- Update format password
- Lupa password massal
**Akses:** `http://localhost/mybba/public/utils/reset_student_passwords.php`  
**⚠️ Warning:** Akan mengubah password SEMUA siswa!  
**Format Password:** `bba#[4 angka terakhir NIS]`

---

#### `fix_student_password.php`
**Fungsi:** Reset password untuk 1 siswa tertentu  
**Kapan Digunakan:** Siswa lupa password atau password error  
**Akses:** `http://localhost/mybba/public/utils/fix_student_password.php?nis=22211611`  
**Parameter:** `?nis=[NIS_SISWA]`  
**Format Password:** `bba#[4 angka terakhir NIS]`

---

### 3. Troubleshooting Tools (Untuk debug)

#### `check_login.php`
**Fungsi:** Diagnose kenapa login siswa gagal  
**Kapan Digunakan:** Troubleshooting login issues  
**Akses:** `http://localhost/mybba/public/utils/check_login.php`  
**Fitur:**
- ✅ Cek siswa ada di tabel `siswa`
- ✅ Cek user account di tabel `users`
- ✅ Verify password hash
- ✅ Show expected password
- ✅ Provide quick fix solutions

---

## 🚀 Quick Reference

### Pertama Kali Setup:
```
1. http://localhost/mybba/public/utils/create_first_user.php
   → Create admin account

2. http://localhost/mybba/public/utils/create_student_accounts.php
   → Create semua akun siswa
```

### Siswa Lupa Password:
```
Option 1 (Specific):
http://localhost/mybba/public/utils/fix_student_password.php?nis=22211611

Option 2 (All):
http://localhost/mybba/public/utils/reset_student_passwords.php
```

### Login Gagal (Troubleshoot):
```
http://localhost/mybba/public/utils/check_login.php
→ Follow instruksi yang muncul
```

---

## 🔒 Security Notes

⚠️ **PENTING untuk Production:**

1. **Batasi Akses ke Folder Ini**
   ```apache
   # .htaccess di folder utils/
   Order Deny,Allow
   Deny from all
   Allow from 127.0.0.1
   Allow from ::1
   ```

2. **Atau Hapus Folder Ini Setelah Setup**
   ```bash
   # Setelah setup selesai dan semua berjalan lancar
   rm -rf public/utils/
   ```

3. **Atau Password Protect**
   ```apache
   AuthType Basic
   AuthName "Restricted Area"
   AuthUserFile /path/to/.htpasswd
   Require valid-user
   ```

4. **Rename Folder**
   ```
   Rename utils/ menjadi nama random
   Example: utils_a7f3k9x2/
   ```

---

## 📝 Changelog

**v1.0 - November 5, 2025**
- Initial organization
- Moved all utility scripts to separate folder
- Created documentation

---

## 💡 Tips

- Backup database sebelum menjalankan script yang mengubah data
- Test di development environment dulu
- Catat username/password yang di-generate
- Hapus atau protect folder ini di production

---

**Last Updated:** November 5, 2025
