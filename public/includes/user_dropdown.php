<!-- Notification Bell & User Dropdown -->
<div class="d-flex align-items-center gap-2">
  <?php include __DIR__ . '/notification_bell.php'; ?>
  
  <!-- User Dropdown Button -->
  <div class="dropdown">
    <button class="btn btn-outline-light btn-sm d-flex align-items-center gap-2" 
            type="button" 
            id="userDropdown" 
            onclick="toggleUserDropdown(event)">
      <span class="d-none d-md-inline" style="font-size: 0.875rem;"><?php echo isset($adminName) ? htmlspecialchars($adminName) : 'User'; ?></span>
      <i class="bi bi-person-circle" style="font-size: 1.25rem;"></i>
      <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow" id="userDropdownMenu" style="display: none;">
      <li>
        <a class="dropdown-item" href="#" onclick="alert('Fitur Settings belum tersedia'); return false;">
          <i class="bi bi-gear me-2"></i> Settings
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <a class="dropdown-item text-danger" href="#" onclick="confirmLogout(); return false;">
          <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</div>

