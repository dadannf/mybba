<?php
/**
 * Shared Component: Top Navbar
 * 
 * Usage: include __DIR__ . '/../../shared/components/navbar.php';
 */

$pageTitle = isset($pageTitle) ? $pageTitle : 'SMK BIT BINA AULIA';
$userName = isset($_SESSION['nama']) ? $_SESSION['nama'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'User');
?>
<nav class="topbar">
  <div class="container-fluid d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <button id="sidebarToggle" class="btn btn-toggle btn-sm me-3" aria-label="Toggle sidebar">☰</button>
      <h1 class="app-title mb-0"><?php echo htmlspecialchars($pageTitle); ?></h1>
    </div>
    
    <?php include __DIR__ . '/../includes/notification_bell.php'; ?>
    <?php include __DIR__ . '/../includes/user_dropdown.php'; ?>
  </div>
</nav>

<script>
// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function() {
      sidebar.classList.toggle('collapsed');
      document.body.classList.toggle('sidebar-collapsed');
    });
  }
});
</script>
