<?php
// =============================================
// Halaman: Print Laporan Tahunan Keuangan
// Deskripsi: Cetak laporan pembayaran tahunan (rekap per siswa)
// =============================================

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';

// Pastikan yang login adalah admin
if ($userRole !== 'admin') {
    $_SESSION['error'] = 'Akses ditolak!';
    header('Location: /admin/index.php');
    exit;
}

// =============================================
// PARAMETER TAHUN AJARAN
// =============================================
$tahunAjaran = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Validasi tahun ajaran
if ($tahunAjaran < 2020 || $tahunAjaran > 2099) {
    $tahunAjaran = date('Y');
}

// Query untuk mendapatkan rekap keuangan per siswa untuk tahun ajaran tertentu
$sql = "SELECT 
            k.*,
            s.nama,
            s.kelas,
            s.jurusan
        FROM keuangan k
        INNER JOIN siswa s ON k.nis = s.nis
        WHERE k.tahun = $tahunAjaran
        ORDER BY s.kelas ASC, s.nama ASC";
$result = $conn->query($sql);

// Statistik
$totalSiswa = 0;
$totalTagihanAll = 0;
$totalBayarAll = 0;
$totalTunggakanAll = 0;
$siswaLunas = 0;
$siswaBelumLunas = 0;

// Group by kelas
$dataByKelas = [];

while ($row = $result->fetch_assoc()) {
    $kelas = $row['kelas'];
    if (!isset($dataByKelas[$kelas])) {
        $dataByKelas[$kelas] = [
            'data' => [],
            'total_tagihan' => 0,
            'total_bayar' => 0,
            'total_tunggakan' => 0
        ];
    }
    
    $tunggakan = $row['total_tagihan'] - $row['total_bayar'];
    $row['tunggakan'] = $tunggakan;
    
    $dataByKelas[$kelas]['data'][] = $row;
    $dataByKelas[$kelas]['total_tagihan'] += $row['total_tagihan'];
    $dataByKelas[$kelas]['total_bayar'] += $row['total_bayar'];
    $dataByKelas[$kelas]['total_tunggakan'] += $tunggakan;
    
    $totalSiswa++;
    $totalTagihanAll += $row['total_tagihan'];
    $totalBayarAll += $row['total_bayar'];
    $totalTunggakanAll += $tunggakan;
    
    if ($tunggakan <= 0) {
        $siswaLunas++;
    } else {
        $siswaBelumLunas++;
    }
}

