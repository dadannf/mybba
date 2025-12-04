<?php
// =============================================
// Halaman: Print Laporan Harian Keuangan
// Deskripsi: Cetak laporan pembayaran harian
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
// PARAMETER TANGGAL
// =============================================
$tanggal = isset($_GET['tanggal']) ? esc($_GET['tanggal']) : date('Y-m-d');

// Query untuk mendapatkan pembayaran pada tanggal tertentu
$sql = "SELECT 
            p.*,
            k.nis,
            k.tahun,
            s.nama,
            s.kelas,
            s.jurusan
        FROM pembayaran p
        INNER JOIN keuangan k ON p.keuangan_id = k.keuangan_id
        INNER JOIN siswa s ON k.nis = s.nis
        WHERE DATE(p.tanggal_bayar) = '$tanggal'
        ORDER BY p.tanggal_bayar ASC, s.nama ASC";
$result = $conn->query($sql);

// Statistik
$totalPembayaran = 0;
$totalNominal = 0;
$jumlahValid = 0;
$jumlahMenunggu = 0;
$jumlahTolak = 0;

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $totalPembayaran++;
    
    if ($row['status'] == 'valid') {
        $totalNominal += $row['nominal_bayar'];
        $jumlahValid++;
    } elseif ($row['status'] == 'menunggu') {
        $jumlahMenunggu++;
    } else {
        $jumlahTolak++;
    }
}

// Array bulan
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
    <title>Laporan Harian - <?php echo date('d-m-Y', strtotime($tanggal)); ?></title>
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
            background: #e3f2fd;
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
            color: #1976d2;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table thead {
            background-color: #2196F3;
            color: white;
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
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #1976D2;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .print-button {
                display: none;
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
        <h2>LAPORAN PEMBAYARAN HARIAN</h2>
        <p style="font-size: 12px; font-weight: bold;">
            Tanggal: <?php echo date('d F Y', strtotime($tanggal)); ?>
        </p>
    </div>
    
    <div class="report-info">
        <p><strong>Jenis Laporan:</strong> Laporan Harian</p>
        <p><strong>Periode:</strong> <?php echo date('d/m/Y', strtotime($tanggal)); ?></p>
        <p><strong>Dicetak:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    
    <div class="stats-box">
        <div class="stat-item">
            <div class="label">Total Transaksi</div>
            <div class="value"><?php echo $totalPembayaran; ?></div>
        </div>
        <div class="stat-item">
            <div class="label">Total Nominal (Valid)</div>
            <div class="value">Rp <?php echo number_format($totalNominal, 0, ',', '.'); ?></div>
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
    
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">Waktu</th>
                <th style="width: 70px;">NIS</th>
                <th style="width: 150px;">Nama Siswa</th>
                <th style="width: 80px;">Kelas</th>
                <th style="width: 80px;">Bulan</th>
                <th style="width: 100px;">Nominal</th>
                <th style="width: 80px;">Metode</th>
                <th style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($data) > 0): 
                $no = 1;
                foreach ($data as $row): 
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td class="text-center"><?php echo date('H:i', strtotime($row['tanggal_bayar'])); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($row['nis']); ?></td>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($row['kelas']); ?></td>
                <td class="text-center"><?php echo isset($row['bulan_untuk']) && isset($bulanList[$row['bulan_untuk']]) ? $bulanList[$row['bulan_untuk']] : '-'; ?></td>
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
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="6" class="text-right" style="padding-right: 10px;">TOTAL VALID</td>
                <td class="text-right">Rp <?php echo number_format($totalNominal, 0, ',', '.'); ?></td>
                <td colspan="2"></td>
            </tr>
            <?php else: ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 30px;">
                    Tidak ada pembayaran pada tanggal ini
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <div>
            <p style="font-size: 10px; color: #666;">
                * Laporan ini hanya mencakup pembayaran yang diinput pada tanggal <?php echo date('d/m/Y', strtotime($tanggal)); ?>
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
