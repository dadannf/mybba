<!-- =============================================
     UNIVERSAL RESPONSIVE SETUP
     Include this at the bottom of <body> tag
     ============================================= -->
<script>
/**
 * Universal Responsive Manager
 * Handles sidebar toggle, table wrapping, and mobile optimizations
 */
(function() {
    'use strict';
    
    // ==========================================
    // Sidebar Toggle for Mobile
    // ==========================================
    function initSidebarToggle() {
        const sidebar = document.querySelector('.sidebar');
        const btnToggle = document.querySelector('.btn-toggle');
        const mainWrapper = document.querySelector('.main-wrapper');
        
        if (!sidebar) return;
        
        // Create overlay if not exists
        let overlay = document.querySelector('.overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'overlay';
            document.body.appendChild(overlay);
        }
        
        // Toggle function
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-open');
        }
        
        // Button toggle click
        if (btnToggle) {
            btnToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }
        
        // Overlay click to close
        overlay.addEventListener('click', function() {
            document.body.classList.remove('sidebar-open');
        });
        
        // Close sidebar when clicking nav link on mobile
        if (window.innerWidth < 768) {
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    document.body.classList.remove('sidebar-open');
                });
            });
        }
        
        // Close sidebar on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.body.classList.remove('sidebar-open');
            }
        });
    }
    
    // ==========================================
    // Wrap Tables in Responsive Container
    // ==========================================
    function wrapTablesResponsive() {
        const tables = document.querySelectorAll('table:not(.no-responsive)');
        
        tables.forEach(function(table) {
            // Skip if already wrapped
            if (table.closest('.table-responsive') || table.closest('.table-container')) {
                return;
            }
            
            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            
            // Wrap table
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
            
            // Add Bootstrap table class if not exists
            if (!table.classList.contains('table')) {
                table.classList.add('table');
            }
        });
    }
    
    // ==========================================
    // Table Scroll Indicator for Mobile
    // ==========================================
    function initTableScrollIndicator() {
        if (window.innerWidth >= 768) return;
        
        const tableContainers = document.querySelectorAll('.table-responsive');
        
        tableContainers.forEach(function(container) {
            const table = container.querySelector('table');
            if (!table) return;
            
            // Check if table is scrollable
            if (table.scrollWidth > container.clientWidth) {
                container.classList.add('has-horizontal-scroll');
            }
            
            // Add scroll event listener
            container.addEventListener('scroll', function() {
                if (this.scrollLeft > 10) {
                    this.classList.add('scrolled');
                } else {
                    this.classList.remove('scrolled');
                }
            });
        });
    }
    
    // ==========================================
    // Form Enhancements for Mobile
    // ==========================================
    function enhanceFormsForMobile() {
        if (window.innerWidth >= 576) return;
        
        // Make button groups stack vertically
        const btnGroups = document.querySelectorAll('.btn-group:not(.btn-group-vertical)');
        btnGroups.forEach(function(group) {
            if (!group.classList.contains('no-stack')) {
                group.classList.add('flex-column');
                group.classList.add('gap-2');
            }
        });
        
        // Make action button groups stack
        const actionBtns = document.querySelectorAll('.d-flex.gap-2:has(.btn)');
        actionBtns.forEach(function(container) {
            container.style.flexDirection = 'column';
        });
    }
    
    // ==========================================
    // Optimize Images for Mobile
    // ==========================================
    function optimizeImagesForMobile() {
        const images = document.querySelectorAll('img:not([data-optimized])');
        
        images.forEach(function(img) {
            // Set max-width
            if (!img.style.maxWidth) {
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
            }
            
            // Mark as optimized
            img.setAttribute('data-optimized', 'true');
        });
    }
    
    // ==========================================
    // Fix Modals for Mobile
    // ==========================================
    function fixModalsForMobile() {
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(function(modal) {
            if (window.innerWidth < 576) {
                const modalDialog = modal.querySelector('.modal-dialog');
                if (modalDialog && !modalDialog.classList.contains('modal-fullscreen-sm-down')) {
                    modalDialog.style.maxWidth = '95%';
                    modalDialog.style.margin = '0.5rem auto';
                }
            }
        });
    }
    
    // ==========================================
    // Prevent Horizontal Scroll
    // ==========================================
    function preventHorizontalScroll() {
        const bodyWidth = document.body.scrollWidth;
        const windowWidth = window.innerWidth;
        
        if (bodyWidth > windowWidth) {
            // Find elements causing overflow
            const allElements = document.querySelectorAll('*');
            allElements.forEach(function(el) {
                const rect = el.getBoundingClientRect();
                if (rect.right > windowWidth || rect.left < 0) {
                    // Add class to identify problematic elements
                    if (window.location.hostname === 'localhost' || 
                        window.location.hostname === '127.0.0.1') {
                        el.setAttribute('data-overflow', 'true');
                    }
                }
            });
        }
    }
    
    // ==========================================
    // Add Touch Class for Touch Devices
    // ==========================================
    function detectTouchDevice() {
        if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
            document.body.classList.add('touch-device');
        } else {
            document.body.classList.add('no-touch');
        }
    }
    
    // ==========================================
    // Initialize All Functions
    // ==========================================
    function init() {
        detectTouchDevice();
        initSidebarToggle();
        wrapTablesResponsive();
        initTableScrollIndicator();
        enhanceFormsForMobile();
        optimizeImagesForMobile();
        fixModalsForMobile();
        preventHorizontalScroll();
        
        // Log initialization
        if (window.location.hostname === 'localhost' || 
            window.location.hostname === '127.0.0.1') {
            console.log('✅ Responsive Manager Initialized');
            console.log('Viewport: ' + window.innerWidth + 'x' + window.innerHeight);
            console.log('Device: ' + (document.body.classList.contains('touch-device') ? 'Touch' : 'Desktop'));
        }
    }
    
    // ==========================================
    // Run on DOM Ready
    // ==========================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // ==========================================
    // Re-run on Window Resize (debounced)
    // ==========================================
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initTableScrollIndicator();
            enhanceFormsForMobile();
            fixModalsForMobile();
        }, 250);
    });
    
    // ==========================================
    // Re-run on Orientation Change
    // ==========================================
    window.addEventListener('orientationchange', function() {
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 100);
    });
    
})();
</script>
