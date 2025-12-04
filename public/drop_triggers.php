<?php
/**
 * Script untuk menghapus trigger yang menyebabkan error saat delete cascade
 * Jalankan file ini sekali saja melalui browser
 */

require_once __DIR__ . '/config.php';

echo "<h2>Dropping MySQL Triggers...</h2>";

try {
    // Drop trigger after_pembayaran_insert
    $result1 = $conn->query("DROP TRIGGER IF EXISTS after_pembayaran_insert");
    echo $result1 ? "✓ Trigger 'after_pembayaran_insert' berhasil dihapus<br>" : "✗ Gagal menghapus trigger 'after_pembayaran_insert'<br>";
    
    // Drop trigger after_pembayaran_update
    $result2 = $conn->query("DROP TRIGGER IF EXISTS after_pembayaran_update");
    echo $result2 ? "✓ Trigger 'after_pembayaran_update' berhasil dihapus<br>" : "✗ Gagal menghapus trigger 'after_pembayaran_update'<br>";
    
    // Drop trigger after_pembayaran_delete
    $result3 = $conn->query("DROP TRIGGER IF EXISTS after_pembayaran_delete");
    echo $result3 ? "✓ Trigger 'after_pembayaran_delete' berhasil dihapus<br>" : "✗ Gagal menghapus trigger 'after_pembayaran_delete'<br>";
    
    echo "<br><hr>";
    echo "<h3 style='color: green;'>✓ Semua trigger berhasil dihapus!</h3>";
    echo "<p>Sekarang Anda bisa menghapus data siswa tanpa error.</p>";
    echo "<p><strong>Catatan:</strong> Total bayar akan diupdate secara manual melalui kode PHP, tidak lagi menggunakan trigger database.</p>";
    
    // Verifikasi trigger yang tersisa
    echo "<br><h4>Verifikasi Trigger yang Tersisa:</h4>";
    $result = $conn->query("SHOW TRIGGERS FROM dbsekolah");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Trigger</th><th>Event</th><th>Table</th><th>Timing</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Trigger']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Event']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Table']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Timing']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: blue;'>✓ Tidak ada trigger yang tersisa di database (SUKSES!)</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}

$conn->close();

echo "<br><hr>";
echo "<p><a href='/admin/students/index.php'>← Kembali ke Data Siswa</a></p>";
echo "<p><strong>PENTING:</strong> Setelah berhasil, Anda bisa menghapus file ini (drop_triggers.php) untuk keamanan.</p>";
?>
