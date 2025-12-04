<?php
// =============================================
// Halaman: Tambah Data Keuangan
// Deskripsi: Form untuk menambahkan data keuangan siswa baru
// =============================================

require_once __DIR__ . '/../../config.php';

// Check authentication
require_once __DIR__ . '/../../auth_check.php';

// =============================================
// PROCESS FORM SUBMISSION
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = esc($_POST['nis'] ?? '');
    $tahun = esc($_POST['tahun'] ?? '');
    $total_tagihan = (float)($_POST['total_tagihan'] ?? 0);
    
    // Validasi
    if (empty($nis) || empty($tahun) || $total_tagihan <= 0) {
        $_SESSION['error'] = 'Semua field harus diisi dengan benar!';
        header('Location: /admin/finance/create.php');
        exit;
    }
    
    // Cek duplikasi (NIS + Tahun)
    $sqlCheck = "SELECT COUNT(*) as count FROM keuangan WHERE nis = '$nis' AND tahun = '$tahun'";
    $resultCheck = $conn->query($sqlCheck);
    $checkData = $resultCheck->fetch_assoc();
    
    if ($checkData['count'] > 0) {
        $_SESSION['error'] = 'Data keuangan untuk NIS dan tahun ini sudah ada!';
        header('Location: /admin/finance/create.php');
        exit;
    }
    
    // Insert data
    $sqlInsert = "INSERT INTO keuangan (nis, tahun, total_tagihan, total_bayar) 
                  VALUES ('$nis', '$tahun', '$total_tagihan', 0)";
    
    if ($conn->query($sqlInsert)) {
        $_SESSION['success'] = 'Data keuangan berhasil ditambahkan!';
        header('Location: /admin/finance/index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Gagal menambahkan data: ' . $conn->error;
        header('Location: /admin/finance/create.php');
        exit;
    }
}

// =============================================
// GET DATA UNTUK DROPDOWN SISWA
// =============================================
$sqlSiswa = "SELECT DISTINCT s.nis, s.nama, s.kelas, s.jurusan
             FROM siswa s
             WHERE s.status_siswa = 'aktif'
             ORDER BY s.nama ASC";
$resultSiswa = $conn->query($sqlSiswa);

