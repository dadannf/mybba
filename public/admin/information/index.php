<?php
// =============================================
// Halaman: Informasi Sekolah (Admin)
// Deskripsi: Mengelola informasi dan pengumuman sekolah
// =============================================

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';

// Pastikan yang login adalah admin
if ($userRole !== 'admin') {
    $_SESSION['error'] = 'Akses ditolak!';
    header('Location: index.php');
    exit;
}

// Proses Tambah Informasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $judul = esc($_POST['judul']);
    $isi = esc($_POST['isi']);
    
    // Handle file upload
    $foto = NULL;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/informasi/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $new_filename = 'info_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
            $foto = 'uploads/informasi/' . $new_filename;
        }
    }
    
    $foto_sql = $foto ? "'$foto'" : "NULL";
    $sql = "INSERT INTO informasi (judul, isi, foto, created_by) 
            VALUES ('$judul', '$isi', $foto_sql, '{$_SESSION['username']}')";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Informasi berhasil ditambahkan!';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan informasi: ' . $conn->error;
    }
    header('Location: /admin/information/index.php');
    exit;
}

// Proses Edit Informasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $info_id = intval($_POST['info_id']);
    $judul = esc($_POST['judul']);
    $isi = esc($_POST['isi']);
    
    // Handle file upload
    $foto_update = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Hapus foto lama jika ada
        $sqlOld = "SELECT foto FROM informasi WHERE informasi_id = $info_id";
        $resultOld = $conn->query($sqlOld);
        if ($resultOld && $row = $resultOld->fetch_assoc()) {
            $oldFotoPath = __DIR__ . '/../../' . $row['foto'];
            if ($row['foto'] && file_exists($oldFotoPath)) {
                unlink($oldFotoPath);
            }
        }
        
        $upload_dir = __DIR__ . '/../../uploads/informasi/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $new_filename = 'info_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
            $foto = 'uploads/informasi/' . $new_filename;
            $foto_update = ", foto = '$foto'";
        }
    }
    
    $sql = "UPDATE informasi SET 
            judul = '$judul',
            isi = '$isi'
            $foto_update
            WHERE informasi_id = $info_id";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Informasi berhasil diupdate!';
    } else {
        $_SESSION['error'] = 'Gagal mengupdate informasi: ' . $conn->error;
    }
    header('Location: /admin/information/index.php');
    exit;
}

// Proses Hapus Informasi
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $info_id = intval($_GET['id']);
    
    // Hapus file foto jika ada
    $sqlFile = "SELECT foto FROM informasi WHERE informasi_id = $info_id";
    $resultFile = $conn->query($sqlFile);
    if ($resultFile && $row = $resultFile->fetch_assoc()) {
        $fotoPath = __DIR__ . '/../../' . $row['foto'];
        if ($row['foto'] && file_exists($fotoPath)) {
            unlink($fotoPath);
        }
    }
    
    $sql = "DELETE FROM informasi WHERE informasi_id = $info_id";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Informasi berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus informasi: ' . $conn->error;
    }
    header('Location: /admin/information/index.php');
    exit;
}

// Query data informasi
$where = "1=1";
$search = isset($_GET['search']) ? esc($_GET['search']) : '';
if ($search) {
    $where .= " AND (judul LIKE '%$search%' OR isi LIKE '%$search%')";
}

$sql = "SELECT * FROM informasi WHERE $where ORDER BY created_at DESC";
$result = $conn->query($sql);