// Hitung persentase
$persenPembayaran = $totalTagihanAll > 0 ? ($totalBayarAll / $totalTagihanAll) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tahunan - Tahun Ajaran <?php echo $tahunAjaran . '/' . ($tahunAjaran + 1); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            padding: 15px;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header p {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .report-title {
            text-align: center;
            margin: 20px 0;
        }
        
        .report-title h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-info {
            background: #f0f0f0;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .report-info p {
            margin: 3px 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            background: #fff3e0;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #ff9800;
        }
        
        .stat-box .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-box .value {
            font-size: 14px;
            font-weight: bold;
            color: #e65100;
        }
        
        .kelas-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .kelas-header {
            background: #FF9800;
            color: white;
            padding: 8px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        table thead {
            background-color: #FFB74D;
        }
        
        table th {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }
        
        table td {
            border: 1px solid #ddd;
            padding: 5px 4px;
            font-size: 9px;
        }
        
        table td.text-center {
            text-align: center;
        }
        
        table td.text-right {
            text-align: right;
            padding-right: 6px;
        }
        
        .progress-bar {
            width: 100%;
            height: 15px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #4CAF50, #81C784);
            transition: width 0.3s;
        }
        
        .progress-text {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            font-size: 8px;
            line-height: 15px;
            font-weight: bold;
            color: #000;
        }
        
        .subtotal-row {
            background: #fff9e6;
            font-weight: bold;
        }
        
        .grand-total-row {
            background: #FF9800;
            color: white;
            font-weight: bold;
            font-size: 11px;
        }
        
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature {
            text-align: center;
            margin-top: 60px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin: 0 auto;
            margin-top: 60px;
        }
        
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #FF9800;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #F57C00;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .print-button {
                display: none;
            }
            
            .kelas-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print</button>
    
    <div class="header">
        <h1>SMK BIT BINA AULIA</h1>
        <p>Jl. Letda Natsir No. 582, Bojong Kulur, Gunungputri, Bogor</p>
        <p>Telp: 021-82415429 | Email: smkbitbinaaulia@ymail.com</p>
        <p>NPSN: 20254256 | Akreditasi: A</p>
    </div>
    
    <div class="report-title">
        <h2>LAPORAN PEMBAYARAN TAHUNAN</h2>
        <p style="font-size: 12px; font-weight: bold;">
            Tahun Ajaran: <?php echo $tahunAjaran . '/' . ($tahunAjaran + 1); ?>
        </p>
    </div>
    
    <div class="report-info">
        <p><strong>Jenis Laporan:</strong> Laporan Tahunan (Rekap Per Siswa)</p>
        <p><strong>Tahun Ajaran:</strong> <?php echo $tahunAjaran . '/' . ($tahunAjaran + 1); ?></p>
        <p><strong>Dicetak:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-box">
            <div class="label">Total Siswa</div>
            <div class="value"><?php echo $totalSiswa; ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Siswa Lunas</div>
            <div class="value" style="color: #4CAF50;"><?php echo $siswaLunas; ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Belum Lunas</div>
            <div class="value" style="color: #F44336;"><?php echo $siswaBelumLunas; ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Total Tagihan</div>
            <div class="value">Rp <?php echo number_format($totalTagihanAll, 0, ',', '.'); ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Total Terbayar</div>
            <div class="value" style="color: #4CAF50;">Rp <?php echo number_format($totalBayarAll, 0, ',', '.'); ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Total Tunggakan</div>
            <div class="value" style="color: #F44336;">Rp <?php echo number_format($totalTunggakanAll, 0, ',', '.'); ?></div>
        </div>
    </div>
    
    <div style="margin-bottom: 20px; padding: 10px; background: #e3f2fd; border-radius: 5px;">
        <p style="font-size: 10px; margin-bottom: 5px;"><strong>Persentase Pembayaran:</strong></p>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo $persenPembayaran; ?>%"></div>
            <div class="progress-text"><?php echo number_format($persenPembayaran, 1); ?>%</div>
        </div>
    </div>
    
    <?php if (count($dataByKelas) > 0): ?>
        <?php foreach ($dataByKelas as $kelas => $kelasData): ?>
        <div class="kelas-section">
            <div class="kelas-header">
                KELAS: <?php echo htmlspecialchars($kelas); ?> 
                (<?php echo count($kelasData['data']); ?> Siswa)
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th style="width: 60px;">NIS</th>
                        <th style="width: 130px;">Nama Siswa</th>
                        <th style="width: 80px;">Total Tagihan</th>
                        <th style="width: 80px;">Total Bayar</th>
                        <th style="width: 80px;">Tunggakan</th>
                        <th style="width: 80px;">Progress</th>
                        <th style="width: 50px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($kelasData['data'] as $row): 
                        $progress = $row['total_tagihan'] > 0 ? ($row['total_bayar'] / $row['total_tagihan']) * 100 : 0;
                        $isLunas = $row['tunggakan'] <= 0;
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['nis']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="text-right">Rp <?php echo number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                        <td class="text-right">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                        <td class="text-right">Rp <?php echo number_format($row['tunggakan'], 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <div class="progress-bar" style="height: 12px;">
                                <div class="progress-fill" style="width: <?php echo $progress; ?>%; background: <?php echo $isLunas ? '#4CAF50' : '#FF9800'; ?>"></div>
                                <div class="progress-text" style="line-height: 12px; font-size: 7px;"><?php echo number_format($progress, 0); ?>%</div>
                            </div>
                        </td>
                        <td class="text-center" style="<?php echo $isLunas ? 'color: #4CAF50; font-weight: bold;' : 'color: #F44336;'; ?>">
                            <?php echo $isLunas ? 'LUNAS' : 'BELUM'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="subtotal-row">
                        <td colspan="3" class="text-right" style="padding-right: 8px;">SUBTOTAL KELAS <?php echo htmlspecialchars($kelas); ?></td>
                        <td class="text-right">Rp <?php echo number_format($kelasData['total_tagihan'], 0, ',', '.'); ?></td>
                        <td class="text-right">Rp <?php echo number_format($kelasData['total_bayar'], 0, ',', '.'); ?></td>
                        <td class="text-right">Rp <?php echo number_format($kelasData['total_tunggakan'], 0, ',', '.'); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
        
        <!-- Grand Total -->
        <table>
            <tr class="grand-total-row">
                <td colspan="3" class="text-right" style="padding: 8px;">GRAND TOTAL</td>
                <td class="text-right" style="padding: 8px;">Rp <?php echo number_format($totalTagihanAll, 0, ',', '.'); ?></td>
                <td class="text-right" style="padding: 8px;">Rp <?php echo number_format($totalBayarAll, 0, ',', '.'); ?></td>
                <td class="text-right" style="padding: 8px;">Rp <?php echo number_format($totalTunggakanAll, 0, ',', '.'); ?></td>
                <td colspan="2" style="padding: 8px;"></td>
            </tr>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; background: #f0f0f0; border-radius: 5px;">
            <p style="font-size: 14px; color: #666;">Tidak ada data keuangan untuk tahun ajaran ini</p>
        </div>
    <?php endif; ?>
    
    <div class="footer">
        <div>
            <p style="font-size: 9px; color: #666;">
                * Laporan ini mencakup seluruh pembayaran tahun ajaran <?php echo $tahunAjaran . '/' . ($tahunAjaran + 1); ?>
            </p>
        </div>
        <div>
            <p>Bogor, <?php echo date('d F Y'); ?></p>
            <div class="signature">
                <div class="signature-line"></div>
                <p style="margin-top: 5px;">Kepala Sekolah</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
