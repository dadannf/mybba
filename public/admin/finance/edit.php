<?php
// =============================================
// Halaman: Edit Data Keuangan
// Deskripsi: Form untuk mengedit data keuangan siswa
// =============================================

require_once __DIR__ . '/../../config.php';

// Check authentication
require_once __DIR__ . '/../../auth_check.php';

// =============================================
// GET ID DARI URL
// =============================================
$keuangan_id = esc($_GET['id'] ?? '');

if (empty($keuangan_id)) {
    $_SESSION['error'] = 'ID keuangan tidak ditemukan!';
    header('Location: /admin/finance/index.php');
    exit;
}

// =============================================
// GET DATA KEUANGAN
// =============================================
$sqlKeuangan = "SELECT k.*, s.nama, s.kelas, s.jurusan
                FROM keuangan k
                INNER JOIN siswa s ON k.nis = s.nis
                WHERE k.keuangan_id = '$keuangan_id'";

$resultKeuangan = $conn->query($sqlKeuangan);

if ($resultKeuangan->num_rows == 0) {
    $_SESSION['error'] = 'Data keuangan tidak ditemukan!';
    header('Location: /admin/finance/index.php');
    exit;
}

$keuangan = $resultKeuangan->fetch_assoc();

// =============================================
// PROCESS FORM SUBMISSION
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tahun = esc($_POST['tahun'] ?? '');
    $total_tagihan = (float)($_POST['total_tagihan'] ?? 0);
    
    // Validasi
    if (empty($tahun) || $total_tagihan <= 0) {
        $_SESSION['error'] = 'Semua field harus diisi dengan benar!';
        header('Location: /admin/finance/edit.php?id=' . $keuangan_id);
        exit;
    }
    
    // Update data
    $sqlUpdate = "UPDATE keuangan 
                  SET tahun = '$tahun', total_tagihan = '$total_tagihan'
                  WHERE keuangan_id = '$keuangan_id'";
    
    if ($conn->query($sqlUpdate)) {
        $_SESSION['success'] = 'Data keuangan berhasil diupdate!';
        header('Location: /admin/finance/index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Gagal mengupdate data: ' . $conn->error;
        header('Location: /admin/finance/edit.php?id=' . $keuangan_id);
        exit;
    }
}

// =============================================
// GET TAHUN UNTUK DROPDOWN
// =============================================
$currentYear = date('Y');
$years = array($currentYear, $currentYear + 1, $currentYear - 1);

// =============================================
// GET HISTORY PEMBAYARAN
// =============================================
$sqlPembayaran = "SELECT * FROM pembayaran WHERE keuangan_id = '$keuangan_id' ORDER BY tanggal_bayar DESC";
$resultPembayaran = $conn->query($sqlPembayaran);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Data Keuangan - Sistem Informasi BBA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/responsive.css">
  <?php include __DIR__ . '/../../includes/navbar_style.php'; ?>
  <?php include __DIR__ . '/../../includes/user_dropdown_style.php'; ?>
  <style>
    .form-section {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 2rem;
      margin-bottom: 2rem;
    }
    .form-section h5 {
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
    }
    .info-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 8px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    .stat-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .stat-row:last-child {
      border-bottom: none;
    }
  </style>
</head>
<body class="has-sidebar">

  <!-- Sidebar -->
  <?php include __DIR__ . '/../../shared/components/sidebar.php'; ?>

  <!-- Overlay untuk mobile (menutup sidebar saat diklik) -->
  <div class="overlay d-none" id="overlay"></div>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <!-- Header -->
    <div class="p-4 border-bottom bg-light">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="mb-1">
            <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Data Keuangan
          </h2>
          <p class="text-muted mb-0">Perbarui informasi keuangan siswa</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="/admin/finance/index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> Kembali
          </a>
          <?php include __DIR__ . '/../../includes/user_dropdown.php'; ?>
        </div>
      </div>
    </div>

    <!-- NOTIFIKASI -->
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show m-4" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="p-4">
      <div class="row">
        <div class="col-lg-8 mx-auto">
          
          <!-- INFO SISWA CARD -->
          <div class="info-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="mb-1"><?php echo $keuangan['nama']; ?></h5>
                <p class="mb-0 opacity-75">NIS: <?php echo $keuangan['nis']; ?></p>
              </div>
              <span class="badge bg-white text-primary"><?php echo $keuangan['kelas']; ?> - <?php echo $keuangan['jurusan']; ?></span>
            </div>
            <div class="stat-row">
              <span>Total Tagihan</span>
              <strong>Rp <?php echo number_format($keuangan['total_tagihan'], 0, ',', '.'); ?></strong>
            </div>
            <div class="stat-row">
              <span>Total Terbayar</span>
              <strong>Rp <?php echo number_format($keuangan['total_bayar'], 0, ',', '.'); ?></strong>
            </div>
            <div class="stat-row">
              <span>Tunggakan</span>
              <strong>Rp <?php echo number_format($keuangan['total_tagihan'] - $keuangan['total_bayar'], 0, ',', '.'); ?></strong>
            </div>
          </div>

          <!-- FORM EDIT -->
          <form method="POST" action="">
            <div class="form-section">
              <h5>
                <i class="bi bi-cash-coin"></i> Detail Keuangan
              </h5>
              
              <div class="mb-3">
                <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                <select name="tahun" class="form-select" required>
                  <option value="">-- Pilih Tahun --</option>
                  <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>/<?php echo $year + 1; ?>" 
                            <?php echo ($keuangan['tahun'] == "$year/" . ($year + 1)) ? 'selected' : ''; ?>>
                      <?php echo $year; ?>/<?php echo $year + 1; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Total Tagihan (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" name="total_tagihan" class="form-control" 
                         value="<?php echo $keuangan['total_tagihan']; ?>" 
                         min="0" step="1000" required>
                </div>
                <small class="text-muted d-block mt-2">
                  <i class="bi bi-info-circle me-1"></i> Nilai total pembayaran tidak bisa diubah dari halaman ini (diupdate otomatis saat ada pembayaran)
                </small>
              </div>
            </div>

            <!-- HISTORY PEMBAYARAN -->
            <?php if ($resultPembayaran->num_rows > 0): ?>
            <div class="form-section">
              <h5>
                <i class="bi bi-clock-history"></i> History Pembayaran
              </h5>
              
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead class="table-light">
                    <tr>
                      <th>Tanggal</th>
                      <th>Bulan</th>
                      <th class="text-end">Nominal</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($pembayaran = $resultPembayaran->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])); ?></td>
                      <td><?php echo $pembayaran['bulan_untuk']; ?></td>
                      <td class="text-end">Rp <?php echo number_format($pembayaran['nominal_bayar'], 0, ',', '.'); ?></td>
                      <td>
                        <span class="badge bg-<?php echo $pembayaran['status'] == 'valid' ? 'success' : 'warning'; ?>">
                          <?php echo ucfirst($pembayaran['status']); ?>
                        </span>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <?php endif; ?>

            <!-- BUTTON ACTION -->
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                <i class="bi bi-check-circle me-2"></i> Update Data Keuangan
              </button>
              <a href="/admin/finance/index.php" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-x-circle me-2"></i> Batal
              </a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  <?php include __DIR__ . '/../../includes/user_dropdown_script.php'; ?>
  <?php include 'includes/navbar_scripts.php'; ?>

  <script>
    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(alert => {
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }, 5000);
    });
  </script>
  <?php include __DIR__ . '/../../includes/navbar_scripts.php'; ?>
</body>
</html>
