<?php
// =============================================
// File: proses_edit_data.php
// Deskripsi: Proses update data pribadi siswa
// =============================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../includes/notification_helper.php';

// Pastikan yang login adalah siswa
if ($userRole !== 'siswa') {
    $_SESSION['error'] = 'Akses ditolak!';
    header('Location: ../index.php');
    exit;
}

// Pastikan request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: profile.php');
    exit;
}

// Ambil data dari form
$nis = $_POST['nis'] ?? '';
$nik = $_POST['nik'] ?? '';
$nama = $_POST['nama'] ?? '';
$tempat_lahir = $_POST['tempat_lahir'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$jk = $_POST['jk'] ?? '';
$kelas = $_POST['kelas'] ?? '';
$jurusan = $_POST['jurusan'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$no_hp = $_POST['no_hp'] ?? '';
$email = $_POST['email'] ?? '';
$ayah = $_POST['ayah'] ?? '';
$ibu = $_POST['ibu'] ?? '';

// Validasi data wajib
if (empty($nis) || empty($nama) || empty($tempat_lahir) || empty($tanggal_lahir) || 
    empty($jk) || empty($kelas) || empty($jurusan) || empty($alamat) || empty($no_hp)) {
    $_SESSION['error'] = 'Semua field wajib harus diisi!';
    header('Location: profile.php');
    exit;
}

// Pastikan NIS yang diupdate adalah milik user yang login
$username = $_SESSION['username'];
if ($nis !== $username) {
    $_SESSION['error'] = 'Anda hanya dapat mengupdate data pribadi Anda sendiri!';
    header('Location: profile.php');
    exit;
}

// Validasi NIK jika diisi (harus 16 digit)
if (!empty($nik) && !preg_match('/^[0-9]{16}$/', $nik)) {
    $_SESSION['error'] = 'NIK harus 16 digit angka!';
    header('Location: profile.php');
    exit;
}

// Validasi email jika diisi
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Format email tidak valid!';
    header('Location: profile.php');
    exit;
}

// Update data siswa
$sql = "UPDATE siswa SET 
        nik = ?,
        nama = ?,
        tempat_lahir = ?,
        tanggal_lahir = ?,
        jk = ?,
        kelas = ?,
        jurusan = ?,
        alamat = ?,
        no_hp = ?,
        email = ?,
        ayah = ?,
        ibu = ?
        WHERE nis = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssssss",
    $nik,
    $nama,
    $tempat_lahir,
    $tanggal_lahir,
    $jk,
    $kelas,
    $jurusan,
    $alamat,
    $no_hp,
    $email,
    $ayah,
    $ibu,
    $nis
);

if ($stmt->execute()) {
    // Buat notifikasi untuk admin tentang perubahan data
    $fieldsChanged = [];
    if (!empty($nik)) $fieldsChanged[] = 'NIK';
    $fieldsChanged[] = 'Data Pribadi';
    if (!empty($email)) $fieldsChanged[] = 'Email';
    
    createDataUpdateNotification($conn, $nis, $nama, $fieldsChanged);
    
    $_SESSION['success'] = 'Data pribadi berhasil diperbarui!';
} else {
    $_SESSION['error'] = 'Gagal memperbarui data: ' . $conn->error;
}

$stmt->close();
$conn->close();

header('Location: profile.php');
exit;
?>
