<?php
// =============================================
// File: get_keuangan_detail.php
// Deskripsi: Mengambil detail keuangan untuk ditampilkan di modal
// =============================================

require_once __DIR__ . '/../../config.php';

$keuangan_id = esc($_GET['id'] ?? '');

// Query data keuangan dengan join siswa
$sql = "SELECT k.*, s.nama, s.kelas, s.jurusan, s.email, s.no_hp, s.nis
        FROM keuangan k
        INNER JOIN siswa s ON k.nis = s.nis
        WHERE k.keuangan_id = '$keuangan_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i> Data keuangan tidak ditemukan!</div>';
    exit;
}

$keu = $result->fetch_assoc();

// Hitung progress
$progress = $keu['total_tagihan'] > 0 ? ($keu['total_bayar'] / $keu['total_tagihan']) * 100 : 0;
$progressClass = $progress >= 100 ? 'success' : ($progress >= 50 ? 'warning' : 'danger');
$tunggakan = $keu['total_tagihan'] - $keu['total_bayar'];

// Query untuk history pembayaran
$sqlPembayaran = "SELECT * FROM pembayaran WHERE keuangan_id = '$keuangan_id' ORDER BY tanggal_bayar DESC LIMIT 10";
$resultPembayaran = $conn->query($sqlPembayaran);
?>

<div class="row g-3">
  <!-- KOLOM 1: INFO SISWA -->
  <div class="col-md-6">
    <!-- KARTU INFO SISWA -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3 text-primary">
          <i class="bi bi-person-circle me-2"></i> Informasi Siswa
        </h6>
        <div class="d-flex align-items-start gap-3">
          <div class="flex-shrink-0">
            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
              <i class="bi bi-person-fill" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <div class="flex-grow-1">
            <p class="mb-1"><small class="text-muted">Nama Siswa</small><br><strong><?php echo $keu['nama']; ?></strong></p>
            <p class="mb-0"><small class="text-muted">NIS</small><br><strong><?php echo $keu['nis']; ?></strong></p>
          </div>
        </div>
        <hr class="my-3">
        <p class="mb-1"><small class="text-muted">Kelas</small><br><span class="badge bg-info"><?php echo $keu['kelas']; ?></span></p>
        <p class="mb-1"><small class="text-muted">Jurusan</small><br><span class="badge bg-secondary"><?php echo $keu['jurusan']; ?></span></p>
        <p class="mb-0"><small class="text-muted">Tahun Ajaran</small><br><span class="badge bg-light text-dark"><?php echo $keu['tahun']; ?></span></p>
      </div>
    </div>

    <!-- KARTU KONTAK -->
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold mb-3 text-primary">
          <i class="bi bi-telephone me-2"></i> Informasi Kontak
        </h6>
        <?php if (!empty($keu['email'])): ?>
        <div class="d-flex align-items-center gap-3 mb-2">
          <i class="bi bi-envelope-fill text-primary fs-5"></i>
          <div>
            <small class="text-muted d-block">Email</small>
            <small><?php echo $keu['email']; ?></small>
          </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($keu['no_hp'])): ?>
        <div class="d-flex align-items-center gap-3">
          <i class="bi bi-telephone-fill text-success fs-5"></i>
          <div>
            <small class="text-muted d-block">No HP</small>
            <small><?php echo $keu['no_hp']; ?></small>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- KOLOM 2: RINGKASAN KEUANGAN & PEMBAYARAN -->
  <div class="col-md-6">
    <!-- KARTU RINGKASAN KEUANGAN -->
    <div class="card border-0 shadow-sm mb-3 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Ringkasan Keuangan</h6>
        
        <div class="mb-3">
          <small class="opacity-75 d-block mb-1">Total Tagihan</small>
          <h5 class="mb-0">Rp <?php echo number_format($keu['total_tagihan'], 0, ',', '.'); ?></h5>
        </div>

        <div class="mb-3">
          <small class="opacity-75 d-block mb-1">Total Terbayar</small>
          <h5 class="mb-0">Rp <?php echo number_format($keu['total_bayar'], 0, ',', '.'); ?></h5>
        </div>

        <div class="mb-3">
          <small class="opacity-75 d-block mb-1">Tunggakan</small>
          <h5 class="mb-0">Rp <?php echo number_format($tunggakan, 0, ',', '.'); ?></h5>
        </div>

        <!-- PROGRESS BAR -->
        <small class="opacity-75 d-block mb-2">Progress Pembayaran</small>
        <div class="progress" style="height: 30px; background-color: rgba(255,255,255,0.2);">
          <div class="progress-bar bg-white text-dark fw-bold" role="progressbar" 
               style="width: <?php echo min($progress, 100); ?>%;" 
               aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
            <?php echo number_format($progress, 0); ?>%
          </div>
        </div>
      </div>
    </div>

    <!-- HISTORY PEMBAYARAN -->
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold mb-3 text-primary">
          <i class="bi bi-clock-history me-2"></i> History Pembayaran (10 Terakhir)
        </h6>
        
        <?php if ($resultPembayaran->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th class="text-end">Nominal</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($pembayaran = $resultPembayaran->fetch_assoc()): ?>
              <tr>
                <td><small><?php echo date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])); ?></small></td>
                <td class="text-end"><small>Rp <?php echo number_format($pembayaran['nominal_bayar'], 0, ',', '.'); ?></small></td>
                <td>
                  <span class="badge bg-<?php echo $pembayaran['status'] == 'valid' ? 'success' : 'warning'; ?> text-white">
                    <small><?php echo ucfirst($pembayaran['status']); ?></small>
                  </span>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="text-center py-3 text-muted">
          <i class="bi bi-inbox fs-3 d-block mb-2"></i>
          <small>Belum ada riwayat pembayaran</small>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
