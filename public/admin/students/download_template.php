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

// Header kolom sesuai dengan struktur tabel siswa
$headers = [
    'nis',          // REQUIRED
    'nisn',         // Optional
    'nik',          // Optional
    'nama',         // REQUIRED
    'tempat_lahir', // Optional
    'tanggal_lahir', // Format: YYYY-MM-DD
    'jk',           // L atau P (REQUIRED)
    'kelas',        // Contoh: 10, 11, 12 (REQUIRED)
    'jurusan',      // Contoh: RPL, TKJ, MM (REQUIRED)
    'agama',        // Optional
    'alamat',       // Optional
    'email',        // Optional
    'no_hp',        // Optional
    'nama_ayah',    // Optional
    'nama_ibu',     // Optional
    'pekerjaan_ayah', // Optional
    'pekerjaan_ibu',  // Optional
    'no_hp_ortu',   // Optional
    'status_siswa', // aktif/lulus/keluar (Default: aktif)
    'tahun_masuk'   // Format: YYYY (Default: tahun sekarang)
];

fputcsv($output, $headers);

// Tambahkan 3 baris contoh data
$samples = [
    [
        '222111001',              // nis
        '0001234567',             // nisn
        '3201010101010001',       // nik
        'Ahmad Rizki Maulana',    // nama
        'Bandung',                // tempat_lahir
        '2007-05-15',             // tanggal_lahir
        'L',                      // jk
        '10',                     // kelas
        'RPL',                    // jurusan
        'Islam',                  // agama
        'Jl. Merdeka No. 123',    // alamat
        'ahmad.rizki@email.com',  // email
        '081234567890',           // no_hp
        'Budi Santoso',           // nama_ayah
        'Siti Aminah',            // nama_ibu
        'Wiraswasta',             // pekerjaan_ayah
        'Ibu Rumah Tangga',       // pekerjaan_ibu
        '081234567891',           // no_hp_ortu
        'aktif',                  // status_siswa
        '2023'                    // tahun_masuk
    ],
    [
        '222111002',
        '0001234568',
        '3201010202020002',
        'Siti Nurhaliza',
        'Jakarta',
        '2007-08-20',
        'P',
        '10',
        'TKJ',
        'Islam',
        'Jl. Kenangan No. 456',
        'siti.nur@email.com',
        '081234567892',
        'Hendra Wijaya',
        'Rina Kusuma',
        'Pegawai Negeri',
        'Guru',
        '081234567893',
        'aktif',
        '2023'
    ],
    [
        '222111003',
        '0001234569',
        '3201010303030003',
        'Budi Pratama',
        'Surabaya',
        '2007-03-10',
        'L',
        '11',
        'MM',
        'Kristen',
        'Jl. Pahlawan No. 789',
        'budi.pratama@email.com',
        '081234567894',
        'Agus Setiawan',
        'Linda Sari',
        'Dokter',
        'Perawat',
        '081234567895',
        'aktif',
        '2022'
    ]
];

foreach ($samples as $sample) {
    fputcsv($output, $sample);
}

fclose($output);
exit;