// =============================================
// GET TAHUN UNTUK DROPDOWN
// =============================================
$currentYear = date('Y');
$years = array($currentYear, $currentYear + 1, $currentYear - 1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Data Keuangan - Sistem Informasi BBA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/responsive.css">
  <?php include __DIR__ . '/../../includes/navbar_style.php'; ?>
  <?php include __DIR__ . '/../../includes/user_dropdown_style.php'; ?>
  <style>
    :root {
      --color-primary: #667eea;
      --color-secondary: #764ba2;
    }
  </style>
  <style>
    .form-section {
      background: white;
      border-radius: 8px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid #667eea;
      box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    
    .form-section h5 {
      color: #667eea;
      font-weight: 700;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
    }
    
    .form-section h5 i {
      margin-right: 0.75rem;
      font-size: 1.2rem;
    }
    
    .form-label {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
      border: 1px solid #ddd;
      border-radius: 6px;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    
    .input-group-text {
      background: #f8f9fa;
      border: 1px solid #ddd;
      color: #667eea;
      font-weight: 600;
    }
    
    .siswa-info-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 8px;
      padding: 1.5rem;
      margin-top: 1rem;
      color: white;
      display: none;
      animation: slideDown 0.3s ease;
    }
    
    .siswa-info-card.show {
      display: block;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .siswa-info-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 1rem;
    }
    
    .siswa-info-item {
      background: rgba(255,255,255,0.15);
      border-radius: 6px;
      padding: 0.75rem;
    }
    
    .siswa-info-item .label {
      font-size: 0.8rem;
      opacity: 0.9;
      margin-bottom: 0.25rem;
    }
    
    .siswa-info-item .value {
      font-size: 1rem;
      font-weight: 700;
    }
    
    .alert-info-helper {
      background: #e3f2fd;
      border-left: 3px solid #2196f3;
      color: #1565c0;
      border-radius: 6px;
      padding: 0.5rem 0.75rem;
      margin-top: 0.5rem;
      font-size: 0.85rem;
    }
    
    .invalid-alert {
      display: none;
      animation: slideDown 0.3s ease;
    }
    
    .invalid-alert.show {
      display: block;
    }
    
    .button-group {
      display: flex;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    @media (max-width: 768px) {
      .button-group {
        flex-direction: column;
      }
    }
  </style>
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
          <h1 class="app-title mb-0">Sistem Informasi BBA</h1>
        </div>
        <?php include __DIR__ . '/../../includes/user_dropdown.php'; ?>
      </div>
    </header>

    <main class="content">
      <div class="container-fluid">
        
        <!-- Notifikasi -->
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
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4><i class="bi bi-plus-circle me-2"></i> Tambah Data Keuangan</h4>
            <p class="text-muted mb-0"><small>Tambahkan data keuangan baru untuk siswa aktif</small></p>
          </div>
          <a href="/admin/finance/index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </a>
        </div>

        <!-- Form Section -->
        <div class="row">
          <div class="col-lg-7 mx-auto">
            <form method="POST" action="" id="formKeuangan">
            
            <!-- DATA SISWA SECTION -->
            <div class="form-section">
              <h5>
                <i class="bi bi-person-fill"></i> Informasi Siswa
              </h5>
              
              <div class="mb-3">
                <label class="form-label">Pilih Siswa <span class="text-danger">*</span></label>
                <select name="nis" id="nisSiswa" class="form-select" required>
                  <option value="">-- Pilih Siswa --</option>
                  <?php 
                  $resultSiswa->data_seek(0);
                  while ($row = $resultSiswa->fetch_assoc()): 
                  ?>
                    <option value="<?php echo $row['nis']; ?>" data-kelas="<?php echo $row['kelas']; ?>" data-jurusan="<?php echo $row['jurusan']; ?>">
                      <?php echo htmlspecialchars($row['nis']) . ' - ' . htmlspecialchars($row['nama']); ?> (<?php echo htmlspecialchars($row['kelas']); ?> - <?php echo htmlspecialchars($row['jurusan']); ?>)
                    </option>
                  <?php endwhile; ?>
                </select>
                <div class="alert-info-helper">
                  <i class="bi bi-info-circle"></i> Hanya siswa dengan status aktif
                </div>
              </div>

              <!-- SISWA INFO CARD -->
              <div id="siswaInfoCard" class="siswa-info-card">
                <div class="siswa-info-row">
                  <div class="siswa-info-item">
                    <div class="label">Nama</div>
                    <div class="value" id="namaInfo">-</div>
                  </div>
                  <div class="siswa-info-item">
                    <div class="label">NIS</div>
                    <div class="value" id="nisInfo">-</div>
                  </div>
                  <div class="siswa-info-item">
                    <div class="label">Kelas</div>
                    <div class="value" id="kelasInfo">-</div>
                  </div>
                  <div class="siswa-info-item">
                    <div class="label">Jurusan</div>
                    <div class="value" id="jurusanInfo">-</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- DATA KEUANGAN SECTION -->
            <div class="form-section">
              <h5>
                <i class="bi bi-cash-coin"></i> Detail Keuangan
              </h5>
              
              <div class="mb-3">
                <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                <select name="tahun" id="tahunSelect" class="form-select" required>
                  <option value="">-- Pilih Tahun Ajaran --</option>
                  <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>/<?php echo $year + 1; ?>" <?php echo ($year == $currentYear) ? 'selected' : ''; ?>>
                      <?php echo $year; ?>/<?php echo $year + 1; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Total Tagihan <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" name="total_tagihan" id="totalTagihan" class="form-control" 
                         placeholder="0" min="0" step="1000" required>
                </div>
                <div class="alert-info-helper">
                  <i class="bi bi-info-circle"></i> Pembayaran awal dimulai dari 0 dan akan terupdate seiring pembayaran siswa
                </div>
              </div>

              <!-- ALERT DUPLIKASI -->
              <div id="alertDuplikasi" class="alert alert-warning alert-dismissible fade invalid-alert" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Data Sudah Ada!</strong> Siswa ini sudah memiliki data keuangan untuk tahun ajaran <span id="duplikatTahun"></span>.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            </div>

            <!-- BUTTON ACTION -->
            <div class="button-group">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Simpan Data
              </button>
              <a href="/admin/finance/index.php" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Batal
              </a>
            </div>

            </form>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  <?php include __DIR__ . '/../../includes/user_dropdown_script.php'; ?>
  <?php include 'includes/navbar_scripts.php'; ?>

  <script>
    // Data siswa dari database
    <?php 
    $resultSiswa->data_seek(0);
    $siswaArray = [];
    while ($row = $resultSiswa->fetch_assoc()) {
      $siswaArray[$row['nis']] = [
        'nama' => $row['nama'],
        'kelas' => $row['kelas'],
        'jurusan' => $row['jurusan']
      ];
    }
    ?>
    const siswaData = <?php echo json_encode($siswaArray); ?>;

    const nisSiswaEl = document.getElementById('nisSiswa');
    const tahunSelectEl = document.getElementById('tahunSelect');
    const totalTagihanEl = document.getElementById('totalTagihan');
    const siswaInfoCardEl = document.getElementById('siswaInfoCard');
    const summaryCardEl = document.getElementById('summaryCard');
    const alertDuplikasiEl = document.getElementById('alertDuplikasi');
    const formEl = document.getElementById('formKeuangan');

    // Update siswa info saat dipilih
    nisSiswaEl.addEventListener('change', function() {
      const nis = this.value;
      
      if (nis && siswaData[nis]) {
        const data = siswaData[nis];
        document.getElementById('nisInfo').textContent = nis;
        document.getElementById('namaInfo').textContent = data.nama;
        document.getElementById('kelasInfo').textContent = data.kelas;
        document.getElementById('jurusanInfo').textContent = data.jurusan;
        siswaInfoCardEl.classList.add('show');
        checkAndUpdateSummary();
      } else {
        siswaInfoCardEl.classList.remove('show');
        summaryCardEl.style.display = 'none';
      }
    });

    // Update summary saat form berubah
    tahunSelectEl.addEventListener('change', checkAndUpdateSummary);
    totalTagihanEl.addEventListener('input', checkAndUpdateSummary);

    function checkAndUpdateSummary() {
      const nis = nisSiswaEl.value;
      const tahun = tahunSelectEl.value;
      const totalTagihan = parseFloat(totalTagihanEl.value) || 0;

      if (nis && tahun && totalTagihan > 0) {
        document.getElementById('summaryNis').textContent = nis + ' - ' + (siswaData[nis]?.nama || '');
        document.getElementById('summaryTahun').textContent = tahun;
        document.getElementById('summaryTagihan').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalTagihan);
        summaryCardEl.style.display = 'block';
      } else {
        summaryCardEl.style.display = 'none';
      }
    }

    // Validasi duplikasi sebelum submit
    formEl.addEventListener('submit', function(e) {
      const nis = nisSiswaEl.value;
      const tahun = tahunSelectEl.value;
      
      if (!nis || !tahun) return;

      e.preventDefault();

      // Disable tombol submit
      const submitBtn = formEl.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Memproses...';

      // Check duplicate via AJAX
      fetch('check_keuangan_duplicate.php?nis=' + encodeURIComponent(nis) + '&tahun=' + encodeURIComponent(tahun))
        .then(response => response.json())
        .then(data => {
          if (data.exists) {
            // Tampilkan alert duplikasi
            document.getElementById('duplikatTahun').textContent = tahun;
            alertDuplikasiEl.classList.add('show');
            window.scrollTo({top: alertDuplikasiEl.offsetTop - 100, behavior: 'smooth'});
            
            // Re-enable tombol
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Simpan Data Keuangan';
          } else {
            // Data tidak duplikat, submit form
            formEl.removeEventListener('submit', arguments.callee);
            formEl.submit();
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan saat memvalidasi data');
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Simpan Data Keuangan';
        });
    });

    // Auto-dismiss alerts setelah 5 detik
    document.querySelectorAll('.alert').forEach(alert => {
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }, 5000);
    });

    // Format currency on input
    totalTagihanEl.addEventListener('blur', function() {
      if (this.value) {
        checkAndUpdateSummary();
      }
    });
  </script>
  <?php include __DIR__ . '/../../includes/navbar_scripts.php'; ?>
</body>
</html>
