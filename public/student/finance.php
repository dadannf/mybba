<?php
// =============================================
// Halaman: Keuangan Siswa
// Deskripsi: Menampilkan data keuangan dan pembayaran siswa yang login
// =============================================

session_start();

require_once __DIR__ . '/../config.php';

// Cek session manual
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu!';
    header('Location: /auth/login.php');
    exit;
}

$userRole = $_SESSION['role'];

// Pastikan yang login adalah siswa
if ($userRole !== 'siswa') {
    $_SESSION['error'] = 'Akses ditolak! Halaman ini hanya untuk siswa.';
    header('Location: ../index.php');
    exit;
}

// Ambil data siswa berdasarkan username (asumsi username = NIS)
$username = $_SESSION['username'];

// Query untuk mendapatkan data siswa
$sqlSiswa = "SELECT * FROM siswa WHERE nis = '$username' LIMIT 1";
$resultSiswa = $conn->query($sqlSiswa);

if ($resultSiswa->num_rows === 0) {
    $_SESSION['error'] = 'Data siswa tidak ditemukan!';
    header('Location: index.php');
    exit;
}

$siswa = $resultSiswa->fetch_assoc();

// Query untuk mendapatkan data keuangan siswa
$sqlKeuangan = "SELECT * FROM keuangan WHERE nis = '$username' ORDER BY tahun DESC";
$resultKeuangan = $conn->query($sqlKeuangan);

// Fungsi untuk menentukan tagihan per bulan berdasarkan kelas
function getTagihanPerBulan($kelas) {
    // Ambil angka kelas dari string (misal: "10 IPA 1" -> 10)
    preg_match('/^(\d+)/', $kelas, $matches);
    $tingkatKelas = isset($matches[1]) ? intval($matches[1]) : 10;
    
    // Kelas 10: Rp 200.000, Kelas 11-12: Rp 190.000
    if ($tingkatKelas == 10) {
        return 200000;
    } else {
        return 190000; // Kelas 11 dan 12
    }
}

