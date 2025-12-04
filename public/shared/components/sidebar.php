<?php
/**
 * Shared Component: Admin Sidebar Navigation
 * 
 * Usage: include __DIR__ . '/../shared/components/sidebar.php';
 * 
 * Auto-detects current page and highlights active menu
 */

// Get current script name
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determine active menu
$dashboard_active = ($current_page == 'index.php' && $current_dir != 'students' && $current_dir != 'finance' && $current_dir != 'information') ? 'active' : '';
$students_active = (strpos($_SERVER['REQUEST_URI'], '/admin/students') !== false || strpos($_SERVER['REQUEST_URI'], 'students') !== false) ? 'active' : '';
$finance_active = (strpos($_SERVER['REQUEST_URI'], '/admin/finance') !== false || strpos($_SERVER['REQUEST_URI'], 'finance') !== false) ? 'active' : '';
$info_active = (strpos($_SERVER['REQUEST_URI'], '/admin/information') !== false || strpos($_SERVER['REQUEST_URI'], 'information') !== false) ? 'active' : '';

// Get admin name from session
$adminName = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin';
$adminRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
?>
<!-- =============================================
  Sidebar (bagian kiri)
  - Berisi profil singkat admin dan menu navigasi
  - Gunakan kelas `.sidebar` untuk styling
============================================= -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header text-center py-4">
    <div class="profile-avatar mb-2">
      <svg width="54" height="54" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="8" r="4" fill="#fff" opacity="0.9"/>
        <path d="M4 20c0-4 4-7 8-7s8 3 8 7" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <div class="profile-name">Admin Sekolah</div>
    <div class="profile-role text-muted"><?php echo htmlspecialchars($adminName); ?></div>
  </div>

  <!-- Navigasi utama pada sidebar -->
  <nav class="sidebar-nav" role="navigation" aria-label="Main menu">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?php echo $dashboard_active; ?>" href="/admin/index.php">
          <i class="bi bi-house-door-fill me-2"></i> 
          <span class="nav-text">Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $students_active; ?>" href="/admin/students/index.php">
          <i class="bi bi-people-fill me-2"></i> 
          <span class="nav-text">Data Siswa</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $finance_active; ?>" href="/admin/finance/index.php">
          <i class="bi bi-cash-stack me-2"></i> 
          <span class="nav-text">Keuangan</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $info_active; ?>" href="/admin/information/index.php">
          <i class="bi bi-info-circle-fill me-2"></i> 
          <span class="nav-text">Informasi</span>
        </a>
      </li>
    </ul>
  </nav>
</aside>
