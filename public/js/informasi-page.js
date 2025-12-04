/**
 * =============================================
 * Informasi Page - Interactive Features
 * =============================================
 * Enhanced functionality untuk halaman informasi
 */

(function() {
  'use strict';

  // ============================================
  // Image Preview untuk Modal Tambah
  // ============================================
  const initAddModalPreview = () => {
    const fileInput = document.getElementById('fileInput');
    const previewArea = document.getElementById('previewArea');
    const imagePreview = document.getElementById('imagePreview');
    
    if (!fileInput) return;
    
    fileInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      
      if (!file) {
        previewArea?.classList.add('d-none');
        return;
      }
      
      const fileType = file.type;
      const fileSize = file.size;
      
      // Check file size (5MB limit)
      if (fileSize > 5 * 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 5MB.');
        fileInput.value = '';
        previewArea?.classList.add('d-none');
        return;
      }
      
      // Preview image
      if (fileType.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          if (imagePreview && previewArea) {
            imagePreview.src = e.target.result;
            previewArea.classList.remove('d-none');
          }
        }
        reader.readAsDataURL(file);
      } else {
        previewArea?.classList.add('d-none');
      }
    });
  };

  // ============================================
  // Image Preview untuk Modal Edit
  // ============================================
  const initEditModalPreview = () => {
    const editFileInput = document.getElementById('editFileInput');
    const editPreviewArea = document.getElementById('editPreviewArea');
    const editImagePreview = document.getElementById('editImagePreview');
    
    if (!editFileInput) return;
    
    editFileInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      
      if (!file) {
        editPreviewArea?.classList.add('d-none');
        return;
      }
      
      const fileType = file.type;
      const fileSize = file.size;
      
      // Check file size (5MB limit)
      if (fileSize > 5 * 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 5MB.');
        editFileInput.value = '';
        editPreviewArea?.classList.add('d-none');
        return;
      }
      
      // Preview image
      if (fileType.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          if (editImagePreview && editPreviewArea) {
            editImagePreview.src = e.target.result;
            editPreviewArea.classList.remove('d-none');
          }
        }
        reader.readAsDataURL(file);
      } else {
        editPreviewArea?.classList.add('d-none');
      }
    });
  };

  // ============================================
  // Character Counter untuk Input Judul
  // ============================================
  const initCharacterCounter = () => {
    const judulInputs = document.querySelectorAll('input[name="judul"]');
    
    judulInputs.forEach(input => {
      const maxLength = parseInt(input.getAttribute('maxlength')) || 200;
      
      input.addEventListener('input', function() {
        const currentLength = this.value.length;
        const remaining = maxLength - currentLength;
        
        // Visual feedback
        if (remaining < 20 && remaining > 0) {
          this.style.borderColor = '#e67e22'; // orange
          this.style.borderWidth = '2px';
        } else if (remaining === 0) {
          this.style.borderColor = '#e74c3c'; // red
          this.style.borderWidth = '2px';
        } else {
          this.style.borderColor = '';
          this.style.borderWidth = '';
        }
      });
    });
  };

  // ============================================
  // Auto-dismiss Alerts
  // ============================================
  const initAlertAutoDismiss = () => {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
      setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert?.close();
      }, 5000); // 5 seconds
    });
  };

  // ============================================
  // Smooth Scroll ke Alert
  // ============================================
  const scrollToAlert = () => {
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
      setTimeout(() => {
        alerts[0].scrollIntoView({ 
          behavior: 'smooth', 
          block: 'nearest' 
        });
      }, 100);
    }
  };

  // ============================================
  // Form Change Detection
  // ============================================
  const initFormChangeDetection = () => {
    let formChanged = false;
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
      const inputs = form.querySelectorAll('input[type="text"], textarea, input[type="file"]');
      
      inputs.forEach(input => {
        input.addEventListener('input', () => {
          formChanged = true;
        });
      });
      
      form.addEventListener('submit', () => {
        formChanged = false;
      });
    });
    
    // Reset saat modal ditutup
    document.querySelectorAll('.modal').forEach(modal => {
      modal.addEventListener('hidden.bs.modal', () => {
        formChanged = false;
        
        // Reset form dalam modal
        const form = modal.querySelector('form');
        if (form && !form.classList.contains('keep-data')) {
          form.reset();
          
          // Reset preview areas
          const previewAreas = modal.querySelectorAll('[id*="preview"]');
          previewAreas.forEach(area => area.classList.add('d-none'));
        }
      });
    });
  };

  // ============================================
  // Keyboard Shortcuts
  // ============================================
  const initKeyboardShortcuts = () => {
    document.addEventListener('keydown', (e) => {
      // Ctrl/Cmd + K = Focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="search"]');
        searchInput?.focus();
      }
      
      // Ctrl/Cmd + N = New Info (open modal)
      if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        const addButton = document.querySelector('[data-bs-target="#modalTambah"]');
        addButton?.click();
      }
    });
  };

  // ============================================
  // Tooltip Initialization
  // ============================================
  const initTooltips = () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => 
      new bootstrap.Tooltip(tooltipTriggerEl)
    );
  };

  // ============================================
  // Card Entrance Animation Observer
  // ============================================
  const initCardAnimation = () => {
    const cards = document.querySelectorAll('.info-card');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1
    });
    
    cards.forEach(card => {
      observer.observe(card);
    });
  };

  // ============================================
  // Search Input Enhancement
  // ============================================
  const initSearchEnhancement = () => {
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput) return;
    
    // Clear button
    const clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'btn btn-sm btn-link text-muted position-absolute end-0 top-50 translate-middle-y me-5';
    clearBtn.innerHTML = '<i class="bi bi-x-circle"></i>';
    clearBtn.style.display = 'none';
    clearBtn.style.zIndex = '10';
    
    searchInput.parentElement.style.position = 'relative';
    searchInput.parentElement.appendChild(clearBtn);
    
    searchInput.addEventListener('input', () => {
      clearBtn.style.display = searchInput.value ? 'block' : 'none';
    });
    
    clearBtn.addEventListener('click', () => {
      searchInput.value = '';
      clearBtn.style.display = 'none';
      searchInput.focus();
    });
  };

  // ============================================
  // Initialize Everything
  // ============================================
  const init = () => {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
      return;
    }
    
    initAddModalPreview();
    initEditModalPreview();
    initCharacterCounter();
    initAlertAutoDismiss();
    scrollToAlert();
    initFormChangeDetection();
    initKeyboardShortcuts();
    initTooltips();
    initCardAnimation();
    initSearchEnhancement();
    
    console.log('✅ Informasi Page: All features initialized');
  };

  // Start initialization
  init();

})();

