<?php
// =============================================
// Halaman: Print Laporan Bulanan Keuangan
// Deskripsi: Cetak laporan pembayaran bulanan
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
// PARAMETER BULAN & TAHUN
// =============================================
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Nama bulan
$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Query untuk mendapatkan pembayaran pada bulan tertentu
$sql = "SELECT 
            p.*,
            k.nis,
            k.tahun as tahun_ajaran,
            k.total_tagihan,
            k.total_bayar,
            s.nama,
            s.kelas,
            s.jurusan
        FROM pembayaran p
        INNER JOIN keuangan k ON p.keuangan_id = k.keuangan_id
        INNER JOIN siswa s ON k.nis = s.nis
        WHERE MONTH(p.tanggal_bayar) = $bulan 
        AND YEAR(p.tanggal_bayar) = $tahun
        ORDER BY p.tanggal_bayar ASC, s.kelas ASC, s.nama ASC";
$result = $conn->query($sql);

// Statistik
$totalPembayaran = 0;
$totalNominalValid = 0;
$totalNominalMenunggu = 0;
$jumlahValid = 0;
$jumlahMenunggu = 0;
$jumlahTolak = 0;

// Group by kelas
$dataByKelas = [];

while ($row = $result->fetch_assoc()) {
    $kelas = $row['kelas'];
    if (!isset($dataByKelas[$kelas])) {
        $dataByKelas[$kelas] = [];
    }
    $dataByKelas[$kelas][] = $row;
    
    $totalPembayaran++;
    
    if ($row['status'] == 'valid') {
        $totalNominalValid += $row['nominal_bayar'];
        $jumlahValid++;
    } elseif ($row['status'] == 'menunggu') {
        $totalNominalMenunggu += $row['nominal_bayar'];
        $jumlahMenunggu++;
    } else {
        $jumlahTolak++;
    }
}

// Bulan list untuk menampilkan bulan pembayaran
$bulanList = [
    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - <?php echo $namaBulan[$bulan] . ' ' . $tahun; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header p {
            font-size: 11px;
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
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .report-info p {
            margin: 3px 0;
        }
        
        .stats-box {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #2e7d32;
        }
        
        .kelas-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .kelas-header {
            background: #4CAF50;
            color: white;
            padding: 8px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table thead {
            background-color: #81C784;
        }
        
        table th {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }
        
        table td {
            border: 1px solid #ddd;
            padding: 6px 5px;
            font-size: 10px;
        }
        
        table td.text-center {
            text-align: center;
        }
        
        table td.text-right {
            text-align: right;
            padding-right: 8px;
        }
        
        .status-valid {
            background: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .status-menunggu {
            background: #FF9800;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .status-tolak {
            background: #F44336;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .subtotal-row {
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
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
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #45a049;
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
        <h2>LAPORAN PEMBAYARAN BULANAN</h2>
        <p style="font-size: 12px; font-weight: bold;">
            Periode: <?php echo $namaBulan[$bulan] . ' ' . $tahun; ?>
        </p>
    </div>
    
    <div class="report-info">
        <p><strong>Jenis Laporan:</strong> Laporan Bulanan</p>
        <p><strong>Bulan:</strong> <?php echo $namaBulan[$bulan]; ?></p>
        <p><strong>Tahun:</strong> <?php echo $tahun; ?></p>
        <p><strong>Dicetak:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    
    <div class="stats-box">
        <div class="stat-item">
            <div class="label">Total Transaksi</div>
            <div class="value"><?php echo $totalPembayaran; ?></div>
        </div>
        <div class="stat-item">
            <div class="label">Total Valid</div>
            <div class="value">Rp <?php echo number_format($totalNominalValid, 0, ',', '.'); ?></div>
        </div>
        <div class="stat-item">
            <div class="label">Total Menunggu</div>
            <div class="value" style="color: #FF9800;">Rp <?php echo number_format($totalNominalMenunggu, 0, ',', '.'); ?></div>
        </div>
        <div class="stat-item">
            <div class="label">Valid</div>
            <div class="value" style="color: #4CAF50;"><?php echo $jumlahValid; ?></div>
        </div>
        <div class="stat-item">
            <div class="label">Menunggu</div>
            <div class="value" style="color: #FF9800;"><?php echo $jumlahMenunggu; ?></div>
        </div>
        <div class="stat-item">
            <div class="label">Ditolak</div>
            <div class="value" style="color: #F44336;"><?php echo $jumlahTolak; ?></div>
        </div>
    </div>
    
    <?php if (count($dataByKelas) > 0): ?>
        <?php foreach ($dataByKelas as $kelas => $rows): 
            $subtotalKelas = 0;
            foreach ($rows as $row) {
                if ($row['status'] == 'valid') {
                    $subtotalKelas += $row['nominal_bayar'];
                }
            }
        ?>
        <div class="kelas-section">
            <div class="kelas-header">KELAS: <?php echo htmlspecialchars($kelas); ?></div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;">No</th>
                        <th style="width: 80px;">Tanggal</th>
                        <th style="width: 70px;">NIS</th>
                        <th style="width: 150px;">Nama Siswa</th>
                        <th style="width: 100px;">Nominal</th>
                        <th style="width: 80px;">Metode</th>
                        <th style="width: 80px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($rows as $row): 
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td class="text-center"><?php echo date('d/m/Y', strtotime($row['tanggal_bayar'])); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['nis']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="text-right">Rp <?php echo number_format($row['nominal_bayar'], 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['metode']); ?></td>
                        <td class="text-center">
                            <?php 
                            if ($row['status'] == 'valid') {
                                echo '<span class="status-valid">Valid</span>';
                            } elseif ($row['status'] == 'menunggu') {
                                echo '<span class="status-menunggu">Menunggu</span>';
                            } else {
                                echo '<span class="status-tolak">Ditolak</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="subtotal-row">
                        <td colspan="5" class="text-right" style="padding-right: 10px;">SUBTOTAL KELAS <?php echo htmlspecialchars($kelas); ?></td>
                        <td class="text-right">Rp <?php echo number_format($subtotalKelas, 0, ',', '.'); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
        
        <!-- Grand Total -->
        <table>
            <tr style="background: #4CAF50; color: white; font-weight: bold; font-size: 12px;">
                <td colspan="5" class="text-right" style="padding: 10px;">GRAND TOTAL (VALID)</td>
                <td class="text-right" style="padding: 10px;">Rp <?php echo number_format($totalNominalValid, 0, ',', '.'); ?></td>
                <td colspan="2"></td>
            </tr>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; background: #f0f0f0; border-radius: 5px;">
            <p style="font-size: 14px; color: #666;">Tidak ada pembayaran pada bulan ini</p>
        </div>
    <?php endif; ?>
    
    <div class="footer">
        <div>
            <p style="font-size: 10px; color: #666;">
                * Laporan ini mencakup pembayaran bulan <?php echo $namaBulan[$bulan] . ' ' . $tahun; ?>
            </p>
        </div>
        <div>
            <p>Bogor, <?php echo date('d F Y'); ?></p>
            <div class="signature">
                <div class="signature-line"></div>
                <p style="margin-top: 5px;">Petugas Keuangan</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
