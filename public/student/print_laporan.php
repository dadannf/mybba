<?php
// =============================================
// Halaman: Print Laporan Keuangan Siswa
// Deskripsi: Cetak laporan keuangan formal dalam format landscape
// =============================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';

// Pastikan yang login adalah siswa
if ($userRole !== 'siswa') {
    die('Akses ditolak!');
}

$username = $_SESSION['username'];

// Query data siswa
$sqlSiswa = "SELECT * FROM siswa WHERE nis = '$username' LIMIT 1";
$resultSiswa = $conn->query($sqlSiswa);

if ($resultSiswa->num_rows === 0) {
    die('Data siswa tidak ditemukan!');
}

$siswa = $resultSiswa->fetch_assoc();

// Function untuk get tagihan per bulan
function getTagihanPerBulan($kelas) {
    $tingkatKelas = (int)$kelas;
    return ($tingkatKelas === 10) ? 200000 : 190000;
}

$tagihanPerBulan = getTagihanPerBulan($siswa['kelas']);

// Mapping bulan akademik (Juli-Juni)
$bulanAkademik = [
    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
];

// Query semua data keuangan siswa
$sqlKeuangan = "SELECT * FROM keuangan WHERE nis = '$username' ORDER BY tahun DESC";
$resultKeuangan = $conn->query($sqlKeuangan);

$dataPembayaran = [];
$totalTagihan = 0;
$totalDibayar = 0;

while ($keuangan = $resultKeuangan->fetch_assoc()) {
    $keuangan_id = $keuangan['keuangan_id'];
    $tahun = $keuangan['tahun'];
    
    // Query semua pembayaran untuk tahun ini
    $sqlPembayaran = "SELECT * FROM pembayaran 
                      WHERE keuangan_id = '$keuangan_id' 
                      ORDER BY pembayaran_id ASC";
    $resultPembayaran = $conn->query($sqlPembayaran);
    
    $pembayaranList = [];
    while ($bayar = $resultPembayaran->fetch_assoc()) {
        $pembayaranList[] = $bayar;
    }
    
    // Generate 12 bulan penuh
    for ($bulan = 1; $bulan <= 12; $bulan++) {
        $nominalTagihan = $tagihanPerBulan;
        $totalTagihan += $nominalTagihan;
        
        // Cari pembayaran untuk bulan ini (berdasarkan urutan)
        $indexPembayaran = $bulan - 1;
        $pembayaran = isset($pembayaranList[$indexPembayaran]) ? $pembayaranList[$indexPembayaran] : null;
        
        if ($pembayaran) {
            // Ada pembayaran (baik SPP maupun dengan catatan)
            $totalDibayar += $pembayaran['nominal_bayar'];
            
            // Cek apakah lunas berdasarkan status = 'valid' DAN nominal >= tagihan
            $isLunas = ($pembayaran['status'] === 'valid' && $pembayaran['nominal_bayar'] >= $nominalTagihan);
            
            $dataPembayaran[] = [
                'tahun_ajaran' => $tahun,
                'bulan' => $bulanAkademik[$bulan],
                'bulan_index' => $bulan,
                'nominal_tagihan' => $nominalTagihan,
                'nominal_bayar' => $pembayaran['nominal_bayar'],
                'tanggal_bayar' => date('d/m/Y', strtotime($pembayaran['tanggal_bayar'])),
                'metode' => $pembayaran['metode'],
                'tempat_bayar' => $pembayaran['tempat_bayar'],
                'status' => $pembayaran['status'],
                'diterima_oleh' => $pembayaran['diterima_oleh'] ?? '-',
                'catatan' => $pembayaran['catatan'] ?? '',
                'is_lunas' => $isLunas
            ];
        } else {
            // Belum bayar
            $dataPembayaran[] = [
                'tahun_ajaran' => $tahun,
                'bulan' => $bulanAkademik[$bulan],
                'bulan_index' => $bulan,
                'nominal_tagihan' => $nominalTagihan,
                'nominal_bayar' => 0,
                'tanggal_bayar' => null,
                'metode' => null,
                'tempat_bayar' => null,
                'status' => null,
                'diterima_oleh' => '-',
                'catatan' => '',
                'is_lunas' => false
            ];
        }
    }
}

