<?php
// =============================================
// Halaman: Data Pribadi Siswa
// Deskripsi: Menampilkan data pribadi siswa yang login
// =============================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';

// Pastikan yang login adalah siswa
if ($userRole !== 'siswa') {
    $_SESSION['error'] = 'Akses ditolak! Halaman ini hanya untuk siswa.';
    header('Location: ../index.php');
    exit;
}

// Ambil data siswa berdasarkan username (asumsi username = NIS)
$username = $_SESSION['username'];

// Query untuk mendapatkan data siswa
$sql = "SELECT * FROM siswa WHERE nis = '$username' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Data siswa tidak ditemukan! Silakan hubungi administrator.';
    header('Location: index.php');
    exit;
}

$siswa = $result->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Data Pribadi - SMK BIT BINA AULIA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/responsive.css">
  
  <style>
    #userDropdown {
      cursor: pointer !important;
      border: none !important;
      background: transparent !important;
      padding: 0.25rem 0.5rem !important;
    }
    
    #userDropdown:hover {
      background-color: rgba(255,255,255,0.1) !important;
      border-radius: 8px;
    }
    
    #userDropdown:focus {
      box-shadow: none !important;
      outline: none !important;
    }
    
    .dropdown {
      position: relative;
    }
    
    #userDropdownMenu {
      position: absolute;
      right: 0;
      top: 100%;
      z-index: 1050;
      min-width: 200px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      margin-top: 0.5rem !important;
      border-radius: 0.375rem;
      background-color: white;
      border: 1px solid rgba(0,0,0,.15);
      padding: 0.5rem 0;
      list-style: none;
    }
    
    .dropdown-item {
      padding: 0.5rem 1rem;
      transition: all 0.2s;
      cursor: pointer;
      display: block;
      width: 100%;
      color: #212529;
      text-decoration: none;
    }
    
    .dropdown-item:hover {
      background-color: #f8f9fa;
    }
    
    .dropdown-item.text-danger:hover {
      background-color: #fff5f5;
    }
    
    .dropdown-divider {
      height: 0;
      margin: 0.5rem 0;
      overflow: hidden;
      border-top: 1px solid #e9ecef;
    }
    
    .profile-header {
      background: #4F46E5;
      color: white;
      padding: 2.5rem 2rem;
      border-radius: 0;
      position: relative;
      overflow: hidden;
    }
    
    .profile-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 50%;
    }
    
    .profile-avatar-large {
      width: 120px;
      height: 120px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      position: relative;
      z-index: 1;
    }
    
    .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #495057;
      margin-bottom: 1.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid #4F46E5;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .info-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
      transition: all 0.3s ease;
    }
    
    .info-card:hover {
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
      transform: translateY(-2px);
    }
    
    .info-item {
      display: flex;
      align-items: flex-start;
      padding: 0.875rem 0;
      border-bottom: 1px solid #f1f3f5;
    }
    
    .info-item:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
    
    .info-item:first-child {
      padding-top: 0;
    }
    
    .info-icon {
      width: 40px;
      height: 40px;
      background: #4F46E5;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.1rem;
      flex-shrink: 0;
      margin-right: 1rem;
    }
    
    .info-content {
      flex: 1;
    }
    
    .info-label {
      font-size: 0.8rem;
      font-weight: 600;
      color: #868e96;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.25rem;
    }
    
    .info-value {
      font-size: 1rem;
      color: #212529;
      font-weight: 500;
    }
    
    .status-badge {
      padding: 0.5rem 1.25rem;
      border-radius: 25px;
      font-weight: 600;
      font-size: 0.875rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .status-aktif {
      background: #10B981;
      color: white;
    }
    
    .status-lulus {
      background: #F59E0B;
      color: white;
    }
    
    .quick-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: white;
      padding: 1.25rem;
      border-radius: 12px;
      text-align: center;
      border: 1px solid #e9ecef;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .stat-icon {
      width: 50px;
      height: 50px;
      background: #4F46E5;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
      margin: 0 auto 0.75rem;
    }
    
    .stat-label {
      font-size: 0.8rem;
      color: #868e96;
      margin-bottom: 0.25rem;
    }
    
    .stat-value {
      font-size: 1.25rem;
      font-weight: 700;
      color: #212529;
    }
    
    /* Fix modal scroll */
    #editDataModal .modal-dialog {
      max-height: calc(100vh - 60px);
      display: flex;
      align-items: center;
    }
    
    #editDataModal .modal-content {
      max-height: calc(100vh - 60px);
      display: flex;
      flex-direction: column;
    }
    
    #editDataModal .modal-body {
      overflow-y: auto;
      max-height: calc(100vh - 200px);
      padding: 1.5rem;
    }
    
    #editDataModal .modal-header,
    #editDataModal .modal-footer {
      flex-shrink: 0;
    }
  </style>
