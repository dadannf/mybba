<?php
// =============================================
// Halaman: Edit Siswa
// Deskripsi: Form untuk mengedit data siswa
// =============================================

require_once __DIR__ . '/../../config.php';

// Check authentication
require_once __DIR__ . '/../../auth_check.php';

// Ambil NIS dari URL
$nis = $_GET['nis'] ?? '';

// Ambil data siswa dari database
$query = "SELECT * FROM siswa WHERE nis = '" . esc($nis) . "'";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    $_SESSION['error'] = 'Data siswa tidak ditemukan!';
    header('Location: /admin/students/index.php');
    exit;
}

$siswa = $result->fetch_assoc();

// Proses form jika ada submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama = esc($_POST['nama']);
        $nisn = esc($_POST['nisn'] ?? '');
        $nik = esc($_POST['nik'] ?? '');
        $tempat_lahir = esc($_POST['tempat_lahir'] ?? '');
        $tanggal_lahir = esc($_POST['tanggal_lahir'] ?? '');
        $jk = esc($_POST['jk']);
        $kelas = esc($_POST['kelas']);
        $jurusan = esc($_POST['jurusan']);
        $alamat = esc($_POST['alamat'] ?? '');
        $email = esc($_POST['email'] ?? '');
        $no_hp = esc($_POST['no_hp'] ?? '');
        $ayah = esc($_POST['ayah'] ?? '');
        $ibu = esc($_POST['ibu'] ?? '');
        $status_siswa = esc($_POST['status_siswa'] ?? 'aktif');
        
        // Handle foto upload jika ada
        $fotoPath = $siswa['foto']; // Gunakan foto lama sebagai default
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/siswa/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'siswa_' . $nis . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetPath)) {
                // Hapus foto lama jika ada
                $oldFotoPath = __DIR__ . '/../../' . $siswa['foto'];
                if ($siswa['foto'] && file_exists($oldFotoPath)) {
                    unlink($oldFotoPath);
                }
                // Simpan path relatif ke database
                $fotoPath = 'uploads/siswa/' . $fileName;
            }
        }
        
        // Update data siswa dengan prepared statement
        $updateQuery = "UPDATE siswa SET 
                        nisn = ?,
                        nik = ?,
                        nama = ?,
                        tempat_lahir = ?,
                        tanggal_lahir = ?,
                        jk = ?,
                        kelas = ?,
                        jurusan = ?,
                        alamat = ?,
                        email = ?,
                        no_hp = ?,
                        ayah = ?,
                        ibu = ?,
                        status_siswa = ?,
                        foto = ?
                        WHERE nis = ?";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param(
            "ssssssssssssssss",
            $nisn, $nik, $nama, $tempat_lahir, $tanggal_lahir, $jk, $kelas, $jurusan,
            $alamat, $email, $no_hp, $ayah, $ibu, $status_siswa, $fotoPath, $nis
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            // Buat notifikasi jika update dilakukan oleh siswa
            if (isset($userRole) && $userRole === 'siswa') {
                notifyDataSiswaUpdate($conn, $nis, $nama);
            }
            
            $_SESSION['success'] = 'Data siswa berhasil diupdate!';
            header('Location: /admin/students/index.php');
            exit;
        } else {
            $stmt->close();
            throw new Exception("Gagal mengupdate data: " . $conn->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Siswa - Sistem Informasi BBA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/css/responsive.css">
  <link rel="stylesheet" href="/css/notifications.css">
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
          <h1 class="app-title mb-0">Sistem Informasi BBA</h1>
        </div>
        <?php include __DIR__ . '/../../includes/user_dropdown.php'; ?>
      </div>
    </header>

    <main class="content">
      <div class="container-fluid">
        
        <!-- Notifikasi Error -->
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
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4><i class="bi bi-pencil-square me-2"></i> Edit Data Siswa</h4>
          <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </a>
        </div>

        <!-- Form Edit Siswa -->
        <form method="POST" action="" enctype="multipart/form-data">
          <div class="row">
            <!-- Data Pribadi -->
            <div class="col-lg-6">
              <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                  <h5 class="mb-0"><i class="bi bi-person-fill me-2"></i> Data Pribadi</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label">NIS <span class="text-danger">*</span></label>
                    <input type="text" name="nis" class="form-control" value="<?php echo htmlspecialchars($siswa['nis']); ?>" required readonly>
                    <small class="text-muted">NIS tidak dapat diubah</small>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control" value="<?php echo htmlspecialchars($siswa['nisn'] ?? ''); ?>" maxlength="10">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control" value="<?php echo htmlspecialchars($siswa['nik'] ?? ''); ?>" maxlength="16">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($siswa['nama']); ?>" required>
                  </div>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Tempat Lahir</label>
                      <input type="text" name="tempat_lahir" class="form-control" value="<?php echo htmlspecialchars($siswa['tempat_lahir'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Tanggal Lahir</label>
                      <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo htmlspecialchars($siswa['tanggal_lahir'] ?? ''); ?>">
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="jk" class="form-select" required>
                      <option value="L" <?php echo $siswa['jk'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                      <option value="P" <?php echo $siswa['jk'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3"><?php echo htmlspecialchars($siswa['alamat'] ?? ''); ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($siswa['email'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">No HP</label>
                    <input type="tel" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($siswa['no_hp'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Foto Siswa Saat Ini</label>
                    <?php if($siswa['foto']): ?>
                      <div class="mb-2">
                        <img src="<?php echo htmlspecialchars($siswa['foto']); ?>" alt="Foto Siswa" class="img-thumbnail" style="max-width: 200px;">
                      </div>
                    <?php else: ?>
                      <p class="text-muted">Belum ada foto</p>
                    <?php endif; ?>
                    <label class="form-label">Ubah Foto Siswa</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <small class="text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Data Sekolah & Orang Tua -->
            <div class="col-lg-6">
              <!-- Data Sekolah -->
              <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                  <h5 class="mb-0"><i class="bi bi-book-fill me-2"></i> Data Sekolah</h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Kelas <span class="text-danger">*</span></label>
                      <select name="kelas" class="form-select" required>
                        <option value="10" <?php echo $siswa['kelas'] == '10' ? 'selected' : ''; ?>>10</option>
                        <option value="11" <?php echo $siswa['kelas'] == '11' ? 'selected' : ''; ?>>11</option>
                        <option value="12" <?php echo $siswa['kelas'] == '12' ? 'selected' : ''; ?>>12</option>
                      </select>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                      <select name="jurusan" class="form-select" required>
                        <option value="Pemasaran" <?php echo $siswa['jurusan'] == 'Pemasaran' ? 'selected' : ''; ?>>Pemasaran</option>
                        <option value="TKJ" <?php echo $siswa['jurusan'] == 'Teknik Komputer dan Jaringan' ? 'selected' : ''; ?>>Teknik Komputer dan Jaringan</option>
                        <option value="TSM" <?php echo $siswa['jurusan'] == 'Teknik Sepeda Motor' ? 'selected' : ''; ?>>Teknik Sepeda Motor</option>
                      </select>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Status Siswa <span class="text-danger">*</span></label>
                    <select name="status_siswa" class="form-select" required>
                      <option value="aktif" <?php echo $siswa['status_siswa'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                      <option value="lulus" <?php echo $siswa['status_siswa'] == 'lulus' ? 'selected' : ''; ?>>Lulus</option>
                      <option value="keluar" <?php echo $siswa['status_siswa'] == 'keluar' ? 'selected' : ''; ?>>Keluar</option>
                      <option value="pindah" <?php echo $siswa['status_siswa'] == 'pindah' ? 'selected' : ''; ?>>Pindah</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Data Orang Tua -->
              <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                  <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i> Data Orang Tua</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label">Nama Ayah</label>
                    <input type="text" name="ayah" class="form-control" value="<?php echo htmlspecialchars($siswa['ayah'] ?? ''); ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Nama Ibu</label>
                    <input type="text" name="ibu" class="form-control" value="<?php echo htmlspecialchars($siswa['ibu'] ?? ''); ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tombol Submit -->
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-end gap-2">
                <a href="index.php" class="btn btn-secondary">
                  <i class="bi bi-x-circle me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-warning">
                  <i class="bi bi-save me-1"></i> Update Data Siswa
                </button>
              </div>
            </div>
          </div>
        </form>

      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/dashboard.js"></script>
  <script src="/js/notifications.js"></script>
  <?php include __DIR__ . '/../../includes/user_dropdown_script.php'; ?>
  <?php include __DIR__ . '/../../includes/navbar_scripts.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
