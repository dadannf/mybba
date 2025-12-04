<?php
/**
 * Helper Functions untuk Notifikasi
 */

/**
 * Membuat notifikasi pembayaran
 */
function createPaymentNotification($conn, $nis, $nama, $nominal, $keuanganId = null) {
    $judul = "Pembayaran Baru";
    $pesan = sprintf(
        "Siswa %s (%s) telah melakukan pembayaran sebesar Rp %s",
        $nama,
        $nis,
        number_format($nominal, 0, ',', '.')
    );
    $link = $keuanganId ? "/admin/finance/detail.php?id=" . $keuanganId : "/admin/finance/index.php";
    
    return createNotification($conn, 'pembayaran', $judul, $pesan, $nis, $nama, $link);
}

/**
 * Membuat notifikasi perubahan data
 */
function createDataUpdateNotification($conn, $nis, $nama, $fieldsChanged = []) {
    $judul = "Perubahan Data Profil";
    
    if (!empty($fieldsChanged)) {
        $fields = implode(', ', $fieldsChanged);
        $pesan = sprintf(
            "Siswa %s (%s) telah mengubah data: %s",
            $nama,
            $nis,
            $fields
        );
    } else {
        $pesan = sprintf(
            "Siswa %s (%s) telah mengubah data profil",
            $nama,
            $nis
        );
    }
    
    $link = "/admin/students/edit.php?nis=" . $nis;
    
    return createNotification($conn, 'perubahan_data', $judul, $pesan, $nis, $nama, $link);
}

/**
 * Fungsi umum untuk membuat notifikasi
 */
function createNotification($conn, $tipe, $judul, $pesan, $nis = null, $nama = null, $link = null) {
    try {
        $sql = "INSERT INTO notifikasi (tipe, judul, pesan, nis, nama_siswa, link) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssss', $tipe, $judul, $pesan, $nis, $nama, $link);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Hapus notifikasi lama (lebih dari 30 hari)
 */
function cleanOldNotifications($conn, $days = 30) {
    try {
        $sql = "DELETE FROM notifikasi WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $days);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error cleaning old notifications: " . $e->getMessage());
        return false;
    }
}
