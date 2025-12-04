<style>
/* User dropdown styling */
#userDropdown {
  cursor: pointer !important;
  border-color: rgba(255,255,255,0.3) !important;
}

#userDropdown:hover {
  background-color: rgba(255,255,255,0.1) !important;
  border-color: rgba(255,255,255,0.5) !important;
}

.dropdown {
  position: relative;
}

#userDropdownMenu {
  position: absolute;
  right: 0;
  top: 100%;
  z-index: 1050;
  min-width: 200px;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  margin-top: 0.5rem !important;
  border-radius: 0.375rem;
  background-color: white;
  border: 1px solid rgba(0,0,0,.15);
  padding: 0.5rem 0;
  list-style: none;
}

.dropdown-item {
  padding: 0.5rem 1rem;
  transition: all 0.2s;
  cursor: pointer;
  display: block;
  width: 100%;
  color: #212529;
  text-decoration: none;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}

.dropdown-item.text-danger:hover {
  background-color: #fff5f5;
}

.dropdown-divider {
  height: 0;
  margin: 0.5rem 0;
  overflow: hidden;
  border-top: 1px solid #e9ecef;
}
</style>
