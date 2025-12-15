<?php
// =============================================
// File: download_template.php
// Deskripsi: Generate template CSV untuk import siswa
// =============================================

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth_check.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    die('Unauthorized access');
}

// Set headers untuk download CSV
$filename = "template_import_siswa.csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header kolom sesuai dengan struktur tabel siswa di database
$headers = [
    'nis',              // REQUIRED
    'nisn',             // Optional
    'nik',              // Optional
    'nama',             // REQUIRED
    'tempat_lahir',     // Optional
    'tanggal_lahir',    // Format: DD/MM/YYYY
    'jk',               // L atau P (REQUIRED)
    'kelas',            // Contoh: 10, 11, 12 (REQUIRED)
    'jurusan',          // Contoh: RPL, TKJ, MM (REQUIRED)
    'ayah',             // Nama ayah (Optional)
    'ibu',              // Nama ibu (Optional)
    'alamat',           // Optional
    'email',            // Optional
    'no_hp',            // Optional
    'status_siswa'      // aktif/lulus/keluar/pindah (Default: aktif)
];

// Write header dengan delimiter semicolon
fputcsv($output, $headers, ';');

// Tambahkan 3 baris contoh data
$samples = [
    [
        '222111001',              // nis
        '1234567',                // nisn
        '3201010101010001',       // nik
        'Ahmad Rizki Maulana',    // nama
        'Bandung',                // tempat_lahir
        '15/05/2007',             // tanggal_lahir (DD/MM/YYYY)
        'L',                      // jk
        '10',                     // kelas
        'RPL',                    // jurusan
        'Budi Santoso',           // ayah
        'Siti Aminah',            // ibu
        'Jl. Merdeka No. 123',    // alamat
        'ahmad.rizki@email.com',  // email
        '81234567890',            // no_hp
        'aktif'                   // status_siswa
    ],
    [
        '222111002',
        '1234568',
        '3201010202020002',
        'Siti Nurhaliza',
        'Jakarta',
        '20/08/2007',
        'P',
        '10',
        'TKJ',
        'Hendra Wijaya',
        'Rina Kusuma',
        'Jl. Kenangan No. 456',
        'siti.nur@email.com',
        '81234567892',
        'aktif'
    ],
    [
        '222111003',
        '1234569',
        '3201010303030003',
        'Budi Pratama',
        'Surabaya',
        '10/03/2007',
        'L',
        '11',
        'MM',
        'Agus Setiawan',
        'Linda Sari',
        'Jl. Pahlawan No. 789',
        'budi.pratama@email.com',
        '81234567894',
        'aktif'
    ]
];

foreach ($samples as $sample) {
    fputcsv($output, $sample, ';');
}

fclose($output);
exit;
