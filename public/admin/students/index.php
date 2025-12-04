<?php
// =============================================
// Halaman: Data Siswa
// Deskripsi: Tampilan daftar siswa dengan tabel
// =============================================

// Include koneksi database
require_once __DIR__ . '/../../config.php';

// Check authentication
require_once __DIR__ . '/../../auth_check.php';

// Proses Delete jika ada request
if (isset($_GET['delete'])) {
    $nis = esc($_GET['delete']);
    
    try {
        // Mulai transaction
        $conn->begin_transaction();
        
        // 1. Hapus data pembayaran yang terkait dengan keuangan siswa ini
        // Urutan penting: hapus pembayaran dulu sebelum keuangan
        $stmt1 = $conn->prepare("DELETE p FROM pembayaran p 
                 INNER JOIN keuangan k ON p.keuangan_id = k.keuangan_id 
                 WHERE k.nis = ?");
        $stmt1->bind_param("s", $nis);
        $stmt1->execute();
        $stmt1->close();
        
        // 2. Hapus data keuangan siswa
        $stmt2 = $conn->prepare("DELETE FROM keuangan WHERE nis = ?");
        $stmt2->bind_param("s", $nis);
        $stmt2->execute();
        $stmt2->close();
        
        // 3. Ambil user_id dari siswa untuk dihapus nanti
        $stmt3 = $conn->prepare("SELECT user_id FROM siswa WHERE nis = ?");
        $stmt3->bind_param("s", $nis);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        $userId = null;
        if ($result3 && $result3->num_rows > 0) {
            $userData = $result3->fetch_assoc();
            $userId = $userData['user_id'];
        }
        $stmt3->close();
        
        // 4. Hapus data siswa
        $stmt4 = $conn->prepare("DELETE FROM siswa WHERE nis = ?");
        $stmt4->bind_param("s", $nis);
        $stmt4->execute();
        $affected = $stmt4->affected_rows;
        $stmt4->close();
        
        // 5. Hapus user terkait jika ada
        if ($userId) {
            $stmt5 = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt5->bind_param("i", $userId);
            $stmt5->execute();
            $stmt5->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = 'Data siswa dan semua data terkait berhasil dihapus!';
        header('Location: /admin/students/index.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback jika ada error
        $conn->rollback();
        $_SESSION['error'] = 'Gagal menghapus data: ' . $e->getMessage();
        header('Location: /admin/students/index.php');
        exit;
    }
}

// Query untuk mengambil data siswa dengan pagination
$where = "1=1";
$search = $_GET['search'] ?? '';
$kelas = $_GET['kelas'] ?? '';
$jurusan = $_GET['jurusan'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // Items per page
$offset = ($page - 1) * $limit;

// Build WHERE clause with prepared statement params
$params = [];
$types = "";

if ($search) {
    $searchParam = "%$search%";
    $where .= " AND (nis LIKE ? OR nama LIKE ?)";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}
if ($kelas) {
    $where .= " AND kelas = ?";
    $params[] = $kelas;
    $types .= "s";
}
if ($jurusan) {
    $jurusanParam = "%$jurusan%";
    $where .= " AND jurusan LIKE ?";
    $params[] = $jurusanParam;
    $types .= "s";
}

// Get total count for pagination
$sqlCount = "SELECT COUNT(*) as total FROM siswa WHERE $where";
if (!empty($params)) {
    $stmtCount = $conn->prepare($sqlCount);
    $stmtCount->bind_param($types, ...$params);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalRows = $resultCount->fetch_assoc()['total'];
    $stmtCount->close();
} else {
    $resultCount = $conn->query($sqlCount);
    $totalRows = $resultCount->fetch_assoc()['total'];
}

$totalPages = ceil($totalRows / $limit);

// Get data with pagination
$sql = "SELECT * FROM siswa WHERE $where ORDER BY nama ASC LIMIT ? OFFSET ?";
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $allParams = array_merge($params, [$limit, $offset]);
    $allTypes = $types . "ii";
    $stmt->bind_param($allTypes, ...$allParams);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Get list kelas dan jurusan untuk filter
$sqlKelas = "SELECT DISTINCT kelas FROM siswa ORDER BY kelas";
$resultKelas = $conn->query($sqlKelas);

$sqlJurusan = "SELECT DISTINCT jurusan FROM siswa ORDER BY jurusan";
$resultJurusan = $conn->query($sqlJurusan);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Data Siswa - SMK BIT Bina Aulia</title>
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
          <button id="sidebarToggle" class="btn btn-toggle btn-sm me-3" aria-label="Toggle sidebar" aria-expanded="true">☰</button>
          <h1 class="app-title mb-0">SMK BIT Bina Aulia</h1>
        </div>
        <?php include __DIR__ . '/../../includes/user_dropdown.php'; ?>
      </div>
    </header>

    <!-- Main content area -->
    <main class="content">
      <div class="container-fluid">
        
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
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> Data Siswa</h4>
          <div class="d-flex gap-2">
            <button onclick="showImportModal()" class="btn btn-success" title="Import dari Excel/CSV">
              <i class="bi bi-file-earmark-arrow-up me-1"></i> <span class="d-none d-sm-inline">Import</span>
            </button>
            <a href="create.php" class="btn btn-primary">
              <i class="bi bi-plus-circle me-1"></i> <span class="d-none d-sm-inline">Tambah</span> Siswa
            </a>
          </div>
        </div>

        <!-- Filter Form -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <form method="GET" id="filterForm" class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select name="kelas" id="kelasFilter" class="form-select">
                  <option value="">Semua Kelas</option>
                  <?php while($k = $resultKelas->fetch_assoc()): ?>
                    <option value="<?php echo $k['kelas']; ?>" <?php echo $kelas == $k['kelas'] ? 'selected' : ''; ?>><?php echo $k['kelas']; ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Jurusan</label>
                <select name="jurusan" id="jurusanFilter" class="form-select">
                  <option value="">Semua Jurusan</option>
                  <?php while($j = $resultJurusan->fetch_assoc()): ?>
                    <option value="<?php echo $j['jurusan']; ?>" <?php echo $jurusan == $j['jurusan'] ? 'selected' : ''; ?>><?php echo $j['jurusan']; ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Cari</label>
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="NIS / Nama Siswa" value="<?php echo htmlspecialchars($search); ?>">
              </div>
              <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                  <i class="bi bi-search me-1"></i> Filter
                </button>
                <?php if($search || $kelas || $jurusan): ?>
                <a href="/admin/students/index.php" class="btn btn-secondary" title="Reset Filter">
                  <i class="bi bi-x-circle"></i>
                </a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <!-- Tabel Data Siswa -->
        <div class="card border-0 shadow-sm">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th width="80" class="text-center">Foto</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th class="d-none d-md-table-cell">Kelas</th>
                    <th class="d-none d-lg-table-cell">Jurusan</th>
                    <th class="d-none d-lg-table-cell">Jenis Kelamin</th>
                    <th class="d-none d-md-table-cell">Status</th>
                    <th width="150" class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if($result->num_rows > 0): ?>
                    <?php while($siswa = $result->fetch_assoc()): ?>
                  <tr>
                    <td class="text-center">
                      <!-- Foto siswa -->
                      <?php if($siswa['foto']): ?>
                        <?php 
                          // Pastikan path foto benar (tambahkan / jika belum ada)
                          $fotoPath = $siswa['foto'];
                          if (strpos($fotoPath, '/') !== 0 && strpos($fotoPath, 'http') !== 0) {
                              $fotoPath = '/' . $fotoPath;
                          }
                        ?>
                        <img src="<?php echo htmlspecialchars($fotoPath); ?>" 
                             alt="Foto" 
                             class="rounded" 
                             style="width: 50px; height: 50px; object-fit: cover;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="bg-secondary rounded d-none align-items-center justify-content-center mx-auto" style="width: 50px; height: 50px;">
                          <i class="bi bi-person-fill text-white"></i>
                        </div>
                      <?php else: ?>
                      <div class="bg-secondary rounded d-flex align-items-center justify-content-center mx-auto" style="width: 50px; height: 50px;">
                        <i class="bi bi-person-fill text-white"></i>
                      </div>
                      <?php endif; ?>
                    </td>
                    <td class="fw-semibold"><?php echo htmlspecialchars($siswa['nis']); ?></td>
                    <td>
                      <div class="fw-bold"><?php echo htmlspecialchars($siswa['nama']); ?></div>
                      <div class="small text-muted d-md-none">
                        <?php echo htmlspecialchars($siswa['kelas']); ?> - <?php echo htmlspecialchars($siswa['jurusan']); ?>
                        <span class="badge bg-<?php echo $siswa['status_siswa'] == 'aktif' ? 'success' : 'secondary'; ?> ms-1">
                          <?php echo ucfirst($siswa['status_siswa']); ?>
                        </span>
                      </div>
                    </td>
                    <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($siswa['kelas']); ?></td>
                    <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($siswa['jurusan']); ?></td>
                    <td class="d-none d-lg-table-cell">
                      <i class="bi bi-<?php echo $siswa['jk'] == 'L' ? 'gender-male text-primary' : 'gender-female text-danger'; ?> me-1"></i>
                      <?php echo $siswa['jk'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                    </td>
                    <td class="d-none d-md-table-cell">
                      <span class="badge bg-<?php echo $siswa['status_siswa'] == 'aktif' ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst($siswa['status_siswa']); ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm" role="group">
                        <button onclick="viewDetail('<?php echo htmlspecialchars($siswa['nis']); ?>')" class="btn btn-info text-white" title="Detail">
                          <i class="bi bi-eye"></i>
                        </button>
                        <a href="edit.php?nis=<?php echo urlencode($siswa['nis']); ?>" class="btn btn-warning text-white" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <button onclick="confirmDelete('<?php echo htmlspecialchars($siswa['nis']); ?>', '<?php echo htmlspecialchars($siswa['nama']); ?>')" class="btn btn-danger" title="Hapus">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                        <p class="mb-0">Tidak ada data siswa</p>
                        <?php if($search || $kelas || $jurusan): ?>
                        <small class="text-muted">Coba ubah filter pencarian</small>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Footer dengan info pagination -->
          <div class="card-footer bg-white border-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div class="text-muted small">
                Menampilkan <?php echo $result->num_rows; ?> dari <?php echo $totalRows; ?> siswa
                <?php if($totalPages > 1): ?>
                  (Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?>)
                <?php endif; ?>
              </div>
              
              <?php if($totalPages > 1): ?>
              <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                  <!-- Previous -->
                  <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&kelas=<?php echo urlencode($kelas); ?>&jurusan=<?php echo urlencode($jurusan); ?>" aria-label="Previous">
                      <span aria-hidden="true">&laquo;</span>
                    </a>
                  </li>
                  
                  <?php
                  // Smart pagination - show max 5 pages
                  $startPage = max(1, $page - 2);
                  $endPage = min($totalPages, $page + 2);
                  
                  if($startPage > 1): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&kelas=<?php echo urlencode($kelas); ?>&jurusan=<?php echo urlencode($jurusan); ?>">1</a>
                    </li>
                    <?php if($startPage > 2): ?>
                      <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                  <?php endif; ?>
                  
                  <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                      <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kelas=<?php echo urlencode($kelas); ?>&jurusan=<?php echo urlencode($jurusan); ?>"><?php echo $i; ?></a>
                    </li>
                  <?php endfor; ?>
                  
                  <?php if($endPage < $totalPages): ?>
                    <?php if($endPage < $totalPages - 1): ?>
                      <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&kelas=<?php echo urlencode($kelas); ?>&jurusan=<?php echo urlencode($jurusan); ?>"><?php echo $totalPages; ?></a>
                    </li>
                  <?php endif; ?>
                  
                  <!-- Next -->
                  <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&kelas=<?php echo urlencode($kelas); ?>&jurusan=<?php echo urlencode($jurusan); ?>" aria-label="Next">
                      <span aria-hidden="true">&raquo;</span>
                    </a>
                  </li>
                </ul>
              </nav>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- Modal Import Siswa -->
  <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-success text-white border-0">
          <h5 class="modal-title fw-bold" id="importModalLabel">
            <i class="bi bi-file-earmark-arrow-up me-2"></i> Import Data Siswa dari CSV/Excel
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          
          <!-- Instruksi -->
          <div class="alert alert-info border-0 mb-4">
            <h6 class="alert-heading fw-bold mb-2">
              <i class="bi bi-info-circle-fill me-2"></i> Panduan Import
            </h6>
            <ol class="mb-0 small">
              <li>Download template CSV terlebih dahulu</li>
              <li>Isi data siswa sesuai kolom yang tersedia</li>
              <li>Upload file CSV yang sudah diisi</li>
              <li>Sistem akan memvalidasi dan menambahkan data secara otomatis</li>
              <li>Foto default akan menggunakan icon personal (bisa diupdate manual nanti)</li>
            </ol>
          </div>

          <!-- Download Template -->
          <div class="card bg-light border-0 mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1"><i class="bi bi-file-earmark-spreadsheet text-success me-2"></i> Template CSV</h6>
                  <small class="text-muted">Download template untuk panduan format data</small>
                </div>
                <a href="download_template.php" class="btn btn-outline-success btn-sm">
                  <i class="bi bi-download me-1"></i> Download
                </a>
              </div>
            </div>
          </div>

          <!-- Upload Form -->
          <form id="importForm" method="POST" action="process_import.php" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="csvFile" class="form-label fw-semibold">
                <i class="bi bi-cloud-upload me-1"></i> Pilih File CSV/Excel
              </label>
              <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv,.xlsx,.xls" required>
              <div class="form-text">Format yang didukung: .csv, .xlsx, .xls (Max 2MB)</div>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="skipDuplicate" name="skipDuplicate" value="1" checked>
              <label class="form-check-label" for="skipDuplicate">
                Skip data duplikat (NIS yang sudah ada akan dilewati)
              </label>
            </div>

            <div id="importProgress" class="d-none">
              <div class="progress mb-2">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
              </div>
              <small class="text-muted">Mengimpor data...</small>
            </div>

            <div id="importResult" class="d-none"></div>
          </form>

        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Batal
          </button>
          <button type="button" class="btn btn-success" onclick="submitImport()">
            <i class="bi bi-upload me-1"></i> Upload & Import
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Detail Siswa -->
  <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title fw-bold" id="detailModalLabel">
            <i class="bi bi-person-badge-fill me-2"></i> Detail Lengkap Siswa
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4" id="detailContent" style="min-height: 400px;">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted fw-semibold">Memuat data siswa...</p>
          </div>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Tutup
          </button>
          <a href="#" id="editButton" class="btn btn-warning text-white px-4">
            <i class="bi bi-pencil-square me-1"></i> Edit Data
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  <script src="/js/notifications.js"></script>
  <?php include __DIR__ . '/../../includes/user_dropdown_script.php'; ?>
  <?php include __DIR__ . '/../../includes/navbar_scripts.php'; ?>
  
  <!-- Script untuk konfirmasi delete dan view detail -->
  <script>
    function confirmDelete(nis, nama) {
      if(confirm('⚠️ PERINGATAN!\n\nAnda akan menghapus siswa: ' + nama + '\n\nData yang akan dihapus:\n- Data Siswa\n- Data Keuangan\n- Riwayat Pembayaran\n- Akun Login\n\nApakah Anda yakin?')) {
        window.location.href = '/admin/students/index.php?delete=' + nis;
      }
    }

    function viewDetail(nis) {
      // Tampilkan modal
      const modal = new bootstrap.Modal(document.getElementById('detailModal'));
      modal.show();
      
      // Reset content dengan loading state
      document.getElementById('detailContent').innerHTML = `
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 text-muted fw-semibold">Memuat data siswa...</p>
        </div>
      `;
      
      // Update link edit button
      document.getElementById('editButton').href = '/admin/students/edit.php?nis=' + nis;
      
      // Load data via AJAX
      fetch('/admin/students/api_get_detail.php?nis=' + nis)
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.text();
        })
        .then(html => {
          document.getElementById('detailContent').innerHTML = html;
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('detailContent').innerHTML = 
            `<div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i> 
              Gagal memuat data siswa. Silakan coba lagi.
            </div>`;
        });
    }
    
    
    function showImportModal() {
      const modal = new bootstrap.Modal(document.getElementById('importModal'));
      modal.show();
      // Reset form
      document.getElementById('importForm').reset();
      document.getElementById('importProgress').classList.add('d-none');
      document.getElementById('importResult').classList.add('d-none');
    }
    
    function submitImport() {
      const form = document.getElementById('importForm');
      const fileInput = document.getElementById('csvFile');
      
      if (!fileInput.files || fileInput.files.length === 0) {
        alert('Pilih file CSV/Excel terlebih dahulu!');
        return;
      }
      
      const file = fileInput.files[0];
      const maxSize = 2 * 1024 * 1024; // 2MB
      
      if (file.size > maxSize) {
        alert('Ukuran file terlalu besar! Maksimal 2MB');
        return;
      }
      
      // Show progress
      document.getElementById('importProgress').classList.remove('d-none');
      
      // Submit via AJAX
      const formData = new FormData(form);
      
      fetch('process_import.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        document.getElementById('importProgress').classList.add('d-none');
        
        if (data.success) {
          // Set success message ke session storage untuk ditampilkan setelah redirect
          const message = `Import berhasil! ${data.imported} data ditambahkan${data.skipped > 0 ? `, ${data.skipped} dilewati` : ''}${data.errors > 0 ? `, ${data.errors} gagal` : ''}`;
          sessionStorage.setItem('importSuccess', message);
          
          // Redirect ke halaman index
          window.location.href = '/admin/students/index.php';
        } else {
          document.getElementById('importResult').classList.remove('d-none');
          document.getElementById('importResult').innerHTML = `
            <div class="alert alert-danger border-0">
              <h6 class="alert-heading fw-bold mb-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Import Gagal!
              </h6>
              <p class="mb-0 small">${data.message}</p>
            </div>
          `;
        }
      })
      .catch(error => {
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('importResult').classList.remove('d-none');
        document.getElementById('importResult').innerHTML = `
          <div class="alert alert-danger border-0">
            <h6 class="alert-heading fw-bold mb-2">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> Error!
            </h6>
            <p class="mb-0 small">Terjadi kesalahan saat mengimpor data.</p>
          </div>
        `;
        console.error('Error:', error);
      });
    }
        // Real-time filter: auto-submit saat kelas atau jurusan berubah
    document.getElementById('kelasFilter').addEventListener('change', function() {
      document.getElementById('filterForm').submit();
    });

    document.getElementById('jurusanFilter').addEventListener('change', function() {
      document.getElementById('filterForm').submit();
    });

    // Auto-submit search dengan debounce (delay 700ms)
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
      }, 700);
    });
    
    // Keyboard navigation support
    document.addEventListener('keydown', function(e) {
      // ESC to close modal
      if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('detailModal'));
        if (modal) modal.hide();
      }
    });
    
    // Check for import success message dari sessionStorage
    document.addEventListener('DOMContentLoaded', function() {
      const importSuccess = sessionStorage.getItem('importSuccess');
      if (importSuccess) {
        // Tampilkan notifikasi success
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
          <i class="bi bi-check-circle-fill me-2"></i>
          ${importSuccess}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Hapus dari sessionStorage
        sessionStorage.removeItem('importSuccess');
        
        // Auto hide setelah 5 detik
        setTimeout(() => {
          alertDiv.classList.remove('show');
          setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
      }
    });
  </script>
</body>
</html>
<?php 
// Close prepared statement if exists
if (isset($stmt)) $stmt->close();
$conn->close(); 
?>
