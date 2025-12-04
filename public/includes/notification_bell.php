<!-- Notification Bell -->
<div class="notification-bell">
  <button class="btn btn-link text-white btn-sm p-2" 
          type="button"
          onclick="notificationSystem.toggleDropdown()"
          aria-label="Notifications"
          style="text-decoration: none;">
    <i class="bi bi-bell-fill" style="font-size: 1.25rem;"></i>
    <span class="badge bg-danger" id="notificationBadge" style="display: none;">0</span>
  </button>
  
  <!-- Notification Dropdown -->
  <div class="notification-dropdown" id="notificationDropdown">
    <div class="notification-header">
      <div class="d-flex justify-content-between align-items-center">
        <h6><i class="bi bi-bell-fill me-2"></i>Notifikasi</h6>
        <button class="btn btn-sm btn-link text-white text-decoration-none p-0" 
                onclick="notificationSystem.markAllAsRead()"
                style="font-size: 0.75rem;">
          <i class="bi bi-check-all me-1"></i>Tandai Semua Dibaca
        </button>
      </div>
    </div>
    <div class="notification-body" id="notificationBody">
      <div class="notification-loading">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
  </div>
</div>
