<?php
// =============================================
// Halaman: Detail Keuangan Siswa
// Deskripsi: Menampilkan detail pembayaran bulanan siswa (12 bulan)
// =============================================

require_once __DIR__ . '/../../config.php';

// Check authentication
require_once __DIR__ . '/../../auth_check.php';

// Ambil keuangan_id dari URL
$keuangan_id = isset($_GET['keuangan_id']) ? esc($_GET['keuangan_id']) : '';

if (empty($keuangan_id)) {
    $_SESSION['error'] = 'ID Keuangan tidak valid!';
    header('Location: /admin/finance/index.php');
    exit;
}

// Query data keuangan dengan join siswa
$sql = "SELECT k.*, s.nama, s.nis, s.kelas, s.jurusan, s.email, s.no_hp 
        FROM keuangan k
        INNER JOIN siswa s ON k.nis = s.nis
        WHERE k.keuangan_id = '$keuangan_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Data keuangan tidak ditemukan!';
    header('Location: /admin/finance/index.php');
    exit;
}

$keuangan = $result->fetch_assoc();

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

// Tagihan per bulan berdasarkan kelas siswa
$tagihanPerBulan = getTagihanPerBulan($keuangan['kelas']);

// Array bulan
$bulanList = [
    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
];

// Query pembayaran yang sudah ada
$sqlPembayaran = "SELECT pembayaran_id, keuangan_id, tanggal_bayar, nominal_bayar, metode, tempat_bayar, bukti_bayar, status 
                  FROM pembayaran 
                  WHERE keuangan_id = '$keuangan_id' 
                  ORDER BY pembayaran_id ASC";
$resultPembayaran = $conn->query($sqlPembayaran);

// Simpan pembayaran dalam array - asumsi pembayaran berurutan dari bulan 1-12
$pembayaranData = [];
$indexBulan = 1;
while ($bayar = $resultPembayaran->fetch_assoc()) {
    $pembayaranData[$indexBulan] = $bayar;
    $indexBulan++;
}

// Hitung total yang sudah dibayar
$totalTerbayar = 0;
foreach ($pembayaranData as $bayar) {
    $totalTerbayar += $bayar['nominal_bayar'];
}

// Total tagihan 12 bulan
$totalTagihan = $tagihanPerBulan * 12;
$sisaTunggakan = $totalTagihan - $totalTerbayar;
$persenPembayaran = ($totalTagihan > 0) ? ($totalTerbayar / $totalTagihan) * 100 : 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Keuangan - <?php echo htmlspecialchars($keuangan['nama']); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/notifications.css">
  <link rel="stylesheet" href="/css/responsive.css">
  
  <?php include __DIR__ . '/../../includes/navbar_style.php'; ?>
  <?php include __DIR__ . '/../../includes/user_dropdown_style.php'; ?>
  
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
    
    @media print {
      /* Sembunyikan elemen yang tidak perlu saat print */
      .sidebar, .topbar, .btn, .no-print, .modal, .btn-group {
        display: none !important;
      }
      
      /* Atur layout untuk print */
      .main-wrapper {
        margin-left: 0 !important;
        padding: 20px !important;
      }
      
      body {
        background: white !important;
      }
      
      .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
      }
      
      /* Print header */
      .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 3px solid #000;
        padding-bottom: 15px;
      }
      
      .print-header h3 {
        margin: 0;
        font-weight: bold;
      }
      
      .print-header p {
        margin: 5px 0;
      }
      
      /* Tabel print */
      table {
        width: 100%;
        border-collapse: collapse;
      }
      
      table th, table td {
        border: 1px solid #333 !important;
        padding: 8px !important;
      }
      
      /* Badge styling untuk print */
      .badge {
        border: 1px solid #333 !important;
        padding: 3px 8px !important;
      }
    }
    
    /* Hide print header di layar normal */
    .print-header {
      display: none;
    }
    
    /* Modal Bukti Transfer Styling */
    #buktiImageContainer {
      min-height: 200px;
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
    }
    
    #buktiImageContainer img {
      transition: transform 0.3s ease;
    }
    
    #buktiImageContainer img:hover {
      transform: scale(1.02);
      cursor: zoom-in;
    }
    
    /* Camera Mirror Effect - Video Preview dan Hasil Foto */
    #camera-preview {
      transform: scaleX(-1);        /* Mirror horizontal untuk preview */
      -webkit-transform: scaleX(-1);
      -moz-transform: scaleX(-1);
    }
    
    /* Hasil foto JUGA di-mirror - konsisten dengan preview */
    #captured-image {
      /* Tidak perlu transform karena sudah di-mirror di canvas */
      /* Foto yang tersimpan sudah dalam bentuk mirror */
    }
    
    /* Hapus style user dropdown yang lama, gunakan include */
  </style>
