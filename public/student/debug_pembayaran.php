<?php
require_once __DIR__ . '/../config.php';

echo "<h2>Debug Struktur Tabel Pembayaran</h2>";

// Get table structure
$sql = "DESCRIBE pembayaran";
$result = $conn->query($sql);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Sample Data:</h3>";
$sql2 = "SELECT * FROM pembayaran LIMIT 5";
$result2 = $conn->query($sql2);

echo "<pre>";
while ($row = $result2->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";
?>
