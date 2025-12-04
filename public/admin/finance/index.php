<?php
// =============================================
// Halaman: Keuangan Siswa
// Deskripsi: Manajemen data keuangan siswa dengan statistik real-time
// =============================================

require_once __DIR__ . '/../../config.php';

// Check authentication  
require_once __DIR__ . '/../../auth_check.php';// =============================================
// FILTER & SEARCH
// =============================================
$tahunFilter = isset($_GET['tahun']) ? esc($_GET['tahun']) : '';
$kelasFilter = isset($_GET['kelas']) ? esc($_GET['kelas']) : '';
$searchInput = isset($_GET['search']) ? esc($_GET['search']) : '';

$where = "1=1";
if ($tahunFilter) {
    $where .= " AND k.tahun = '$tahunFilter'";
}
if ($kelasFilter) {
    $where .= " AND s.kelas = '$kelasFilter'";
}
if ($searchInput) {
    $where .= " AND (s.nama LIKE '%$searchInput%' OR s.nis LIKE '%$searchInput%')";
}

$sql = "SELECT k.*, s.nama, s.kelas, s.jurusan 
        FROM keuangan k 
        INNER JOIN siswa s ON k.nis = s.nis 
        WHERE $where 
        ORDER BY k.tahun DESC, s.nama ASC";
$result = $conn->query($sql);

// Query untuk dropdown filter tahun
$sqlTahun = "SELECT DISTINCT tahun FROM keuangan ORDER BY tahun DESC";
$resultTahun = $conn->query($sqlTahun);

// Query untuk dropdown filter kelas
$sqlKelas = "SELECT DISTINCT s.kelas FROM keuangan k INNER JOIN siswa s ON k.nis = s.nis ORDER BY s.kelas ASC";
$resultKelas = $conn->query($sqlKelas);

// Query untuk statistik
$sqlStats = "SELECT 
    SUM(k.total_tagihan) as total_tagihan_all,
    SUM(k.total_bayar) as total_bayar_all,
    COUNT(*) as total_data
FROM keuangan k
INNER JOIN siswa s ON k.nis = s.nis
WHERE $where";
$resultStats = $conn->query($sqlStats);
$stats = $resultStats->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Keuangan - SMK BIT Bina Aulia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS Custom Files -->
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/notifications.css">
  <link rel="stylesheet" href="/css/responsive.css">
  <link rel="stylesheet" href="/css/custom-components.css">
  <link rel="stylesheet" href="/css/admin-portal.css">
  <link rel="stylesheet" href="/css/print-styles.css">
  
  <?php include __DIR__ . '/../../includes/navbar_style.php'; ?>
  <?php include __DIR__ . '/../../includes/user_dropdown_style.php'; ?>
