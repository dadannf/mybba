<?php
// =============================================
// Halaman: Detail Pembayaran Siswa
// Deskripsi: Menampilkan detail pembayaran bulanan dengan fitur upload bukti
// =============================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';

// Pastikan yang login adalah siswa
if ($userRole !== 'siswa') {
    $_SESSION['error'] = 'Akses ditolak!';
    header('Location: ../index.php');
    exit;
}

$keuangan_id = isset($_GET['keuangan_id']) ? esc($_GET['keuangan_id']) : '';

if (empty($keuangan_id)) {
    $_SESSION['error'] = 'ID Keuangan tidak valid!';
    header('Location: keuangan.php');
    exit;
}

// Pastikan keuangan_id milik siswa yang login
$username = $_SESSION['username'];
$sqlCheck = "SELECT k.*, s.nama, s.kelas, s.jurusan 
             FROM keuangan k
             INNER JOIN siswa s ON k.nis = s.nis
             WHERE k.keuangan_id = '$keuangan_id' AND k.nis = '$username'";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck->num_rows === 0) {
    $_SESSION['error'] = 'Data tidak ditemukan atau bukan milik Anda!';
    header('Location: keuangan.php');
    exit;
}

$keuangan = $resultCheck->fetch_assoc();

// Mapping bulan
$bulanMap = [
    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
];

// Query pembayaran
$sqlPembayaran = "SELECT * FROM pembayaran WHERE keuangan_id = '$keuangan_id' ORDER BY index_bulan ASC";
$resultPembayaran = $conn->query($sqlPembayaran);

$pembayaranData = [];
while ($row = $resultPembayaran->fetch_assoc()) {
    $pembayaranData[$row['index_bulan']] = $row;
}

