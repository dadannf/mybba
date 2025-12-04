<?php
// =============================================
// Halaman: Tambah Siswa
// Deskripsi: Form untuk menambah data siswa baru
// =============================================

// Include koneksi database
require_once __DIR__ . '/../../config.php';

// Check authentication
require_once __DIR__ . '/../../auth_check.php';

// Proses form jika ada submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nis = esc($_POST['nis']);
    $nisn = esc($_POST['nisn'] ?? '');
    $nama = esc($_POST['nama']);
    $kelas = esc($_POST['kelas']);
    $jurusan = esc($_POST['jurusan']);
    $jk = esc($_POST['jk']);
    $tempat_lahir = esc($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = esc($_POST['tanggal_lahir'] ?? '');
    $alamat = esc($_POST['alamat'] ?? '');
    $email = esc($_POST['email'] ?? '');
    $no_hp = esc($_POST['no_hp'] ?? '');
    $nik = esc($_POST['nik'] ?? '');
    $ayah = esc($_POST['ayah'] ?? '');
    $ibu = esc($_POST['ibu'] ?? '');
    
    // Generate password: bba#[4 digit terakhir NIS]
    $last4Digits = substr($nis, -4);
    $plainPassword = 'bba#' . $last4Digits;
    $password = password_hash($plainPassword, PASSWORD_DEFAULT);
    
    // Handle upload foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = __DIR__ . "/../../uploads/siswa/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $new_filename = "siswa_" . $nis . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            // Simpan path relatif ke database
            $foto = "uploads/siswa/" . $new_filename;
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 1. Insert ke tabel users
        $sqlUser = "INSERT INTO users (username, password, role, email) VALUES ('$nis', '$password', 'siswa', '$email')";
        $conn->query($sqlUser);
        $user_id = $conn->insert_id;
        
        // 2. Insert ke tabel siswa
        $sqlSiswa = "INSERT INTO siswa (nis, nisn, user_id, nama, tempat_lahir, tanggal_lahir, jk, kelas, jurusan, nik, ayah, ibu, alamat, email, no_hp, status_siswa, foto) 
                     VALUES ('$nis', '$nisn', $user_id, '$nama', '$tempat_lahir', ".($tanggal_lahir ? "'$tanggal_lahir'" : "NULL").", '$jk', '$kelas', '$jurusan', '$nik', '$ayah', '$ibu', '$alamat', '$email', '$no_hp', 'aktif', ".($foto ? "'$foto'" : "NULL").")";
        $conn->query($sqlSiswa);
        
        // 3. Auto-create data keuangan untuk tahun ajaran saat ini
        $currentYear = date('Y');
        $tahunAjaran = $currentYear . '/' . ($currentYear + 1);
        
        // Hitung total tagihan berdasarkan kelas (12 bulan)
        preg_match('/^(\d+)/', $kelas, $matches);
        $tingkatKelas = isset($matches[1]) ? intval($matches[1]) : 10;
        $tagihanPerBulan = ($tingkatKelas == 10) ? 200000 : 190000;
        $totalTagihan = $tagihanPerBulan * 12; // Total 12 bulan
        
        $sqlKeuangan = "INSERT INTO keuangan (nis, tahun, total_tagihan, total_bayar) 
                        VALUES ('$nis', '$tahunAjaran', $totalTagihan, 0)";
        $conn->query($sqlKeuangan);
        
        // Commit transaction
        $conn->commit();
        
        // Set success message di session
        $_SESSION['success'] = "Data siswa berhasil ditambahkan! NIS: $nis | Nama: $nama | Username: $nis | Password: $plainPassword | Data keuangan tahun $tahunAjaran telah dibuat otomatis.";
        header('Location: /admin/students/index.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Gagal menambahkan data: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Siswa - Sistem Informasi BBA</title>
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
          <h4><i class="bi bi-person-plus-fill me-2"></i> Tambah Siswa Baru</h4>
          <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </a>
        </div>

        <!-- Form Tambah Siswa -->
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
                    <input type="text" name="nis" id="nisInput" class="form-control" required placeholder="Contoh: 2221">
                    <div id="nisAlert" class="alert alert-danger mt-2 d-none" role="alert">
                      <i class="bi bi-exclamation-triangle-fill me-2"></i>
                      <strong>NIS sudah terdaftar!</strong> Silakan gunakan NIS yang berbeda.
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" id="nisnInput" class="form-control" maxlength="10" placeholder="Nomor Induk Siswa Nasional">
                    <div id="nisnAlert" class="alert alert-danger mt-2 d-none" role="alert">
                      <i class="bi bi-exclamation-triangle-fill me-2"></i>
                      <strong>NISN sudah terdaftar!</strong> Silakan gunakan NISN yang berbeda.
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control" placeholder="Nomor Induk Kependudukan">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" required placeholder="Contoh: Bagas Dwi Saputra">
                  </div>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Tempat Lahir</label>
                      <input type="text" name="tempat_lahir" class="form-control" placeholder="Contoh: Jakarta">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Tanggal Lahir</label>
                      <input type="date" name="tanggal_lahir" class="form-control">
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="jk" class="form-select" required>
                      <option value="">Pilih Jenis Kelamin</option>
                      <option value="L">Laki-laki</option>
                      <option value="P">Perempuan</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap siswa"></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@contoh.com">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">No HP Siswa</label>
                    <input type="tel" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Foto Siswa</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
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
                        <option value="">Pilih Kelas</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                      </select>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                      <select name="jurusan" class="form-select" required>
                        <option value="">Pilih Jurusan</option>
                        <option value="Pemasaran">Pemasaran</option>
                        <option value="TKJ">Teknik Komputer dan Jaringan</option>
                        <option value="TSM">Teknik Sepeda Motor</option>
                      </select>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Password Default</label>
                    <input type="text" class="form-control" value="Password = NIS" readonly>
                    <small class="text-muted">Password otomatis sama dengan NIS</small>
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
                    <input type="text" name="ayah" class="form-control" placeholder="Nama lengkap ayah">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Nama Ibu</label>
                    <input type="text" name="ibu" class="form-control" placeholder="Nama lengkap ibu">
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
                <button type="submit" class="btn btn-primary" id="submitBtn">
                  <i class="bi bi-save me-1"></i> Simpan Data Siswa
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
  
  <!-- Script validasi real-time NIS dan NISN -->
  <script>
    let nisExists = false;
    let nisnExists = false;
    let nisTimeout;
    let nisnTimeout;

    // Validasi NIS
    document.getElementById('nisInput').addEventListener('input', function() {
      const nis = this.value.trim();
      const nisAlert = document.getElementById('nisAlert');
      
      // Clear timeout sebelumnya
      clearTimeout(nisTimeout);
      
      if (nis.length === 0) {
        nisAlert.classList.add('d-none');
        nisExists = false;
        updateSubmitButton();
        return;
      }
      
      // Delay 500ms sebelum cek ke server (debounce)
      nisTimeout = setTimeout(() => {
        fetch('/api/check_duplicate.php?field=nis&value=' + encodeURIComponent(nis))
          .then(response => response.json())
          .then(data => {
            if (data.exists) {
              nisAlert.classList.remove('d-none');
              nisExists = true;
              this.classList.add('is-invalid');
            } else {
              nisAlert.classList.add('d-none');
              nisExists = false;
              this.classList.remove('is-invalid');
              this.classList.add('is-valid');
            }
            updateSubmitButton();
          })
          .catch(error => {
            console.error('Error:', error);
          });
      }, 500);
    });

    // Validasi NISN
    document.getElementById('nisnInput').addEventListener('input', function() {
      const nisn = this.value.trim();
      const nisnAlert = document.getElementById('nisnAlert');
      
      // Clear timeout sebelumnya
      clearTimeout(nisnTimeout);
      
      if (nisn.length === 0) {
        nisnAlert.classList.add('d-none');
        nisnExists = false;
        updateSubmitButton();
        this.classList.remove('is-invalid');
        this.classList.remove('is-valid');
        return;
      }
      
      // Delay 500ms sebelum cek ke server (debounce)
      nisnTimeout = setTimeout(() => {
        fetch('/api/check_duplicate.php?field=nisn&value=' + encodeURIComponent(nisn))
          .then(response => response.json())
          .then(data => {
            if (data.exists) {
              nisnAlert.classList.remove('d-none');
              nisnExists = true;
              this.classList.add('is-invalid');
            } else {
              nisnAlert.classList.add('d-none');
              nisnExists = false;
              this.classList.remove('is-invalid');
              this.classList.add('is-valid');
            }
            updateSubmitButton();
          })
          .catch(error => {
            console.error('Error:', error);
          });
      }, 500);
    });

    // Update status tombol submit
    function updateSubmitButton() {
      const submitBtn = document.getElementById('submitBtn');
      if (nisExists || nisnExists) {
        submitBtn.disabled = true;
        submitBtn.classList.add('disabled');
      } else {
        submitBtn.disabled = false;
        submitBtn.classList.remove('disabled');
      }
    }

    // Validasi sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
      if (nisExists || nisnExists) {
        e.preventDefault();
        alert('Tidak dapat menyimpan! NIS atau NISN sudah terdaftar.');
        return false;
      }
    });
  </script>
</body>
</html>