$totalTunggakan = $totalTagihan - $totalDibayar;
$persenLunas = $totalTagihan > 0 ? ($totalDibayar / $totalTagihan) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - <?php echo htmlspecialchars($siswa['nama']); ?></title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 8mm;
            }
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8pt;
            line-height: 1.2;
            color: #000;
            background: #fff;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 8px;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        
        .school-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 1px;
            text-transform: uppercase;
        }
        
        .school-address {
            font-size: 6pt;
            margin-bottom: 1px;
        }
        
        .report-title {
            font-size: 9pt;
            font-weight: bold;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        /* Student Info */
        .student-info {
            margin: 15px 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 30px;
            background: #f8f9fa;
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        
        .info-row {
            display: flex;
            font-size: 10pt;
        }
        
        .info-label {
            width: 120px;
            font-weight: normal;
        }
        
        .info-colon {
            width: 15px;
        }
        
        .info-value {
            flex: 1;
            font-weight: bold;
        }
        
        /* Summary */
        .summary-box {
            background: #e3f2fd;
            border: 2px solid #1976d2;
            padding: 12px;
            margin: 15px 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }
        
        .summary-item {
            padding: 8px;
        }
        
        .summary-label {
            font-size: 9pt;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 12pt;
            font-weight: bold;
        }
        
        .summary-value.total { color: #1976d2; }
        .summary-value.paid { color: #2e7d32; }
        .summary-value.unpaid { color: #c62828; }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0;
            font-size: 6pt;
        }
        
        table, th, td {
            border: 1px solid #333;
        }
        
        th {
            background-color: #1976d2;
            color: #fff;
            padding: 4px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 7pt;
            vertical-align: middle;
        }
        
        td {
            padding: 3px 2px;
            vertical-align: middle;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .status-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 1px;
            font-size: 5pt;
            font-weight: bold;
        }
        
        .status-lunas {
            background-color: #c8e6c9;
            color: #1b5e20;
        }
        
        .status-belum {
            background-color: #ffcdd2;
            color: #b71c1c;
        }
        
        .badge-tunai {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 1px;
            font-size: 5pt;
            font-weight: bold;
            background-color: #00bcd4;
            color: #fff;
        }
        
        .year-group {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 6pt;
        }
        
        /* Footer */
        .footer {
            margin-top: 4px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        
        .signature-box {
            text-align: center;
            width: 140px;
            font-size: 6pt;
        }
        
        .signature-line {
            margin-top: 18px;
            border-top: 1px solid #000;
            padding-top: 2px;
            font-size: 6pt;
        }
        
        .print-info {
            font-size: 6pt;
            color: #666;
            text-align: center;
            margin-top: 8px;
            border-top: 1px dashed #999;
            padding-top: 4px;
        }
        
        /* Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        .print-button:hover {
            background-color: #1d4ed8;
        }
        
        .year-group {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ CETAK LAPORAN
    </button>
    
    <div class="container">
        <!-- Header Sekolah -->
        <div class="header">
            <div class="school-name">SMK BIT BINA AULIA</div>
            <div class="school-address">Jl. Pendidikan No. 123, Jakarta</div>
            <div class="school-address">Telp: (021) 1234567 | Email: info@smkbitbinaaulia.sch.id</div>
            <div class="report-title">Laporan Keuangan Siswa</div>
        </div>
        
        <!-- Data Siswa -->
        <div class="student-info">
            <div class="info-row">
                <span class="info-label">NIS</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?php echo htmlspecialchars($siswa['nis']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Kelas</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?php echo htmlspecialchars($siswa['kelas']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Nama Lengkap</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?php echo htmlspecialchars($siswa['nama']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Jurusan</span>
                <span class="info-colon">:</span>
                <span class="info-value"><?php echo htmlspecialchars($siswa['jurusan']); ?></span>
            </div>
        </div>
        
        <!-- Ringkasan -->
        <div class="summary-box">
            <div class="summary-item">
                <div class="summary-label">Total Tagihan</div>
                <div class="summary-value total">Rp <?php echo number_format($totalTagihan, 0, ',', '.'); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Dibayar</div>
                <div class="summary-value paid">Rp <?php echo number_format($totalDibayar, 0, ',', '.'); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Tunggakan</div>
                <div class="summary-value unpaid">Rp <?php echo number_format($totalTunggakan, 0, ',', '.'); ?></div>
            </div>
        </div>
        
        <!-- Tabel Detail Pembayaran -->
        <table>
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th width="100">Bulan</th>
                    <th width="100">Tagihan</th>
                    <th width="80">Metode</th>
                    <th width="120">Tempat Bayar</th>
                    <th width="80">Status</th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (count($dataPembayaran) > 0):
                    $no = 1;
                    $currentYear = '';
                    foreach ($dataPembayaran as $data):
                        // Group by year
                        if ($currentYear !== $data['tahun_ajaran']):
                            $currentYear = $data['tahun_ajaran'];
                ?>
                <tr class="year-group">
                    <td colspan="7" style="padding: 8px;">
                        TAHUN AJARAN <?php echo htmlspecialchars($currentYear); ?>
                    </td>
                </tr>
                <?php 
                        endif;
                        
                        // Tentukan status dan aksi
                        $statusClass = '';
                        $statusText = '';
                        $aksiText = '';
                        
                        if ($data['is_lunas']) {
                            $statusClass = 'status-lunas';
                            $statusText = 'Valid';
                            $aksiText = 'Lunas';
                        } else {
                            $statusClass = 'status-belum';
                            $statusText = 'Belum';
                            $aksiText = 'Belum Terbuka';
                        }
                ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td class="text-left"><?php echo htmlspecialchars($data['bulan']); ?></td>
                    <td class="text-right">Rp <?php echo number_format($data['nominal_tagihan'], 0, ',', '.'); ?></td>
                    <td class="text-center">
                        <?php 
                        if ($data['metode']) {
                            echo '<span class="badge-tunai">' . htmlspecialchars(strtoupper($data['metode'])) . '</span>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td class="text-center"><?php echo $data['tempat_bayar'] ? htmlspecialchars($data['tempat_bayar']) : '-'; ?></td>
                    <td class="text-center">
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span style="color: <?php echo $data['is_lunas'] ? '#2e7d32' : '#ff6b35'; ?>; font-weight: bold;">
                            <?php echo $aksiText; ?>
                        </span>
                    </td>
                </tr>
                <?php 
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">
                        Tidak ada data pembayaran
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #e3f2fd; font-weight: bold;">
                    <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                    <td class="text-right">Rp <?php echo number_format($totalTagihan, 0, ',', '.'); ?></td>
                    <td colspan="2" class="text-center">Total Tunggakan</td>
                    <td colspan="2" class="text-center">
                        <span style="font-size: 11pt; color: <?php echo $totalTunggakan > 0 ? '#c62828' : '#2e7d32'; ?>">
                            Rp <?php echo number_format($totalTunggakan, 0, ',', '.'); ?>
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Footer dengan Tanda Tangan -->
        <div class="footer">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div style="font-weight: bold;">Kepala Sekolah</div>
                <div class="signature-line">
                    <strong>Drs. H. Ahmad Subhan, M.Pd</strong><br>
                    NIP: 196501011990031005
                </div>
            </div>
            
            <div class="signature-box">
                <div>Jakarta, <?php echo date('d F Y'); ?></div>
                <div style="font-weight: bold;">Bendahara Sekolah</div>
                <div class="signature-line">
                    <strong>Sri Wahyuni, S.E</strong><br>
                    NIP: 197203151998022001
                </div>
            </div>
        </div>
        
        <div class="print-info">
            Dokumen ini dicetak pada: <?php echo date('d F Y, H:i:s'); ?> WIB<br>
            Sistem Informasi Keuangan SMK BIT BINA AULIA
        </div>
    </div>
    
    <script>
        window.onload = function() {
            setTimeout(function() {
                document.querySelector('.print-button').focus();
            }, 500);
        };
    </script>
</body>
</html>
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - <?php echo htmlspecialchars($siswa['nama']); ?></title>
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 15mm;
            }
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 7pt;
            line-height: 1.1;
            color: #000;
            background: #fff;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 5px;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .school-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .school-address {
            font-size: 10pt;
            margin-bottom: 3px;
        }
        
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Student Info */
        .student-info {
            margin: 20px 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 30px;
        }
        
        .info-row {
            display: flex;
            font-size: 10pt;
        }
        
        .info-label {
            width: 140px;
            font-weight: normal;
        }
        
        .info-colon {
            width: 15px;
        }
        
        .info-value {
            flex: 1;
            font-weight: bold;
        }
        
        /* Summary Box */
        .summary-box {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3px;
            margin: 3px 0;
            page-break-inside: avoid;
        }
        
        .summary-item {
            border: 1px solid #333;
            padding: 2px;
            text-align: center;
            border-radius: 1px;
        }
        
        .summary-label {
            font-size: 7pt;
            margin-bottom: 3px;
            font-weight: bold;
            color: #555;
        }
        
        .summary-value {
            font-size: 9pt;
            font-weight: bold;
        }
        
        .summary-value.total { color: #1976d2; }
        .summary-value.paid { color: #2e7d32; }
        .summary-value.unpaid { color: #c62828; }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 9pt;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th {
            background-color: #333;
            color: #fff;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }
        
        td {
            padding: 8px;
            vertical-align: middle;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-lunas {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #16a34a;
        }
        
        .status-belum {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #dc2626;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 10pt;
        }
        
        .print-info {
            font-size: 8pt;
            color: #666;
            text-align: center;
            margin-top: 20px;
            border-top: 1px dashed #999;
            padding-top: 10px;
        }
        
        /* Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        .print-button:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>  
    <script>
        // Auto focus untuk print
        window.onload = function() {
            // Beri delay sedikit agar halaman ter-render sempurna
            setTimeout(function() {
                document.querySelector('.print-button').focus();
            }, 500);
        };
    </script>
</body>
</html>