$nominalPerBulan = 100000; // Rp 100.000 per bulan
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Pembayaran - Portal Siswa BBA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/responsive.css">
  
  <style>
    /* Mobile Sidebar & Toggle Button Enhancements */
    @media (max-width: 767.98px) {
      .btn-toggle {
        font-size: 1.2rem;
        padding: 0.5rem 0.75rem;
        line-height: 1;
        min-width: 44px;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
      }
      
      .topbar {
        position: sticky;
        top: 0;
        z-index: 1030;
      }
      
      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }
      
      body.sidebar-open .sidebar {
        transform: translateX(0);
      }
      
      .overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        transition: opacity 0.3s ease;
      }
      
      .overlay.d-none {
        display: none !important;
      }
    }
    
    .status-valid { color: #28a745; font-weight: bold; }
    .status-menunggu { color: #ffc107; font-weight: bold; }
    .status-tolak { color: #dc3545; font-weight: bold; }
    
    #userDropdown {
      cursor: pointer !important;
      border-color: rgba(255,255,255,0.3) !important;
    }
    
    #userDropdown:hover {
      background-color: rgba(255,255,255,0.1) !important;
      border-color: rgba(255,255,255,0.5) !important;
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
      <div class="profile-name"><?php echo htmlspecialchars($keuangan['nama']); ?></div>
      <div class="profile-role text-muted">
        <?php 
        echo htmlspecialchars($keuangan['kelas']); 
        if (!empty($keuangan['jurusan'])) {
          echo ' - ' . htmlspecialchars($keuangan['jurusan']);
        }
        ?>
      </div>
    </div>

    <nav class="sidebar-nav">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link" href="index.php">
            <i class="bi bi-house-door-fill me-2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="profile.php">
            <i class="bi bi-person-fill me-2"></i> Data Pribadi
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="finance.php">
            <i class="bi bi-cash-stack me-2"></i> Keuangan Saya
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- Overlay untuk mobile (menutup sidebar saat diklik) -->
  <div class="overlay d-none" id="overlay"></div>

  <div class="main-wrapper">
    <header class="topbar shadow-sm">
      <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <button id="sidebarToggle" class="btn btn-toggle btn-sm me-3">☰</button>
          <h1 class="app-title mb-0">Portal Siswa - BBA</h1>
        </div>
        <div class="d-flex align-items-center">
          <div class="dropdown">
            <button class="btn btn-outline-light btn-sm d-flex align-items-center gap-2" 
                    type="button" 
                    id="userDropdown" 
                    onclick="toggleUserDropdown(event)">
              <span class="d-none d-md-inline" style="font-size: 0.875rem;"><?php echo htmlspecialchars($adminName); ?></span>
              <i class="bi bi-person-circle" style="font-size: 1.25rem;"></i>
              <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4><i class="bi bi-receipt me-2"></i>Detail Pembayaran Bulanan</h4>
          <a href="finance.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
          </a>
        </div>

        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Siswa</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($keuangan['nama']); ?></p>
                <p><strong>NIS:</strong> <?php echo htmlspecialchars($keuangan['nis']); ?></p>
              </div>
              <div class="col-md-6">
                <p><strong>Kelas:</strong> <?php echo htmlspecialchars($keuangan['kelas']); ?></p>
                <p><strong>Tahun Ajaran:</strong> <?php echo htmlspecialchars($keuangan['tahun']); ?></p>
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th width="5%">No</th>
                <th width="15%">Bulan</th>
                <th width="15%">Tagihan</th>
                <th width="15%">Metode</th>
                <th width="15%">Bank</th>
                <th width="15%">Status</th>
                <th width="20%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 1; $i <= 12; $i++): 
                $bulan = $bulanMap[$i];
                $sudahBayar = isset($pembayaranData[$i]);
                $pembayaran = $sudahBayar ? $pembayaranData[$i] : null;
              ?>
              <tr>
                <td><?php echo $i; ?></td>
                <td><strong><?php echo $bulan; ?></strong></td>
                <td>Rp <?php echo number_format($nominalPerBulan, 0, ',', '.'); ?></td>
                <td>
                  <?php 
                    if ($sudahBayar) {
                      echo htmlspecialchars($pembayaran['metode_bayar']);
                    } else {
                      echo '<span class="text-muted">-</span>';
                    }
                  ?>
                </td>
                <td>
                  <?php 
                    if ($sudahBayar) {
                      echo htmlspecialchars($pembayaran['tempat_bayar']);
                    } else {
                      echo '<span class="text-muted">-</span>';
                    }
                  ?>
                </td>
                <td>
                  <?php 
                    if ($sudahBayar) {
                      $status = $pembayaran['status_bayar'];
                      if ($status === 'valid') {
                        echo '<span class="status-valid"><i class="bi bi-check-circle"></i> LUNAS</span>';
                      } elseif ($status === 'menunggu') {
                        echo '<span class="status-menunggu"><i class="bi bi-clock"></i> MENUNGGU VERIFIKASI</span>';
                      } else {
                        echo '<span class="status-tolak"><i class="bi bi-x-circle"></i> DITOLAK</span>';
                      }
                    } else {
                      echo '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> Belum Dibayar</span>';
                    }
                  ?>
                </td>
                <td>
                  <?php 
                    if (!$sudahBayar || $pembayaran['status_bayar'] === 'tolak') {
                      echo '<button class="btn btn-sm btn-primary" onclick="bayarBulan(' . $i . ', \'' . $bulan . '\')">
                              <i class="bi bi-cash me-1"></i>Bayar
                            </button>';
                    } elseif ($pembayaran['status_bayar'] === 'menunggu') {
                      echo '<span class="badge bg-warning text-dark">Menunggu Verifikasi</span>';
                    } else {
                      echo '<span class="badge bg-success">Sudah Lunas</span>';
                    }
                  ?>
                </td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>

      </div>
    </main>
  </div>

  <!-- Modal Bayar -->
  <div class="modal fade" id="modalBayar" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Upload Bukti Pembayaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" name="keuangan_id" value="<?php echo $keuangan_id; ?>">
            <input type="hidden" name="index_bulan" id="indexBulan">
            <input type="hidden" name="redirect_siswa" value="1">
            
            <div class="alert alert-info">
              <strong>Bulan:</strong> <span id="namaBulan"></span><br>
              <strong>Nominal:</strong> Rp <?php echo number_format($nominalPerBulan, 0, ',', '.'); ?>
            </div>

            <div class="mb-3">
              <label class="form-label">Tanggal Bayar</label>
              <input type="date" class="form-control" name="tanggal_bayar" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Nominal Bayar</label>
              <input type="number" class="form-control" name="nominal_bayar" 
                     value="<?php echo $nominalPerBulan; ?>" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Metode Pembayaran</label>
              <select class="form-select" name="metode_bayar" required onchange="toggleBankPayment(this.value)">
                <option value="">Pilih Metode</option>
                <option value="Transfer">Transfer Bank</option>
                <option value="Tunai">Tunai</option>
              </select>
            </div>

            <div class="mb-3" id="bank_wrapper">
              <label class="form-label">Bank Tujuan</label>
              <select class="form-select" name="tempat_bayar">
                <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
              </select>
              <div class="form-text">Hanya tersedia transfer ke rekening BRI</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Bukti Pembayaran (JPG/PNG/PDF)</label>
              <input type="file" class="form-control" name="bukti_bayar" 
                     accept=".jpg,.jpeg,.png,.pdf" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Upload Bukti</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  
  <script>
  // Toggle bank wrapper based on payment method
  function toggleBankPayment(metode) {
    const bankWrapper = document.getElementById('bank_wrapper');
    const bankSelect = document.querySelector('select[name="tempat_bayar"]');
    
    if (metode === 'Transfer') {
      bankWrapper.style.display = 'block';
      bankSelect.required = true;
      bankSelect.value = 'BRI';
    } else if (metode === 'Tunai') {
      bankWrapper.style.display = 'none';
      bankSelect.required = false;
      bankSelect.value = 'Kas Sekolah';
    } else {
      bankWrapper.style.display = 'none';
      bankSelect.required = false;
      bankSelect.value = '';
    }
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
  
  function bayarBulan(index, bulan) {
    document.getElementById('indexBulan').value = index;
    document.getElementById('namaBulan').textContent = bulan;
    const modal = new bootstrap.Modal(document.getElementById('modalBayar'));
    modal.show();
  }
  
  // Handle form submission with AJAX
  const formBayar = document.querySelector('#modalBayar form');
  if (formBayar) {
    formBayar.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...';
      
      fetch('../proses_pembayaran.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(data.message);
          window.location.reload(); // Reload page to show updated status
        } else {
          alert('Error: ' + data.message);
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }
      })
      .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      });
    });
  }
  </script>
</body>
</html>
<?php $conn->close(); ?>
