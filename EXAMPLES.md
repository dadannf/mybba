# 💡 Code Examples - MyBBA

## Quick Reference untuk Developer

### 1. Membuat Halaman Baru dengan Layout Template

```php
<?php
// File: public/admin/example/index.php

// Include config & auth
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../shared/middleware/auth_check.php';

// Check role
$required_role = 'admin';
if (!hasRole($required_role)) {
    redirect('/auth/login.php');
}

// Set page variables
$pageTitle = 'Contoh Halaman';
$sidebarType = 'admin'; // or 'student'
$additionalCSS = ['/css/custom.css'];
$additionalJS = ['/js/custom.js'];

// Define content file
$contentFile = __DIR__ . '/content.php';

// Include layout
include __DIR__ . '/../../shared/layouts/main.php';
```

**Content file (content.php):**
```php
<div class="container-fluid py-4">
    <h2>Contoh Halaman</h2>
    <p>Konten halaman di sini...</p>
</div>
```

---

### 2. Membuat Halaman Tanpa Layout (Manual)

```php
<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../shared/middleware/auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Halaman Manual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/dashboard.css" rel="stylesheet">
</head>
<body class="has-sidebar">
    
    <?php include __DIR__ . '/../../shared/components/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include __DIR__ . '/../../shared/components/navbar.php'; ?>
        
        <main class="content-area">
            <div class="container-fluid py-4">
                <h2>Konten Halaman</h2>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

### 3. CRUD Operations dengan Helper Functions

**Create:**
```php
<?php
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    
    // Validate
    if (empty($nama) || empty($email)) {
        setFlash('danger', 'Semua field harus diisi');
        redirect('/admin/students/create.php');
    }
    
    // Insert to database
    $sql = "INSERT INTO siswa (nama, email) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $nama, $email);
    
    if ($stmt->execute()) {
        setFlash('success', 'Data berhasil ditambahkan');
        redirect('/admin/students/index.php');
    } else {
        setFlash('danger', 'Gagal menambahkan data');
        redirect('/admin/students/create.php');
    }
}
?>
```

**Read:**
```php
<?php
require_once __DIR__ . '/../../config.php';

// Get all students
$sql = "SELECT * FROM siswa ORDER BY nama ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "</tr>";
}
?>
```

**Update:**
```php
<?php
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $nama = sanitize($_POST['nama']);
    
    $sql = "UPDATE siswa SET nama = ? WHERE nis = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $nama, $id);
    
    if ($stmt->execute()) {
        setFlash('success', 'Data berhasil diupdate');
    } else {
        setFlash('danger', 'Gagal update data');
    }
    
    redirect('/admin/students/index.php');
}
?>
```

**Delete:**
```php
<?php
require_once __DIR__ . '/../../config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "DELETE FROM siswa WHERE nis = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        setFlash('success', 'Data berhasil dihapus');
    } else {
        setFlash('danger', 'Gagal menghapus data');
    }
}

redirect('/admin/students/index.php');
?>
```

---

### 4. File Upload

```php
<?php
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $targetDir = __DIR__ . '/../../uploads/siswa';
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    
    $result = uploadFile($_FILES['foto'], $targetDir, $allowedTypes);
    
    if ($result['success']) {
        $filename = $result['filename'];
        
        // Save to database
        $sql = "UPDATE siswa SET foto = ? WHERE nis = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $filename, $nis);
        $stmt->execute();
        
        setFlash('success', 'Foto berhasil diupload');
    } else {
        setFlash('danger', $result['message']);
    }
    
    redirect('/admin/students/index.php');
}
?>
```

---

### 5. AJAX Endpoint

```php
<?php
// File: public/api/get_student.php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get student ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// Query database
$sql = "SELECT * FROM siswa WHERE nis = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'data' => $row
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak ditemukan'
    ]);
}
?>
```

**JavaScript untuk consume API:**
```javascript
fetch('/api/get_student.php?id=123')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(data.data);
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
```

---

### 6. Flash Messages

```php
<?php
// Set flash message
setFlash('success', 'Operasi berhasil!');
setFlash('danger', 'Terjadi kesalahan!');
setFlash('warning', 'Perhatian!');
setFlash('info', 'Informasi penting');

// Redirect
redirect('/admin/students/index.php');
?>
```

**Display flash (sudah otomatis di layout template):**
```php
<?php
$flash = getFlash();
if ($flash):
?>
    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
```

---

### 7. Format Helper Functions

```php
<?php
// Format currency
echo formatRupiah(5000000); // Output: Rp 5.000.000

// Format date
echo formatTanggal('2024-01-15'); // Output: 15 Januari 2024

// Sanitize input
$clean = sanitize($_POST['input']);

// Check login
if (isLoggedIn()) {
    echo "User logged in";
}

// Check role
if (hasRole('admin')) {
    echo "User is admin";
}
?>
```

---

### 8. Modal dengan AJAX

**HTML:**
```html
<button class="btn btn-primary" onclick="showDetail(123)">Lihat Detail</button>

<!-- Modal -->
<div class="modal fade" id="detailModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                Loading...
            </div>
        </div>
    </div>
</div>
```

**JavaScript:**
```javascript
function showDetail(id) {
    fetch(`/api/get_student.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const student = data.data;
                document.getElementById('modalContent').innerHTML = `
                    <p><strong>Nama:</strong> ${student.nama}</p>
                    <p><strong>NIS:</strong> ${student.nis}</p>
                    <p><strong>Kelas:</strong> ${student.kelas}</p>
                `;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                modal.show();
            }
        });
}
```

---

### 9. Form Validation

**Client-side (JavaScript):**
```javascript
document.getElementById('myForm').addEventListener('submit', function(e) {
    const nama = document.getElementById('nama').value.trim();
    
    if (nama === '') {
        e.preventDefault();
        alert('Nama harus diisi');
        return false;
    }
    
    return true;
});
```

**Server-side (PHP):**
```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    
    if (empty($nama)) {
        $errors[] = 'Nama harus diisi';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid';
    }
    
    if (empty($errors)) {
        // Process data
    } else {
        setFlash('danger', implode('<br>', $errors));
        redirect($_SERVER['PHP_SELF']);
    }
}
?>
```

---

### 10. Pagination

```php
<?php
// Get page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Count total records
$countSql = "SELECT COUNT(*) as total FROM siswa";
$countResult = $conn->query($countSql);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get records for current page
$sql = "SELECT * FROM siswa LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Display pagination
echo '<nav><ul class="pagination">';
for ($i = 1; $i <= $totalPages; $i++) {
    $active = ($i === $page) ? 'active' : '';
    echo "<li class='page-item $active'>";
    echo "<a class='page-link' href='?page=$i'>$i</a>";
    echo "</li>";
}
echo '</ul></nav>';
?>
```

---

**Need more examples?** Check the existing code in `public/admin/` and `public/student/` folders.
