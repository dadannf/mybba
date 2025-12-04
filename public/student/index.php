<?php
// =============================================
// Halaman: Dashboard Siswa
// Deskripsi: Tampilan dashboard untuk siswa dengan informasi sekolah
// =============================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';

// Pastikan yang login adalah siswa
if ($userRole !== 'siswa') {
    $_SESSION['error'] = 'Akses ditolak! Halaman ini hanya untuk siswa.';
    header('Location: ../index.php');
    exit;
}

// Ambil data siswa yang login berdasarkan username (NIS)
$username = $_SESSION['username']; // NIS siswa
$sqlSiswa = "SELECT nama, kelas, jurusan FROM siswa WHERE nis = '" . esc($username) . "'";
$resultSiswa = $conn->query($sqlSiswa);

if ($resultSiswa && $resultSiswa->num_rows > 0) {
    $dataSiswa = $resultSiswa->fetch_assoc();
    $namaSiswa = $dataSiswa['nama'];
    $kelasSiswa = $dataSiswa['kelas'];
    $jurusanSiswa = $dataSiswa['jurusan'];
} else {
    $namaSiswa = $adminName; // Fallback ke username jika data tidak ditemukan
    $kelasSiswa = '';
    $jurusanSiswa = '';
}

// Query untuk menghitung total siswa
$sqlTotalSiswa = "SELECT COUNT(*) as total FROM siswa";
$resultTotal = $conn->query($sqlTotalSiswa);
$totalStudents = $resultTotal->fetch_assoc()['total'];

// Query untuk menghitung siswa aktif
$sqlSiswaAktif = "SELECT COUNT(*) as total FROM siswa WHERE status_siswa = 'aktif'";
$resultAktif = $conn->query($sqlSiswaAktif);
$siswaAktif = $resultAktif->fetch_assoc()['total'];

// Query untuk menghitung siswa lulus
$sqlSiswaLulus = "SELECT COUNT(*) as total FROM siswa WHERE status_siswa = 'lulus'";
$resultLulus = $conn->query($sqlSiswaLulus);
$siswaLulus = $resultLulus->fetch_assoc()['total'];

// Query untuk informasi dari admin (limit 6 terbaru)
// CONVERT untuk mengatasi perbedaan collation
$sqlInformasi = "SELECT i.informasi_id, i.judul, i.isi, i.foto, i.created_at, i.created_by, u.username as penulis 
                 FROM informasi i
                 LEFT JOIN users u ON i.created_by COLLATE utf8mb4_general_ci = u.username COLLATE utf8mb4_general_ci
                 WHERE u.role = 'admin'
                 ORDER BY i.created_at DESC 
                 LIMIT 6";
$resultInformasi = $conn->query($sqlInformasi);