// ============================================
// Global Function untuk Edit (dipanggil dari PHP)
// ============================================
window.editInfo = function(info) {
  // Set values
  document.getElementById('edit_info_id').value = info.informasi_id;
  document.getElementById('edit_judul').value = info.judul;
  document.getElementById('edit_isi').value = info.isi;
  
  // Reset preview area
  const editPreviewArea = document.getElementById('editPreviewArea');
  if (editPreviewArea) {
    editPreviewArea.classList.add('d-none');
  }
  
  // Display current foto
  const currentFotoDiv = document.getElementById('current_foto');
  if (!currentFotoDiv) return;
  
  if (info.foto) {
    const fileExt = info.foto.split('.').pop().toLowerCase();
    const fileName = info.foto.split('/').pop();
    
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
      currentFotoDiv.innerHTML = `
        <div class="current-file-display">
          <div class="d-flex align-items-center gap-3">
            <img src="/${info.foto}" 
                 style="max-width: 150px; max-height: 150px; border-radius: 8px; object-fit: cover;" 
                 class="preview-image"
                 alt="${info.judul}">
            <div class="flex-grow-1">
              <div class="badge badge-gradient-blue mb-2">
                <i class="bi bi-image-fill me-1"></i> Gambar
              </div>
              <div class="text-muted small mb-2">${fileName}</div>
              <a href="/${info.foto}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye me-1"></i> Lihat Ukuran Penuh
              </a>
            </div>
          </div>
        </div>
      `;
    } else if (fileExt === 'pdf') {
      currentFotoDiv.innerHTML = `
        <div class="current-file-display">
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded" 
                 style="min-width: 100px; height: 100px;">
              <i class="bi bi-file-pdf-fill text-danger" style="font-size: 3rem;"></i>
            </div>
            <div class="flex-grow-1">
              <div class="badge bg-danger mb-2">
                <i class="bi bi-file-pdf-fill me-1"></i> PDF Document
              </div>
              <div class="text-muted small mb-2">${fileName}</div>
              <a href="/${info.foto}" target="_blank" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-download me-1"></i> Unduh File
              </a>
            </div>
          </div>
        </div>
      `;
    } else {
      currentFotoDiv.innerHTML = `
        <div class="current-file-display">
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 rounded" 
                 style="min-width: 100px; height: 100px;">
              <i class="bi bi-file-earmark text-info" style="font-size: 3rem;"></i>
            </div>
            <div class="flex-grow-1">
              <div class="badge bg-info mb-2">
                <i class="bi bi-file-earmark me-1"></i> File
              </div>
              <div class="text-muted small mb-2">${fileName}</div>
              <a href="/${info.foto}" target="_blank" class="btn btn-sm btn-outline-info">
                <i class="bi bi-eye me-1"></i> Lihat File
              </a>
            </div>
          </div>
        </div>
      `;
    }
  } else {
    currentFotoDiv.innerHTML = `
      <div class="current-file-display text-center py-4">
        <i class="bi bi-file-earmark-x text-muted" style="font-size: 2.5rem; opacity: 0.5;"></i>
        <p class="mb-0 mt-2 text-muted">Tidak ada lampiran</p>
      </div>
    `;
  }
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
  modal.show();
};