// Array bulan
$bulanList = [
    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Keuangan - SMK BIT BINA AULIA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS Custom Files -->
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/custom-components.css">
  <link rel="stylesheet" href="/css/responsive.css">
  <link rel="stylesheet" href="/css/siswa-portal.css">
  
  <style>
    /* User Dropdown Styles - Inline untuk portal siswa */
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
    
    .keuangan-card {
      border-left: 4px solid #667eea;
      transition: transform 0.2s;
    }
    
    .keuangan-card:hover {
      transform: translateX(5px);
    }
    
    .progress-custom {
      height: 25px;
      border-radius: 10px;
    }
    
    .badge-lunas {
      background: #28a745;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
    }
    
    .badge-belum {
      background: #ffc107;
      color: #000;
      padding: 0.5rem 1rem;
      border-radius: 20px;
    }
    
    /* Pulse animation for auto-approved button */
    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
      }
      50% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
      }
    }
    
    .pulse-animation {
      animation: pulse 1.5s infinite;
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
          <a class="nav-link" href="profile.php">
            <i class="bi bi-person-fill me-2"></i> 
            <span class="nav-text">Data Pribadi</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="finance.php">
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
              <span class="d-none d-md-inline text-white" style="font-size: 0.875rem;"><?php echo htmlspecialchars($siswa['nama'] ?? 'Siswa'); ?></span>
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
        
        <div class="card mb-4 shadow-sm">
          <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="mb-0">
              <i class="bi bi-wallet2 me-2"></i>
              Informasi Keuangan - <?php echo htmlspecialchars($siswa['nama']); ?>
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <p><strong>NIS:</strong> <?php echo htmlspecialchars($siswa['nis']); ?></p>
                <p><strong>Kelas:</strong> <?php echo htmlspecialchars($siswa['kelas']); ?></p>
              </div>
              <div class="col-md-6">
                <p><strong>Jurusan:</strong> <?php echo htmlspecialchars($siswa['jurusan']); ?></p>
                <p><strong>Status:</strong> 
                  <span class="badge bg-success"><?php echo strtoupper($siswa['status_siswa']); ?></span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <?php if ($resultKeuangan->num_rows === 0): ?>
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Belum ada data keuangan. Silakan hubungi bagian administrasi.
          </div>
        <?php else: ?>
          <?php while ($keuangan = $resultKeuangan->fetch_assoc()): 
            // Tagihan per bulan berdasarkan kelas siswa
            $tagihanPerBulan = getTagihanPerBulan($siswa['kelas']);
            
            // Query pembayaran yang sudah ada untuk keuangan ini
            $keuangan_id = $keuangan['keuangan_id'];
            $sqlPembayaran = "SELECT pembayaran_id, keuangan_id, tanggal_bayar, nominal_bayar, metode, tempat_bayar, bukti_bayar, status 
                              FROM pembayaran 
                              WHERE keuangan_id = '$keuangan_id' 
                              ORDER BY pembayaran_id ASC";
            $resultPembayaran = $conn->query($sqlPembayaran);

            // Simpan pembayaran dalam array - asumsi pembayaran berurutan dari bulan 1-12
            $pembayaranData = [];
            $indexBulan = 1;
            $bulanTerakhirValid = 0; // Bulan terakhir yang sudah valid/menunggu
            
            while ($bayar = $resultPembayaran->fetch_assoc()) {
                if ($indexBulan <= 12) {
                    $pembayaranData[$indexBulan] = $bayar;
                    
                    // Hitung bulan terakhir yang statusnya valid atau menunggu
                    if ($bayar['status'] === 'valid' || $bayar['status'] === 'menunggu') {
                        $bulanTerakhirValid = $indexBulan;
                    }
                    
                    $indexBulan++;
                }
            }
            
            // Bulan berikutnya yang bisa dibayar adalah bulan setelah bulan terakhir yang valid/menunggu
            $bulanBerikutnyaBisaDibayar = $bulanTerakhirValid + 1;

            // Ambil data real-time dari database (progress otomatis dihitung)
            $totalTagihan = floatval($keuangan['total_tagihan']);
            $totalTerbayar = floatval($keuangan['total_bayar']); // Real-time dari database
            $persentase = floatval($keuangan['progress'] ?? 0); // Progress otomatis dari database
            $sisaTunggakan = $totalTagihan - $totalTerbayar;
            $isLunas = $sisaTunggakan <= 0;
          ?>
          
          <!-- Card Info Keuangan -->
          <div class="card mb-4 shadow-sm" id="keuangan-<?php echo $keuangan_id; ?>">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
              <h5 class="mb-0">
                <i class="bi bi-calendar-event me-2"></i>
                Tahun Ajaran <?php echo htmlspecialchars($keuangan['tahun']); ?>
              </h5>
            </div>
            <div class="card-body">
              <!-- Summary Cards -->
              <div class="row g-3 mb-4">
                <div class="col-md-3">
                  <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="card-body">
                      <small class="opacity-75 d-block">Total Tagihan</small>
                      <h5 class="mb-0">Rp <?php echo number_format($totalTagihan, 0, ',', '.'); ?></h5>
                      <small class="opacity-75">12 Bulan</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body">
                      <small class="opacity-75 d-block">Terbayar</small>
                      <h5 class="mb-0">Rp <span class="total-terbayar"><?php echo number_format($totalTerbayar, 0, ',', '.'); ?></span></h5>
                      <small class="opacity-75 bulan-terbayar"><?php echo count($pembayaranData); ?> dari 12 bulan</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card border-0 shadow-sm bg-warning text-dark">
                    <div class="card-body">
                      <small class="opacity-75 d-block">Tunggakan</small>
                      <h5 class="mb-0">Rp <span class="sisa-tunggakan"><?php echo number_format($sisaTunggakan, 0, ',', '.'); ?></span></h5>
                      <small class="opacity-75 bulan-tersisa"><?php echo (12 - count($pembayaranData)); ?> bulan tersisa</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body">
                      <small class="opacity-75 d-block">Progress</small>
                      <h5 class="mb-0 progress-value"><?php echo number_format($persentase, 1); ?>%</h5>
                      <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-white" style="width: <?php echo $persentase; ?>%"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tabel Pembayaran Bulanan -->
              <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 5%">No</th>
                      <th style="width: 15%">Bulan</th>
                      <th style="width: 15%">Tagihan</th>
                      <th style="width: 15%">Metode</th>
                      <th style="width: 20%">Tempat Bayar</th>
                      <th style="width: 15%">Status</th>
                      <th style="width: 15%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $no = 1;
                    foreach ($bulanList as $indexBulan => $bulan): 
                      $sudahBayar = isset($pembayaranData[$indexBulan]);
                      $nominal = $sudahBayar ? $pembayaranData[$indexBulan]['nominal_bayar'] : 0;
                      $metode = $sudahBayar ? strtoupper($pembayaranData[$indexBulan]['metode']) : '-';
                      $tempatBayar = $sudahBayar ? ($pembayaranData[$indexBulan]['tempat_bayar'] ?? '-') : '-';
                      $status = $sudahBayar ? $pembayaranData[$indexBulan]['status'] : 'belum';
                      $pembayaranId = $sudahBayar ? $pembayaranData[$indexBulan]['pembayaran_id'] : null;
                      
                      // Tentukan apakah bulan ini bisa dibayar
                      // Logika: 
                      // 1. Jika bulan ini belum dibayar DAN merupakan bulan berikutnya yang bisa dibayar → BISA
                      // 2. Jika bulan ini statusnya 'tolak' → BISA (bayar ulang)
                      // 3. Selain itu → TIDAK BISA
                      $bisaDibayar = false;
                      $alasanTidakBisa = '';
                      
                      if (!$sudahBayar) {
                          if ($indexBulan == $bulanBerikutnyaBisaDibayar) {
                              $bisaDibayar = true;
                          } else if ($indexBulan > $bulanBerikutnyaBisaDibayar) {
                              $alasanTidakBisa = 'Belum Terbuka';
                          }
                      } else {
                          if ($status == 'tolak') {
                              $bisaDibayar = true;
                          }
                      }
                      
                      // Status badge color
                      if ($status == 'valid') {
                        $statusClass = 'success';
                        $statusIcon = 'check-circle-fill';
                      } elseif ($status == 'menunggu') {
                        $statusClass = 'warning';
                        $statusIcon = 'clock-fill';
                      } elseif ($status == 'tolak') {
                        $statusClass = 'danger';
                        $statusIcon = 'x-circle-fill';
                      } else {
                        $statusClass = 'secondary';
                        $statusIcon = 'dash-circle';
                      }
                    ?>
                    <tr>
                      <td class="text-center"><?php echo $no++; ?></td>
                      <td><strong><?php echo $bulan; ?></strong></td>
                      <td>Rp <?php echo number_format($tagihanPerBulan, 0, ',', '.'); ?></td>
                      <td>
                        <?php if ($sudahBayar): ?>
                          <span class="badge bg-info"><?php echo $metode; ?></span>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($sudahBayar): ?>
                          <small><?php echo htmlspecialchars($tempatBayar); ?></small>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge bg-<?php echo $statusClass; ?>">
                          <i class="bi bi-<?php echo $statusIcon; ?> me-1"></i>
                          <?php echo ucfirst($status); ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($sudahBayar && $status == 'valid'): ?>
                          <span class="text-success"><small><i class="bi bi-check-circle"></i> Lunas</small></span>
                        <?php elseif ($sudahBayar && $status == 'menunggu'): ?>
                          <span class="text-warning"><small><i class="bi bi-clock"></i> Menunggu Validasi</small></span>
                        <?php elseif ($bisaDibayar): ?>
                          <?php if ($status == 'tolak'): ?>
                            <button onclick="openBayarModal(<?php echo $keuangan['keuangan_id']; ?>, <?php echo $indexBulan; ?>, '<?php echo $bulan; ?>', <?php echo $tagihanPerBulan; ?>)" class="btn btn-danger btn-sm">
                              <i class="bi bi-arrow-repeat"></i> Bayar Ulang
                            </button>
                          <?php else: ?>
                            <button onclick="openBayarModal(<?php echo $keuangan['keuangan_id']; ?>, <?php echo $indexBulan; ?>, '<?php echo $bulan; ?>', <?php echo $tagihanPerBulan; ?>)" class="btn btn-primary btn-sm">
                              <i class="bi bi-cash"></i> Bayar
                            </button>
                          <?php endif; ?>
                        <?php else: ?>
                          <?php if ($alasanTidakBisa): ?>
                            <span class="text-muted"><small><i class="bi bi-lock"></i> <?php echo $alasanTidakBisa; ?></small></span>
                          <?php else: ?>
                            <span class="text-muted"><small>-</small></span>
                          <?php endif; ?>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <tfoot class="table-light">
                    <tr>
                      <th colspan="2" class="text-end">TOTAL</th>
                      <th>Rp <?php echo number_format($totalTagihan, 0, ',', '.'); ?></th>
                      <th colspan="3">
                        Terbayar: <span class="text-success fw-bold">Rp <span class="footer-terbayar"><?php echo number_format($totalTerbayar, 0, ',', '.'); ?></span></span>
                        <?php if ($sisaTunggakan > 0): ?>
                          <span class="footer-sisa-wrapper">| Sisa: <span class="text-danger fw-bold">Rp <span class="footer-sisa"><?php echo number_format($sisaTunggakan, 0, ',', '.'); ?></span></span></span>
                        <?php endif; ?>
                      </th>
                      <th>
                        <?php if ($isLunas): ?>
                          <span class="badge bg-success status-badge"><i class="bi bi-check-circle me-1"></i> LUNAS</span>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark status-badge"><i class="bi bi-exclamation-circle me-1"></i> BELUM LUNAS</span>
                        <?php endif; ?>
                      </th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          
          <?php endwhile; ?>
        <?php endif; ?>

        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Informasi Pembayaran:</strong>
          <ul class="mb-0 mt-2">
            <li><strong>Pembayaran harus dilakukan secara berurutan</strong> (Januari → Februari → Maret, dst.)</li>
            <li>Bulan berikutnya akan <strong>terbuka otomatis</strong> setelah bulan sebelumnya dibayar (status Valid atau Menunggu)</li>
            <li>Jika pembayaran ditolak oleh admin, silakan <strong>bayar ulang</strong> dengan bukti yang benar</li>
            <li>Upload bukti pembayaran untuk mempercepat proses validasi</li>
            <li>Pembayaran akan diverifikasi oleh admin dalam 1x24 jam</li>
          </ul>
        </div>

      </div>
    </main>
  </div>

  <!-- Modal Form Pembayaran -->
  <div class="modal fade" id="modalBayar" tabindex="-1" aria-labelledby="modalBayarLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalBayarLabel">
            <i class="bi bi-cash-coin me-2"></i>Form Pembayaran
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formBayar" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" id="bayar_keuangan_id" name="keuangan_id">
            <input type="hidden" id="bayar_index_bulan" name="index_bulan">
            
            <div class="alert alert-info">
              <strong>Bulan:</strong> <span id="info_bulan">-</span><br>
              <strong>Tagihan:</strong> <span id="info_tagihan">-</span>
            </div>

            <div class="mb-3">
              <label for="tanggal_bayar" class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="tanggal_bayar" name="tanggal_bayar" required>
            </div>

            <div class="mb-3">
              <label for="nominal_bayar" class="form-label">Nominal Bayar <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="nominal_bayar" name="nominal_bayar" min="1" required>
              <div class="form-text">Minimal Rp 1</div>
            </div>

            <div class="mb-3">
              <label for="metode" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
              <select class="form-select" id="metode" name="metode" required>
                <option value="">-- Pilih Metode --</option>
                <option value="Transfer">Transfer Bank</option>
              </select>
              <div class="form-text">
                <i class="bi bi-info-circle"></i> Siswa hanya dapat melakukan pembayaran via Transfer Bank. 
                Untuk pembayaran Tunai, hubungi bagian administrasi.
              </div>
            </div>

            <div class="mb-3">
              <label for="tempat_bayar" class="form-label">Bank <span class="text-danger">*</span></label>
              <select class="form-select" id="tempat_bayar" name="tempat_bayar" required>
                <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
              </select>
              <div class="form-text">Hanya tersedia transfer ke rekening BRI</div>
                <option value="Sekolah">Sekolah</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="bukti_bayar" class="form-label">
                <i class="bi bi-image"></i> Bukti Pembayaran 
                <span class="badge bg-info">Auto Validasi OCR</span>
              </label>
              
              <!-- Button Group untuk Upload atau Ambil Foto -->
              <div class="btn-group w-100 mb-2" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('bukti_bayar').click()">
                  <i class="bi bi-upload"></i> Upload File
                </button>
                <button type="button" class="btn btn-outline-success" onclick="openCamera()">
                  <i class="bi bi-camera"></i> Ambil Foto
                </button>
              </div>
              
              <input type="file" class="form-control d-none" id="bukti_bayar" name="bukti_bayar" accept="image/*,.pdf">
              
              <!-- Preview Image -->
              <div id="image_preview" class="mb-2" style="display: none;">
                <img id="preview_img" src="" alt="Preview" class="img-fluid rounded border" style="max-height: 300px;">
                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImage()">
                  <i class="bi bi-x-circle"></i> Hapus
                </button>
              </div>
              
              <div class="form-text">
                <i class="bi bi-robot"></i> Sistem akan validasi bukti transfer otomatis menggunakan AI OCR
              </div>
            </div>
            
            <!-- Modal Camera -->
            <div class="modal fade" id="cameraModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                      <i class="bi bi-camera-fill me-2"></i>Ambil Foto Bukti Transfer
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeCamera()"></button>
                  </div>
                  <div class="modal-body text-center">
                    <div id="cameraError" class="alert alert-danger" style="display: none;"></div>
                    
                    <video id="camera_video" autoplay playsinline style="width: 100%; max-width: 640px; border-radius: 8px; transform: scaleX(-1);"></video>
                    <canvas id="camera_canvas" style="display: none;"></canvas>
                    
                    <div class="alert alert-info mt-3 mb-0">
                      <i class="bi bi-info-circle me-2"></i>
                      <small>Posisikan bukti transfer dengan jelas dan pastikan semua teks terbaca</small>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCamera()">
                      <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-success" onclick="capturePhoto()">
                      <i class="bi bi-camera-fill me-1"></i>Ambil Foto
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- OCR Result Section (hidden by default) -->
            <div id="ocr_result_section" class="mb-3" style="display: none;">
              <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                  <i class="bi bi-robot"></i> Hasil Validasi OCR
                </div>
                <div class="card-body" id="ocr_result_content">
                  <!-- OCR results will be inserted here -->
                </div>
              </div>
            </div>

            <div class="alert alert-warning mb-0">
              <i class="bi bi-info-circle me-1"></i>
              <small>Status pembayaran akan ditentukan otomatis berdasarkan hasil validasi OCR.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSubmitPayment" disabled>
              <i class="bi bi-upload me-1"></i>Upload Bukti Transfer Dulu
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  
  <script>
  let modalBayar;
  
  document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi modal
    const modalElement = document.getElementById('modalBayar');
    if (modalElement) {
      modalBayar = new bootstrap.Modal(modalElement);
    }
    
    // Set tanggal bayar default ke hari ini
    const tanggalInput = document.getElementById('tanggal_bayar');
    if (tanggalInput) {
      const today = new Date().toISOString().split('T')[0];
      tanggalInput.value = today;
    }
    
    // Handle file upload - Auto OCR validation
    const buktiBayarInput = document.getElementById('bukti_bayar');
    if (buktiBayarInput) {
      buktiBayarInput.addEventListener('change', async function(e) {
        if (this.files && this.files[0]) {
          const file = this.files[0];
          
          // Show preview
          const preview = document.getElementById('image_preview');
          const previewImg = document.getElementById('preview_img');
          previewImg.src = URL.createObjectURL(file);
          preview.style.display = 'block';
          
          try {
            await validateWithOCR(file);
          } catch (error) {
            // Error already handled in validateWithOCR, but log here for debugging
            console.error('File upload handler error:', error);
            // Optionally show toast notification
            if (typeof showToast === 'function') {
              showToast('error', 'OCR validation error. Cek console untuk detail.');
            }
          }
        }
      });
    }
    
    // Handle form submit
    const formBayar = document.getElementById('formBayar');
    if (formBayar) {
      formBayar.addEventListener('submit', function(e) {
        e.preventDefault();
        submitPembayaran();
      });
    }
  });
  
  // ==================== CAMERA FUNCTIONS ====================
  let cameraStream = null;
  
  // Open camera modal and start video stream
  async function openCamera() {
    const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
    const video = document.getElementById('camera_video');
    const errorDiv = document.getElementById('cameraError');
    
    modal.show();
    errorDiv.style.display = 'none';
    
    try {
      // Request camera access
      cameraStream = await navigator.mediaDevices.getUserMedia({ 
        video: { 
          facingMode: 'environment', // Use back camera on mobile
          width: { ideal: 1920 },
          height: { ideal: 1080 }
        } 
      });
      
      video.srcObject = cameraStream;
      video.play();
    } catch (error) {
      console.error('Camera access error:', error);
      errorDiv.textContent = 'Tidak dapat mengakses kamera: ' + error.message;
      errorDiv.style.display = 'block';
    }
  }
  
  // Capture photo from video stream
  async function capturePhoto() {
    const video = document.getElementById('camera_video');
    const canvas = document.getElementById('camera_canvas');
    const context = canvas.getContext('2d');
    
    // Set canvas size to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw current video frame to canvas (without mirroring the result)
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert canvas to blob
    canvas.toBlob(async (blob) => {
      if (!blob) {
        alert('Gagal mengambil foto. Coba lagi.');
        return;
      }
      
      // Create file from blob
      const file = new File([blob], 'camera_capture.jpg', { type: 'image/jpeg' });
      
      // Close camera and modal
      closeCamera();
      
      // Show preview
      const preview = document.getElementById('image_preview');
      const previewImg = document.getElementById('preview_img');
      previewImg.src = URL.createObjectURL(blob);
      preview.style.display = 'block';
      
      // Trigger OCR validation
      try {
        await validateWithOCR(file);
      } catch (error) {
        console.error('OCR validation error:', error);
        if (typeof showToast === 'function') {
          showToast('error', 'Validasi OCR gagal. Coba lagi.');
        }
      }
    }, 'image/jpeg', 0.9);
  }
  
  // Close camera and stop stream
  function closeCamera() {
    if (cameraStream) {
      cameraStream.getTracks().forEach(track => track.stop());
      cameraStream = null;
    }
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('cameraModal'));
    if (modal) {
      modal.hide();
    }
  }
  
  // Clear image preview
  function clearImage() {
    const preview = document.getElementById('image_preview');
    const previewImg = document.getElementById('preview_img');
    const fileInput = document.getElementById('bukti_bayar');
    const ocrSection = document.getElementById('ocr_result_section');
    const submitBtn = document.getElementById('btnSubmitPayment');
    
    // Clear preview
    previewImg.src = '';
    preview.style.display = 'none';
    
    // Clear file input
    fileInput.value = '';
    
    // Reset button state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-upload me-1"></i>Upload Bukti Transfer Dulu';
    submitBtn.classList.remove('btn-success', 'btn-danger', 'btn-warning', 'pulse-animation');
    submitBtn.classList.add('btn-primary');
    
    // Hide OCR result
    ocrSection.style.display = 'none';
    
    // Clear OCR data
    const formBayar = document.getElementById('formBayar');
    delete formBayar.dataset.ocrResult;
    delete formBayar.dataset.ocrDecision;
  }
  // ==================== END CAMERA FUNCTIONS ====================
  
  function openBayarModal(keuanganId, indexBulan, namaBulan, tagihan) {
    // Pastikan modal sudah diinisialisasi
    if (!modalBayar) {
      const modalElement = document.getElementById('modalBayar');
      if (modalElement) {
        modalBayar = new bootstrap.Modal(modalElement);
      } else {
        alert('Error: Modal element tidak ditemukan');
        return;
      }
    }
    
    // Set data ke hidden inputs
    document.getElementById('bayar_keuangan_id').value = keuanganId;
    document.getElementById('bayar_index_bulan').value = indexBulan;
    
    // Set info bulan dan tagihan
    document.getElementById('info_bulan').textContent = namaBulan;
    document.getElementById('info_tagihan').textContent = 'Rp ' + tagihan.toLocaleString('id-ID');
    
    // Set nominal default
    document.getElementById('nominal_bayar').value = tagihan;
    
    // Set tanggal bayar default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_bayar').value = today;
    
    // Reset form state
    const formBayar = document.getElementById('formBayar');
    const submitBtn = document.getElementById('btnSubmitPayment');
    const fileInput = document.getElementById('bukti_bayar');
    const ocrSection = document.getElementById('ocr_result_section');
    const preview = document.getElementById('image_preview');
    const previewImg = document.getElementById('preview_img');
    
    // Clear file input
    fileInput.value = '';
    
    // Clear image preview
    if (previewImg) {
      previewImg.src = '';
    }
    if (preview) {
      preview.style.display = 'none';
    }
    
    // Reset button state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class=\"bi bi-upload me-1\"></i>Upload Bukti Transfer Dulu';
    submitBtn.classList.remove('btn-success', 'btn-danger', 'btn-warning', 'pulse-animation');
    submitBtn.classList.add('btn-primary');
    
    // Hide OCR result
    ocrSection.style.display = 'none';
    
    // Clear OCR data
    delete formBayar.dataset.ocrResult;
    delete formBayar.dataset.ocrDecision;
    
    // Set default values for student (Transfer only)
    document.getElementById('metode').value = 'Transfer';
    document.getElementById('tempat_bayar').value = 'BRI';
    
    // Tampilkan modal
    modalBayar.show();
  }
  
  // Fungsi validasi OCR
  async function validateWithOCR(file) {
    const ocrSection = document.getElementById('ocr_result_section');
    const ocrContent = document.getElementById('ocr_result_content');
    
    // Show loading
    ocrSection.style.display = 'block';
    ocrContent.innerHTML = `
      <div class="text-center py-4">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mb-0"><i class="bi bi-robot"></i> Memvalidasi bukti transfer dengan AI OCR...</p>
        <small class="text-muted">Mohon tunggu beberapa detik</small>
      </div>
    `;
    
    try {
      // Prepare form data for OCR
      const formData = new FormData();
      formData.append('file', file);  // OCR API expects 'file' field
      
      // Get expected values from form
      const expectedAmount = document.getElementById('nominal_bayar').value;
      const expectedNIS = '<?php echo $siswa['nis']; ?>';
      const expectedNama = '<?php echo $siswa['nama']; ?>';
      
      // Client-side validation
      if (!file) {
        throw new Error('File tidak ditemukan');
      }
      if (!expectedAmount || parseFloat(expectedAmount) <= 0) {
        throw new Error('Nominal bayar tidak valid');
      }
      if (!expectedNIS || expectedNIS.trim() === '') {
        throw new Error('NIS tidak ditemukan');
      }
      if (!expectedNama || expectedNama.trim() === '') {
        throw new Error('Nama siswa tidak ditemukan');
      }
      
      // Check file size (max 10MB)
      const maxSize = 10 * 1024 * 1024; // 10MB
      if (file.size > maxSize) {
        throw new Error(`File terlalu besar (${(file.size / 1024 / 1024).toFixed(2)}MB). Maksimal 10MB.`);
      }
      
      // Check file type
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/bmp'];
      if (!allowedTypes.includes(file.type.toLowerCase())) {
        throw new Error(`Tipe file tidak didukung (${file.type}). Gunakan JPG, PNG, atau BMP.`);
      }
      
      // Add expected data as JSON in separate fields
      formData.append('expected_amount', expectedAmount);
      formData.append('expected_nis', expectedNIS);
      formData.append('expected_nama', expectedNama);
      formData.append('uploader_type', 'siswa');
      formData.append('uploader_id', expectedNIS);
      
      // Debug logging
      console.log('=== OCR Request Debug ===');
      console.log('File:', file.name, 'Size:', file.size, 'Type:', file.type);
      console.log('Expected Amount:', expectedAmount, 'Type:', typeof expectedAmount);
      console.log('Expected NIS:', expectedNIS, 'Type:', typeof expectedNIS);
      console.log('Expected Nama:', expectedNama, 'Type:', typeof expectedNama);
      console.log('FormData entries:');
      for (let [key, value] of formData.entries()) {
        console.log(`  ${key}:`, value instanceof File ? `File(${value.name})` : value);
      }
      console.log('========================');
      
      // Call OCR API with timeout
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 30000); // 30s timeout
      
      // Dynamic OCR API URL - support ngrok/production
      const ocrApiUrl = '<?php echo OCR_API_URL; ?>/api/v1/validate-transfer';
      console.log('OCR API URL:', ocrApiUrl);
      
      let response;
      try {
        response = await fetch(ocrApiUrl, {
          method: 'POST',
          body: formData,
          signal: controller.signal
        });
      } catch (fetchError) {
        if (fetchError.name === 'AbortError') {
          throw new Error('OCR validation timeout (30s). Server mungkin sedang sibuk.');
        }
        throw new Error('Tidak dapat terhubung ke OCR server. Pastikan server berjalan.');
      } finally {
        clearTimeout(timeoutId);
      }
      
      if (!response.ok) {
        // Enhanced error handling - get detailed error from server
        let errorMessage = 'OCR API error: ' + response.statusText;
        try {
          const errorData = await response.json();
          // FastAPI/Uvicorn typically returns 'detail' field for errors
          if (errorData.detail) {
            errorMessage += '\n\nDetails: ' + (typeof errorData.detail === 'string' 
              ? errorData.detail 
              : JSON.stringify(errorData.detail, null, 2));
          } else if (errorData.error) {
            errorMessage += '\n\nError: ' + JSON.stringify(errorData.error, null, 2);
          }
          console.error('Server error response:', errorData);
        } catch (parseError) {
          // If response is not JSON
          console.warn('Could not parse error response as JSON:', parseError);
          try {
            const textError = await response.text();
            if (textError) {
              errorMessage += '\n\nRaw response: ' + textError.substring(0, 300);
              console.error('Server error (text):', textError);
            }
          } catch (textError) {
            console.error('Could not read error response:', textError);
          }
        }
        throw new Error(errorMessage);
      }
      
      const result = await response.json();
      
      // Validate response schema
      if (!result || typeof result !== 'object') {
        throw new Error('Invalid response format from OCR server');
      }
      
      // Display OCR results
      if (result.success) {
        // Defensive null checking
        const data = result.data || {};
        const decision = data.decision || 'review';
        const score = typeof data.validation_score === 'number' ? data.validation_score : 0;
        const parsedData = data.parsed_data || {};
        const confidenceScores = data.confidence_scores || {};
        
        // Badge color based on decision
        let badgeClass = 'bg-warning';
        let badgeIcon = 'exclamation-triangle';
        let badgeText = 'NEED REVIEW';
        
        if (decision === 'accept') {
          badgeClass = 'bg-success';
          badgeIcon = 'check-circle-fill';
          badgeText = 'AUTO APPROVED';
        } else if (decision === 'reject') {
          badgeClass = 'bg-danger';
          badgeIcon = 'x-circle-fill';
          badgeText = 'AUTO REJECTED';
        }
        
        ocrContent.innerHTML = `
          <div class="text-center mb-3">
            <span class="badge ${badgeClass} fs-5 px-4 py-2">
              <i class="bi bi-${badgeIcon}"></i> ${badgeText}
            </span>
          </div>
          
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <div class="card bg-light">
                <div class="card-body">
                  <h6 class="card-title text-muted mb-2">
                    <i class="bi bi-bank"></i> Bank Detected
                  </h6>
                  <p class="fs-5 fw-bold mb-0">${parsedData.bank_name || '-'}</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card bg-light">
                <div class="card-body">
                  <h6 class="card-title text-muted mb-2">
                    <i class="bi bi-cash"></i> Amount Detected
                  </h6>
                  <p class="fs-5 fw-bold mb-0">Rp ${(parsedData.transfer_amount || 0).toLocaleString('id-ID')}</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <h6><i class="bi bi-graph-up"></i> Confidence Scores</h6>
            <div class="mb-2">
              <small class="text-muted">Overall Score</small>
              <div class="progress" style="height: 20px;">
                <div class="progress-bar ${score >= 85 ? 'bg-success' : score >= 50 ? 'bg-warning' : 'bg-danger'}" 
                     style="width: ${score}%">
                  ${Math.round(score)}%
                </div>
              </div>
            </div>
            <div class="mb-2">
              <small class="text-muted">OCR Quality</small>
              <div class="progress" style="height: 15px;">
                <div class="progress-bar bg-info" style="width: ${((confidenceScores.overall_ocr || 0) * 100)}%">
                  ${Math.round((confidenceScores.overall_ocr || 0) * 100)}%
                </div>
              </div>
            </div>
            <div class="mb-2">
              <small class="text-muted">Amount Match</small>
              <div class="progress" style="height: 15px;">
                <div class="progress-bar ${(confidenceScores.amount_match || 0) >= 0.98 ? 'bg-success' : 'bg-warning'}" 
                     style="width: ${((confidenceScores.amount_match || 0) * 100)}%">
                  ${Math.round((confidenceScores.amount_match || 0) * 100)}%
                </div>
              </div>
            </div>
          </div>
          
          <div class="alert alert-${decision === 'accept' ? 'success' : decision === 'reject' ? 'danger' : 'warning'} mb-0">
            <strong><i class="bi bi-info-circle"></i> Decision:</strong><br>
            <small>${data.decision_reason || 'No decision reason provided'}</small>
          </div>
        `;
        
        // Store OCR result for submission
        const formBayar = document.getElementById('formBayar');
        formBayar.dataset.ocrResult = JSON.stringify(result);
        formBayar.dataset.ocrDecision = decision;
        
        // AUTO-ACTION based on decision
        if (decision === 'reject') {
          // REJECT: Block submission & force re-upload
          handleOCRRejection();
        } else if (decision === 'accept') {
          // ACCEPT: Auto-submit after confirmation
          handleOCRApproval();
        } else {
          // REVIEW: Enable manual submission
          handleOCRReview();
        }
        
      } else {
        throw new Error(result.message || 'OCR validation failed');
      }
      
    } catch (error) {
      console.error('OCR Error:', error);
      
      // Sanitize error message to prevent XSS
      const sanitizeHTML = (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
      };
      
      // Enhanced error display with sanitization and length limit
      const errorLines = (error.message || 'Unknown error').split('\n');
      const mainError = sanitizeHTML(errorLines[0].substring(0, 200)); // Limit 200 chars
      const details = errorLines.slice(1).join('\n').substring(0, 500); // Limit 500 chars
      const sanitizedDetails = sanitizeHTML(details);
      
      ocrContent.innerHTML = `
        <div class="alert alert-danger mb-0">
          <h6 class="alert-heading">
            <i class="bi bi-exclamation-triangle-fill"></i> OCR Validation Error
          </h6>
          <p class="mb-2"><strong>${mainError}</strong></p>
          ${sanitizedDetails ? `<hr><pre class="mb-0" style="font-size: 12px; white-space: pre-wrap;">${sanitizedDetails}</pre>` : ''}
          <hr>
          <small class="text-muted">
            <i class="bi bi-info-circle"></i> Pembayaran akan diproses manual oleh admin.
            <br>Jika error terus terjadi, silakan hubungi admin atau upload bukti transfer langsung tanpa validasi OCR.
          </small>
        </div>
      `;
    }
  }
  
  // Handler untuk OCR REJECT
  function handleOCRRejection() {
    const submitBtn = document.querySelector('#formBayar button[type="submit"]');
    const fileInput = document.getElementById('bukti_bayar');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-x-circle"></i> Pembayaran Ditolak';
    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-danger');
    
    // Clear file input
    fileInput.value = '';
    
    // Show alert to re-upload
    setTimeout(() => {
      if (confirm('❌ Bukti transfer DITOLAK!\n\nValidasi OCR menunjukkan bukti transfer tidak sesuai atau kualitas gambar kurang baik.\n\nSilakan upload bukti transfer yang BARU dengan:\n- Gambar lebih jelas (min 1000x1000px)\n- Nominal sesuai dengan tagihan\n- Tidak blur atau gelap\n\nUpload ulang sekarang?')) {
        fileInput.click();
      }
    }, 500);
  }
  
  // Handler untuk OCR ACCEPT
  function handleOCRApproval() {
    const submitBtn = document.querySelector('#formBayar button[type="submit"]');
    
    // Enable and highlight submit button
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Proses Pembayaran (Tervalidasi)';
    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-success');
    submitBtn.classList.add('pulse-animation');
    
    // Auto-submit after 2 seconds confirmation
    setTimeout(() => {
      if (confirm('✅ Bukti transfer TERVALIDASI!\n\nValidasi OCR berhasil dengan confidence tinggi.\n\nLanjutkan pembayaran otomatis?')) {
        submitBtn.click();
      }
    }, 1000);
  }
  
  // Handler untuk OCR REVIEW
  function handleOCRReview() {
    const submitBtn = document.querySelector('#formBayar button[type="submit"]');
    
    // Enable submit but with warning
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Proses Pembayaran (Perlu Review Admin)';
    submitBtn.classList.remove('btn-primary');
    submitBtn.classList.add('btn-warning');
    
    // Optional: Show info
    setTimeout(() => {
      alert('⚠️ Perlu Review Manual\n\nValidasi OCR tidak dapat memastikan keakuratan bukti transfer.\n\nAnda masih bisa submit pembayaran, tapi akan diverifikasi manual oleh admin.');
    }, 500);
  }
  
  function submitPembayaran() {
    const form = document.getElementById('formBayar');
    const formData = new FormData(form);
    
    // Check OCR decision before submit
    const ocrDecision = form.dataset.ocrDecision;
    if (ocrDecision === 'reject') {
      alert('❌ Tidak dapat submit!\n\nBukti transfer ditolak oleh sistem OCR.\nSilakan upload bukti transfer yang baru.');
      return false;
    }
    
    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
    
    // Show loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'paymentLoadingOverlay';
    loadingOverlay.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
    `;
    loadingOverlay.innerHTML = `
      <div class="card shadow-lg" style="max-width: 500px; width: 90%;">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">
            <i class="bi bi-cloud-upload"></i> Memproses Pembayaran
          </h5>
        </div>
        <div class="card-body text-center">
          <div class="mb-4">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          
          <div class="progress mb-3" style="height: 20px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" 
                 style="width: 50%" 
                 id="uploadProgressBar">
              <small>Uploading...</small>
            </div>
          </div>
          
          <div class="text-start mb-3">
            <div class="mb-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <small>Menyimpan data pembayaran...</small>
            </div>
            <div class="mb-2">
              <i class="bi bi-robot text-primary"></i>
              <small>Validasi OCR dengan AI...</small>
            </div>
            <div class="mb-2">
              <i class="bi bi-database text-info"></i>
              <small>Menyimpan hasil validasi...</small>
            </div>
          </div>
          
          <div class="alert alert-info mb-0">
            <i class="bi bi-robot"></i>
            <small>Sistem AI sedang memvalidasi bukti pembayaran Anda secara otomatis.</small>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(loadingOverlay);
    
    // Animate progress
    setTimeout(() => {
      if (document.getElementById('uploadProgressBar')) {
        document.getElementById('uploadProgressBar').style.width = '75%';
        document.getElementById('uploadProgressBar').innerHTML = '<small>Validating...</small>';
      }
    }, 1000);
    
    fetch('/api/process_payment_student.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      // Cek apakah response adalah JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        return response.text().then(text => {
          console.error('Server response (HTML):', text);
          throw new Error('Server mengembalikan HTML bukan JSON. Mungkin session timeout atau ada error PHP.');
        });
      }
      return response.json();
    })
    .then(data => {
      // Complete progress
      if (document.getElementById('uploadProgressBar')) {
        document.getElementById('uploadProgressBar').style.width = '100%';
        document.getElementById('uploadProgressBar').innerHTML = '<small>Complete!</small>';
      }

      
      // Remove loading overlay after short delay
      setTimeout(() => {
        if (loadingOverlay) {
          loadingOverlay.remove();
        }
        
        if (data.success) {
          // Hide modal bayar
          modalBayar.hide();
          
          // Show success message with OCR result
          let alertClass = 'success';
          let alertIcon = 'check-circle-fill';
          
          if (data.ocr_decision === 'reject') {
            alertClass = 'danger';
            alertIcon = 'x-circle-fill';
          } else if (data.ocr_decision === 'review') {
            alertClass = 'warning';
            alertIcon = 'exclamation-triangle-fill';
          }
          
          const alertDiv = document.createElement('div');
          alertDiv.className = `alert alert-${alertClass} alert-dismissible fade show position-fixed`;
          alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 400px; max-width: 500px;';
          alertDiv.innerHTML = `
            <i class="bi bi-${alertIcon} fs-4 me-2"></i>
            <strong>${data.message}</strong>
            ${data.auto_validated ? '<br><small>💰 Total bayar Anda telah otomatis diperbarui!</small>' : ''}
            ${data.ocr_decision === 'review' ? '<br><small>🔍 Admin akan segera memeriksa pembayaran Anda.</small>' : ''}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          `;
          document.body.appendChild(alertDiv);
          
          // Auto close after 5 seconds
          setTimeout(() => {
            alertDiv.remove();
            location.reload();
          }, 5000);
        } else {
          alert('Error: ' + data.message);
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }
      }, 800);
    })
    .catch(error => {
      console.error('Fetch error:', error);
      
      // Remove loading overlay
      if (loadingOverlay) {
        loadingOverlay.remove();
      }
      
      let errorMsg = 'Terjadi kesalahan: ' + error.message;
      
      // Cek apakah error karena JSON parse (HTML response)
      if (error.message.includes('JSON') || error.message.includes('HTML')) {
        errorMsg += '\n\n🔍 Kemungkinan penyebab:';
        errorMsg += '\n1. Session timeout - silakan refresh dan login ulang';
        errorMsg += '\n2. File PHP memiliki error';
        errorMsg += '\n\n💡 Solusi: Refresh halaman (F5) dan login ulang';
      }
      
      alert(errorMsg);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
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
  
  <!-- Real-time Keuangan Progress Updater -->
  <script src="/js/keuangan-progress.js"></script>
  <script>
    // Initialize auto-update untuk semua keuangan yang ditampilkan
    document.addEventListener('DOMContentLoaded', function() {
      <?php
      // Reset pointer result keuangan untuk ambil semua ID
      $resultKeuangan->data_seek(0);
      $keuanganIds = [];
      while ($keu = $resultKeuangan->fetch_assoc()) {
        $keuanganIds[] = $keu['keuangan_id'];
      }
      ?>
      const keuanganIds = <?php echo json_encode($keuanganIds); ?>;
      
      if (keuanganIds.length > 0) {
        // Start auto-update setiap 30 detik
        window.keuanganProgressUpdater.init(keuanganIds);
        console.log('✅ Real-time progress update diaktifkan untuk', keuanganIds.length, 'keuangan');
      }
    });
  </script>
  
</body>
</html>
<?php $conn->close(); ?>