</head>
<body class="has-sidebar">

  <!-- Sidebar -->
  <?php include __DIR__ . '/../../shared/components/sidebar.php'; ?>

  <!-- Overlay untuk mobile (menutup sidebar saat diklik) -->
  <div class="overlay d-none" id="overlay"></div>

  <!-- Konten utama -->
  <div class="main-wrapper">
    <header class="topbar shadow-sm">
      <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <button id="sidebarToggle" class="btn btn-toggle btn-sm me-3">☰</button>
          <h1 class="app-title mb-0">SMK BIT Bina Aulia</h1>
        </div>
        <?php include __DIR__ . '/../../includes/user_dropdown.php'; ?>
      </div>
    </header>

    <main class="content">
      <div class="container-fluid">
        
        <!-- Print Header (hanya muncul saat print) -->
        <div class="print-header">
          <h2>LAPORAN REKAP KEUANGAN SISWA</h2>
          <p>SMK BIT Bina Aulia</p>
          <p>Jl. Letda Natsir No. 582, Bojong Kulur, Gunungputri, Bogor</p>
          <p>Telp: 021-82415429 | NPSN: 20254256 | Akreditasi: A</p>
          <p style="font-size: 12px; margin-top: 10px;">
            Tanggal Cetak: <?php echo date('d F Y, H:i'); ?> WIB
          </p>
        </div>

        <!-- Print Info Filter (hanya muncul saat print jika ada filter) -->
        <?php if ($tahunFilter || $kelasFilter || $searchInput): ?>
        <div class="print-info">
          <strong>Filter Aktif:</strong>
          <?php if ($tahunFilter): ?>
            <span>Tahun Ajaran: <?php echo htmlspecialchars($tahunFilter); ?></span>
          <?php endif; ?>
          <?php if ($kelasFilter): ?>
            <span>| Kelas: <?php echo htmlspecialchars($kelasFilter); ?></span>
          <?php endif; ?>
          <?php if ($searchInput): ?>
            <span>| Pencarian: <?php echo htmlspecialchars($searchInput); ?></span>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Statistik untuk Print -->
        <div class="stats-print">
          <div class="stat-box">
            <h6>Total Tagihan</h6>
            <h4>Rp <?php echo number_format($stats['total_tagihan_all'] ?? 0, 0, ',', '.'); ?></h4>
          </div>
          <div class="stat-box">
            <h6>Total Terbayar</h6>
            <h4>Rp <?php echo number_format($stats['total_bayar_all'] ?? 0, 0, ',', '.'); ?></h4>
          </div>
          <div class="stat-box">
            <h6>Tunggakan</h6>
            <h4>Rp <?php echo number_format(($stats['total_tagihan_all'] ?? 0) - ($stats['total_bayar_all'] ?? 0), 0, ',', '.'); ?></h4>
          </div>
        </div>
        
        <!-- Notifikasi Success/Error -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php 
            echo htmlspecialchars($_SESSION['success']); 
            unset($_SESSION['success']);
          ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
          ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Header halaman -->
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4><i class="bi bi-cash-stack me-2"></i> Data Keuangan Siswa</h4>
              <p class="text-muted mb-0"><small>Data keuangan dibuat otomatis saat menambah siswa baru</small></p>
            </div>
            <div class="no-print">
              <button onclick="showPrintModal()" class="btn btn-success">
                <i class="bi bi-printer me-1"></i> Print Laporan
              </button>
            </div>
          </div>
        </div>

        <!-- Statistik Cards -->
        <div class="row g-3 mb-4 no-print">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1 opacity-75">Total Tagihan</h6>
                    <h3 class="mb-0 fw-bold">Rp <?php echo number_format($stats['total_tagihan_all'] ?? 0, 0, ',', '.'); ?></h3>
                  </div>
                  <i class="bi bi-receipt fs-1 opacity-50"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1 opacity-75">Total Terbayar</h6>
                    <h3 class="mb-0 fw-bold">Rp <?php echo number_format($stats['total_bayar_all'] ?? 0, 0, ',', '.'); ?></h3>
                  </div>
                  <i class="bi bi-cash-coin fs-1 opacity-50"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1 opacity-75">Tunggakan</h6>
                    <h3 class="mb-0 fw-bold">Rp <?php echo number_format(($stats['total_tagihan_all'] ?? 0) - ($stats['total_bayar_all'] ?? 0), 0, ',', '.'); ?></h3>
                  </div>
                  <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Form -->
        <div class="card border-0 shadow-sm mb-4 no-print">
          <div class="card-body">
            <form method="GET" id="filterForm" class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Tahun Ajaran</label>
                <select name="tahun" id="tahunFilter" class="form-select">
                  <option value="">Semua Tahun</option>
                  <?php while($t = $resultTahun->fetch_assoc()): ?>
                    <option value="<?php echo $t['tahun']; ?>" <?php echo $tahunFilter == $t['tahun'] ? 'selected' : ''; ?>><?php echo $t['tahun']; ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select name="kelas" id="kelasFilter" class="form-select">
                  <option value="">Semua Kelas</option>
                  <?php while($k = $resultKelas->fetch_assoc()): ?>
                    <option value="<?php echo $k['kelas']; ?>" <?php echo $kelasFilter == $k['kelas'] ? 'selected' : ''; ?>><?php echo $k['kelas']; ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Cari</label>
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="NIS / Nama Siswa" value="<?php echo htmlspecialchars($searchInput); ?>">
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-search me-1"></i> Filter
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Tabel Data Keuangan -->
        <div class="card border-0 shadow-sm">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Tahun Ajaran</th>
                    <th>Total Tagihan</th>
                    <th>Total Bayar</th>
                    <th>Progress</th>
                    <th width="150" class="no-print">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if($result->num_rows > 0): ?>
                    <?php 
                    $no = 1;
                    while($keu = $result->fetch_assoc()): 
                      // Gunakan progress real-time dari database (otomatis dihitung)
                      $progress = floatval($keu['progress'] ?? 0);
                      $progressClass = $progress >= 100 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
                    ?>
                  <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($keu['nis']); ?></td>
                    <td><strong><?php echo htmlspecialchars($keu['nama']); ?></strong></td>
                    <td><?php echo htmlspecialchars($keu['kelas']); ?> - <?php echo htmlspecialchars($keu['jurusan']); ?></td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($keu['tahun']); ?></span></td>
                    <td>Rp <?php echo number_format($keu['total_tagihan'], 0, ',', '.'); ?></td>
                    <td data-total-bayar="<?php echo $keu['keuangan_id']; ?>">Rp <?php echo number_format($keu['total_bayar'], 0, ',', '.'); ?></td>
                    <td>
                      <div class="progress" style="height: 25px;" data-keuangan-id="<?php echo $keu['keuangan_id']; ?>">
                        <div class="progress-bar bg-<?php echo $progressClass; ?>" role="progressbar" style="width: <?php echo $progress; ?>%">
                          <?php echo number_format($progress, 1); ?>%
                        </div>
                      </div>
                    </td>
                    <td class="no-print">
                      <a href="detail.php?keuangan_id=<?php echo $keu['keuangan_id']; ?>" class="btn btn-sm btn-info text-white" title="Lihat Detail">
                        <i class="bi bi-eye me-1"></i> Detail
                      </a>
                    </td>
                  </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="9" class="text-center text-muted py-4">Tidak ada data keuangan</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                  <tr>
                    <th colspan="5" class="text-end">TOTAL</th>
                    <th>Rp <?php echo number_format($stats['total_tagihan_all'] ?? 0, 0, ',', '.'); ?></th>
                    <th>Rp <?php echo number_format($stats['total_bayar_all'] ?? 0, 0, ',', '.'); ?></th>
                    <th colspan="2">
                      <?php 
                      $totalProgress = ($stats['total_tagihan_all'] ?? 0) > 0 
                        ? (($stats['total_bayar_all'] ?? 0) / ($stats['total_tagihan_all'] ?? 1)) * 100 
                        : 0;
                      ?>
                      <span class="badge bg-primary">Rata-rata: <?php echo number_format($totalProgress, 1); ?>%</span>
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          
          <div class="card-footer bg-white border-0">
            <div class="d-flex justify-content-between align-items-center">
              <div class="text-muted small">
                Menampilkan <?php echo $result->num_rows; ?> data keuangan
              </div>
            </div>
          </div>
        </div>

        <!-- Print Footer (hanya muncul saat print) -->
        <div class="print-footer">
          <p><strong>Total Data: <?php echo $result->num_rows; ?> siswa</strong></p>
          <p style="margin-top: 20px;">_________________________</p>
          <p>Petugas Keuangan</p>
        </div>

      </div>
    </main>
  </div>

  <!-- Modal Pilih Jenis Laporan -->
  <div class="modal fade" id="printModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-success text-white border-0">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-printer me-2"></i> Pilih Jenis Laporan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <p class="mb-4 text-muted">Pilih jenis laporan yang ingin dicetak:</p>
          
          <!-- Laporan Harian -->
          <div class="card mb-3 border-primary" style="cursor: pointer;" onclick="selectLaporanHarian()">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="bi bi-calendar-day fs-1 text-primary"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1 fw-bold">Laporan Harian</h6>
                  <small class="text-muted">Pembayaran per tanggal tertentu</small>
                </div>
                <div>
                  <i class="bi bi-chevron-right text-muted"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Laporan Bulanan -->
          <div class="card mb-3 border-success" style="cursor: pointer;" onclick="selectLaporanBulanan()">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="bi bi-calendar-month fs-1 text-success"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1 fw-bold">Laporan Bulanan</h6>
                  <small class="text-muted">Pembayaran per bulan (group by kelas)</small>
                </div>
                <div>
                  <i class="bi bi-chevron-right text-muted"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Laporan Tahunan -->
          <div class="card mb-3 border-warning" style="cursor: pointer;" onclick="selectLaporanTahunan()">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="bi bi-calendar-range fs-1 text-warning"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-1 fw-bold">Laporan Tahunan</h6>
                  <small class="text-muted">Rekap pembayaran per siswa (1 tahun ajaran)</small>
                </div>
                <div>
                  <i class="bi bi-chevron-right text-muted"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Form Laporan Harian -->
  <div class="modal fade" id="formHarianModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="bi bi-calendar-day me-2"></i>Laporan Harian</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formHarian">
            <div class="mb-3">
              <label for="tanggal_harian" class="form-label">Pilih Tanggal</label>
              <input type="date" class="form-control" id="tanggal_harian" required>
            </div>
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle me-2"></i>
              <small>Laporan akan menampilkan semua pembayaran pada tanggal yang dipilih</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" onclick="printLaporanHarian()">
            <i class="bi bi-printer me-1"></i>Print
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Form Laporan Bulanan -->
  <div class="modal fade" id="formBulananModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="bi bi-calendar-month me-2"></i>Laporan Bulanan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formBulanan">
            <div class="mb-3">
              <label for="bulan_bulanan" class="form-label">Pilih Bulan</label>
              <select class="form-select" id="bulan_bulanan" required>
                <option value="">-- Pilih Bulan --</option>
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="tahun_bulanan" class="form-label">Tahun</label>
              <input type="number" class="form-control" id="tahun_bulanan" min="2020" max="2099" required>
            </div>
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle me-2"></i>
              <small>Laporan akan menampilkan pembayaran per kelas untuk bulan yang dipilih</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-success" onclick="printLaporanBulanan()">
            <i class="bi bi-printer me-1"></i>Print
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Form Laporan Tahunan -->
  <div class="modal fade" id="formTahunanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title"><i class="bi bi-calendar-range me-2"></i>Laporan Tahunan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formTahunan">
            <div class="mb-3">
              <label for="tahun_tahunan" class="form-label">Tahun Ajaran</label>
              <select class="form-select" id="tahun_tahunan" required>
                <option value="">-- Pilih Tahun --</option>
                <?php
                // Generate tahun dari database
                $sqlTahunList = "SELECT DISTINCT tahun FROM keuangan ORDER BY tahun DESC";
                $resultTahunList = $conn->query($sqlTahunList);
                $firstTahun = null;
                while ($t = $resultTahunList->fetch_assoc()):
                  if ($firstTahun === null) $firstTahun = $t['tahun'];
                ?>
                <option value="<?php echo intval($t['tahun']); ?>">
                  <?php echo $t['tahun'] . '/' . ($t['tahun'] + 1); ?>
                </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="alert alert-info mb-0">
              <i class="bi bi-info-circle me-2"></i>
              <small>Laporan akan menampilkan rekap pembayaran seluruh siswa per kelas untuk 1 tahun ajaran</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-warning" onclick="printLaporanTahunan()">
            <i class="bi bi-printer me-1"></i>Print
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    // Set default tahun tahunan
    <?php if ($firstTahun): ?>
    document.addEventListener('DOMContentLoaded', function() {
      const tahunTahunanSelect = document.getElementById('tahun_tahunan');
      if (tahunTahunanSelect && tahunTahunanSelect.value === '') {
        tahunTahunanSelect.value = '<?php echo $firstTahun; ?>';
      }
    });
    <?php endif; ?>
  </script>

  <!-- Modal Detail Keuangan -->
  <div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-cash-stack me-2"></i> Detail Keuangan
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4" id="detailContent" style="min-height: 300px;">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted fw-semibold">Memuat data keuangan...</p>
          </div>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  <script src="/js/notifications.js"></script>
  <?php include __DIR__ . '/../../includes/user_dropdown_script.php'; ?>
  
  <script>
    // Fungsi untuk menampilkan modal pilih jenis laporan
    function showPrintModal() {
      const modal = new bootstrap.Modal(document.getElementById('printModal'));
      modal.show();
    }
    
    // Fungsi untuk memilih laporan harian
    function selectLaporanHarian() {
      // Tutup modal pilihan
      const printModal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
      printModal.hide();
      
      // Set default tanggal ke hari ini
      document.getElementById('tanggal_harian').value = new Date().toISOString().split('T')[0];
      
      // Tampilkan modal form harian
      setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('formHarianModal'));
        modal.show();
      }, 300);
    }
    
    // Fungsi untuk memilih laporan bulanan
    function selectLaporanBulanan() {
      // Tutup modal pilihan
      const printModal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
      printModal.hide();
      
      // Set default bulan dan tahun ke sekarang
      const now = new Date();
      document.getElementById('bulan_bulanan').value = now.getMonth() + 1;
      document.getElementById('tahun_bulanan').value = now.getFullYear();
      
      // Tampilkan modal form bulanan
      setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('formBulananModal'));
        modal.show();
      }, 300);
    }
    
    // Fungsi untuk memilih laporan tahunan
    function selectLaporanTahunan() {
      // Tutup modal pilihan
      const printModal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
      printModal.hide();
      
      // Tampilkan modal form tahunan
      setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('formTahunanModal'));
        modal.show();
      }, 300);
    }
    
    // Fungsi print laporan harian
    function printLaporanHarian() {
      const tanggal = document.getElementById('tanggal_harian').value;
      
      if (!tanggal) {
        alert('Silakan pilih tanggal terlebih dahulu!');
        return;
      }
      
      const url = '/admin/finance/print_harian.php?tanggal=' + tanggal;
      window.open(url, '_blank');
      
      // Tutup modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('formHarianModal'));
      modal.hide();
    }
    
    // Fungsi print laporan bulanan
    function printLaporanBulanan() {
      const bulan = document.getElementById('bulan_bulanan').value;
      const tahun = document.getElementById('tahun_bulanan').value;
      
      if (!bulan || !tahun) {
        alert('Silakan lengkapi bulan dan tahun terlebih dahulu!');
        return;
      }
      
      const url = '/admin/finance/print_bulanan.php?bulan=' + bulan + '&tahun=' + tahun;
      window.open(url, '_blank');
      
      // Tutup modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('formBulananModal'));
      modal.hide();
    }
    
    // Fungsi print laporan tahunan
    function printLaporanTahunan() {
      const tahunSelect = document.getElementById('tahun_tahunan');
      const tahun = tahunSelect.value;
      
      if (!tahun || tahun === '' || isNaN(tahun)) {
        alert('Silakan pilih tahun ajaran terlebih dahulu!');
        return;
      }
      
      // Validasi tahun adalah angka
      const tahunInt = parseInt(tahun);
      if (tahunInt < 2020 || tahunInt > 2099) {
        alert('Tahun tidak valid!');
        return;
      }
      
      const url = '/admin/finance/print_tahunan.php?tahun=' + tahunInt;
      window.open(url, '_blank');
      
      // Tutup modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('formTahunanModal'));
      modal.hide();
    }
    
    function viewDetail(id) {
      const modal = new bootstrap.Modal(document.getElementById('detailModal'));
      modal.show();
      
      fetch('/admin/finance/api_get_detail.php?id=' + id)
        .then(response => response.text())
        .then(html => {
          document.getElementById('detailContent').innerHTML = html;
        })
        .catch(error => {
          document.getElementById('detailContent').innerHTML = 
            '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i> Gagal memuat data</div>';
        });
    }

    // Real-time filter
    document.getElementById('tahunFilter').addEventListener('change', function() {
      document.getElementById('filterForm').submit();
    });

    document.getElementById('kelasFilter').addEventListener('change', function() {
      document.getElementById('filterForm').submit();
    });

    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
      }, 500);
    });

    // =============================================
    // AJAX Real-Time Progress Update
    // Update progress bar setiap 10 detik tanpa reload halaman
    // =============================================
    let updateProgressInterval;
    
    function updateProgressBars() {
      // Ambil parameter filter yang aktif
      const params = new URLSearchParams(window.location.search);
      
      fetch('/admin/finance/api_get_progress.php?' + params.toString())
        .then(response => response.json())
        .then(result => {
          if (result.success && result.data) {
            result.data.forEach(item => {
              // Cari progress bar berdasarkan keuangan_id
              const progressContainer = document.querySelector(`[data-keuangan-id="${item.keuangan_id}"]`);
              if (progressContainer) {
                const progressBar = progressContainer.querySelector('.progress-bar');
                const progress = item.progress;
                
                // Update width dan text
                progressBar.style.width = progress + '%';
                progressBar.textContent = progress.toFixed(1) + '%';
                
                // Update class warna berdasarkan progress
                progressBar.className = 'progress-bar';
                if (progress >= 100) {
                  progressBar.classList.add('bg-success');
                } else if (progress >= 50) {
                  progressBar.classList.add('bg-warning');
                } else {
                  progressBar.classList.add('bg-danger');
                }
                
                // Animate perubahan (smooth transition)
                progressBar.style.transition = 'width 0.5s ease, background-color 0.5s ease';
              }
              
              // Update total bayar di kolom tabel
              const row = progressContainer?.closest('tr');
              if (row) {
                const totalBayarCell = row.querySelector('td[data-total-bayar="' + item.keuangan_id + '"]');
                if (totalBayarCell) {
                  totalBayarCell.textContent = 'Rp ' + item.total_bayar;
                }
              }
            });
            
            console.log('[Finance] Progress bars updated at ' + new Date().toLocaleTimeString());
          }
        })
        .catch(error => {
          console.error('[Finance] Error updating progress:', error);
        });
    }
    
    // Mulai auto-update setiap 10 detik
    function startProgressUpdate() {
      updateProgressInterval = setInterval(updateProgressBars, 10000); // 10 detik
      console.log('[Finance] Auto-update progress started (every 10s)');
    }
    
    // Stop auto-update
    function stopProgressUpdate() {
      if (updateProgressInterval) {
        clearInterval(updateProgressInterval);
        console.log('[Finance] Auto-update progress stopped');
      }
    }
    
    // Mulai saat halaman dimuat
    startProgressUpdate();
    
    // Stop saat user meninggalkan halaman
    window.addEventListener('beforeunload', stopProgressUpdate);
    
    // Update sekali lagi setelah 2 detik (untuk memastikan data terbaru)
    setTimeout(updateProgressBars, 2000);
  </script>
  <?php include __DIR__ . '/../../includes/navbar_scripts.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
