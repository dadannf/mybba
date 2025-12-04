<?php
// =============================================
// File: get_siswa_detail.php
// Deskripsi: Mengambil detail siswa untuk ditampilkan di modal
// =============================================

require_once __DIR__ . '/../../config.php';

$nis = esc($_GET['nis'] ?? '');

// Query data siswa
$sql = "SELECT * FROM siswa WHERE nis = '$nis'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i> Data siswa tidak ditemukan!</div>';
    exit;
}

$siswa = $result->fetch_assoc();
?>

<!-- Foto dan Info Singkat -->
<div class="row">
  <div class="col-md-4 text-center mb-4">
    <div class="mb-3">
      <?php if($siswa['foto']): ?>
        <?php 
          // Pastikan path foto benar (tambahkan / jika belum ada)
          $fotoPath = $siswa['foto'];
          if (strpos($fotoPath, '/') !== 0 && strpos($fotoPath, 'http') !== 0) {
              $fotoPath = '/' . $fotoPath;
          }
        ?>
        <img src="<?php echo htmlspecialchars($fotoPath); ?>" 
             alt="Foto Siswa" 
             class="rounded-3 border border-3 border-primary shadow-sm" 
             style="width: 200px; height: 200px; object-fit: cover;"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
        <div class="bg-gradient rounded-3 d-none align-items-center justify-content-center border border-3 border-primary shadow-sm" style="width: 200px; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <i class="bi bi-person-fill text-white" style="font-size: 6rem;"></i>
        </div>
      <?php else: ?>
      <div class="bg-gradient rounded-3 d-inline-flex align-items-center justify-content-center border border-3 border-primary shadow-sm" style="width: 200px; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <i class="bi bi-person-fill text-white" style="font-size: 6rem;"></i>
      </div>
      <?php endif; ?>
    </div>
    
    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($siswa['nama']); ?></h5>
    <p class="text-muted small mb-1"><i class="bi bi-credit-card me-1"></i>NIS: <?php echo htmlspecialchars($siswa['nis']); ?></p>
    <p class="text-muted small mb-3"><i class="bi bi-book me-1"></i>Kelas <?php echo htmlspecialchars($siswa['kelas']); ?> - <?php echo htmlspecialchars($siswa['jurusan']); ?></p>
    
    <div class="d-grid gap-2">
      <span class="badge bg-<?php echo $siswa['status_siswa'] == 'aktif' ? 'success' : ($siswa['status_siswa'] == 'lulus' ? 'primary' : 'secondary'); ?> py-2 fs-6">
        <i class="bi bi-<?php echo $siswa['status_siswa'] == 'aktif' ? 'check-circle' : ($siswa['status_siswa'] == 'lulus' ? 'mortarboard' : 'x-circle'); ?> me-1"></i>
        <?php echo ucfirst($siswa['status_siswa']); ?>
      </span>
    </div>
    
    <!-- Info Kontak -->
    <?php if($siswa['email'] || $siswa['no_hp']): ?>
    <div class="card bg-light border-0 shadow-sm mt-3">
      <div class="card-body p-3">
        <h6 class="fw-bold mb-3 text-dark">
          <i class="bi bi-telephone-fill me-2 text-primary"></i> Informasi Kontak
        </h6>
        <?php if($siswa['email']): ?>
        <div class="d-flex align-items-center mb-2 p-2 bg-white rounded">
          <div class="flex-shrink-0">
            <i class="bi bi-envelope-fill text-primary fs-5"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <small class="text-muted d-block mb-0">Email</small>
            <span class="fw-semibold small text-break"><?php echo htmlspecialchars($siswa['email']); ?></span>
          </div>
        </div>
        <?php endif; ?>
        <?php if($siswa['no_hp']): ?>
        <div class="d-flex align-items-center p-2 bg-white rounded">
          <div class="flex-shrink-0">
            <i class="bi bi-phone-fill text-success fs-5"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <small class="text-muted d-block mb-0">No. HP</small>
            <span class="fw-semibold small"><?php echo htmlspecialchars($siswa['no_hp']); ?></span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  
  <div class="col-md-8">
    <!-- Data Pribadi -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-primary text-white py-2">
        <h6 class="mb-0"><i class="bi bi-person-fill me-2"></i> Data Pribadi</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1">NIS</small>
              <span class="fw-semibold"><?php echo htmlspecialchars($siswa['nis']); ?></span>
            </div>
          </div>
          <div class="col-6">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1">NISN</small>
              <span class="fw-semibold"><?php echo $siswa['nisn'] ? htmlspecialchars($siswa['nisn']) : '-'; ?></span>
            </div>
          </div>
          <div class="col-12">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1">NIK</small>
              <span class="fw-semibold"><?php echo $siswa['nik'] ? htmlspecialchars($siswa['nik']) : '-'; ?></span>
            </div>
          </div>
          <div class="col-12">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1">Nama Lengkap</small>
              <span class="fw-semibold"><?php echo htmlspecialchars($siswa['nama']); ?></span>
            </div>
          </div>
          <div class="col-6">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1">Jenis Kelamin</small>
              <span class="fw-semibold">
                <i class="bi bi-gender-<?php echo $siswa['jk'] == 'L' ? 'male text-primary' : 'female text-danger'; ?> me-1"></i>
                <?php echo $siswa['jk'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
              </span>
            </div>
          </div>
          <div class="col-6">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1">Tempat Lahir</small>
              <span class="fw-semibold"><?php echo $siswa['tempat_lahir'] ? htmlspecialchars($siswa['tempat_lahir']) : '-'; ?></span>
            </div>
          </div>
          <div class="col-12">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1">Tanggal Lahir</small>
              <span class="fw-semibold">
                <i class="bi bi-calendar3 text-primary me-1"></i>
                <?php echo $siswa['tanggal_lahir'] ? formatTanggalIndo($siswa['tanggal_lahir']) : '-'; ?>
              </span>
            </div>
          </div>
          <div class="col-12">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1">Alamat</small>
              <span class="fw-semibold"><?php echo $siswa['alamat'] ? htmlspecialchars($siswa['alamat']) : '-'; ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Data Sekolah -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-success text-white py-2">
        <h6 class="mb-0"><i class="bi bi-book-fill me-2"></i> Data Sekolah</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-4">
            <div class="p-2 bg-light rounded text-center">
              <i class="bi bi-building fs-2 text-success mb-2 d-block"></i>
              <small class="text-muted d-block mb-1">Kelas</small>
              <h5 class="mb-0 fw-bold text-success"><?php echo htmlspecialchars($siswa['kelas']); ?></h5>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 bg-light rounded text-center">
              <i class="bi bi-mortarboard fs-2 text-success mb-2 d-block"></i>
              <small class="text-muted d-block mb-1">Jurusan</small>
              <h5 class="mb-0 fw-bold text-success"><?php echo htmlspecialchars($siswa['jurusan']); ?></h5>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 bg-light rounded text-center">
              <i class="bi bi-check-circle fs-2 text-<?php echo $siswa['status_siswa'] == 'aktif' ? 'success' : 'secondary'; ?> mb-2 d-block"></i>
              <small class="text-muted d-block mb-1">Status</small>
              <h6 class="mb-0 fw-bold text-<?php echo $siswa['status_siswa'] == 'aktif' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($siswa['status_siswa']); ?></h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Data Orang Tua -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-dark py-2">
        <h6 class="mb-0"><i class="bi bi-people-fill me-2"></i> Data Orang Tua / Wali</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1"><i class="bi bi-person me-1"></i>Nama Ayah</small>
              <span class="fw-semibold"><?php echo $siswa['ayah'] ? htmlspecialchars($siswa['ayah']) : '-'; ?></span>
            </div>
          </div>
          <div class="col-6">
            <div class="p-2 bg-light rounded">
              <small class="text-muted d-block mb-1"><i class="bi bi-person me-1"></i>Nama Ibu</small>
              <span class="fw-semibold"><?php echo $siswa['ibu'] ? htmlspecialchars($siswa['ibu']) : '-'; ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $conn->close(); ?>
