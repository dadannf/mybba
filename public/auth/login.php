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
  <title>Login - SMK BIT BINA AULIA</title>
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
      background: #1e3a8a;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow-x: hidden;
      overflow-y: auto;
      padding: 20px 0;
    }
    
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(30, 58, 138, 0.95) 0%, rgba(79, 70, 229, 0.9) 100%);
      z-index: 0;
      pointer-events: none;
    }
    
    body::after {
      content: '';
      position: fixed;
      top: -50%;
      right: -10%;
      width: 600px;
      height: 600px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      z-index: 0;
      pointer-events: none;
    }
    
    .login-container {
      max-width: 480px;
      width: 100%;
      padding: 20px;
      position: relative;
      z-index: 1;
    }
    
    .login-card {
      background: white;
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
    }
    
    .login-header {
      background: #4F46E5;
      color: white;
      padding: 3rem 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .login-header::before {
      content: '';
      position: absolute;
      top: -30%;
      left: -20%;
      width: 400px;
      height: 400px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 50%;
    }
    
    .education-icon {
      width: 90px;
      height: 90px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      position: relative;
      z-index: 1;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }
    
    .education-icon i {
      font-size: 3rem;
      color: white;
    }
    
    .login-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      position: relative;
      z-index: 1;
    }
    
    .login-header p {
      margin: 0;
      opacity: 0.95;
      font-size: 0.95rem;
      position: relative;
      z-index: 1;
      font-weight: 400;
    }
    
    .school-name {
      font-weight: 600;
      font-size: 1.05rem;
      margin-top: 0.5rem;
      letter-spacing: 0.5px;
    }
    
    .login-body {
      padding: 2.5rem 2rem;
    }
    
    .form-label {
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }
    
    .form-control {
      border-radius: 12px;
      padding: 0.875rem 1rem;
      border: 2px solid #e2e8f0;
      transition: all 0.3s;
      font-size: 0.95rem;
    }
    
    .form-control:focus {
      border-color: #4F46E5;
      box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    
    .input-group-text {
      background: #f8fafc;
      border: 2px solid #e2e8f0;
      border-right: none;
      border-radius: 12px 0 0 12px;
      color: #64748b;
    }
    
    .input-group .form-control {
      border-left: none;
      border-radius: 0 12px 12px 0;
      padding-left: 0;
    }
    
    .input-group:focus-within .input-group-text {
      border-color: #4F46E5;
      background: #eef2ff;
      color: #4F46E5;
    }
    
    .btn-login {
      background: #4F46E5;
      border: none;
      border-radius: 12px;
      padding: 0.875rem;
      font-weight: 600;
      color: white;
      width: 100%;
      transition: all 0.3s;
      font-size: 1rem;
    }
    
    .btn-login:hover {
      background: #4338ca;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
      color: white;
    }
    
    .btn-login:active {
      transform: translateY(0);
    }
    
    .alert {
      border-radius: 12px;
      border: none;
      padding: 1rem 1.25rem;
    }
    
    .alert-danger {
      background: #fef2f2;
      color: #991b1b;
    }
    
    .alert-success {
      background: #f0fdf4;
      color: #166534;
    }
    
    .register-link {
      text-align: center;
      margin-top: 1.75rem;
      padding-top: 1.75rem;
      border-top: 2px solid #f1f5f9;
    }
    
    .register-link p {
      color: #64748b;
      font-size: 0.95rem;
    }
    
    .register-link a {
      color: #4F46E5;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }
    
    .register-link a:hover {
      color: #4338ca;
      text-decoration: underline;
    }
    
    .password-toggle {
      cursor: pointer;
      color: #64748b;
      transition: all 0.2s;
      background: #f8fafc;
      border: 2px solid #e2e8f0;
      border-left: none;
      border-radius: 0 12px 12px 0;
    }
    
    .password-toggle:hover {
      color: #4F46E5;
      background: #eef2ff;
    }
    
    .footer-text {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      padding: 1rem;
      border-radius: 12px;
      margin-top: 1.5rem;
    }
    
    .footer-text p {
      margin: 0;
      font-size: 0.875rem;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="education-icon">
          <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h1>Selamat Datang</h1>
        <p>di Website</p>
        <p class="school-name">SMK BIT BINA AULIA</p>
      </div>
      
      <div class="login-body">
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
        
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php 
              echo htmlspecialchars($_SESSION['success']); 
              unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <form action="process_login.php" method="POST">
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
                     placeholder="Masukkan username"
                     required 
                     autofocus>
            </div>
          </div>
          
          <div class="mb-4">
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
                     placeholder="Masukkan password"
                     required>
              <span class="input-group-text password-toggle" onclick="togglePassword()">
                <i class="bi bi-eye" id="toggleIcon"></i>
              </span>
            </div>
          </div>
          
          <button type="submit" class="btn btn-login">
            <i class="bi bi-box-arrow-in-right me-2"></i> Login
          </button>
        </form>
        
        <div class="register-link">
          <p class="mb-0">Belum punya akun? 
            <a href="register.php">Daftar Sekarang</a>
          </p>
        </div>
      </div>
    </div>
    
    <div class="text-center footer-text">
      <p class="text-white mb-0">
        &copy; <?php echo date('Y'); ?> dadannf - Sistem Informasi Sekolah
      </p>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');
      
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
  </script>
</body>
</html>