// ============================================
// Global Function untuk View Detail (dipanggil dari PHP)
// ============================================
window.viewInfoDetail = function(info) {
  // Debug: Log info data untuk troubleshooting
  console.log('Info Data:', info);
  console.log('Foto Path:', info.foto);
  
  // Set title
  document.getElementById('detail_title').textContent = info.judul;
  
  // Set date and author
  const date = new Date(info.created_at);
  const options = { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };
  document.getElementById('detail_date').textContent = date.toLocaleDateString('id-ID', options);
  document.getElementById('detail_author').textContent = info.created_by;
  
  // Set content with proper line breaks
  document.getElementById('detail_content').innerHTML = info.isi.replace(/\n/g, '<br>');
  
  // Set header image/placeholder
  const detailHeader = document.getElementById('detail_header');
  if (info.foto) {
    const fileExt = info.foto.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
    
    if (isImage) {
      // Cek apakah path sudah include leading slash atau tidak
      const fotoPath = info.foto.startsWith('/') ? info.foto : '/' + info.foto;
      
      detailHeader.innerHTML = `
        <img src="${fotoPath}" 
             style="width: 100%; height: 100%; object-fit: cover;" 
             alt="${info.judul}"
             onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22><rect width=%22400%22 height=%22300%22 fill=%22%23ecf0f1%22/><text x=%2250%25%22 y=%2250%25%22 font-family=%22Arial%22 font-size=%2220%22 fill=%22%23999%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22>Foto tidak dapat dimuat</text></svg>';">
        <div class="position-absolute bottom-0 start-0 end-0 p-3" 
             style="background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);">
          <span class="badge bg-light text-dark">
            <i class="bi bi-image-fill me-1"></i> Foto Terlampir
          </span>
        </div>
      `;
    } else if (fileExt === 'pdf') {
      detailHeader.innerHTML = `
        <div class="d-flex align-items-center justify-content-center h-100" 
             style="background-color: #5dade2;">
          <div class="text-white text-center">
            <i class="bi bi-file-earmark-pdf-fill" style="font-size: 5rem; opacity: 0.8;"></i>
            <p class="mt-3 mb-0 fw-semibold fs-5">Dokumen PDF Terlampir</p>
          </div>
        </div>
      `;
    } else {
      detailHeader.innerHTML = `
        <div class="d-flex align-items-center justify-content-center h-100" 
             style="background-color: #5dade2;">
          <div class="text-white text-center">
            <i class="bi bi-file-earmark-fill" style="font-size: 5rem; opacity: 0.8;"></i>
            <p class="mt-3 mb-0 fw-semibold fs-5">File Terlampir</p>
          </div>
        </div>
      `;
    }
  } else {
    detailHeader.innerHTML = `
      <div class="d-flex align-items-center justify-content-center h-100" 
           style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);">
        <div class="text-white text-center">
          <i class="bi bi-megaphone-fill" style="font-size: 5rem; opacity: 0.8;"></i>
          <p class="mt-3 mb-0 fw-semibold fs-5">Pengumuman Sekolah</p>
        </div>
      </div>
    `;
  }
  
  // Set attachment section
  const detailAttachment = document.getElementById('detail_attachment');
  if (info.foto) {
    const fileExt = info.foto.split('.').pop().toLowerCase();
    const fileName = info.foto.split('/').pop();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
    const fotoPath = info.foto.startsWith('/') ? info.foto : '/' + info.foto;
    
    detailAttachment.innerHTML = `
      <div class="border rounded p-3" style="background-color: #f8f9fa;">
        <h6 class="fw-bold mb-3">
          <i class="bi bi-paperclip me-2"></i>Lampiran File
        </h6>
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded" 
               style="width: 60px; height: 60px; background-color: ${isImage ? '#3498db' : fileExt === 'pdf' ? '#e74c3c' : '#95a5a6'};">
            <i class="bi bi-${isImage ? 'image-fill' : fileExt === 'pdf' ? 'file-pdf-fill' : 'file-earmark-fill'} text-white" 
               style="font-size: 1.8rem;"></i>
          </div>
          <div class="flex-grow-1">
            <div class="fw-semibold">${fileName}</div>
            <small class="text-muted">${isImage ? 'Gambar' : fileExt === 'pdf' ? 'Dokumen PDF' : 'File'}</small>
          </div>
          <a href="${fotoPath}" target="_blank" class="btn btn-primary">
            <i class="bi bi-${isImage ? 'eye' : 'download'} me-1"></i> 
            ${isImage ? 'Lihat' : 'Unduh'}
          </a>
        </div>
      </div>
    `;
  } else {
    detailAttachment.innerHTML = '';
  }
  
  // Store info for edit button
  window.currentInfoData = info;
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('modalDetail'));
  modal.show();
};

// ============================================
// Function untuk membuka Edit dari Detail Modal
// ============================================
window.openEditFromDetail = function() {
  // Close detail modal
  const detailModal = bootstrap.Modal.getInstance(document.getElementById('modalDetail'));
  if (detailModal) {
    detailModal.hide();
  }
  
  // Open edit modal with current data
  setTimeout(() => {
    if (window.currentInfoData) {
      editInfo(window.currentInfoData);
    }
  }, 300); // Wait for detail modal to close
};
