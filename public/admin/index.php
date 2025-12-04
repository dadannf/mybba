<?php
// =============================================
// Halaman: Dashboard Admin
// Deskripsi: Tampilan dashboard dengan data real-time dari database
// =============================================

// Include koneksi database
require_once __DIR__ . '/../config.php';

// Check authentication
require_once __DIR__ . '/../auth_check.php';

// Get admin name from session
$adminName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

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

// Query untuk menghitung total keuangan (opsional)
$sqlTotalKeuangan = "SELECT COUNT(*) as total FROM keuangan";
$resultKeuangan = $conn->query($sqlTotalKeuangan);
$totalKeuangan = $resultKeuangan->fetch_assoc()['total'];

// Query untuk mengambil informasi terbaru (limit 6)
$sqlInformasi = "SELECT * FROM informasi ORDER BY created_at DESC LIMIT 6";
$resultInformasi = $conn->query($sqlInformasi);

// Query untuk total informasi
$sqlTotalInfo = "SELECT COUNT(*) as total FROM informasi";
$resultTotalInfo = $conn->query($sqlTotalInfo);
$totalInformasi = $resultTotalInfo->fetch_assoc()['total'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - SMK BIT Bina Aulia</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/notifications.css">
  <link rel="stylesheet" href="/css/responsive.css">
  <?php include __DIR__ . '/../includes/user_dropdown_style.php'; ?>
</head>
<body class="has-sidebar">

  <!-- =============================================
    Sidebar (bagian kiri)
    - Berisi profil singkat admin dan menu navigasi
    - Gunakan kelas `.sidebar` untuk styling
    ============================================= -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header text-center py-4">
      <div class="profile-avatar mb-2"> <!-- simple circular avatar -->
        <svg width="54" height="54" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12" cy="8" r="4" fill="#fff" opacity="0.9"/>
          <path d="M4 20c0-4 4-7 8-7s8 3 8 7" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="profile-name">Admin Sekolah</div>
      <div class="profile-role text-muted"><?php echo htmlspecialchars($adminName); ?></div>
    </div>

  <!-- Navigasi utama pada sidebar -->
  <nav class="sidebar-nav" role="navigation" aria-label="Main menu">
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link active" href="/admin/index.php"><i class="bi bi-house-door-fill me-2"></i> <span class="nav-text">Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/students/index.php"><i class="bi bi-people-fill me-2"></i> <span class="nav-text">Data Siswa</span></a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/finance/index.php"><i class="bi bi-cash-stack me-2"></i> <span class="nav-text">Keuangan</span></a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/information/index.php"><i class="bi bi-info-circle-fill me-2"></i> <span class="nav-text">Informasi</span></a></li>
      </ul>
    </nav>
  </aside>

  <!-- =============================================
    Konten utama (main-wrapper)
    - Berisi topbar, overlay, dan area konten
    ============================================= -->
  <div class="main-wrapper">
    <header class="topbar shadow-sm">
      <div class="container-fluid d-flex align-items-center justify-content-between">
        <!-- Topbar: toggle sidebar dan judul aplikasi -->
        <div class="d-flex align-items-center">
          <button id="sidebarToggle" class="btn btn-toggle btn-sm me-3" aria-label="Toggle sidebar" aria-expanded="true">☰</button>
          <h1 class="app-title mb-0">SMK BIT Bina Aulia</h1>
        </div>
        <?php include __DIR__ . '/../includes/user_dropdown.php'; ?>
      </div>
    </header>

    <!-- Main content area -->
    <main class="content">
      <div class="container-fluid">
        <!-- Overlay (dipakai pada layar kecil saat sidebar terbuka) -->
        <div id="overlay" class="overlay d-none"></div>
        <!-- Kartu sambutan -->
        <div class="welcome card mb-4">
          <div class="card-body">
            <h4 class="mb-0">Selamat Datang di Dashboard Sekolah</h4>
          </div>
        </div>

        <!-- Baris kartu info (4 card responsif) -->
        <div class="row g-3 mb-4">
          <div class="col-12 col-sm-6 col-md-3">
            <!-- Card: Total Siswa -->
            <div class="info-card text-white bg-primary p-3 rounded shadow-sm h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="card-title fw-bold mb-1">Total Siswa</div>
                  <div class="display-4 fw-bold mt-2"><?php echo (int)$totalStudents; ?></div>
                  <small class="opacity-75">Seluruh siswa terdaftar</small>
                </div>
                <div>
                  <i class="bi bi-people-fill fs-1 opacity-50"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <!-- Card: Siswa Aktif -->
            <div class="info-card text-white bg-success p-3 rounded shadow-sm h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="card-title fw-bold mb-1">Siswa Aktif</div>
                  <div class="display-4 fw-bold mt-2"><?php echo (int)$siswaAktif; ?></div>
                  <small class="opacity-75">Status aktif</small>
                </div>
                <div>
                  <i class="bi bi-check-circle-fill fs-1 opacity-50"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <!-- Card: Siswa Lulus -->
            <div class="info-card text-dark bg-warning p-3 rounded shadow-sm h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="card-title fw-bold mb-1">Siswa Lulus</div>
                  <div class="display-4 fw-bold mt-2"><?php echo (int)$siswaLulus; ?></div>
                  <small class="opacity-75">Alumni</small>
                </div>
                <div>
                  <i class="bi bi-mortarboard-fill fs-1 opacity-50"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <!-- Card: Data Keuangan -->
            <div class="info-card text-white bg-danger p-3 rounded shadow-sm h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="card-title fw-bold mb-1">Data Keuangan</div>
                  <div class="display-4 fw-bold mt-2"><?php echo (int)$totalKeuangan; ?></div>
                  <small class="opacity-75">Total data keuangan</small>
                </div>
                <div>
                  <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section: Informasi & Pengumuman Terbaru -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-header" style="background-color: #2c3e50; color: white;">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">
                    <i class="bi bi-megaphone-fill me-2"></i>Informasi & Pengumuman Terbaru
                  </h5>
                  <a href="/admin/information/index.php" class="btn btn-sm btn-light">
                    <i class="bi bi-plus-circle me-1"></i> Kelola Informasi
                  </a>
                </div>
              </div>
              <div class="card-body p-0">
                <?php if ($resultInformasi && $resultInformasi->num_rows > 0): ?>
                  <!-- Grid Layout untuk Informasi -->
                  <div class="row g-3 p-3">
                    <?php while($info = $resultInformasi->fetch_assoc()): ?>
                      <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 border shadow-sm" style="transition: all 0.3s ease;">
                          <!-- Header dengan foto atau icon -->
                          <?php 
                          $fileExt = '';
                          $isImage = false;
                          if ($info['foto']) {
                            $fileExt = strtolower(pathinfo($info['foto'], PATHINFO_EXTENSION));
                            $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                          }
                          ?>
                          
                          <?php if ($info['foto'] && $isImage): ?>
                            <!-- Foto -->
                            <div style="height: 150px; overflow: hidden; background: #f8f9fa;">
                              <img src="/<?php echo htmlspecialchars($info['foto']); ?>" 
                                   style="width: 100%; height: 100%; object-fit: cover;"
                                   alt="<?php echo htmlspecialchars($info['judul']); ?>">
                            </div>
                          <?php elseif ($info['foto'] && $fileExt === 'pdf'): ?>
                            <!-- PDF Icon -->
                            <div class="d-flex align-items-center justify-content-center" 
                                 style="height: 150px; background-color: #5dade2;">
                              <div class="text-white text-center">
                                <i class="bi bi-file-earmark-pdf-fill" style="font-size: 2.5rem; opacity: 0.8;"></i>
                                <p class="mt-2 mb-0 small fw-semibold">PDF</p>
                              </div>
                            </div>
                          <?php else: ?>
                            <!-- Default Icon -->
                            <div class="d-flex align-items-center justify-content-center" 
                                 style="height: 150px; background-color: #95a5a6;">
                              <div class="text-white text-center">
                                <i class="bi bi-megaphone-fill" style="font-size: 2.5rem; opacity: 0.8;"></i>
                                <p class="mt-2 mb-0 small fw-semibold">Info</p>
                              </div>
                            </div>
                          <?php endif; ?>
                          
                          <!-- Card Body -->
                          <div class="card-body">
                            <h6 class="card-title fw-bold mb-2" style="color: #2c3e50; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                              <?php echo htmlspecialchars($info['judul']); ?>
                            </h6>
                            <p class="card-text text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                              <?php 
                              $isi = htmlspecialchars($info['isi']);
                              echo strlen($isi) > 80 ? substr($isi, 0, 80) . '...' : $isi;
                              ?>
                            </p>
                            <div class="d-flex align-items-center text-muted" style="font-size: 0.75rem;">
                              <i class="bi bi-clock me-1"></i>
                              <span><?php echo date('d M Y', strtotime($info['created_at'])); ?></span>
                            </div>
                          </div>
                          <div class="card-footer bg-white border-top-0">
                            <button class="btn btn-sm btn-outline-primary w-100" 
                                    onclick="viewDashboardInfo(<?php echo htmlspecialchars(json_encode($info), ENT_QUOTES, 'UTF-8'); ?>)">
                              <i class="bi bi-eye me-1"></i> Lihat Detail
                            </button>
                          </div>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  </div>
                  
                  <!-- Footer dengan link ke halaman informasi -->
                  <?php if ($totalInformasi > 6): ?>
                    <div class="border-top p-3 text-center bg-light">
                      <a href="/admin/information/index.php" class="btn btn-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i> 
                        Lihat Semua Informasi (<?php echo $totalInformasi; ?>)
                      </a>
                    </div>
                  <?php endif; ?>
                  
                <?php else: ?>
                  <!-- Empty State -->
                  <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e0;"></i>
                    <h5 class="text-muted mt-3 mb-2">Belum Ada Informasi</h5>
                    <p class="text-muted mb-3">Mulai buat informasi atau pengumuman untuk warga sekolah</p>
                    <a href="/admin/information/index.php" class="btn btn-primary">
                      <i class="bi bi-plus-circle me-1"></i> Buat Informasi Pertama
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- Bootstrap JS bundle (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  <script src="/js/notifications.js"></script>
  <?php include __DIR__ . '/../includes/user_dropdown_script.php'; ?>
  
  <!-- Modal Detail Informasi -->
  <div class="modal fade" id="modalInfoDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header text-white border-0" style="background-color: #2c3e50;">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-info-circle-fill me-2"></i>Detail Informasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <!-- Header dengan foto/placeholder -->
          <div id="dashboard_detail_header" style="height: 250px; overflow: hidden; background-color: #ecf0f1;">
            <!-- Will be filled by JavaScript -->
          </div>
          
          <!-- Content -->
          <div class="p-4">
            <!-- Badge & Meta -->
            <div class="mb-3">
              <span class="badge bg-primary mb-2">
                <i class="bi bi-megaphone-fill me-1"></i> Informasi Sekolah
              </span>
              <div class="d-flex align-items-center text-muted" style="font-size: 0.9rem;">
                <i class="bi bi-calendar3 me-1"></i>
                <span id="dashboard_detail_date"></span>
                <span class="mx-2">•</span>
                <i class="bi bi-person-fill me-1"></i>
                <span id="dashboard_detail_author"></span>
              </div>
            </div>
            
            <!-- Judul -->
            <h3 class="fw-bold mb-3" style="color: #2c3e50;" id="dashboard_detail_title"></h3>
            
            <!-- Divider -->
            <hr class="my-4">
            
            <!-- Isi Konten -->
            <div style="font-size: 1rem; line-height: 1.8; color: #555; text-align: justify;" id="dashboard_detail_content"></div>
            
            <!-- Lampiran File -->
            <div id="dashboard_detail_attachment" class="mt-4"></div>
          </div>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
  // Function untuk menampilkan detail informasi di dashboard
  function viewDashboardInfo(info) {
    // Set title
    document.getElementById('dashboard_detail_title').textContent = info.judul;
    
    // Set date and author
    const date = new Date(info.created_at);
    const options = { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    document.getElementById('dashboard_detail_date').textContent = date.toLocaleDateString('id-ID', options);
    document.getElementById('dashboard_detail_author').textContent = info.created_by;
    
    // Set content with proper line breaks
    document.getElementById('dashboard_detail_content').innerHTML = info.isi.replace(/\n/g, '<br>');
    
    // Set header image/placeholder
    const detailHeader = document.getElementById('dashboard_detail_header');
    if (info.foto) {
      const fileExt = info.foto.split('.').pop().toLowerCase();
      const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
      const fotoPath = info.foto.startsWith('/') ? info.foto : '/' + info.foto;
      
      if (isImage) {
        detailHeader.innerHTML = `
          <img src="${fotoPath}" 
               style="width: 100%; height: 100%; object-fit: cover;" 
               alt="${info.judul}">
        `;
      } else if (fileExt === 'pdf') {
        detailHeader.innerHTML = `
          <div class="d-flex align-items-center justify-content-center h-100" 
               style="background-color: #5dade2;">
            <div class="text-white text-center">
              <i class="bi bi-file-earmark-pdf-fill" style="font-size: 4rem; opacity: 0.8;"></i>
              <p class="mt-3 mb-0 fw-semibold fs-5">Dokumen PDF Terlampir</p>
            </div>
          </div>
        `;
      } else {
        detailHeader.innerHTML = `
          <div class="d-flex align-items-center justify-content-center h-100" 
               style="background-color: #5dade2;">
            <div class="text-white text-center">
              <i class="bi bi-file-earmark-fill" style="font-size: 4rem; opacity: 0.8;"></i>
              <p class="mt-3 mb-0 fw-semibold fs-5">File Terlampir</p>
            </div>
          </div>
        `;
      }
    } else {
      detailHeader.innerHTML = `
        <div class="d-flex align-items-center justify-content-center h-100" 
             style="background-color: #95a5a6;">
          <div class="text-white text-center">
            <i class="bi bi-megaphone-fill" style="font-size: 4rem; opacity: 0.8;"></i>
            <p class="mt-3 mb-0 fw-semibold fs-5">Pengumuman Sekolah</p>
          </div>
        </div>
      `;
    }
    
    // Set attachment section
    const detailAttachment = document.getElementById('dashboard_detail_attachment');
    if (info.foto) {
      const fileExt = info.foto.split('.').pop().toLowerCase();
      const fileName = info.foto.split('/').pop();
      const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
      const fotoPath = info.foto.startsWith('/') ? info.foto : '/' + info.foto;
      
      detailAttachment.innerHTML = `
        <div class="border rounded p-3" style="background-color: #f8f9fa;">
          <h6 class="fw-bold mb-3">
            <i class="bi bi-paperclip me-2"></i>Lampiran File
          </h6>
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded" 
                 style="width: 50px; height: 50px; background-color: ${isImage ? '#3498db' : fileExt === 'pdf' ? '#e74c3c' : '#95a5a6'};">
              <i class="bi bi-${isImage ? 'image-fill' : fileExt === 'pdf' ? 'file-pdf-fill' : 'file-earmark-fill'} text-white" 
                 style="font-size: 1.5rem;"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold small">${fileName}</div>
              <small class="text-muted">${isImage ? 'Gambar' : fileExt === 'pdf' ? 'Dokumen PDF' : 'File'}</small>
            </div>
            <a href="${fotoPath}" target="_blank" class="btn btn-sm btn-primary">
              <i class="bi bi-${isImage ? 'eye' : 'download'} me-1"></i> 
              ${isImage ? 'Lihat' : 'Unduh'}
            </a>
          </div>
        </div>
      `;
    } else {
      detailAttachment.innerHTML = '';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('modalInfoDetail'));
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
  
  // Close dropdown when clicking outside
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
