<!-- =============================================
     RESPONSIVE SCRIPTS - Universal JavaScript
     Include di semua halaman untuk responsive behavior
     ============================================= -->
<script>
// =============================================
// Mobile Sidebar Toggle
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Create mobile toggle button if not exists
    if (window.innerWidth < 768 && !document.querySelector('.btn-toggle-mobile')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'btn-toggle-mobile';
        toggleBtn.innerHTML = '<i class="bi bi-list"></i>';
        toggleBtn.setAttribute('aria-label', 'Toggle Menu');
        document.body.appendChild(toggleBtn);
        
        // Toggle sidebar on mobile
        toggleBtn.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });
    }
    
    // Close sidebar when clicking overlay
    const overlay = document.querySelector('.overlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            document.body.classList.remove('sidebar-open');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.btn-toggle-mobile');
            const btnToggle = document.querySelector('.btn-toggle');
            
            if (sidebar && !sidebar.contains(e.target) && 
                e.target !== toggleBtn && 
                e.target !== btnToggle &&
                !e.target.closest('.btn-toggle-mobile') &&
                !e.target.closest('.btn-toggle')) {
                document.body.classList.remove('sidebar-open');
            }
        }
    });
});

// =============================================
// Responsive Table Enhancement
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Wrap tables in responsive container
    const tables = document.querySelectorAll('table:not(.no-responsive)');
    tables.forEach(function(table) {
        if (!table.closest('.table-responsive') && !table.closest('.table-container')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    // Add horizontal scroll indicator for mobile
    if (window.innerWidth < 768) {
        const tableContainers = document.querySelectorAll('.table-responsive, .table-container');
        tableContainers.forEach(function(container) {
            container.addEventListener('scroll', function() {
                if (this.scrollLeft > 0) {
                    this.classList.add('scrolled');
                } else {
                    this.classList.remove('scrolled');
                }
            });
        });
    }
});

// =============================================
// Responsive Form Enhancement
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Make button groups responsive on mobile
    if (window.innerWidth < 576) {
        const btnGroups = document.querySelectorAll('.btn-group:not(.btn-group-responsive)');
        btnGroups.forEach(function(group) {
            group.classList.add('btn-group-responsive');
        });
    }
});

// =============================================
// Viewport Change Handler
// =============================================
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        // Close sidebar when resizing from mobile to desktop
        if (window.innerWidth >= 768) {
            document.body.classList.remove('sidebar-open');
            
            // Remove mobile toggle button on desktop
            const mobileToggle = document.querySelector('.btn-toggle-mobile');
            if (mobileToggle) {
                mobileToggle.style.display = 'none';
            }
        } else {
            // Show mobile toggle button on mobile
            const mobileToggle = document.querySelector('.btn-toggle-mobile');
            if (mobileToggle) {
                mobileToggle.style.display = 'block';
            }
        }
        
        // Recalculate table responsiveness
        const tables = document.querySelectorAll('.table-responsive');
        tables.forEach(function(table) {
            if (table.scrollWidth > table.clientWidth) {
                table.classList.add('has-horizontal-scroll');
            } else {
                table.classList.remove('has-horizontal-scroll');
            }
        });
    }, 250);
});

// =============================================
// Touch Device Optimization
// =============================================
if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
    document.body.classList.add('touch-device');
    
    // Improve tap targets on touch devices
    const clickableElements = document.querySelectorAll('a, button, .btn, .nav-link');
    clickableElements.forEach(function(el) {
        const styles = window.getComputedStyle(el);
        const minHeight = parseInt(styles.minHeight) || 0;
        const minWidth = parseInt(styles.minWidth) || 0;
        
        if (minHeight < 44) {
            el.style.minHeight = '44px';
        }
        if (minWidth < 44) {
            el.style.minWidth = '44px';
        }
    });
}

// =============================================
// Orientation Change Handler
// =============================================
window.addEventListener('orientationchange', function() {
    // Force recalculation after orientation change
    setTimeout(function() {
        window.dispatchEvent(new Event('resize'));
    }, 100);
});

// =============================================
// Prevent Horizontal Scroll
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Find and fix elements causing horizontal scroll
    function checkHorizontalOverflow() {
        const body = document.body;
        const html = document.documentElement;
        
        const scrollWidth = Math.max(body.scrollWidth, html.scrollWidth);
        const clientWidth = Math.max(body.clientWidth, html.clientWidth);
        
        if (scrollWidth > clientWidth) {
            // Find offending elements
            const allElements = document.querySelectorAll('*');
            allElements.forEach(function(el) {
                if (el.scrollWidth > el.clientWidth && 
                    el.scrollWidth > clientWidth) {
                    // Log in console for debugging
                    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                        console.warn('Element causing horizontal scroll:', el);
                    }
                }
            });
        }
    }
    
    // Check on load and resize
    checkHorizontalOverflow();
    window.addEventListener('resize', checkHorizontalOverflow);
});

// =============================================
// Responsive Image Loading
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Lazy load images on mobile for performance
    if ('IntersectionObserver' in window && window.innerWidth < 768) {
        const imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    imageObserver.unobserve(img);
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    }
});

// =============================================
// Console Info (Development Only)
// =============================================
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    console.log('%c Responsive System Loaded ', 'background: #0b63a8; color: white; padding: 5px 10px; border-radius: 3px;');
    console.log('Viewport:', window.innerWidth + 'x' + window.innerHeight);
    console.log('Device Type:', 'ontouchstart' in window ? 'Touch' : 'Non-touch');
    console.log('Pixel Ratio:', window.devicePixelRatio);
}
</script>
