<script>
function toggleUserDropdown(event) {
  event.stopPropagation();
  const menu = document.getElementById('userDropdownMenu');
  if (menu) {
    const isShowing = menu.style.display === 'block';
    menu.style.display = isShowing ? 'none' : 'block';
  }
}

function confirmLogout() {
  if (confirm('Apakah Anda yakin ingin keluar?')) {
    window.location.href = '/auth/logout.php';
  }
  return false;
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
  const menu = document.getElementById('userDropdownMenu');
  const dropdown = document.getElementById('userDropdown');
  
  if (menu && dropdown) {
    const isClickInsideDropdown = dropdown.contains(event.target);
    const isClickInsideMenu = menu.contains(event.target);
    
    if (!isClickInsideDropdown && !isClickInsideMenu && menu.style.display === 'block') {
      menu.style.display = 'none';
    }
  }
});
</script>
