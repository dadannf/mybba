<?php
// =============================================
// Halaman: Print Rekap Pembayaran Siswa
// Deskripsi: Cetak laporan detail pembayaran bulanan siswa
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
// FILTER & SEARCH
// =============================================
$tahunFilter = isset($_GET['tahun']) ? esc($_GET['tahun']) : '';
$kelasFilter = isset($_GET['kelas']) ? esc($_GET['kelas']) : '';
$searchInput = isset($_GET['search']) ? esc($_GET['search']) : '';

$where = "1=1";
if ($tahunFilter) {
    $where .= " AND k.tahun = '$tahunFilter'";
}
if ($kelasFilter) {
    $where .= " AND s.kelas = '$kelasFilter'";
}
if ($searchInput) {
    $where .= " AND (s.nama LIKE '%$searchInput%' OR s.nis LIKE '%$searchInput%')";
}

// Query untuk mendapatkan data keuangan siswa
$sql = "SELECT k.*, s.nama, s.kelas, s.jurusan 
        FROM keuangan k 
        INNER JOIN siswa s ON k.nis = s.nis 
        WHERE $where 
        ORDER BY s.kelas ASC, s.nama ASC";
$result = $conn->query($sql);

// Query untuk statistik
$sqlStats = "SELECT 
    SUM(k.total_tagihan) as total_tagihan_all,
    SUM(k.total_bayar) as total_bayar_all,
    COUNT(*) as total_data
FROM keuangan k
INNER JOIN siswa s ON k.nis = s.nis
WHERE $where";
$resultStats = $conn->query($sqlStats);
$stats = $resultStats->fetch_assoc();

// Array bulan
$bulanList = [
    1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
    5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
    9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
];

// Fungsi untuk menentukan tagihan per bulan berdasarkan kelas
function getTagihanPerBulan($kelas) {
    // Ambil angka kelas dari string (misal: "10 IPA 1" -> 10)
    preg_match('/^(\d+)/', $kelas, $matches);
    $tingkatKelas = isset($matches[1]) ? intval($matches[1]) : 10;
    
    // Kelas 10: Rp 200.000, Kelas 11-12: Rp 190.000
    if ($tingkatKelas == 10) {
        return 200000;
    } else {
        return 190000; // Kelas 11 dan 12
    }
}