// Statistik
$sqlStats = "SELECT COUNT(*) as total FROM informasi";
$statsResult = $conn->query($sqlStats);
$stats = $statsResult->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Informasi Sekolah - SMK BIT Bina Aulia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS Custom Files -->
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/responsive.css">
  <link rel="stylesheet" href="/css/notifications.css">
  <link rel="stylesheet" href="/css/custom-components.css">
  <link rel="stylesheet" href="/css/admin-portal.css">
  <link rel="stylesheet" href="/css/informasi-page.css">
  
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
        
        <!-- Notifikasi -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Header dengan Warna Solid Elegan -->
        <div class="mb-4">
          <div class="card border-0 shadow-sm" style="background-color: #2c3e50;">
            <div class="card-body text-white py-4">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h3 class="mb-2 fw-bold"><i class="bi bi-megaphone-fill me-2"></i> Informasi & Pengumuman</h3>
                  <p class="mb-0" style="opacity: 0.9;">Kelola dan publikasikan informasi penting untuk seluruh warga sekolah</p>
                </div>
                <button class="btn btn-light btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                  <i class="bi bi-plus-circle-fill me-2"></i> Buat Informasi Baru
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistik Cards dengan Warna Solid Elegan -->
        <div class="row g-4 mb-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #3498db;">
              <div class="card-body text-white">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <div style="opacity: 0.85;" class="mb-1"><small>Total Informasi</small></div>
                    <h2 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h2>
                    <small style="opacity: 0.85;">Pengumuman aktif</small>
                  </div>
                  <div class="ms-3">
                    <i class="bi bi-file-text-fill" style="font-size: 3.5rem; opacity: 0.25;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #27ae60;">
              <div class="card-body text-white">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <div style="opacity: 0.85;" class="mb-1"><small>Dengan Lampiran</small></div>
                    <h2 class="mb-0 fw-bold">
                      <?php 
                      $sqlWithPhoto = "SELECT COUNT(*) as total FROM informasi WHERE foto IS NOT NULL";
                      $photoResult = $conn->query($sqlWithPhoto);
                      echo $photoResult->fetch_assoc()['total'];
                      ?>
                    </h2>
                    <small style="opacity: 0.85;">File terlampir</small>
                  </div>
                  <div class="ms-3">
                    <i class="bi bi-image-fill" style="font-size: 3.5rem; opacity: 0.25;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #8e44ad;">
              <div class="card-body text-white">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <div style="opacity: 0.85;" class="mb-1"><small>Terakhir Dipublikasi</small></div>
                    <h6 class="mb-0 fw-bold">
                      <?php 
                      $sqlLast = "SELECT created_at FROM informasi ORDER BY created_at DESC LIMIT 1";
                      $lastResult = $conn->query($sqlLast);
                      if($lastResult->num_rows > 0) {
                        $lastDate = $lastResult->fetch_assoc()['created_at'];
                        echo date('d M Y', strtotime($lastDate));
                      } else {
                        echo "Belum ada";
                      }
                      ?>
                    </h6>
                    <small style="opacity: 0.85;">Info terbaru</small>
                  </div>
                  <div class="ms-3">
                    <i class="bi bi-calendar-check-fill" style="font-size: 3.5rem; opacity: 0.25;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter & Search dengan Desain Modern -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
              <div class="col-md-10">
                <label class="form-label fw-semibold text-muted mb-2">
                  <i class="bi bi-search me-1"></i> Cari Informasi
                </label>
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                  </span>
                  <input type="text" name="search" class="form-control border-start-0 ps-0" 
                         placeholder="Ketik judul atau isi informasi yang dicari..." 
                         value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                  <i class="bi bi-search me-1"></i> Cari
                </button>
              </div>
              <?php if(isset($_GET['search']) && $_GET['search'] != ''): ?>
              <div class="col-12">
                <a href="/admin/information/index.php" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-x-circle me-1"></i> Reset Pencarian
                </a>
              </div>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <!-- Card Grid Layout untuk Informasi -->
        <?php if ($result->num_rows > 0): ?>
          <div class="row g-4">
            <?php $no = 1; while($info = $result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card h-100 border-0 shadow-sm hover-lift" style="transition: all 0.3s ease;">
                
                <!-- Header Card dengan Foto atau Placeholder -->
                <?php 
                $fileExt = '';
                $isImage = false;
                if ($info['foto']) {
                  $fileExt = strtolower(pathinfo($info['foto'], PATHINFO_EXTENSION));
                  $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                }
                ?>
                
                <?php if ($info['foto'] && $isImage): ?>
                  <!-- Tampilkan foto sebagai thumbnail -->
                  <div class="card-img-top" style="height: 200px; overflow: hidden; background: #f8f9fa;">
                    <img src="/<?php echo htmlspecialchars($info['foto']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;"
                         alt="<?php echo htmlspecialchars($info['judul']); ?>">
                  </div>
                <?php elseif ($info['foto'] && $fileExt === 'pdf'): ?>
                  <!-- Icon untuk PDF -->
                  <div class="card-img-top d-flex align-items-center justify-content-center" 
                       style="height: 200px; background-color: #5dade2;">
                    <div class="text-white text-center">
                      <i class="bi bi-file-earmark-pdf-fill" style="font-size: 4rem; opacity: 0.7;"></i>
                      <p class="mt-2 mb-0 fw-semibold">Dokumen PDF</p>
                    </div>
                  </div>
                <?php elseif ($info['foto']): ?>
                  <!-- Icon untuk file lainnya -->
                  <div class="card-img-top d-flex align-items-center justify-content-center" 
                       style="height: 200px; background-color: #5dade2;">
                    <div class="text-white text-center">
                      <i class="bi bi-file-earmark-fill" style="font-size: 4rem; opacity: 0.7;"></i>
                      <p class="mt-2 mb-0 fw-semibold">File Terlampir</p>
                    </div>
                  </div>
                <?php else: ?>
                  <!-- Placeholder jika tidak ada foto -->
                  <div class="card-img-top d-flex align-items-center justify-content-center" 
                       style="height: 200px; background-color: #95a5a6;">
                    <div class="text-white text-center">
                      <i class="bi bi-megaphone-fill" style="font-size: 4rem; opacity: 0.7;"></i>
                      <p class="mt-2 mb-0 fw-semibold">Pengumuman</p>
                    </div>
                  </div>
                <?php endif; ?>
                
                <!-- Card Body -->
                <div class="card-body d-flex flex-column">
                  <!-- Badge Nomor -->
                  <div class="mb-2">
                    <span class="badge" style="background-color: #3498db; color: white;">
                      <i class="bi bi-hash"></i><?php echo $no++; ?>
                    </span>
                  </div>
                  
                  <!-- Judul -->
                  <h5 class="card-title fw-bold mb-3" style="color: #2d3748; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 3.2rem;">
                    <?php echo htmlspecialchars($info['judul']); ?>
                  </h5>
                  
                  <!-- Preview Isi/Konten -->
                  <div class="card-text text-muted mb-3 flex-grow-1" style="font-size: 0.95rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                    <?php 
                    $isi = htmlspecialchars($info['isi']);
                    $preview = strlen($isi) > 120 ? substr($isi, 0, 120) . '...' : $isi;
                    echo nl2br($preview);
                    ?>
                  </div>
                  
                  <!-- Meta Info -->
                  <div class="d-flex align-items-center text-muted mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-clock me-1"></i>
                    <span><?php echo date('d M Y, H:i', strtotime($info['created_at'])); ?></span>
                  </div>
                  
                  <!-- Action Buttons -->
                  <div class="d-grid gap-2">
                    <!-- Tombol Lihat Informasi -->
                    <button class="btn btn-outline-primary" 
                            onclick="viewInfoDetail(<?php echo htmlspecialchars(json_encode($info), ENT_QUOTES, 'UTF-8'); ?>)">
                      <i class="bi bi-eye"></i> Lihat Informasi
                    </button>
                    
                    <div class="d-flex gap-2">
                      <button class="btn btn-sm btn-warning flex-grow-1" 
                              onclick="editInfo(<?php echo htmlspecialchars(json_encode($info), ENT_QUOTES, 'UTF-8'); ?>)"
                              title="Edit Informasi">
                        <i class="bi bi-pencil-square"></i> Edit
                      </button>
                      <a href="?action=hapus&id=<?php echo $info['informasi_id']; ?>" 
                         class="btn btn-sm btn-danger flex-grow-1" 
                         onclick="return confirm('Yakin ingin menghapus informasi ini?\n\nJudul: <?php echo htmlspecialchars($info['judul']); ?>')"
                         title="Hapus Informasi">
                        <i class="bi bi-trash"></i> Hapus
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <!-- Empty State dengan Desain Modern -->
          <div class="text-center py-5">
            <div class="mb-4">
              <i class="bi bi-inbox" style="font-size: 5rem; color: #cbd5e0;"></i>
            </div>
            <h4 class="text-muted mb-2">Belum Ada Informasi</h4>
            <p class="text-muted mb-4">
              <?php if(isset($_GET['search']) && $_GET['search'] != ''): ?>
                Tidak ditemukan informasi dengan kata kunci "<?php echo htmlspecialchars($_GET['search']); ?>"
              <?php else: ?>
                Mulai buat informasi atau pengumuman untuk warga sekolah
              <?php endif; ?>
            </p>
            <?php if(isset($_GET['search']) && $_GET['search'] != ''): ?>
              <a href="/admin/information/index.php" class="btn btn-primary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Semua Informasi
              </a>
            <?php else: ?>
              <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i> Buat Informasi Pertama
              </button>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>
    </main>
  </div>

  <!-- Modal Tambah dengan Desain Modern -->
  <div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header text-white border-0" style="background-color: #2c3e50;">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-plus-circle-fill me-2"></i>Buat Informasi Baru
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="tambah">
          <div class="modal-body p-4">
            <!-- Info Helper -->
            <div class="alert border-0 mb-4" style="background-color: #d6eaf8; color: #1b4f72;">
              <div class="d-flex align-items-start">
                <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                <div>
                  <strong>Tips:</strong> Buat judul yang menarik dan isi yang jelas agar informasi mudah dipahami oleh pembaca.
                </div>
              </div>
            </div>

            <div class="row g-4">
              <div class="col-12">
                <label class="form-label fw-semibold">
                  <i class="bi bi-type me-1"></i> Judul Informasi 
                  <span class="text-danger">*</span>
                </label>
                <input type="text" name="judul" class="form-control form-control-lg" 
                       placeholder="Contoh: Pengumuman Libur Semester Genap 2024"
                       required maxlength="200">
                <small class="text-muted">Maksimal 200 karakter</small>
              </div>
              
              <div class="col-12">
                <label class="form-label fw-semibold">
                  <i class="bi bi-text-paragraph me-1"></i> Isi/Konten Informasi 
                  <span class="text-danger">*</span>
                </label>
                <textarea name="isi" class="form-control" rows="8" 
                          placeholder="Tulis detail informasi atau pengumuman di sini...&#10;&#10;Anda dapat menambahkan:&#10;- Tanggal dan waktu&#10;- Lokasi atau tempat&#10;- Persyaratan atau ketentuan&#10;- Kontak yang dapat dihubungi"
                          required></textarea>
                <small class="text-muted">
                  <i class="bi bi-lightbulb me-1"></i> 
                  Tekan Enter untuk membuat baris baru. Tulis sejelas mungkin.
                </small>
              </div>
              
              <div class="col-12">
                <label class="form-label fw-semibold">
                  <i class="bi bi-paperclip me-1"></i> Lampiran Foto/File (Opsional)
                </label>
                <div class="border rounded p-3 bg-light">
                  <input type="file" name="foto" class="form-control" accept="image/*,.pdf" id="fileInput">
                  <div class="mt-2">
                    <small class="text-muted d-block">
                      <i class="bi bi-check-circle text-success me-1"></i> Format yang didukung: JPG, PNG, GIF, PDF
                    </small>
                    <small class="text-muted d-block">
                      <i class="bi bi-check-circle text-success me-1"></i> Ukuran maksimal: 5MB
                    </small>
                  </div>
                  <!-- Preview area -->
                  <div id="previewArea" class="mt-3 d-none">
                    <strong class="d-block mb-2 text-muted">Preview:</strong>
                    <img id="imagePreview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-lg px-4" style="background-color: #2c3e50; color: white; border: none;">
              <i class="bi bi-check-lg me-1"></i> Publikasikan Informasi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit dengan Desain Modern -->
  <div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header text-white border-0" style="background-color: #e67e22;">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-pencil-square me-2"></i>Edit Informasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" id="formEdit" enctype="multipart/form-data">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="info_id" id="edit_info_id">
          <div class="modal-body p-4">
            <!-- Warning Helper -->
            <div class="alert border-0 mb-4" style="background-color: #fdebd0; color: #7d6608;">
              <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                <div>
                  <strong>Perhatian:</strong> Perubahan akan langsung terlihat oleh seluruh pengguna.
                </div>
              </div>
            </div>

            <div class="row g-4">
              <div class="col-12">
                <label class="form-label fw-semibold">
                  <i class="bi bi-type me-1"></i> Judul Informasi 
                  <span class="text-danger">*</span>
                </label>
                <input type="text" name="judul" id="edit_judul" class="form-control form-control-lg" 
                       required maxlength="200">
                <small class="text-muted">Maksimal 200 karakter</small>
              </div>
              
              <div class="col-12">
                <label class="form-label fw-semibold">
                  <i class="bi bi-text-paragraph me-1"></i> Isi/Konten Informasi 
                  <span class="text-danger">*</span>
                </label>
                <textarea name="isi" id="edit_isi" class="form-control" rows="8" required></textarea>
                <small class="text-muted">
                  <i class="bi bi-lightbulb me-1"></i> 
                  Tekan Enter untuk membuat baris baru
                </small>
              </div>
              
              <div class="col-12">
                <label class="form-label fw-semibold">
                  <i class="bi bi-paperclip me-1"></i> Lampiran Saat Ini
                </label>
                <div class="border rounded p-3 bg-light mb-3" id="current_foto"></div>
                
                <label class="form-label fw-semibold">
                  <i class="bi bi-upload me-1"></i> Unggah Lampiran Baru (Opsional)
                </label>
                <div class="border rounded p-3 bg-light">
                  <input type="file" name="foto" class="form-control" accept="image/*,.pdf" id="editFileInput">
                  <div class="mt-2">
                    <small class="text-muted d-block">
                      <i class="bi bi-info-circle me-1"></i> Kosongkan jika tidak ingin mengubah lampiran
                    </small>
                    <small class="text-muted d-block">
                      <i class="bi bi-check-circle text-success me-1"></i> Format: JPG, PNG, GIF, PDF (Max 5MB)
                    </small>
                  </div>
                  <!-- Preview area for edit -->
                  <div id="editPreviewArea" class="mt-3 d-none">
                    <strong class="d-block mb-2 text-muted">Preview Lampiran Baru:</strong>
                    <img id="editImagePreview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-lg px-4" style="background-color: #e67e22; color: white; border: none;">
              <i class="bi bi-check-lg me-1"></i> Perbarui Informasi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Detail Informasi (BARU) -->
  <div class="modal fade" id="modalDetail" tabindex="-1">
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
          <div id="detail_header" class="position-relative" style="height: 300px; overflow: hidden; background-color: #ecf0f1;">
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
                <span id="detail_date"></span>
                <span class="mx-2">•</span>
                <i class="bi bi-person-fill me-1"></i>
                <span id="detail_author"></span>
              </div>
            </div>
            
            <!-- Judul -->
            <h3 class="fw-bold mb-3" style="color: #2c3e50;" id="detail_title"></h3>
            
            <!-- Divider -->
            <hr class="my-4">
            
            <!-- Isi Konten -->
            <div class="content-text" style="font-size: 1rem; line-height: 1.8; color: #555;" id="detail_content">
              <!-- Will be filled by JavaScript -->
            </div>
            
            <!-- Lampiran File -->
            <div id="detail_attachment" class="mt-4">
              <!-- Will be filled by JavaScript if there's attachment -->
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> Tutup
          </button>
          <button type="button" class="btn btn-primary" id="detail_edit_btn" onclick="openEditFromDetail()">
            <i class="bi bi-pencil-square me-1"></i> Edit Informasi
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Custom CSS untuk Halaman Informasi -->
  <style>
    /* Hover effect untuk card */
    .hover-lift {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .hover-lift:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Animasi untuk card image overlay */
    .card-img-top {
      position: relative;
      overflow: hidden;
    }
    
    .card-img-top::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.3) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .hover-lift:hover .card-img-top::after {
      opacity: 1;
    }
    
    /* Smooth animation untuk input search */
    .input-group-lg .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
      border-color: #667eea;
    }
    
    /* Card title line clamp */
    .card-title {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      min-height: 3.2rem;
    }
    
    /* Card text line clamp */
    .card-text {
      display: -webkit-box;
      -webkit-line-clamp: 4;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    /* Button hover effects */
    .btn-outline-info:hover {
      transform: scale(1.02);
      transition: all 0.2s ease;
    }
    
    /* Badge animation */
    .badge {
      animation: fadeInDown 0.5s ease;
    }
    
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Statistik card hover */
    .row.g-4 > div > .card {
      transition: all 0.3s ease;
    }
    
    .row.g-4 > div > .card:hover {
      transform: scale(1.03);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2) !important;
    }
    
    /* Empty state animation */
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .text-center .bi-inbox {
      animation: float 3s ease-in-out infinite;
    }
    
    /* Modal header gradient */
    .modal-header.bg-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    /* Textarea auto-expand hint */
    textarea.form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }
    
    /* File input styling */
    input[type="file"].form-control {
      padding: 0.575rem 0.75rem;
    }
    
    /* Image thumbnail in edit modal */
    .img-thumbnail {
      border: 2px solid #e2e8f0;
      border-radius: 8px;
      padding: 0.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .card-title {
        font-size: 1.1rem;
      }
      
      .card-img-top {
        height: 160px !important;
      }
      
      .hover-lift:hover {
        transform: translateY(-4px);
      }
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  <script src="/js/notifications.js"></script>
  <script src="/js/informasi-page.js"></script>
  <?php include __DIR__ . '/../../includes/user_dropdown_script.php'; ?>
  <?php include __DIR__ . '/../../includes/navbar_scripts.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