</head>
<body class="has-sidebar">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header text-center py-4">
      <div class="profile-avatar mb-2">
        <svg width="54" height="54" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12" cy="8" r="4" fill="#fff" opacity="0.9"/>
          <path d="M4 20c0-4 4-7 8-7s8 3 8 7" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="profile-name"><?php echo htmlspecialchars($siswa['nama']); ?></div>
      <div class="profile-role text-muted">
        <?php 
        echo htmlspecialchars($siswa['kelas']);
        if (!empty($siswa['jurusan'])) {
          echo ' - ' . htmlspecialchars($siswa['jurusan']);
        }
        ?>
      </div>
    </div>

    <nav class="sidebar-nav" role="navigation" aria-label="Main menu">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link" href="index.php">
            <i class="bi bi-house-door-fill me-2"></i> 
            <span class="nav-text">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="profile.php">
            <i class="bi bi-person-fill me-2"></i> 
            <span class="nav-text">Data Pribadi</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="finance.php">
            <i class="bi bi-cash-stack me-2"></i> 
            <span class="nav-text">Keuangan Saya</span>
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  <div class="main-wrapper">
    <header class="topbar shadow-sm">
      <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <button id="sidebarToggle" class="btn btn-toggle btn-sm me-3">☰</button>
          <h1 class="app-title mb-0">SMK BIT BINA AULIA</h1>
        </div>
        <div class="d-flex align-items-center">
          <div class="dropdown">
            <button class="btn d-flex align-items-center gap-2" 
                    type="button" 
                    id="userDropdown" 
                    onclick="toggleUserDropdown(event)">
              <span class="d-none d-md-inline text-white" style="font-size: 0.875rem;"><?php echo htmlspecialchars($adminName); ?></span>
              <i class="bi bi-person-circle text-white" style="font-size: 1.5rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" id="userDropdownMenu" style="display: none;">
              <li>
                <a class="dropdown-item" href="profile.php">
                  <i class="bi bi-person me-2"></i> Profil Saya
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="#" onclick="confirmLogout(); return false;">
                  <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <main class="content">
      <div class="container-fluid">
        <div id="overlay" class="overlay d-none"></div>
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-3 fs-4"></i>
            <div>
              <strong>Berhasil!</strong> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div>
              <strong>Error!</strong> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>Data Pribadi Siswa</h4>
            <p class="text-muted mb-0">Informasi lengkap data pribadi Anda</p>
          </div>
          <button class="btn btn-primary" onclick="openEditModal()">
            <i class="bi bi-pencil-square me-2"></i>Edit Data
          </button>
        </div>
        
        <div class="row">
          <div class="col-12">
            <!-- Profile Header Card -->
            <div class="card shadow-sm mb-4 border-0 overflow-hidden">
              <div class="profile-header text-center">
                <div class="profile-avatar-large">
                  <i class="bi bi-person-fill" style="font-size: 3.5rem; color: #4F46E5;"></i>
                </div>
                <h3 class="mb-2 fw-bold"><?php echo htmlspecialchars($siswa['nama']); ?></h3>
                <p class="mb-3 opacity-90" style="font-size: 1.1rem;">
                  <i class="bi bi-credit-card me-2"></i>NIS: <?php echo htmlspecialchars($siswa['nis']); ?>
                </p>
                <div>
                  <span class="status-badge <?php echo $siswa['status_siswa'] === 'aktif' ? 'status-aktif' : 'status-lulus'; ?>">
                    <i class="bi bi-<?php echo $siswa['status_siswa'] === 'aktif' ? 'check-circle-fill' : 'award-fill'; ?>"></i>
                    <?php echo strtoupper($siswa['status_siswa']); ?>
                  </span>
                </div>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="bi bi-book-fill"></i>
                </div>
                <div class="stat-label">Kelas</div>
                <div class="stat-value"><?php echo htmlspecialchars($siswa['kelas']); ?></div>
              </div>
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="bi bi-diagram-3-fill"></i>
                </div>
                <div class="stat-label">Jurusan</div>
                <div class="stat-value" style="font-size: 0.95rem;"><?php echo htmlspecialchars($siswa['jurusan']); ?></div>
              </div>
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="bi bi-gender-ambiguous"></i>
                </div>
                <div class="stat-label">Jenis Kelamin</div>
                <div class="stat-value"><?php echo $siswa['jk'] === 'L' ? 'L' : 'P'; ?></div>
              </div>
            </div>

            <!-- Data Pribadi Section -->
            <div class="info-card">
              <h5 class="section-title">
                <i class="bi bi-person-lines-fill"></i>
                Identitas Pribadi
              </h5>
              
              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-card-text"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">NISN</div>
                  <div class="info-value"><?php echo htmlspecialchars($siswa['nisn']); ?></div>
                </div>
              </div>

              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-credit-card-2-front"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">NIK</div>
                  <div class="info-value"><?php echo htmlspecialchars($siswa['nik'] ?? '-'); ?></div>
                </div>
              </div>

              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-person-fill"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">Nama Lengkap</div>
                  <div class="info-value"><?php echo htmlspecialchars($siswa['nama']); ?></div>
                </div>
              </div>

              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-gender-ambiguous"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">Jenis Kelamin</div>
                  <div class="info-value"><?php echo $siswa['jk'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                </div>
              </div>

              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-cake2-fill"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">Tempat, Tanggal Lahir</div>
                  <div class="info-value">
                    <?php 
                      echo htmlspecialchars($siswa['tempat_lahir']) . ', ' . 
                           date('d F Y', strtotime($siswa['tanggal_lahir'])); 
                    ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Data Kontak Section -->
            <div class="info-card">
              <h5 class="section-title">
                <i class="bi bi-telephone-fill"></i>
                Informasi Kontak
              </h5>
              
              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-house-fill"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">Alamat Lengkap</div>
                  <div class="info-value"><?php echo nl2br(htmlspecialchars($siswa['alamat'])); ?></div>
                </div>
              </div>

              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-phone-fill"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">Nomor HP / Telepon</div>
                  <div class="info-value">
                    <a href="tel:<?php echo htmlspecialchars($siswa['no_hp']); ?>" class="text-decoration-none">
                      <?php echo htmlspecialchars($siswa['no_hp']); ?>
                    </a>
                  </div>
                </div>
              </div>

              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-envelope-fill"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">Email</div>
                  <div class="info-value">
                    <a href="mailto:<?php echo htmlspecialchars($siswa['email'] ?? ''); ?>" class="text-decoration-none">
                      <?php echo htmlspecialchars($siswa['email'] ?? '-'); ?>
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Data Keluarga Section -->
            <div class="info-card">
              <h5 class="section-title">
                <i class="bi bi-people-fill"></i>
                Informasi Keluarga
              </h5>
              
              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-person-heart"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">Nama Ayah</div>
                  <div class="info-value"><?php echo htmlspecialchars($siswa['ayah'] ?? '-'); ?></div>
                </div>
              </div>

              <div class="info-item">
                <div class="info-icon">
                  <i class="bi bi-person-hearts"></i>
                </div>
                <div class="info-content">
                  <div class="info-label">Nama Ibu</div>
                  <div class="info-value"><?php echo htmlspecialchars($siswa['ibu'] ?? '-'); ?></div>
                </div>
              </div>
            </div>

            <!-- Alert Info -->
            <div class="alert alert-info d-flex align-items-start border-0 shadow-sm">
              <i class="bi bi-info-circle-fill fs-4 me-3"></i>
              <div>
                <strong>Informasi Penting</strong>
                <p class="mb-0 mt-1">Anda dapat memperbarui data pribadi dengan klik tombol <strong>"Edit Data"</strong> di atas. Pastikan data yang Anda masukkan sudah benar. Untuk perubahan data NIS dan NISN, silakan hubungi bagian Tata Usaha (TU) sekolah.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- Modal Edit Data Pribadi -->
  <div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header" style="background: #4F46E5; color: white;">
          <h5 class="modal-title" id="editDataModalLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit Data Pribadi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formEditData" method="POST" action="process_update.php">
          <div class="modal-body">
            <!-- Alert -->
            <div class="alert alert-warning d-flex align-items-start">
              <i class="bi bi-exclamation-triangle-fill me-3"></i>
              <div>
                <strong>Perhatian!</strong>
                <p class="mb-0 mt-1">Pastikan data yang Anda masukkan sudah benar. Beberapa data seperti NIS dan NISN tidak dapat diubah.</p>
              </div>
            </div>

            <input type="hidden" name="nis" value="<?php echo htmlspecialchars($siswa['nis']); ?>">

            <!-- Data Identitas -->
            <h6 class="text-primary fw-bold mb-3 pb-2 border-bottom">
              <i class="bi bi-person-lines-fill me-2"></i>Identitas Pribadi
            </h6>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">NIS <span class="text-muted">(Tidak dapat diubah)</span></label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($siswa['nis']); ?>" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">NISN <span class="text-muted">(Tidak dapat diubah)</span></label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($siswa['nisn']); ?>" disabled>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">NIK</label>
              <input type="text" class="form-control" name="nik" value="<?php echo htmlspecialchars($siswa['nik'] ?? ''); ?>" 
                     maxlength="16" pattern="[0-9]{16}" placeholder="16 digit NIK">
              <small class="text-muted">Masukkan 16 digit NIK sesuai KTP</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($siswa['nama']); ?>" required>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Tempat Lahir <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="tempat_lahir" value="<?php echo htmlspecialchars($siswa['tempat_lahir']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo $siswa['tanggal_lahir']; ?>" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
              <select class="form-select" name="jk" required>
                <option value="L" <?php echo $siswa['jk'] === 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="P" <?php echo $siswa['jk'] === 'P' ? 'selected' : ''; ?>>Perempuan</option>
              </select>
            </div>

            <!-- Data Akademik -->
            <h6 class="text-primary fw-bold mb-3 pb-2 border-bottom mt-4">
              <i class="bi bi-book-fill me-2"></i>Data Akademik
            </h6>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="kelas" value="<?php echo htmlspecialchars($siswa['kelas']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Jurusan <span class="text-danger">*</span></label>
                <select class="form-select" name="jurusan" required>
                  <option value="">Pilih Jurusan</option>
                  <option value="Teknik Otomotif" <?php echo $siswa['jurusan'] === 'Teknik Otomotif' ? 'selected' : ''; ?>>Teknik Otomotif</option>
                  <option value="Teknik Jaringan Komputer dan Telekomunikasi" <?php echo $siswa['jurusan'] === 'Teknik Jaringan Komputer dan Telekomunikasi' ? 'selected' : ''; ?>>Teknik Jaringan Komputer dan Telekomunikasi</option>
                  <option value="Bisnis Daring dan Pemasaran" <?php echo $siswa['jurusan'] === 'Bisnis Daring dan Pemasaran' ? 'selected' : ''; ?>>Bisnis Daring dan Pemasaran</option>
                </select>
              </div>
            </div>

            <!-- Data Kontak -->
            <h6 class="text-primary fw-bold mb-3 pb-2 border-bottom mt-4">
              <i class="bi bi-telephone-fill me-2"></i>Informasi Kontak
            </h6>

            <div class="mb-3">
              <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
              <textarea class="form-control" name="alamat" rows="3" required><?php echo htmlspecialchars($siswa['alamat']); ?></textarea>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nomor HP / Telepon <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="no_hp" value="<?php echo htmlspecialchars($siswa['no_hp']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($siswa['email'] ?? ''); ?>">
              </div>
            </div>

            <!-- Data Keluarga -->
            <h6 class="text-primary fw-bold mb-3 pb-2 border-bottom mt-4">
              <i class="bi bi-people-fill me-2"></i>Informasi Keluarga
            </h6>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Ayah</label>
                <input type="text" class="form-control" name="ayah" value="<?php echo htmlspecialchars($siswa['ayah'] ?? ''); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Ibu</label>
                <input type="text" class="form-control" name="ibu" value="<?php echo htmlspecialchars($siswa['ibu'] ?? ''); ?>">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-2"></i>Batal
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-2"></i>Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>

  <script>
  function openEditModal() {
    const modal = new bootstrap.Modal(document.getElementById('editDataModal'));
    modal.show();
  }

  function toggleUserDropdown(event) {
    event.stopPropagation();
    const menu = document.getElementById('userDropdownMenu');
    if (menu) {
      const isShowing = menu.style.display === 'block';
      menu.style.display = isShowing ? 'none' : 'block';
    }
  }
  
  function confirmLogout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
      window.location.href = '/auth/logout.php';
    }
    return false;
  }
  
  document.addEventListener('click', function(event) {
    const menu = document.getElementById('userDropdownMenu');
    const dropdown = document.getElementById('userDropdown');
    
    if (menu && dropdown) {
      const isClickInsideDropdown = dropdown.contains(event.target);
      const isClickInsideMenu = menu.contains(event.target);
      
      if (!isClickInsideDropdown && !isClickInsideMenu && menu.style.display === 'block') {
        menu.style.display = 'none';
      }
    }
  });
  </script>
</body>
</html>
<?php $conn->close(); ?>
