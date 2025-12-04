<?php
/**
 * Main Layout Template
 * 
 * Usage:
 * $pageTitle = 'Dashboard';
 * $contentFile = __DIR__ . '/content.php';
 * include __DIR__ . '/../../shared/layouts/main.php';
 */

if (!isset($pageTitle)) $pageTitle = 'SMK BIT BINA AULIA';
if (!isset($sidebarType)) $sidebarType = 'admin'; // admin or student
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?> - MyBBA</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link href="/css/dashboard.css" rel="stylesheet">
  <link href="/css/custom-components.css" rel="stylesheet">
  <link href="/css/notifications.css" rel="stylesheet">
  
  <?php if (isset($additionalCSS)): ?>
    <?php foreach ($additionalCSS as $css): ?>
      <link href="<?php echo $css; ?>" rel="stylesheet">
    <?php endforeach; ?>
  <?php endif; ?>
</head>
<body class="has-sidebar">

  <?php 
  // Include appropriate sidebar
  if ($sidebarType === 'student') {
      include __DIR__ . '/../components/student_sidebar.php';
  } else {
      include __DIR__ . '/../components/sidebar.php';
  }
  ?>

  <div class="main-content">
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <main class="content-area">
      <?php 
      // Show flash messages
      $flash = getFlash();
      if ($flash): 
      ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($flash['message']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      
      <?php 
      // Include page content
      if (isset($contentFile) && file_exists($contentFile)) {
          include $contentFile;
      }
      ?>
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Custom JS -->
  <script src="/js/utils.js"></script>
  <script src="/js/notifications.js"></script>
  
  <?php if (isset($additionalJS)): ?>
    <?php foreach ($additionalJS as $js): ?>
      <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
