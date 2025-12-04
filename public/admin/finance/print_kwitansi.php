<?php
session_start();
require_once '../../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../../login.php');
  exit;
}

// Get pembayaran_id from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
  die('ID Pembayaran tidak ditemukan');
}

$pembayaran_id = intval($_GET['id']);

try {
  // Use global $conn from config.php
  global $conn;
  
  // Get payment details with student info
  $stmt = $conn->prepare("
    SELECT 
      p.pembayaran_id,
      p.tanggal_bayar,
      p.nominal_bayar,
      p.metode,
      p.tempat_bayar,
      p.catatan,
      p.diterima_oleh,
      s.nama as nama_siswa,
      s.nis,
      s.kelas,
      k.tahun as tahun_ajaran
    FROM pembayaran p
    JOIN keuangan k ON p.keuangan_id = k.keuangan_id
    JOIN siswa s ON k.nis = s.nis
    WHERE p.pembayaran_id = ?
  ");
  
  $stmt->bind_param("i", $pembayaran_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $payment = $result->fetch_assoc();
  
  if (!$payment) {
    die('Data pembayaran tidak ditemukan');
  }
  
  // Get admin name who processed the payment
  $penerima = !empty($payment['diterima_oleh']) ? $payment['diterima_oleh'] : '-';
  
  // Extract non-SPP payment from catatan if exists
  $nominalSPP = $payment['nominal_bayar'];
  $nominalNonSPP = 0;
  $totalDibayar = $nominalSPP;
  
  if (!empty($payment['catatan']) && preg_match('/Pembayaran Non-SPP: Rp ([\d\.]+)/i', $payment['catatan'], $matches)) {
    $nominalNonSPPStr = str_replace('.', '', $matches[1]);
    $nominalNonSPP = intval($nominalNonSPPStr);
    $totalDibayar = $nominalSPP + $nominalNonSPP;
  }
  
  // Format date
  $tanggal_bayar = date('d F Y', strtotime($payment['tanggal_bayar']));
  $waktu_cetak = date('d F Y H:i:s');
  
} catch (Exception $e) {
  die('Database Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bukti Pembayaran - <?php echo htmlspecialchars($payment['nama_siswa']); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    @media print {
      .no-print {
        display: none !important;
      }
      body {
        margin: 0;
        padding: 20px;
      }
      .container {
        max-width: 100% !important;
      }
    }
    
    .kwitansi-container {
      max-width: 800px;
      margin: 40px auto;
      border: 2px solid #333;
      padding: 30px;
      background: white;
    }
    
    .header-kwitansi {
      text-align: center;
      border-bottom: 3px double #333;
      padding-bottom: 20px;
      margin-bottom: 30px;
    }
    
    .header-kwitansi h2 {
      margin: 0;
      font-weight: bold;
      color: #2c3e50;
    }
    
    .header-kwitansi p {
      margin: 5px 0;
      color: #555;
    }
    
    .info-row {
      display: flex;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }
    
    .info-row:last-child {
      border-bottom: none;
    }
    
    .info-label {
      width: 200px;
      font-weight: 600;
      color: #333;
    }
    
    .info-value {
      flex: 1;
      color: #555;
    }
    
    .nominal-box {
      background: #f8f9fa;
      border: 2px solid #dee2e6;
      padding: 15px;
      margin: 20px 0;
      text-align: center;
      border-radius: 5px;
    }
    
    .nominal-box .label {
      font-size: 14px;
      color: #666;
      margin-bottom: 5px;
    }
    
    .nominal-box .amount {
      font-size: 28px;
      font-weight: bold;
      color: #28a745;
    }
    
    .footer-kwitansi {
      margin-top: 40px;
      display: flex;
      justify-content: space-between;
    }
    
    .signature-box {
      text-align: center;
      width: 200px;
    }
    
    .signature-line {
      border-top: 1px solid #333;
      margin-top: 60px;
      padding-top: 5px;
    }
    
    .print-info {
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px dashed #ccc;
      text-align: center;
      color: #999;
      font-size: 12px;
    }
  </style>
</head>
<body>
  <div class="no-print text-center mb-3">
    <button onclick="window.print()" class="btn btn-primary me-2">
      <i class="bi bi-printer me-1"></i> Cetak Kwitansi
    </button>
    <button onclick="window.close()" class="btn btn-secondary">
      <i class="bi bi-x-lg me-1"></i> Tutup
    </button>
  </div>

  <div class="kwitansi-container">
    <div class="header-kwitansi">
      <h2>BUKTI PEMBAYARAN</h2>
      <p><strong>SMK BIT BINA AULIA</strong></p>
      <p>JL. LETDA NATSIR No. 582, Bojong Kulur, Gunungputri, Kabupaten Bogor</p>
      <p>Telp: 021-82415429 | Email: smkbitbinaaulia@ymail.com</p>
    </div>

    <div class="info-section">
      <div class="info-row">
        <div class="info-label">No. Pembayaran:</div>
        <div class="info-value"><strong><?php echo str_pad($payment['pembayaran_id'], 6, '0', STR_PAD_LEFT); ?></strong></div>
      </div>
      
      <div class="info-row">
        <div class="info-label">Tanggal Pembayaran:</div>
        <div class="info-value"><?php echo $tanggal_bayar; ?></div>
      </div>
      
      <div class="info-row">
        <div class="info-label">Nama Siswa:</div>
        <div class="info-value"><strong><?php echo htmlspecialchars($payment['nama_siswa']); ?></strong></div>
      </div>
      
      <div class="info-row">
        <div class="info-label">NIS:</div>
        <div class="info-value"><?php echo htmlspecialchars($payment['nis']); ?></div>
      </div>
      
      <div class="info-row">
        <div class="info-label">Kelas:</div>
        <div class="info-value"><?php echo htmlspecialchars($payment['kelas']); ?></div>
      </div>
      
      <div class="info-row">
        <div class="info-label">Tahun Ajaran:</div>
        <div class="info-value"><?php echo htmlspecialchars($payment['tahun_ajaran']); ?></div>
      </div>
    </div>

    <div class="nominal-box">
      <div class="label">RINCIAN PEMBAYARAN</div>
      <?php if ($nominalNonSPP > 0): ?>
        <table style="width: 100%; margin: 10px 0;">
          <tr>
            <td style="text-align: left; padding: 5px;">Pembayaran SPP:</td>
            <td style="text-align: right; padding: 5px; font-weight: bold;">Rp <?php echo number_format($nominalSPP, 0, ',', '.'); ?></td>
          </tr>
          <tr>
            <td style="text-align: left; padding: 5px;">Pembayaran Non-SPP:</td>
            <td style="text-align: right; padding: 5px; font-weight: bold;">Rp <?php echo number_format($nominalNonSPP, 0, ',', '.'); ?></td>
          </tr>
          <tr style="border-top: 2px solid #333;">
            <td style="text-align: left; padding: 10px 5px 5px 5px; font-weight: bold;">TOTAL DIBAYAR:</td>
            <td style="text-align: right; padding: 10px 5px 5px 5px;">
              <div class="amount" style="font-size: 24px;">Rp <?php echo number_format($totalDibayar, 0, ',', '.'); ?></div>
            </td>
          </tr>
        </table>
      <?php else: ?>
        <div class="amount">Rp <?php echo number_format($nominalSPP, 0, ',', '.'); ?></div>
      <?php endif; ?>
    </div>

    <div class="info-section">
      <div class="info-row">
        <div class="info-label">Metode Pembayaran:</div>
        <div class="info-value">
          <span class="badge bg-success"><?php echo strtoupper($payment['metode']); ?></span>
        </div>
      </div>
      
      <?php if (!empty($payment['catatan'])): ?>
      <div class="info-row">
        <div class="info-label">Catatan:</div>
        <div class="info-value"><?php echo nl2br(htmlspecialchars($payment['catatan'])); ?></div>
      </div>
      <?php endif; ?>
      
      <div class="info-row">
        <div class="info-label">Diterima Oleh:</div>
        <div class="info-value"><?php echo htmlspecialchars($penerima); ?></div>
      </div>
    </div>

    <div class="footer-kwitansi">
      <div class="signature-box">
        <div>Siswa/Wali,</div>
        <div class="signature-line">
          <?php echo htmlspecialchars($payment['nama_siswa']); ?>
        </div>
      </div>
      
      <div class="signature-box">
        <div><?php echo date('d F Y'); ?></div>
        <div>Petugas,</div>
        <div class="signature-line">
          <?php echo htmlspecialchars($penerima); ?>
        </div>
      </div>
    </div>

    <div class="print-info">
      Dicetak pada: <?php echo $waktu_cetak; ?><br>
      Dokumen ini sah sebagai bukti pembayaran yang telah diverifikasi oleh sistem.
    </div>
  </div>

  <script>
    // Auto print on load (optional)
    // window.onload = function() { window.print(); }
  </script>
</body>
</html>
