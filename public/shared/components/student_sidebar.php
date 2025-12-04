<?php
/**
 * Shared Component: Student Sidebar Navigation
 * 
 * Usage: include __DIR__ . '/../../shared/components/student_sidebar.php';
 */

$current_page = basename($_SERVER['PHP_SELF']);
$dashboard_active = ($current_page == 'index.php') ? 'active' : '';
$finance_active = ($current_page == 'finance.php') ? 'active' : '';
$profile_active = ($current_page == 'profile.php') ? 'active' : '';

$studentName = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Siswa';
$studentNIS = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header text-center py-4">
    <div class="profile-avatar mb-2">
      <svg width="54" height="54" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="8" r="4" fill="#fff" opacity="0.9"/>
        <path d="M4 20c0-4 4-7 8-7s8 3 8 7" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <div class="profile-name"><?php echo htmlspecialchars($studentName); ?></div>
    <div class="profile-role text-muted">NIS: <?php echo htmlspecialchars($studentNIS); ?></div>
  </div>

  <nav class="sidebar-nav" role="navigation" aria-label="Main menu">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?php echo $dashboard_active; ?>" href="/student/index.php">
          <i class="bi bi-house-door-fill me-2"></i> 
          <span class="nav-text">Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $finance_active; ?>" href="/student/finance.php">
          <i class="bi bi-cash-stack me-2"></i> 
          <span class="nav-text">Keuangan</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $profile_active; ?>" href="/student/profile.php">
          <i class="bi bi-person-fill me-2"></i> 
          <span class="nav-text">Profil Saya</span>
        </a>
      </li>
    </ul>
  </nav>
</aside>