// Informasi sekolah
$namaSekolah = "SMK BIT BINA AULIA";
$alamatSekolah = "JL. LETDA NATSIR No. 582, Bojong Kulur, Gunungputri, Kabupaten Bogor";
$teleponSekolah = "021-82415429";
$emailSekolah = "smkbitbinaaulia@ymail.com";
$akreditasi = "A";
$totalGuru = 25; // Bisa diambil dari database jika ada tabel guru
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Siswa - SMK BIT BINA AULIA</title>
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
    
    .info-box {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      border-radius: 15px;
      margin-bottom: 2rem;
    }
    
    .info-box h3 {
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .info-box p {
      margin-bottom: 0.25rem;
      opacity: 0.95;
    }
    
    /* Informasi Card Styles */
    .info-card-hover {
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .info-card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .info-card-hover img {
      transition: transform 0.3s ease;
    }
    
    .info-card-hover:hover img {
      transform: scale(1.05);
    }
    
    .isi-content {
      line-height: 1.8;
      font-size: 1rem;
    }
    
    .isi-content p {
      margin-bottom: 1rem;
    }
    
    .isi-content img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      margin: 1rem 0;
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
      <div class="profile-name"><?php echo htmlspecialchars($namaSiswa); ?></div>
      <div class="profile-role text-muted">
        <?php 
        if (!empty($kelasSiswa)) {
          echo htmlspecialchars($kelasSiswa);
          if (!empty($jurusanSiswa)) {
            echo ' - ' . htmlspecialchars($jurusanSiswa);
          }
        } else {
          echo 'Siswa';
        }
        ?>
      </div>
    </div>

    <nav class="sidebar-nav" role="navigation" aria-label="Main menu">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link active" href="index.php">
            <i class="bi bi-house-door-fill me-2"></i> 
            <span class="nav-text">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="profile.php">
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
          <button id="sidebarToggle" class="btn btn-toggle btn-sm me-3" aria-label="Toggle sidebar" aria-expanded="true">☰</button>
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
        
        <div class="welcome card mb-4">
          <div class="card-body">
            <h4 class="mb-0">
              <i class="bi bi-emoji-smile text-primary me-2"></i>
              Selamat Datang, <?php echo htmlspecialchars($username); ?>!
            </h4>
            <p class="text-muted mb-0 mt-2">Portal Siswa SMK BIT BINA AULIA</p>
          </div>
        </div>

        <div class="info-box shadow">
          <h3><i class="bi bi-building me-2"></i><?php echo $namaSekolah; ?></h3>
          <p class="mb-2"><i class="bi bi-award me-2"></i>Akreditasi: <?php echo $akreditasi; ?> | NPSN: 20254256</p>
          <p class="mb-2"><i class="bi bi-geo-alt me-2"></i><?php echo $alamatSekolah; ?></p>
          <p class="mb-2"><i class="bi bi-telephone me-2"></i><?php echo $teleponSekolah; ?></p>
          <p class="mb-0"><i class="bi bi-envelope me-2"></i><?php echo $emailSekolah; ?></p>
        </div>


        <!-- Section Informasi dari Admin -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="mb-0"><i class="bi bi-megaphone-fill me-2 text-primary"></i>Informasi & Pengumuman</h4>
            </div>
          </div>
        </div>

        <?php if ($resultInformasi && $resultInformasi->num_rows > 0): ?>
        <div class="row g-3">
          <?php while($info = $resultInformasi->fetch_assoc()): ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 border-0 info-card-hover">
              <?php if (!empty($info['foto'])): ?>
              <div class="position-relative overflow-hidden" style="height: 200px;">
                <img src="/<?php echo htmlspecialchars($info['foto']); ?>" 
                     class="card-img-top w-100 h-100" 
                     style="object-fit: cover;"
                     alt="<?php echo htmlspecialchars($info['judul']); ?>"
                     onerror="this.parentElement.style.display='none'">
              </div>
              <?php endif; ?>
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <span class="badge bg-primary me-2">
                    <i class="bi bi-shield-check me-1"></i>Admin
                  </span>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    <?php 
                    $date = new DateTime($info['created_at']);
                    echo $date->format('d M Y');
                    ?>
                  </small>
                </div>
                <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($info['judul']); ?></h5>
                <p class="card-text text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                  <?php echo htmlspecialchars(strip_tags($info['isi'])); ?>
                </p>
                <button type="button" class="btn btn-sm btn-outline-primary w-100" 
                        onclick="showInfoDetail(<?php echo $info['informasi_id']; ?>)">
                  <i class="bi bi-eye me-1"></i>Lihat Detail
                </button>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="row">
          <div class="col-12">
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              Belum ada informasi atau pengumuman dari admin.
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </main>
  </div>

  <!-- Modal Detail Informasi -->
  <div class="modal fade" id="detailInfoModal" tabindex="-1" aria-labelledby="detailInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="detailInfoModalLabel">
            <i class="bi bi-info-circle-fill me-2"></i>Detail Informasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="modalInfoContent">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  
  <script>
  // Fungsi untuk menampilkan detail informasi
  function showInfoDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailInfoModal'));
    const modalContent = document.getElementById('modalInfoContent');
    
    // Show loading
    modalContent.innerHTML = `
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Memuat detail informasi...</p>
      </div>
    `;
    
    modal.show();
    
    // Fetch detail informasi
    fetch(`/api/get_informasi_detail.php?id=${id}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const info = data.data;
          const date = new Date(info.created_at);
          const formattedDate = date.toLocaleDateString('id-ID', { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });
          
          modalContent.innerHTML = `
            ${info.foto ? `
              <div class="mb-4">
                <img src="/${info.foto}" 
                     class="img-fluid rounded w-100" 
                     style="max-height: 400px; object-fit: cover;"
                     alt="${info.judul}">
              </div>
            ` : ''}
            
            <div class="mb-3">
              <h4 class="fw-bold mb-3">${info.judul}</h4>
              <div class="d-flex gap-3 mb-3 text-muted small">
                <span>
                  <i class="bi bi-person-fill me-1"></i>
                  ${info.penulis || 'Admin'}
                </span>
                <span>
                  <i class="bi bi-calendar-fill me-1"></i>
                  ${formattedDate}
                </span>
              </div>
            </div>
            
            <div class="border-top pt-3">
              <div class="isi-content">
                ${info.isi}
              </div>
            </div>
          `;
        } else {
          modalContent.innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              ${data.message || 'Gagal memuat detail informasi'}
            </div>
          `;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        modalContent.innerHTML = `
          <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Terjadi kesalahan saat memuat data
          </div>
        `;
      });
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
