<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: /admin/index.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrasi - Sistem Informasi BBA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/auth-pages.css">
  <link rel="stylesheet" href="/css/responsive.css">
  <style>
    * {
      font-family: 'Poppins', sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 0;
    }
    
    .register-container {
      max-width: 500px;
      width: 100%;
      padding: 15px;
    }
    
    .register-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      overflow: hidden;
    }
    
    .register-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      text-align: center;
    }
    
    .register-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .register-header p {
      margin: 0;
      opacity: 0.9;
      font-size: 0.875rem;
    }
    
    .register-body {
      padding: 2rem;
    }
    
    .form-label {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
    }
    
    .form-control, .form-select {
      border-radius: 10px;
      padding: 0.65rem 1rem;
      border: 2px solid #e9ecef;
      transition: all 0.3s;
      font-size: 0.9rem;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .input-group-text {
      background: transparent;
      border: 2px solid #e9ecef;
      border-right: none;
      border-radius: 10px 0 0 10px;
    }
    
    .input-group .form-control {
      border-left: none;
      border-radius: 0 10px 10px 0;
    }
    
    .input-group:focus-within .input-group-text {
      border-color: #667eea;
    }
    
    .btn-register {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 10px;
      padding: 0.75rem;
      font-weight: 600;
      color: white;
      width: 100%;
      transition: transform 0.2s;
    }
    
    .btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
      color: white;
    }
    
    .alert {
      border-radius: 10px;
      border: none;
      font-size: 0.875rem;
    }
    
    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid #e9ecef;
    }
    
    .login-link a {
      color: #667eea;
      font-weight: 600;
      text-decoration: none;
    }
    
    .login-link a:hover {
      text-decoration: underline;
    }
    
    .password-toggle {
      cursor: pointer;
      color: #6c757d;
      transition: color 0.2s;
    }
    
    .password-toggle:hover {
      color: #667eea;
    }
    
    .password-strength {
      height: 4px;
      border-radius: 2px;
      background: #e9ecef;
      margin-top: 0.5rem;
      overflow: hidden;
    }
    
    .password-strength-bar {
      height: 100%;
      transition: width 0.3s, background 0.3s;
      width: 0;
    }
    
    .strength-weak { background: #dc3545; width: 33%; }
    .strength-medium { background: #ffc107; width: 66%; }
    .strength-strong { background: #28a745; width: 100%; }
    
    small.text-muted {
      font-size: 0.75rem;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <i class="bi bi-person-plus fs-1 mb-3 d-block"></i>
        <h1>Buat Akun Baru</h1>
        <p>Lengkapi data di bawah untuk mendaftar</p>
      </div>
      
      <div class="register-body">
        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <?php 
              echo htmlspecialchars($_SESSION['error']); 
              unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <form action="process_register.php" method="POST" id="registerForm">
          <div class="mb-3">
            <label for="username" class="form-label">
              <i class="bi bi-person me-1"></i> Username
            </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-person-fill"></i>
              </span>
              <input type="text" 
                     class="form-control" 
                     id="username" 
                     name="username" 
                     placeholder="Minimal 4 karakter"
                     required 
                     minlength="4"
                     maxlength="50"
                     autofocus>
            </div>
            <small class="text-muted">Username harus unik dan minimal 4 karakter</small>
          </div>
          
          <div class="mb-3">
            <label for="email" class="form-label">
              <i class="bi bi-envelope me-1"></i> Email
            </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-envelope-fill"></i>
              </span>
              <input type="email" 
                     class="form-control" 
                     id="email" 
                     name="email" 
                     placeholder="contoh@email.com">
            </div>
            <small class="text-muted">Opsional - untuk pemulihan akun</small>
          </div>
          
          <div class="mb-3">
            <label for="role" class="form-label">
              <i class="bi bi-shield-check me-1"></i> Role
            </label>
            <select class="form-select" id="role" name="role" required>
              <option value="">Pilih Role</option>
              <option value="admin">Admin</option>
              <option value="siswa">Siswa</option>
            </select>
            <small class="text-muted">Pilih role sesuai kebutuhan</small>
          </div>
          
          <div class="mb-3">
            <label for="password" class="form-label">
              <i class="bi bi-lock me-1"></i> Password
            </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-lock-fill"></i>
              </span>
              <input type="password" 
                     class="form-control" 
                     id="password" 
                     name="password" 
                     placeholder="Minimal 6 karakter"
                     required
                     minlength="6"
                     oninput="checkPasswordStrength()">
              <span class="input-group-text password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                <i class="bi bi-eye" id="toggleIcon1"></i>
              </span>
            </div>
            <div class="password-strength">
              <div class="password-strength-bar" id="strengthBar"></div>
            </div>
            <small class="text-muted" id="strengthText">Password minimal 6 karakter</small>
          </div>
          
          <div class="mb-4">
            <label for="confirm_password" class="form-label">
              <i class="bi bi-lock-fill me-1"></i> Konfirmasi Password
            </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-lock-fill"></i>
              </span>
              <input type="password" 
                     class="form-control" 
                     id="confirm_password" 
                     name="confirm_password" 
                     placeholder="Ulangi password"
                     required
                     minlength="6"
                     oninput="checkPasswordMatch()">
              <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                <i class="bi bi-eye" id="toggleIcon2"></i>
              </span>
            </div>
            <small class="text-danger d-none" id="passwordMismatch">Password tidak cocok!</small>
          </div>
          
          <button type="submit" class="btn btn-register" id="submitBtn">
            <i class="bi bi-person-plus me-2"></i> Daftar Sekarang
          </button>
        </form>
        
        <div class="login-link">
          <p class="mb-0">Sudah punya akun? 
            <a href="login.php">Login di sini</a>
          </p>
        </div>
      </div>
    </div>
    
    <div class="text-center mt-3">
      <p class="text-white mb-0">
        <small>&copy; <?php echo date('Y'); ?> dadannf - Sistem Informasi Sekolah</small>
      </p>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function togglePassword(inputId, iconId) {
      const passwordInput = document.getElementById(inputId);
      const toggleIcon = document.getElementById(iconId);
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
      }
    }
    
    function checkPasswordStrength() {
      const password = document.getElementById('password').value;
      const strengthBar = document.getElementById('strengthBar');
      const strengthText = document.getElementById('strengthText');
      
      // Remove all classes
      strengthBar.className = 'password-strength-bar';
      
      if (password.length === 0) {
        strengthText.textContent = 'Password minimal 6 karakter';
        strengthText.className = 'text-muted';
        return;
      }
      
      // Calculate strength
      let strength = 0;
      if (password.length >= 6) strength++;
      if (password.length >= 8) strength++;
      if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
      if (/[0-9]/.test(password)) strength++;
      if (/[^a-zA-Z0-9]/.test(password)) strength++;
      
      if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
        strengthText.textContent = 'Password lemah';
        strengthText.className = 'text-danger';
      } else if (strength <= 3) {
        strengthBar.classList.add('strength-medium');
        strengthText.textContent = 'Password sedang';
        strengthText.className = 'text-warning';
      } else {
        strengthBar.classList.add('strength-strong');
        strengthText.textContent = 'Password kuat';
        strengthText.className = 'text-success';
      }
    }
    
    function checkPasswordMatch() {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const mismatchText = document.getElementById('passwordMismatch');
      const submitBtn = document.getElementById('submitBtn');
      
      if (confirmPassword.length > 0) {
        if (password !== confirmPassword) {
          mismatchText.classList.remove('d-none');
          submitBtn.disabled = true;
        } else {
          mismatchText.classList.add('d-none');
          submitBtn.disabled = false;
        }
      }
    }
    
    // Validate form before submit
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Password dan Konfirmasi Password tidak cocok!');
        return false;
      }
    });
  </script>
</body>
</html>
