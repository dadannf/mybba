/**
 * Notification System JavaScript
 * Auto-refresh notifikasi setiap 30 detik
 */

class NotificationSystem {
  constructor() {
    this.apiUrl = '/api/notifikasi.php';
    this.refreshInterval = 30000; // 30 detik
    this.notificationBell = document.querySelector('.notification-bell');
    this.notificationBadge = document.getElementById('notificationBadge');
    this.notificationDropdown = document.getElementById('notificationDropdown');
    this.notificationBody = document.getElementById('notificationBody');
    
    if (this.notificationBell && this.notificationBody) {
      this.init();
    }
  }

  init() {
    // Load notifikasi pertama kali
    this.loadNotifications();
    
    // Auto-refresh setiap 30 detik
    setInterval(() => this.loadNotifications(), this.refreshInterval);
    
    // Update badge count
    this.updateBadgeCount();
    setInterval(() => this.updateBadgeCount(), this.refreshInterval);
    
    // Close dropdown saat klik di luar
    document.addEventListener('click', (e) => {
      if (!this.notificationBell.contains(e.target)) {
        this.closeDropdown();
      }
    });
  }

  async loadNotifications() {
    try {
      const response = await fetch(`${this.apiUrl}?action=get&limit=10`);
      const data = await response.json();
      
      if (data.success) {
        this.renderNotifications(data.data);
      }
    } catch (error) {
      console.error('Error loading notifications:', error);
    }
  }

  async updateBadgeCount() {
    try {
      const response = await fetch(`${this.apiUrl}?action=count`);
      const data = await response.json();
      
      if (data.success && data.count > 0) {
        this.notificationBadge.textContent = data.count > 99 ? '99+' : data.count;
        this.notificationBadge.style.display = 'inline-block';
      } else {
        this.notificationBadge.style.display = 'none';
      }
    } catch (error) {
      console.error('Error updating badge count:', error);
    }
  }

  renderNotifications(notifications) {
    if (!notifications || notifications.length === 0) {
      this.notificationBody.innerHTML = `
        <div class="notification-empty">
          <i class="bi bi-bell-slash"></i>
          <p>Tidak ada notifikasi</p>
        </div>
      `;
      return;
    }

    const html = notifications.map(notif => this.createNotificationItem(notif)).join('');
    this.notificationBody.innerHTML = html;
  }

  createNotificationItem(notif) {
    const isUnread = notif.is_read == 0 ? 'unread' : '';
    const icon = notif.tipe === 'pembayaran' ? 
      '<i class="bi bi-credit-card"></i>' : 
      '<i class="bi bi-person-lines-fill"></i>';
    const iconClass = notif.tipe === 'pembayaran' ? 'payment' : 'update';
    const timeAgo = this.getTimeAgo(notif.created_at);

    return `
      <div class="notification-item ${isUnread}" 
           onclick="notificationSystem.handleNotificationClick(${notif.notifikasi_id}, '${notif.link || '#'}')">
        <div class="d-flex align-items-start">
          <div class="notification-icon ${iconClass}">
            ${icon}
          </div>
          <div class="notification-content">
            <div class="notification-title">${this.escapeHtml(notif.judul)}</div>
            <div class="notification-message">${this.escapeHtml(notif.pesan)}</div>
            <div class="notification-time">
              <i class="bi bi-clock"></i> ${timeAgo}
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async handleNotificationClick(notifId, link) {
    // Tandai sebagai sudah dibaca
    await this.markAsRead(notifId);
    
    // Redirect ke link
    if (link && link !== '#') {
      window.location.href = link;
    }
  }

  async markAsRead(notifId) {
    try {
      const formData = new FormData();
      formData.append('action', 'mark_read');
      formData.append('notifikasi_id', notifId);

      const response = await fetch(this.apiUrl, {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      if (data.success) {
        this.loadNotifications();
        this.updateBadgeCount();
      }
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  }

  async markAllAsRead() {
    try {
      const formData = new FormData();
      formData.append('action', 'mark_all_read');

      const response = await fetch(this.apiUrl, {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      if (data.success) {
        this.loadNotifications();
        this.updateBadgeCount();
      }
    } catch (error) {
      console.error('Error marking all as read:', error);
    }
  }

  toggleDropdown() {
    this.notificationDropdown.classList.toggle('show');
    if (this.notificationDropdown.classList.contains('show')) {
      this.loadNotifications();
    }
  }

  closeDropdown() {
    this.notificationDropdown.classList.remove('show');
  }

  getTimeAgo(timestamp) {
    const now = new Date();
    const notifTime = new Date(timestamp);
    const diffMs = now - notifTime;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins} menit yang lalu`;
    if (diffHours < 24) return `${diffHours} jam yang lalu`;
    if (diffDays < 7) return `${diffDays} hari yang lalu`;
    
    return notifTime.toLocaleDateString('id-ID', { 
      day: 'numeric', 
      month: 'short', 
      year: 'numeric' 
    });
  }

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize notification system saat DOM ready
let notificationSystem;
document.addEventListener('DOMContentLoaded', function() {
  notificationSystem = new NotificationSystem();
});