</head>
<body class="has-sidebar">

  <!-- Sidebar -->
  <?php include __DIR__ . '/../../shared/components/sidebar.php'; ?>

  <!-- Overlay untuk mobile (menutup sidebar saat diklik) -->
  <div class="overlay d-none" id="overlay"></div>

  <!-- Konten utama -->
  <div class="main-wrapper">
    <!-- Print Header (hanya muncul saat print) -->
    <div class="print-header">
      <h3>LAPORAN PEMBAYARAN SISWA</h3>
      <p>Yayasan Baiturrahman Arrisalah</p>
      <p>Jl. Contoh No. 123, Kota, Provinsi | Telp: (021) 12345678</p>
    </div>
    
    <header class="topbar shadow-sm no-print">
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
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4><i class="bi bi-receipt me-2"></i> Detail Keuangan Siswa</h4>
            <p class="text-muted mb-0"><small>Record pembayaran bulanan tahun ajaran <?php echo htmlspecialchars($keuangan['tahun']); ?></small></p>
          </div>
          <div class="no-print">
            <a href="/admin/finance/print_laporan.php?nis=<?php echo urlencode($keuangan['nis']); ?>" target="_blank" class="btn btn-primary me-2">
              <i class="bi bi-file-earmark-text me-1"></i> Cetak Laporan
            </a>
            <a href="/admin/finance/index.php" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
          </div>
        </div>

        <!-- Info Siswa Card -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <small class="text-muted d-block">NIS</small>
                <strong><?php echo htmlspecialchars($keuangan['nis']); ?></strong>
              </div>
              <div class="col-md-3">
                <small class="text-muted d-block">Nama Siswa</small>
                <strong><?php echo htmlspecialchars($keuangan['nama']); ?></strong>
              </div>
              <div class="col-md-3">
                <small class="text-muted d-block">Kelas</small>
                <strong><?php echo htmlspecialchars($keuangan['kelas']); ?></strong>
              </div>
              <div class="col-md-3">
                <small class="text-muted d-block">Jurusan</small>
                <strong><?php echo htmlspecialchars($keuangan['jurusan']); ?></strong>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
              <div class="card-body">
                <small class="opacity-75 d-block">Total Tagihan</small>
                <h4 class="mb-0">Rp <?php echo number_format($totalTagihan, 0, ',', '.'); ?></h4>
                <small class="opacity-75">12 Bulan x Rp 100.000</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
              <div class="card-body">
                <small class="opacity-75 d-block">Terbayar</small>
                <h4 class="mb-0">Rp <?php echo number_format($totalTerbayar, 0, ',', '.'); ?></h4>
                <small class="opacity-75"><?php echo count($pembayaranData); ?> dari 12 bulan</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
              <div class="card-body">
                <small class="opacity-75 d-block">Tunggakan</small>
                <h4 class="mb-0">Rp <?php echo number_format($sisaTunggakan, 0, ',', '.'); ?></h4>
                <small class="opacity-75"><?php echo (12 - count($pembayaranData)); ?> bulan tersisa</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
              <div class="card-body">
                <small class="opacity-75 d-block">Progress</small>
                <h4 class="mb-0"><?php echo number_format($persenPembayaran, 1); ?>%</h4>
                <div class="progress mt-2" style="height: 8px;">
                  <div class="progress-bar bg-white" style="width: <?php echo min($persenPembayaran, 100); ?>%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabel Pembayaran Bulanan -->
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-calendar-month me-2"></i> Pembayaran Bulanan (12 Bulan)</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 12%">Bulan</th>
                    <th style="width: 12%">Tagihan</th>
                    <th style="width: 10%">Metode</th>
                    <th style="width: 15%">Tempat Bayar</th>
                    <th style="width: 12%">Status</th>
                    <th style="width: 12%">Bukti TF</th>
                    <th style="width: 12%" class="no-print">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $no = 1;
                  foreach ($bulanList as $indexBulan => $bulan): 
                    $sudahBayar = isset($pembayaranData[$indexBulan]);
                    $nominal = $sudahBayar ? $pembayaranData[$indexBulan]['nominal_bayar'] : 0;
                    $metode = $sudahBayar ? strtoupper($pembayaranData[$indexBulan]['metode']) : '-';
                    $tempatBayar = $sudahBayar ? ($pembayaranData[$indexBulan]['tempat_bayar'] ?: '-') : '-';
                    $status = $sudahBayar ? $pembayaranData[$indexBulan]['status'] : 'belum';
                    $pembayaranId = $sudahBayar ? $pembayaranData[$indexBulan]['pembayaran_id'] : null;
                    $buktiBayar = $sudahBayar ? $pembayaranData[$indexBulan]['bukti_bayar'] : null;
                    
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
                    <td><?php echo $no++; ?></td>
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
                        <small><?php 
                          // Debug: tampilkan nilai asli
                          $displayTempat = $pembayaranData[$indexBulan]['tempat_bayar'];
                          if (empty($displayTempat) || $displayTempat == '0') {
                            echo '<span class="text-danger">Data kosong</span>';
                          } else {
                            echo htmlspecialchars($displayTempat);
                          }
                        ?></small>
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
                      <?php if ($sudahBayar && $buktiBayar): ?>
                        <button onclick="showBuktiBayar('<?php echo addslashes($buktiBayar); ?>', '<?php echo addslashes($bulan); ?>')" 
                                class="btn btn-info btn-sm" 
                                title="Lihat Bukti Transfer">
                          <i class="bi bi-image me-1"></i> Pratinjau
                        </button>
                        <!-- Debug: <?php echo htmlspecialchars($buktiBayar); ?> -->
                      <?php else: ?>
                        <span class="text-muted"><small>-</small></span>
                      <?php endif; ?>
                    </td>
                    <td class="no-print">
                      <?php if ($sudahBayar && $status == 'menunggu'): ?>
                        <div class="btn-group btn-group-sm" role="group">
                          <button onclick="updateStatus(<?php echo $pembayaranId; ?>, 'valid')" class="btn btn-success" title="Validasi">
                            <i class="bi bi-check-lg"></i>
                          </button>
                          <button onclick="updateStatus(<?php echo $pembayaranId; ?>, 'tolak')" class="btn btn-danger" title="Tolak">
                            <i class="bi bi-x-lg"></i>
                          </button>
                        </div>
                      <?php elseif ($sudahBayar && $status == 'valid'): ?>
                        <?php if ($metode == 'TUNAI'): ?>
                          <button onclick="printKwitansi(<?php echo $pembayaranId; ?>)" class="btn btn-info btn-sm" title="Cetak Kwitansi">
                            <i class="bi bi-printer"></i> Cetak
                          </button>
                        <?php else: ?>
                          <span class="text-muted"><small>Valid</small></span>
                        <?php endif; ?>
                      <?php elseif ($sudahBayar && $status == 'tolak'): ?>
                        <button onclick="openBayarModal(<?php echo $keuangan['keuangan_id']; ?>, <?php echo $indexBulan; ?>, '<?php echo $bulan; ?>', <?php echo $tagihanPerBulan; ?>)" class="btn btn-primary btn-sm">
                          <i class="bi bi-cash"></i> Bayar Ulang
                        </button>
                      <?php else: ?>
                        <button onclick="openBayarModal(<?php echo $keuangan['keuangan_id']; ?>, <?php echo $indexBulan; ?>, '<?php echo $bulan; ?>', <?php echo $tagihanPerBulan; ?>)" class="btn btn-primary btn-sm">
                          <i class="bi bi-cash"></i> Bayar
                        </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                  <tr>
                    <th colspan="2">TOTAL</th>
                    <th>Rp <?php echo number_format($totalTagihan, 0, ',', '.'); ?></th>
                    <th>Rp <?php echo number_format($totalTerbayar, 0, ',', '.'); ?></th>
                    <th colspan="3">
                      <?php if ($sisaTunggakan > 0): ?>
                        <span class="text-danger">Sisa: Rp <?php echo number_format($sisaTunggakan, 0, ',', '.'); ?></span>
                      <?php else: ?>
                        <span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i> LUNAS</span>
                      <?php endif; ?>
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
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
            <input type="hidden" id="bayar_nis" value="">
            <input type="hidden" id="bayar_nama" value="">>
            
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
              <select class="form-select" id="metode" name="metode" required onchange="togglePaymentMethod(this.value)">
                <option value="">-- Pilih Metode --</option>
                <option value="Transfer">Transfer Bank</option>
                <option value="Tunai">Tunai (TU Sekolah)</option>
              </select>
            </div>

            <div class="mb-3" id="tempat_bayar_wrapper">
              <label for="tempat_bayar" class="form-label">Bank <span class="text-danger">*</span></label>
              <select class="form-select" id="tempat_bayar" name="tempat_bayar">
                <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
              </select>
              <div class="form-text">Hanya tersedia transfer ke rekening BRI</div>
            </div>

            <!-- Form untuk Transfer Bank -->
            <div id="bukti_section">
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
                  <button type="button" class="btn btn-outline-success" onclick="openCameraAdmin()">
                    <i class="bi bi-camera"></i> Ambil Foto
                  </button>
                </div>
                
                <input type="file" class="form-control d-none" id="bukti_bayar" name="bukti_bayar" accept="image/*,.pdf">
                
                <!-- Preview Image -->
                <div id="image_preview_admin" class="mb-2" style="display: none;">
                  <img id="preview_img_admin" src="" alt="Preview" class="img-fluid rounded border" style="max-height: 300px;">
                  <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImageAdmin()">
                    <i class="bi bi-x-circle"></i> Hapus
                  </button>
                </div>
                
                <div class="form-text">
                  <i class="bi bi-robot"></i> Sistem akan validasi bukti transfer otomatis menggunakan AI OCR
                </div>
              </div>
            </div>
            <!-- End of bukti_section (Transfer Bank) -->

            <!-- Form untuk Pembayaran Tunai -->
            <div id="tunai_section" style="display: none;">
              <div class="alert alert-info">
                <i class="bi bi-cash-coin me-1"></i>
                <strong>Pembayaran Tunai</strong> - Tidak perlu upload bukti pembayaran
              </div>
              
              <div class="mb-3">
                <label for="tempat_bayar_tunai" class="form-label">Tempat Bayar <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="tempat_bayar_tunai" name="tempat_bayar_tunai" 
                  value="TU Sekolah" readonly>
                <div class="form-text">Pembayaran tunai diterima di TU sekolah</div>
              </div>
              
              <div class="mb-3">
                <label for="catatan_tunai" class="form-label">Catatan Pembayaran</label>
                <textarea class="form-control" id="catatan_tunai" name="catatan" rows="2" 
                  placeholder="Contoh: Pembayaran diterima langsung di kantor administrasi"></textarea>
              </div>

              <div class="mb-3">
                <label for="penerima_kasir" class="form-label">Diterima Oleh</label>
                <input type="text" class="form-control" id="penerima_kasir" name="penerima_kasir" 
                  placeholder="Nama kasir/admin yang menerima">
              </div>
              
              <div class="alert alert-success">
                <i class="bi bi-printer me-1"></i>
                Setelah pembayaran diproses, Anda dapat mencetak bukti pembayaran sebagai kwitansi
              </div>
            </div>
            <!-- End of tunai_section -->

            <!-- OCR Validation Result Section -->
            <div id="ocr-validation-result" class="alert" style="display: none;">
              <div class="d-flex align-items-start">
                <div class="flex-grow-1">
                  <h6 class="alert-heading mb-2">
                    <i class="bi bi-robot me-1"></i> Hasil Validasi OCR
                  </h6>
                  <div id="ocr-result-content"></div>
                </div>
              </div>
            </div>

            <div class="alert alert-warning mb-0">
              <i class="bi bi-info-circle me-1"></i>
              <small>Admin dapat langsung approve/reject berdasarkan validasi OCR atau manual verification.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Proses Pembayaran
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Camera -->
  <div class="modal fade" id="cameraModalAdmin" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="bi bi-camera-fill me-2"></i>Ambil Foto Bukti Transfer
          </h5>
          <button type="button" class="btn-close btn-close-white" onclick="closeCameraAdmin()"></button>
        </div>
        <div class="modal-body text-center">
          <div id="cameraErrorAdmin" class="alert alert-danger" style="display: none;"></div>
          
          <video id="camera_video_admin" autoplay playsinline style="width: 100%; max-width: 640px; border-radius: 8px; transform: scaleX(-1);"></video>
          <canvas id="camera_canvas_admin" style="display: none;"></canvas>
          
          <div class="alert alert-info mt-3 mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <small>Posisikan bukti transfer dengan jelas dan pastikan semua teks terbaca</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeCameraAdmin()">
            <i class="bi bi-x-lg me-1"></i>Batal
          </button>
          <button type="button" class="btn btn-success" onclick="capturePhotoAdmin()">
            <i class="bi bi-camera-fill me-1"></i>Ambil Foto
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Pratinjau Bukti Transfer -->
  <div class="modal fade" id="modalBuktiBayar" tabindex="-1" aria-labelledby="modalBuktiBayarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="modalBuktiBayarLabel">
            <i class="bi bi-image me-2"></i>Bukti Transfer - <span id="buktiBulan">-</span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center p-4">
          <div id="buktiImageContainer">
            <!-- Image or PDF preview will be loaded here -->
          </div>
        </div>
        <div class="modal-footer">
          <a id="downloadBukti" href="#" target="_blank" class="btn btn-primary" download>
            <i class="bi bi-download me-1"></i> Unduh Bukti
          </a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Script Pembayaran & Validasi -->
  <script>
  let modalBayar;
  let modalBuktiBayar;
  
  document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // POLYFILL untuk browser lama (getUserMedia)
    // ============================================
    
    // Step 1: Pastikan navigator.mediaDevices exist
    if (navigator.mediaDevices === undefined) {
      navigator.mediaDevices = {};
      console.log('📱 Created navigator.mediaDevices object');
    }
    
    // Step 2: Polyfill getUserMedia - comprehensive fallback
    if (navigator.mediaDevices.getUserMedia === undefined) {
      console.log('🔧 Applying getUserMedia polyfill...');
      
      navigator.mediaDevices.getUserMedia = function(constraints) {
        // Cari API getUserMedia yang tersedia (berbagai vendor prefix)
        const getUserMedia = navigator.getUserMedia || 
                           navigator.webkitGetUserMedia || 
                           navigator.mozGetUserMedia || 
                           navigator.msGetUserMedia ||
                           (navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        
        // Jika tidak ada sama sekali, coba fallback ke enumerateDevices
        if (!getUserMedia) {
          console.error('❌ No getUserMedia API found in browser');
          
          // Last resort: check if we can at least enumerate devices
          if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
            console.log('⚠️ Found enumerateDevices, but getUserMedia not available');
            return Promise.reject(new Error('Browser Anda tidak mendukung akses kamera melalui web. Gunakan browser terbaru: Chrome 53+, Firefox 36+, Safari 11+, atau Edge 12+'));
          }
          
          return Promise.reject(new Error('Browser Anda tidak mendukung akses kamera. Silakan update browser atau gunakan: Chrome, Firefox, Safari, atau Edge versi terbaru.'));
        }
        
        console.log('✅ Found getUserMedia API:', getUserMedia.name || 'legacy API');
        
        // Wrap callback-based API menjadi Promise
        return new Promise(function(resolve, reject) {
          try {
            getUserMedia.call(navigator, constraints, resolve, reject);
          } catch (e) {
            console.error('Error calling getUserMedia:', e);
            reject(e);
          }
        });
      };
      
      console.log('✅ Polyfill applied successfully');
    }
    
    // Step 3: Polyfill enumerateDevices jika tidak ada
    if (!navigator.mediaDevices.enumerateDevices) {
      console.log('🔧 Applying enumerateDevices polyfill...');
      
      navigator.mediaDevices.enumerateDevices = function() {
        return Promise.resolve([]);
      };
    }
    
    // Inisialisasi modal
    const modalElement = document.getElementById('modalBayar');
    if (modalElement) {
      modalBayar = new bootstrap.Modal(modalElement);
    }
    
    const modalBuktiElement = document.getElementById('modalBuktiBayar');
    if (modalBuktiElement) {
      modalBuktiBayar = new bootstrap.Modal(modalBuktiElement);
    }
    
    // Debug: Cek Bootstrap dropdown
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    console.log('Dropdown elements found:', dropdownElementList.length);
    
    // Inisialisasi dropdown secara manual jika perlu
    dropdownElementList.forEach(function(dropdownToggle) {
      new bootstrap.Dropdown(dropdownToggle);
      console.log('Dropdown initialized');
    });
    
    // Set tanggal bayar default ke hari ini
    const tanggalInput = document.getElementById('tanggal_bayar');
    if (tanggalInput) {
      const today = new Date().toISOString().split('T')[0];
      tanggalInput.value = today;
    }
    
    // Handle form submit
    const formBayar = document.getElementById('formBayar');
    if (formBayar) {
      formBayar.addEventListener('submit', function(e) {
        e.preventDefault();
        submitPembayaran();
      });
    }
    
    // ============================================
    // OCR VALIDATION - AUTO VALIDATE ON FILE UPLOAD
    // ============================================
    const buktiInput = document.getElementById('bukti_bayar');
    if (buktiInput) {
      buktiInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
          console.log('File selected, triggering OCR validation...');
          validateWithOCR(this.files[0]);
        }
      });
    }
    
    // ==================== CAMERA FUNCTIONS (NEW MODAL STYLE) ====================
    let cameraStreamAdmin = null;
    
    // Open camera modal and start video stream
    window.openCameraAdmin = async function() {
      const modal = new bootstrap.Modal(document.getElementById('cameraModalAdmin'));
      const video = document.getElementById('camera_video_admin');
      const errorDiv = document.getElementById('cameraErrorAdmin');
      
      modal.show();
      errorDiv.style.display = 'none';
      
      try {
        // Request camera access
        cameraStreamAdmin = await navigator.mediaDevices.getUserMedia({ 
          video: { 
            facingMode: 'environment', // Use back camera on mobile
            width: { ideal: 1920 },
            height: { ideal: 1080 }
          } 
        });
        
        video.srcObject = cameraStreamAdmin;
        video.play();
      } catch (error) {
        console.error('Camera access error:', error);
        errorDiv.textContent = 'Tidak dapat mengakses kamera: ' + error.message;
        errorDiv.style.display = 'block';
      }
    };
    
    // Capture photo from video stream
    window.capturePhotoAdmin = async function() {
      const video = document.getElementById('camera_video_admin');
      const canvas = document.getElementById('camera_canvas_admin');
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
        closeCameraAdmin();
        
        // Show preview
        const preview = document.getElementById('image_preview_admin');
        const previewImg = document.getElementById('preview_img_admin');
        previewImg.src = URL.createObjectURL(blob);
        preview.style.display = 'block';
        
        // Trigger OCR validation
        try {
          await validateWithOCR(file);
        } catch (error) {
          console.error('OCR validation error:', error);
          alert('Validasi OCR gagal: ' + error.message);
        }
      }, 'image/jpeg', 0.9);
    };
    
    // Close camera and stop stream
    window.closeCameraAdmin = function() {
      if (cameraStreamAdmin) {
        cameraStreamAdmin.getTracks().forEach(track => track.stop());
        cameraStreamAdmin = null;
      }
      
      const modal = bootstrap.Modal.getInstance(document.getElementById('cameraModalAdmin'));
      if (modal) {
        modal.hide();
      }
    };
    
    // Clear image preview
    window.clearImageAdmin = function() {
      const preview = document.getElementById('image_preview_admin');
      const previewImg = document.getElementById('preview_img_admin');
      const fileInput = document.getElementById('bukti_bayar');
      const ocrSection = document.getElementById('ocr_result_section');
      const submitBtn = document.getElementById('btnSubmitPembayaran');
      
      // Clear preview
      previewImg.src = '';
      preview.style.display = 'none';
      
      // Clear file input
      fileInput.value = '';
      
      // Hide OCR result
      if (ocrSection) {
        ocrSection.style.display = 'none';
      }
      
      // Reset button if exists
      if (submitBtn) {
        submitBtn.disabled = false;
      }
      
      // Clear OCR data
      const formBayar = document.getElementById('formBayar');
      if (formBayar) {
        delete formBayar.dataset.ocrResult;
        delete formBayar.dataset.ocrDecision;
      }
    };
    
    // Update file upload handler to show preview
    const buktiBayarInput = document.getElementById('bukti_bayar');
    if (buktiBayarInput) {
      buktiBayarInput.addEventListener('change', async function(e) {
        if (this.files && this.files[0]) {
          const file = this.files[0];
          
          // Show preview for images
          if (file.type.startsWith('image/')) {
            const preview = document.getElementById('image_preview_admin');
            const previewImg = document.getElementById('preview_img_admin');
            previewImg.src = URL.createObjectURL(file);
            preview.style.display = 'block';
          }
          
          // Trigger OCR
          try {
            await validateWithOCR(file);
          } catch (error) {
            console.error('OCR validation error:', error);
          }
        }
      });
    }
    // ==================== END CAMERA FUNCTIONS ====================
    
    // ============================================
    // CEK SUPPORT KAMERA SAAT TAB DIBUKA
    // ============================================
    const cameraTab = document.getElementById('camera-tab');
    if (cameraTab) {
      cameraTab.addEventListener('click', function() {
        console.log('📱 Camera tab clicked');
        const support = checkCameraSupport();
        const startBtn = document.getElementById('start-camera-btn');
        const cameraPanelInfo = document.querySelector('#camera-panel .form-text');
        
        if (!support.supported && startBtn && cameraPanelInfo) {
          // Disable tombol dan tampilkan peringatan
          startBtn.disabled = true;
          startBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Kamera Tidak Didukung';
          startBtn.classList.remove('btn-primary');
          startBtn.classList.add('btn-secondary');
          
          cameraPanelInfo.innerHTML = `
            <i class="bi bi-exclamation-circle me-1 text-danger"></i>
            <span class="text-danger">${support.message}</span>
          `;
        }
      });
    }
    
    // ============================================
    // ATTACH EVENT LISTENER KE TOMBOL KAMERA
    // Backup jika onclick tidak berfungsi
    // ============================================
    const startCameraBtn = document.getElementById('start-camera-btn');
    if (startCameraBtn) {
      console.log('✅ Start camera button found, attaching event listener');
      
      // Remove existing onclick to prevent double trigger
      // startCameraBtn.removeAttribute('onclick');
      
      // Add event listener as backup
      startCameraBtn.addEventListener('click', function(e) {
        console.log('🎬 Camera button clicked via event listener!');
        // Function akan dipanggil via onclick attribute
      });
    } else {
      console.error('❌ Start camera button NOT FOUND!');
    }
    
    // Reset tombol kamera saat kembali ke tab upload
    const uploadTab = document.getElementById('upload-tab');
    if (uploadTab) {
      uploadTab.addEventListener('click', function() {
        // Stop kamera jika sedang aktif
        stopCamera();
        retakePhoto();
      });
    }
  });
  
  function openBayarModal(keuanganId, indexBulan, namaBulan, tagihan) {
    console.log('openBayarModal called:', {keuanganId, indexBulan, namaBulan, tagihan});
    
    // Pastikan modal sudah diinisialisasi
    if (!modalBayar) {
      const modalElement = document.getElementById('modalBayar');
      if (modalElement) {
        modalBayar = new bootstrap.Modal(modalElement);
        console.log('Modal diinisialisasi di openBayarModal');
      } else {
        console.error('Element modalBayar tidak ditemukan!');
        alert('Error: Modal element tidak ditemukan');
        return;
      }
    }
    
    // Set data ke hidden inputs
    document.getElementById('bayar_keuangan_id').value = keuanganId;
    document.getElementById('bayar_index_bulan').value = indexBulan;
    
    // ⚠️ CRITICAL FIX: Update NIS dan Nama untuk OCR validation
    // Data diambil dari PHP variable yang sudah di-load dari database
    const nisValue = '<?php echo htmlspecialchars($keuangan["nis"]); ?>';
    const namaValue = '<?php echo htmlspecialchars($keuangan["nama"]); ?>';
    
    document.getElementById('bayar_nis').value = nisValue;
    document.getElementById('bayar_nama').value = namaValue;
    
    console.log('OCR Data Updated:', {nis: nisValue, nama: namaValue});
    
    // Set info bulan dan tagihan
    document.getElementById('info_bulan').textContent = namaBulan;
    document.getElementById('info_tagihan').textContent = 'Rp ' + tagihan.toLocaleString('id-ID');
    
    // Set nominal default
    document.getElementById('nominal_bayar').value = tagihan;
    
    // Set tanggal bayar default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_bayar').value = today;
    
    // Reset file input dan OCR result
    const fileInput = document.getElementById('bukti_bayar');
    if (fileInput) {
      fileInput.value = '';
    }
    
    // Clear image preview
    const preview = document.getElementById('image_preview_admin');
    const previewImg = document.getElementById('preview_img_admin');
    if (previewImg) {
      previewImg.src = '';
    }
    if (preview) {
      preview.style.display = 'none';
    }
    
    const ocrResult = document.getElementById('ocr-validation-result');
    if (ocrResult) {
      ocrResult.style.display = 'none';
    }
    
    // Reset metode pembayaran
    document.getElementById('metode').value = '';
    togglePaymentMethod('');
    
    console.log('Menampilkan modal...');
    // Tampilkan modal
    if (modalBayar) {
      modalBayar.show();
    } else {
      console.error('Modal belum diinisialisasi');
      alert('Terjadi kesalahan saat membuka form pembayaran');
    }
  }
  
  // Fungsi toggle tempat bayar berdasarkan metode (Admin)
  function togglePaymentMethod(metode) {
    const tempatBayarWrapper = document.getElementById('tempat_bayar_wrapper');
    const tempatBayarSelect = document.getElementById('tempat_bayar');
    const buktiSection = document.getElementById('bukti_section');
    const tunaiSection = document.getElementById('tunai_section');
    const buktiInput = document.getElementById('bukti_bayar');
    
    if (metode === 'Transfer') {
      // Show bank selection and upload section
      tempatBayarWrapper.style.display = 'block';
      tempatBayarSelect.required = true;
      tempatBayarSelect.value = 'BRI'; // Auto select BRI
      
      buktiSection.style.display = 'block';
      tunaiSection.style.display = 'none';
      buktiInput.required = false; // Optional (admin can approve manually)
    } else if (metode === 'Tunai') {
      // Hide bank selection, show tunai form
      tempatBayarWrapper.style.display = 'none';
      tempatBayarSelect.required = false;
      tempatBayarSelect.value = 'Kas Sekolah'; // Default untuk tunai
      
      buktiSection.style.display = 'none';
      tunaiSection.style.display = 'block';
      buktiInput.required = false; // Not needed for cash
    } else {
      // Reset state
      tempatBayarWrapper.style.display = 'none';
      tempatBayarSelect.required = false;
      tempatBayarSelect.value = '';
      
      buktiSection.style.display = 'none';
      tunaiSection.style.display = 'none';
      buktiInput.required = false;
    }
  }
  
  // Function untuk cetak kwitansi pembayaran tunai
  function printKwitansi(pembayaranId) {
    if (!pembayaranId) {
      alert('ID pembayaran tidak valid');
      return;
    }
    
    // Open print page in new window
    const url = 'print_kwitansi.php?id=' + pembayaranId;
    window.open(url, '_blank', 'width=900,height=800,scrollbars=yes');
  }
  
  // Function untuk menampilkan bukti transfer
  function showBuktiBayar(filePath, bulan) {
    console.log('Original filePath from database:', filePath);
    
    // Set judul modal dengan nama bulan
    document.getElementById('buktiBulan').textContent = bulan;
    
    // Cek ekstensi file
    const fileExt = filePath.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
    const isPdf = fileExt === 'pdf';
    
    // Normalize path
    // Database menyimpan hanya nama file (bukti_12_2_1762103155.jpg)
    // Kita perlu tambahkan path lengkap: /uploads/bukti_bayar/
    let fullPath;
    
    if (filePath.startsWith('uploads/')) {
      // Jika sudah ada path uploads/ (data lama mungkin)
      fullPath = '/' + filePath;
    } else if (filePath.startsWith('/uploads/')) {
      // Jika sudah absolute path
      fullPath = filePath;
    } else if (filePath.includes('/')) {
      // Jika ada slash tapi belum lengkap
      fullPath = '/' + filePath;
    } else {
      // CASE NORMAL: Hanya nama file (bukti_13_5_1762411437.png)
      fullPath = '/uploads/bukti_bayar/' + filePath;
    }
    
    console.log('Full path for display:', fullPath);
    
    // Set download link
    document.getElementById('downloadBukti').href = fullPath;
    
    // Tampilkan preview
    const container = document.getElementById('buktiImageContainer');
    
    if (isImage) {
      container.innerHTML = `
        <div class="text-center">
          <img src="${fullPath}" 
               class="img-fluid rounded shadow" 
               style="max-height: 500px; max-width: 100%; object-fit: contain;" 
               alt="Bukti Transfer ${bulan}"
               onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'alert alert-danger\\'><i class=\\'bi bi-exclamation-triangle fs-1 mb-3 d-block\\'></i><h5>Gambar Tidak Ditemukan</h5><p><strong>File:</strong> ${filePath}</p><p><strong>Path:</strong> ${fullPath}</p><p class=\\'small text-muted\\'>Pastikan file ada di: <code>public/uploads/bukti_bayar/${filePath}</code></p></div>';">
          <div class="mt-2 small text-muted">
            <i class="bi bi-info-circle me-1"></i>Klik "Unduh Bukti" untuk menyimpan file
          </div>
        </div>
      `;
    } else if (isPdf) {
      container.innerHTML = `
        <div class="alert alert-info text-center">
          <i class="bi bi-file-pdf-fill fs-1 mb-3 d-block"></i>
          <h5>Dokumen PDF</h5>
          <p class="mb-3">File bukti transfer dalam format PDF</p>
          <a href="${fullPath}" target="_blank" class="btn btn-primary">
            <i class="bi bi-eye me-1"></i> Buka PDF di Tab Baru
          </a>
        </div>
      `;
    } else {
      container.innerHTML = `
        <div class="alert alert-warning text-center">
          <i class="bi bi-file-earmark fs-1 mb-3 d-block"></i>
          <h5>File Tidak Didukung</h5>
          <p class="mb-3">Format file: ${fileExt.toUpperCase()}</p>
          <a href="${fullPath}" target="_blank" class="btn btn-secondary">
            <i class="bi bi-download me-1"></i> Unduh File
          </a>
        </div>
      `;
    }
    
    // Tampilkan modal
    if (modalBuktiBayar) {
      modalBuktiBayar.show();
    } else {
      console.error('Modal bukti bayar belum diinisialisasi');
      alert('Terjadi kesalahan saat membuka preview');
    }
  }
  
  // ============================================
  // FUNGSI KAMERA
  // ============================================
  let cameraStream = null;
  
  // Debug: Log saat script loaded
  console.log('📜 Camera script loaded!');
  console.log('🔍 Checking if functions are defined...');
  console.log('startCamera:', typeof startCamera);
  console.log('capturePhoto:', typeof capturePhoto);
  console.log('retakePhoto:', typeof retakePhoto);
  console.log('stopCamera:', typeof stopCamera);
  
  // Cek kompatibilitas browser untuk akses kamera
  function checkCameraSupport() {
    console.log('🔍 Checking camera support...');
    console.log('Browser:', navigator.userAgent);
    
    // Cek 1: navigator.mediaDevices
    if (!navigator.mediaDevices) {
      console.error('❌ navigator.mediaDevices not available');
      return {
        supported: false,
        message: 'Browser Anda terlalu lama dan tidak mendukung API kamera modern. Update browser atau gunakan: Chrome 53+, Firefox 36+, Safari 11+, Edge 12+'
      };
    }
    
    // Cek 2: getUserMedia method
    if (!navigator.mediaDevices.getUserMedia) {
      console.error('❌ getUserMedia not available');
      
      // Cek apakah ada API lama yang tersedia
      const hasLegacyAPI = !!(navigator.getUserMedia || 
                             navigator.webkitGetUserMedia || 
                             navigator.mozGetUserMedia || 
                             navigator.msGetUserMedia);
      
      if (hasLegacyAPI) {
        console.warn('⚠️ Only legacy getUserMedia API found - polyfill should handle this');
        // Polyfill seharusnya sudah handle ini
      }
      
      return {
        supported: false,
        message: 'Akses kamera tidak tersedia. Pastikan Anda menggunakan browser versi terbaru dan izinkan akses kamera di pengaturan browser.'
      };
    }
    
    console.log('✅ navigator.mediaDevices.getUserMedia available');
    
    // Cek 3: Secure context (HTTPS/localhost)
    const isSecureContext = window.isSecureContext || 
                           location.protocol === 'https:' || 
                           location.hostname === 'localhost' || 
                           location.hostname === '127.0.0.1' ||
                           location.hostname.endsWith('.local') || // Laragon
                           location.hostname.endsWith('.test') ||  // Valet
                           /^192\.168\.\d{1,3}\.\d{1,3}$/.test(location.hostname) || // LAN
                           /^10\.\d{1,3}\.\d{1,3}\.\d{1,3}$/.test(location.hostname) || // Private
                           /^172\.(1[6-9]|2[0-9]|3[0-1])\.\d{1,3}\.\d{1,3}$/.test(location.hostname); // Private
    
    if (!isSecureContext) {
      console.warn('⚠️ Not a secure context (HTTP)');
      console.warn('Protocol:', location.protocol);
      console.warn('Hostname:', location.hostname);
      console.warn('Browser may restrict camera access');
      
      // Warning saja, tidak block
    } else {
      console.log('✅ Secure context detected');
    }
    
    console.log('✅ Camera support check passed');
    return { supported: true };
  }
  
  async function startCamera() {
    console.log('🚀 startCamera() function called!');
    
    try {
      // Disable button untuk prevent double click
      const startBtn = document.getElementById('start-camera-btn');
      const infoAlert = document.getElementById('camera-info-alert');
      
      if (startBtn) {
        startBtn.disabled = true;
        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengaktifkan...';
      }
      
      // Show info alert
      if (infoAlert) {
        infoAlert.style.display = 'block';
      }
      
      // Cek kompatibilitas browser terlebih dahulu
      console.log('🔍 Checking browser compatibility...');
      const support = checkCameraSupport();
      if (!support.supported) {
        console.error('❌ Camera not supported:', support.message);
        alert(support.message);
        
        // Reset button
        if (startBtn) {
          startBtn.disabled = false;
          startBtn.innerHTML = '<i class="bi bi-camera-video me-1"></i> Aktifkan Kamera';
        }
        if (infoAlert) {
          infoAlert.style.display = 'none';
        }
        return;
      }
      
      // Log environment info untuk debugging
      console.log('=== Camera Access Debug Info ===');
      console.log('Protocol:', location.protocol);
      console.log('Hostname:', location.hostname);
      console.log('Secure Context:', window.isSecureContext);
      console.log('User Agent:', navigator.userAgent);
      console.log('================================');
      
      // Cek apakah getUserMedia tersedia setelah polyfill
      if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
        throw new Error('getUserMedia tidak tersedia setelah polyfill. Browser Anda mungkin terlalu lama.');
      }
      
      // OPTIMASI: Gunakan constraint sederhana untuk performa lebih cepat
      // Resolusi tinggi (1920x1080) bisa menyebabkan loading lama
      const constraints = {
        video: {
          width: { ideal: 1280, max: 1920 },
          height: { ideal: 720, max: 1080 },
          facingMode: 'user' // 'user' untuk webcam depan (lebih cepat), 'environment' untuk belakang
        }
      };
      
      console.log('📷 Requesting camera access with optimized constraints:', constraints);
      console.log('⏳ Meminta akses kamera... (Mohon izinkan akses kamera di browser)');
      
      // Set timeout untuk detect jika stuck
      const timeout = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Timeout: Kamera tidak merespons setelah 15 detik')), 15000);
      });
      
      // Race antara getUserMedia dan timeout
      cameraStream = await Promise.race([
        navigator.mediaDevices.getUserMedia(constraints),
        timeout
      ]);
      
      console.log('✅ Camera stream obtained:', cameraStream);
      console.log('Video tracks:', cameraStream.getVideoTracks());
      
      const video = document.getElementById('camera-preview');
      if (!video) {
        throw new Error('Video element not found');
      }
      
      video.srcObject = cameraStream;
      video.style.display = 'block';
      
      // Tunggu video benar-benar ready sebelum show controls
      await new Promise((resolve) => {
        video.onloadedmetadata = () => {
          console.log('📹 Video metadata loaded. Resolution:', video.videoWidth, 'x', video.videoHeight);
          resolve();
        };
        // Fallback jika onloadedmetadata tidak trigger
        setTimeout(resolve, 1000);
      });
      
      // Play video (beberapa browser butuh explicit play)
      try {
        await video.play();
        console.log('▶️ Video playing');
      } catch (playError) {
        console.warn('⚠️ Auto-play warning (non-critical):', playError.message);
        // Tidak fatal, video tetap bisa jalan
      }
      
      // Hide info alert
      if (infoAlert) {
        infoAlert.style.display = 'none';
      }
      
      // Sembunyikan tombol start, tampilkan tombol capture
      if (startBtn) {
        startBtn.style.display = 'none';
      }
      const captureBtn = document.getElementById('capture-btn');
      if (captureBtn) {
        captureBtn.style.display = 'block';
      }
      
      console.log('✅ Kamera berhasil diaktifkan');
      
    } catch (error) {
      console.error('❌ Error mengakses kamera:', error);
      console.error('Error name:', error.name);
      console.error('Error message:', error.message);
      console.error('Error stack:', error.stack);
      
      // Hide info alert
      const infoAlert = document.getElementById('camera-info-alert');
      if (infoAlert) {
        infoAlert.style.display = 'none';
      }
      
      // Reset button state
      const startBtn = document.getElementById('start-camera-btn');
      if (startBtn) {
        startBtn.disabled = false;
        startBtn.innerHTML = '<i class="bi bi-camera-video me-1"></i> Aktifkan Kamera';
        startBtn.style.display = 'block';
      }
      
      let errorMsg = 'Gagal mengakses kamera.\n\n';
      let helpMsg = '';
      
      // Check jika timeout
      if (error.message && error.message.includes('Timeout')) {
        errorMsg += '⏱️ Kamera Tidak Merespons\n\n';
        errorMsg += 'Kamera terlalu lama untuk diaktifkan (>15 detik).\n\n';
        helpMsg = 'Kemungkinan Penyebab:\n';
        helpMsg += '1. Kamera sedang digunakan aplikasi lain (Zoom, Teams, dll)\n';
        helpMsg += '2. Driver webcam bermasalah\n';
        helpMsg += '3. Permission browser tertunda\n\n';
        helpMsg += 'Solusi:\n';
        helpMsg += '1. Tutup semua aplikasi yang menggunakan kamera\n';
        helpMsg += '2. Restart browser\n';
        helpMsg += '3. Coba lagi dengan klik "Aktifkan Kamera"\n';
        helpMsg += '4. Jika masih gagal, restart komputer';
        
      } else if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
        errorMsg += '❌ Izin Ditolak\n\n';
        errorMsg += 'Anda tidak memberikan izin akses kamera.\n\n';
        helpMsg = 'Solusi:\n';
        helpMsg += '1. Klik icon 🔒 atau ⓘ di address bar\n';
        helpMsg += '2. Ubah "Camera" menjadi "Allow"\n';
        helpMsg += '3. Refresh halaman';
        
      } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
        errorMsg += '❌ Kamera Tidak Ditemukan\n\n';
        errorMsg += 'Tidak ada kamera yang terdeteksi di perangkat Anda.\n\n';
        helpMsg = 'Solusi:\n';
        helpMsg += '1. Pastikan webcam terpasang dengan benar\n';
        helpMsg += '2. Cek Device Manager (Windows)\n';
        helpMsg += '3. Test dengan aplikasi Camera bawaan\n';
        helpMsg += '4. Restart komputer';
        
      } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
        errorMsg += '❌ Kamera Sedang Digunakan\n\n';
        errorMsg += 'Kamera tidak dapat diakses karena sedang digunakan aplikasi lain.\n\n';
        helpMsg = 'Solusi:\n';
        helpMsg += '1. Tutup aplikasi: Zoom, Skype, Teams, Discord\n';
        helpMsg += '2. Tutup tab browser lain yang pakai kamera\n';
        helpMsg += '3. Restart browser\n';
        helpMsg += '4. Restart komputer jika perlu';
        
      } else if (error.name === 'OverconstrainedError' || error.name === 'ConstraintNotSatisfiedError') {
        errorMsg += '❌ Kamera Tidak Mendukung Resolusi\n\n';
        errorMsg += 'Kamera Anda tidak mendukung resolusi yang diminta.\n\n';
        helpMsg = 'Mencoba dengan resolusi lebih rendah...';
        
        // Retry dengan constraint lebih sederhana
        console.log('🔄 Retrying with simpler constraints...');
        try {
          // Constraint paling sederhana - pasti didukung semua kamera
          const simpleConstraints = { video: true };
          console.log('Trying basic constraints:', simpleConstraints);
          
          cameraStream = await navigator.mediaDevices.getUserMedia(simpleConstraints);
          
          const video = document.getElementById('camera-preview');
          video.srcObject = cameraStream;
          video.style.display = 'block';
          
          // Wait for video ready
          await new Promise((resolve) => {
            video.onloadedmetadata = () => {
              console.log('📹 Video ready (basic mode):', video.videoWidth, 'x', video.videoHeight);
              resolve();
            };
            setTimeout(resolve, 1000);
          });
          
          await video.play();
          
          const startBtn = document.getElementById('start-camera-btn');
          if (startBtn) {
            startBtn.style.display = 'none';
          }
          const captureBtn = document.getElementById('capture-btn');
          if (captureBtn) {
            captureBtn.style.display = 'block';
          }
          
          console.log('✅ Camera activated with basic constraints');
          return; // Success dengan constraint sederhana
        } catch (retryError) {
          console.error('❌ Retry failed:', retryError);
          errorMsg += '\nRetry gagal: ' + retryError.message;
          
          // Reset button
          const startBtn = document.getElementById('start-camera-btn');
          if (startBtn) {
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="bi bi-camera-video me-1"></i> Aktifkan Kamera';
            startBtn.style.display = 'block';
          }
        }
        
      } else if (error.name === 'NotSupportedError') {
        errorMsg += '❌ Tidak Didukung\n\n';
        errorMsg += 'Browser atau sistem Anda tidak mendukung akses kamera.\n\n';
        helpMsg = 'Solusi:\n';
        helpMsg += '1. Update browser ke versi terbaru\n';
        helpMsg += '2. Gunakan Chrome, Firefox, atau Edge\n';
        helpMsg += '3. Untuk HTTPS, baca: SETUP_KAMERA_LARAGON.md';
        
      } else if (error.name === 'TypeError') {
        errorMsg += '❌ Browser Tidak Kompatibel\n\n';
        errorMsg += 'Browser Anda tidak mendukung API kamera modern.\n\n';
        helpMsg = 'Solusi:\n';
        helpMsg += '1. Update browser: Chrome 53+, Firefox 36+, Safari 11+\n';
        helpMsg += '2. Atau gunakan browser lain yang lebih baru';
        
      } else if (error.name === 'SecurityError') {
        errorMsg += '❌ Akses Diblokir (Security)\n\n';
        errorMsg += 'Browser memblokir akses kamera karena alasan keamanan.\n\n';
        helpMsg = 'Solusi untuk Laragon/HTTP:\n\n';
        helpMsg += '1. Chrome: chrome://flags/#unsafely-treat-insecure-origin-as-secure\n';
        helpMsg += '2. Enable dan tambahkan: http://' + location.hostname + '\n';
        helpMsg += '3. Relaunch browser\n\n';
        helpMsg += 'Atau gunakan HTTPS untuk production.\n';
        helpMsg += 'Baca: SETUP_KAMERA_LARAGON.md untuk detail.';
        
      } else if (error.message && error.message.includes('getUserMedia')) {
        errorMsg += '❌ API Tidak Tersedia\n\n';
        errorMsg += error.message + '\n\n';
        helpMsg = 'Browser Anda tidak mendukung akses kamera.\n\n';
        helpMsg += 'Browser yang Didukung:\n';
        helpMsg += '• Chrome 53+\n';
        helpMsg += '• Firefox 36+\n';
        helpMsg += '• Safari 11+\n';
        helpMsg += '• Edge 12+\n\n';
        helpMsg += 'Silakan update atau ganti browser.';
        
      } else {
        errorMsg += '❌ Error Tidak Dikenal\n\n';
        errorMsg += error.message || 'Unknown error';
        helpMsg = '\n\nInfo Debug:\n';
        helpMsg += 'Browser: ' + navigator.userAgent.substring(0, 50) + '...\n';
        helpMsg += 'Protocol: ' + location.protocol + '\n';
        helpMsg += 'Hostname: ' + location.hostname;
      }
      
      alert(errorMsg + helpMsg);
    }
  }
  
  function capturePhoto() {
    const video = document.getElementById('camera-preview');
    const canvas = document.getElementById('camera-canvas');
    const capturedImage = document.getElementById('captured-image');
    const cameraResult = document.getElementById('camera-result');
    const captureBtn = document.getElementById('capture-btn');
    
    // Set canvas size sama dengan video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame ke canvas dengan MIRROR EFFECT
    const context = canvas.getContext('2d');
    
    // Flip horizontal (mirror) - sama seperti yang user lihat di preview
    context.translate(canvas.width, 0);
    context.scale(-1, 1);
    
    // Draw video yang sudah di-flip
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Reset transformasi untuk operasi selanjutnya
    context.setTransform(1, 0, 0, 1, 0, 0);
    
    // Convert canvas ke base64
    const photoData = canvas.toDataURL('image/jpeg', 0.8);
    
    // Simpan ke hidden input
    document.getElementById('camera-photo-data').value = photoData;
    
    // Tampilkan preview hasil foto
    capturedImage.src = photoData;
    
    // Stop kamera
    stopCamera();
    
    // Sembunyikan video, tampilkan hasil
    video.style.display = 'none';
    captureBtn.style.display = 'none';
    cameraResult.style.display = 'block';
    
    console.log('📸 Foto berhasil diambil (mirrored)');
  }
  
  async function retakePhoto() {
    console.log('🔄 Retake photo - reactivating camera...');
    
    const video = document.getElementById('camera-preview');
    const cameraResult = document.getElementById('camera-result');
    const startBtn = document.getElementById('start-camera-btn');
    const captureBtn = document.getElementById('capture-btn');
    const retakeBtn = document.getElementById('retake-photo-btn');
    const infoAlert = document.getElementById('camera-info-alert');
    
    // Disable retake button dan show loading
    if (retakeBtn) {
      retakeBtn.disabled = true;
      retakeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengaktifkan...';
    }
    
    // Clear data foto
    document.getElementById('camera-photo-data').value = '';
    
    // Hide hasil foto
    cameraResult.style.display = 'none';
    
    // PENTING: Stop kamera yang lama terlebih dahulu untuk prevent konflik
    console.log('🛑 Stopping previous camera stream...');
    if (cameraStream) {
      cameraStream.getTracks().forEach(track => {
        track.stop();
        console.log('Track stopped:', track.kind);
      });
      cameraStream = null;
      console.log('✅ Previous camera stream stopped');
    }
    
    // Tambahkan delay kecil untuk memastikan kamera benar-benar release
    await new Promise(resolve => setTimeout(resolve, 200));
    
    // Langsung aktifkan kamera lagi (tanpa perlu klik tombol)
    try {
      // Show loading alert
      if (infoAlert) {
        infoAlert.style.display = 'block';
      }
      
      // Gunakan constraint yang sama seperti startCamera
      const constraints = {
        video: {
          width: { ideal: 1280, max: 1920 },
          height: { ideal: 720, max: 1080 },
          facingMode: 'user'
        }
      };
      
      console.log('📷 Restarting camera with fresh stream...');
      
      // Timeout 15 detik
      const timeout = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Timeout: Kamera tidak merespons')), 15000);
      });
      
      // Request kamera lagi
      cameraStream = await Promise.race([
        navigator.mediaDevices.getUserMedia(constraints),
        timeout
      ]);
      
      console.log('✅ Camera restarted successfully');
      
      // Set video stream
      video.srcObject = cameraStream;
      video.style.display = 'block';
      
      // Wait for video ready
      await new Promise((resolve) => {
        video.onloadedmetadata = () => {
          console.log('📹 Video ready:', video.videoWidth, 'x', video.videoHeight);
          resolve();
        };
        setTimeout(resolve, 1000);
      });
      
      // Play video
      await video.play();
      
      // Reset retake button state (enable back for next time)
      if (retakeBtn) {
        retakeBtn.disabled = false;
        retakeBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Ambil Ulang';
      }
      
      // Hide alert dan tombol start, show tombol capture
      if (infoAlert) {
        infoAlert.style.display = 'none';
      }
      if (startBtn) {
        startBtn.style.display = 'none';
      }
      if (captureBtn) {
        captureBtn.style.display = 'block';
      }
      
      console.log('✅ Ready to capture again');
      
    } catch (error) {
      console.error('❌ Error restarting camera:', error);
      
      // Hide alert
      if (infoAlert) {
        infoAlert.style.display = 'none';
      }
      
      // Reset retake button
      if (retakeBtn) {
        retakeBtn.disabled = false;
        retakeBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Ambil Ulang';
      }
      
      // Show error - tapi tetap tampilkan hasil foto
      cameraResult.style.display = 'block';
      
      // Show error message dengan opsi
      const retry = confirm(
        'Gagal mengaktifkan kamera kembali.\n\n' + 
        'Error: ' + error.message + '\n\n' +
        'Klik OK untuk mencoba lagi, atau Cancel untuk tetap dengan foto ini.'
      );
      
      if (retry) {
        // Coba lagi
        retakePhoto();
      }
    }
  }
  
  function stopCamera() {
    console.log('🛑 Stopping camera...');
    
    if (cameraStream) {
      // Stop semua tracks (video/audio)
      cameraStream.getTracks().forEach(track => {
        track.stop();
        console.log('Track stopped:', track.kind, track.label);
      });
      cameraStream = null;
      console.log('✅ Camera stream released');
    }
    
    // Bersihkan video element untuk prevent memory leak
    const video = document.getElementById('camera-preview');
    if (video && video.srcObject) {
      video.srcObject = null;
      console.log('✅ Video element cleared');
    }
  }
  
  // Event listener untuk membersihkan kamera saat modal ditutup
  document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('modalBayar');
    if (modalElement) {
      modalElement.addEventListener('hidden.bs.modal', function() {
        stopCamera();
        retakePhoto(); // Reset semua state kamera
      });
    }
  });
  
  // ============================================
  // TEST FUNCTION - Debugging
  // ============================================
  function testCameraButton() {
    console.log('🧪 Testing camera button...');
    const btn = document.getElementById('start-camera-btn');
    if (btn) {
      console.log('✅ Button found!');
      console.log('Button disabled?', btn.disabled);
      console.log('Button display:', btn.style.display);
      console.log('Button onclick:', btn.onclick);
      console.log('Button innerHTML:', btn.innerHTML);
    } else {
      console.error('❌ Button NOT found!');
    }
    
    const video = document.getElementById('camera-preview');
    console.log('Video element:', video ? '✅ Found' : '❌ Not found');
    
    const canvas = document.getElementById('camera-canvas');
    console.log('Canvas element:', canvas ? '✅ Found' : '❌ Not found');
  }
  
  // Make function available globally for console testing
  window.testCameraButton = testCameraButton;
  window.startCamera = startCamera;
  window.capturePhoto = capturePhoto;
  window.retakePhoto = retakePhoto;
  window.stopCamera = stopCamera;
  
  console.log('✅ Camera functions attached to window object');
  
  function confirmLogout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
      window.location.href = '/auth/logout.php';
    }
    return false;
  }
  
  function toggleUserDropdown(event) {
    event.stopPropagation(); // Prevent event bubbling
    const menu = document.getElementById('userDropdownMenu');
    if (menu) {
      const isShowing = menu.style.display === 'block';
      menu.style.display = isShowing ? 'none' : 'block';
      console.log('Dropdown toggled. Display:', menu.style.display);
    } else {
      console.error('Dropdown menu not found!');
    }
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
        console.log('Dropdown closed by outside click');
      }
    }
  });
  
  function submitPembayaran() {
    const form = document.getElementById('formBayar');
    const formData = new FormData(form);
    
    // Deteksi role dari PHP session
    const userRole = '<?php echo $userRole; ?>';
    const endpoint = userRole === 'siswa' ? '/api/process_payment_student.php' : '/api/process_payment.php';
    
    // Debug: Log semua data yang akan dikirim
    console.log('User role:', userRole);
    console.log('Endpoint:', endpoint);
    console.log('Data yang akan dikirim:');
    for (let [key, value] of formData.entries()) {
      console.log(key + ': ' + value);
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
      document.getElementById('uploadProgressBar').style.width = '75%';
      document.getElementById('uploadProgressBar').innerHTML = '<small>Validating...</small>';
    }, 1000);
    
    fetch(endpoint, {
      method: 'POST',
      body: formData
    })
    .then(response => {
      // Cek apakah response adalah JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        return response.text().then(text => {
          throw new Error('Server mengembalikan HTML bukan JSON. Mungkin ada error PHP. Cek console untuk detail.');
        });
      }
      return response.json();
    })
    .then(data => {
      console.log('Response dari server:', data);
      
      // Complete progress
      document.getElementById('uploadProgressBar').style.width = '100%';
      document.getElementById('uploadProgressBar').innerHTML = '<small>Complete!</small>';
      
      
      // Remove loading overlay after short delay
      setTimeout(() => {
        if (loadingOverlay) {
          loadingOverlay.remove();
        }
        
        if (data.success) {
          // Show success message
          let message = data.message;
          if (data.auto_validated) {
            message += '\n\n✅ Bukti pembayaran telah divalidasi otomatis oleh AI!';
          }
          
          alert(message);
          modalBayar.hide();
          
          // For Tunai payment with valid status, show print option
          if (data.metode === 'Tunai' && data.status === 'valid' && data.pembayaran_id) {
            const printNow = confirm('Pembayaran Tunai berhasil!\n\nApakah Anda ingin mencetak kwitansi sekarang?');
            if (printNow) {
              printKwitansi(data.pembayaran_id);
            }
          }
          
          location.reload();
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
        errorMsg += '\n1. File PHP memiliki error syntax';
        errorMsg += '\n2. Session timeout (silakan refresh dan login ulang)';
        errorMsg += '\n3. Path file salah';
        errorMsg += '\n\n💡 Solusi: Buka Console (F12) untuk melihat detail error';
      }
      
      alert(errorMsg);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    });
  }
  
  function updateStatus(pembayaranId, newStatus) {
    const statusText = newStatus === 'valid' ? 'validasi' : 'tolak';
    const confirmMsg = `Apakah Anda yakin ingin ${statusText} pembayaran ini?`;
    
    if (!confirm(confirmMsg)) {
      return;
    }
    
    const formData = new FormData();
    formData.append('pembayaran_id', pembayaranId);
    formData.append('status', newStatus);
    
    fetch('/api/update_payment_status.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
        location.reload(); // Reload halaman untuk update tampilan
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      alert('Terjadi kesalahan: ' + error.message);
    });
  }
  
  // ============================================
  // OCR VALIDATION FUNCTION - ADMIN VERSION
  // ============================================
  async function validateWithOCR(file) {
    const resultDiv = document.getElementById('ocr-validation-result');
    const resultContent = document.getElementById('ocr-result-content');
    
    if (!file) {
      console.log('No file provided for OCR');
      return;
    }
    
    // Show loading
    resultDiv.className = 'alert alert-info';
    resultDiv.style.display = 'block';
    resultContent.innerHTML = `
      <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm me-2" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div>
          <strong>Memvalidasi bukti transfer dengan AI...</strong><br>
          <small>Mohon tunggu, sistem sedang membaca dan menganalisis bukti pembayaran</small>
        </div>
      </div>
    `;
    
    // Get expected data
    const expectedNominal = parseFloat(document.getElementById('nominal_bayar').value) || 0;
    const keuanganId = document.getElementById('bayar_keuangan_id').value;
    const expectedNis = document.getElementById('bayar_nis').value;
    const expectedNama = document.getElementById('bayar_nama').value;
    
    // Debug log
    console.log('=== OCR Validation Data ===');
    console.log('Expected Amount:', expectedNominal);
    console.log('Expected NIS:', expectedNis);
    console.log('Expected Nama:', expectedNama);
    console.log('Keuangan ID:', keuanganId);
    console.log('===========================');
    
    // Prepare FormData for OCR API
    const formData = new FormData();
    formData.append('file', file);
    formData.append('expected_amount', expectedNominal);
    formData.append('expected_nis', expectedNis);
    formData.append('expected_nama', expectedNama);
    formData.append('keuangan_id', keuanganId);
    formData.append('user_type', 'admin');
    formData.append('uploader_type', 'admin');
    formData.append('uploader_id', '1'); // Ganti dengan ID admin sebenarnya jika sudah ada
    
    // Dynamic OCR API URL - support ngrok/production
    const ocrApiUrl = '<?php echo OCR_API_URL; ?>/api/v1/validate-transfer';
    console.log('OCR API URL:', ocrApiUrl);
    
    try {
      const response = await fetch(ocrApiUrl, {
        method: 'POST',
        body: formData
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const result = await response.json();
      console.log('OCR Result:', result);
      
      if (result.success) {
        displayOCRResult(result.data, expectedNominal);
      } else {
        resultDiv.className = 'alert alert-warning';
        resultContent.innerHTML = `
          <strong>Validasi Gagal</strong><br>
          <small>${result.error || 'Tidak dapat memvalidasi bukti transfer'}</small><br>
          <small class="text-muted">Admin tetap dapat memproses pembayaran secara manual</small>
        `;
      }
      
    } catch (error) {
      console.error('OCR Error:', error);
      resultDiv.className = 'alert alert-secondary';
      resultContent.innerHTML = `
        <strong>OCR Server Tidak Tersedia</strong><br>
        <small>Sistem OCR sedang offline. Admin dapat memproses pembayaran secara manual.</small><br>
        <small class="text-muted">Error: ${error.message}</small>
      `;
    }
  }
  
  function displayOCRResult(data, expectedAmount) {
    const resultDiv = document.getElementById('ocr-validation-result');
    const resultContent = document.getElementById('ocr-result-content');

    // Data mapping agar konsisten dengan siswa
    const decision = data.decision || 'review';
    const score = typeof data.validation_score === 'number' ? data.validation_score : 0;
    const parsedData = data.parsed_data || data.extracted_data || {};
    const confidenceScores = data.confidence_scores || {};
    const decisionReason = data.decision_reason || data.recommendation || '-';

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

    // Modern UI mirip siswa
    resultDiv.className = '';
    resultContent.innerHTML = `
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
              <p class="fs-5 fw-bold mb-0">${parsedData.bank_name || parsedData.bank || '-'}</p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-body">
              <h6 class="card-title text-muted mb-2">
                <i class="bi bi-cash"></i> Amount Detected
              </h6>
              <p class="fs-5 fw-bold mb-0">Rp ${(parsedData.transfer_amount || parsedData.amount || 0).toLocaleString('id-ID')}</p>
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
        <small>${decisionReason}</small>
      </div>
    `;
  }
  
  </script>
  
  <!-- Load JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  <script src="/js/notifications.js"></script>
  <?php include __DIR__ . '/../../includes/user_dropdown_script.php'; ?>
  <?php include __DIR__ . '/../../includes/navbar_scripts.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