// Tidak perlu panggil fungsi di sini, akan dipanggil di dalam loop
// $tagihanPerBulan = getTagihanPerBulan($keuangan['kelas']); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pembayaran Siswa - <?php echo $tahunFilter ?: date('Y'); ?></title>
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
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header p {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .info-section {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        
        .info-item {
            font-size: 9px;
        }
        
        .info-item strong {
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
        }
        
        table thead {
            background-color: #90EE90;
        }
        
        table th {
            border: 1px solid #000;
            padding: 5px 3px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            white-space: nowrap;
        }
        
        table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-size: 8px;
        }
        
        table td.text-left {
            text-align: left;
            padding-left: 5px;
        }
        
        table td.text-right {
            text-align: right;
            padding-right: 5px;
        }
        
        .grand-total {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
        }
        
        .signature {
            margin-top: 50px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin: 0 auto;
            margin-top: 60px;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
            
            .no-print {
                display: none;
            }
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
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>
    
    <div class="header">
        <h1>SMK BIT BINA AULIA</h1>
        <p>Jl. Letda Natsir No. 582, Bojong Kulur, Gunungputri, Bogor</p>
        <p>Telp: 021-82415429 | Email: smkbitbinaaulia@ymail.com</p>
        <p>NPSN: 20254256 | Akreditasi: A</p>
    </div>
    
    <div style="text-align: center; margin-bottom: 15px;">
        <h2 style="font-size: 12px; font-weight: bold;">REKAP PEMBAYARAN SISWA</h2>
    </div>
    
    <div class="info-section">
        <div>
            <div class="info-item"><strong>Jenis Pembayaran</strong> : SPP</div>
            <div class="info-item"><strong>Periode Tahun</strong> : <?php echo $tahunFilter ?: date('Y') . '/' . (date('Y')+1); ?></div>
            <div class="info-item"><strong>Kelas</strong> : <?php echo $kelasFilter ?: 'Semua Kelas'; ?></div>
        </div>
        <div style="text-align: right;">
            <div class="info-item">Tanggal Cetak: <?php echo date('d-m-Y'); ?></div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">No</th>
                <th rowspan="2" style="width: 60px;">NIS/Induk</th>
                <th rowspan="2" style="width: 150px;">Nama Siswa</th>
                <th colspan="12">Rincian Pembayaran</th>
                <th rowspan="2" style="width: 70px;">Total dibayar</th>
                <th rowspan="2" style="width: 70px;">Total Tagihan</th>
                <th rowspan="2" style="width: 70px;">Sisa Tagihan</th>
            </tr>
            <tr>
                <?php foreach ($bulanList as $bulan): ?>
                    <th style="width: 40px;"><?php echo substr($bulan, 0, 3); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result->num_rows > 0):
                $no = 1;
                $grandTotalTagihan = 0;
                $grandTotalBayar = 0;
                
                while ($siswa = $result->fetch_assoc()):
                    $keuangan_id = $siswa['keuangan_id'];
                    
                    // Query pembayaran per siswa
                    $sqlPembayaran = "SELECT * FROM pembayaran 
                                      WHERE keuangan_id = '$keuangan_id' 
                                      AND status = 'valid'
                                      ORDER BY pembayaran_id ASC";
                    $resultPembayaran = $conn->query($sqlPembayaran);
                    
                    // Simpan pembayaran dalam array
                    $pembayaranData = [];
                    $indexBulan = 1;
                    while ($bayar = $resultPembayaran->fetch_assoc()) {
                        if ($indexBulan <= 12) {
                            $pembayaranData[$indexBulan] = $bayar['nominal_bayar'];
                            $indexBulan++;
                        }
                    }
                    
                    // Hitung tagihan berdasarkan kelas siswa
                    $tagihanPerBulan = getTagihanPerBulan($siswa['kelas']);
                    $totalTagihan = $tagihanPerBulan * 12;
                    $totalBayar = $siswa['total_bayar'];
                    $sisaTagihan = $totalTagihan - $totalBayar;
                    
                    $grandTotalTagihan += $totalTagihan;
                    $grandTotalBayar += $totalBayar;
            ?>
            <tr>
                <td><?php echo $no++; ?>.</td>
                <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
                <td class="text-left"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <td><?php echo isset($pembayaranData[$i]) ? number_format($pembayaranData[$i], 0, ',', '.') : '0'; ?></td>
                <?php endfor; ?>
                
                <td class="text-right"><?php echo number_format($totalBayar, 0, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($totalTagihan, 0, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($sisaTagihan, 0, ',', '.'); ?></td>
            </tr>
            <?php 
                endwhile;
                $grandSisa = $grandTotalTagihan - $grandTotalBayar;
            ?>
            <tr class="grand-total">
                <td colspan="3" style="text-align: right; padding-right: 10px;">Grand Total</td>
                <td colspan="12" style="text-align: right; padding-right: 10px;">
                    <?php echo number_format($grandTotalBayar, 0, ',', '.'); ?>
                </td>
                <td class="text-right"><?php echo number_format($grandTotalBayar, 0, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($grandTotalTagihan, 0, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($grandSisa, 0, ',', '.'); ?></td>
            </tr>
            <?php else: ?>
            <tr>
                <td colspan="18" style="text-align: center; padding: 20px;">Tidak ada data</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Balaraja, <?php echo date('d-m-Y'); ?></p>
        <p>Mengetahui</p>
        <div class="signature">
            <div class="signature-line"></div>
            <p style="margin-top: 5px;">Petugas Keuangan</p>
        </div>
    </div>
    
    <script>
        // Auto print saat halaman dimuat (opsional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
<?php $conn->close(); ?>
