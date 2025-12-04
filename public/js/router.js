// =============================================
// SPA Router - Handle navigasi tanpa reload halaman
// =============================================

class SPARouter {
    constructor() {
        console.log('SPARouter initialized');

        // Gunakan path relatif dari lokasi index.php
        this.routes = {
            'dashboard': 'views/dashboard.php',
            'data-siswa': 'views/data-siswa.php',
            'keuangan': 'views/keuangan.php',
            'informasi': 'views/informasi.php'
        };

        this.contentArea = document.getElementById('app-content');

        if (!this.contentArea) {
            console.error('Content area #app-content not found!');
            return;
        }

        console.log('Content area found:', this.contentArea);
        this.init();
    }

    init() {
        console.log('Router init() called');

        // Listen for hash changes
        window.addEventListener('hashchange', () => {
            console.log('Hash changed to:', window.location.hash);
            this.loadRoute();
        });

        // Load initial route
        console.log('Loading initial route, hash:', window.location.hash);
        this.loadRoute();
    }

    getRoute() {
        const hash = window.location.hash.slice(1) || '/dashboard';
        // Split hash untuk mendapatkan route dan query params
        const [route, queryString] = hash.split('?');
        const routeName = route.replace('/', '');
        return {
            name: routeName || 'dashboard',
            query: queryString || ''
        };
    }

    async loadRoute() {
        const route = this.getRoute();
        const viewPath = this.routes[route.name] || this.routes['dashboard'];

        // Tambahkan query params jika ada
        const fullPath = route.query ? `${viewPath}?${route.query}` : viewPath;

        // Debug log
        console.log('Loading route:', route.name, 'Path:', fullPath);

        // Update active nav link
        this.updateActiveNav(route.name);

        // Show loading state
        this.showLoading();

        try {
            const response = await fetch(fullPath);

            if (!response.ok) {
                console.error('Failed to load:', fullPath, 'Status:', response.status);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const html = await response.text();
            console.log('Loaded HTML length:', html.length);
            this.contentArea.innerHTML = html;

            // Execute any scripts in the loaded content
            this.executeScripts();

            // Scroll to top
            window.scrollTo(0, 0);

        } catch (error) {
            console.error('Error loading route:', error);
            this.showError();
        }
    }

    showLoading() {
        this.contentArea.innerHTML = `
      <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    `;
    }

    showError() {
        this.contentArea.innerHTML = `
      <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Error!</strong> Gagal memuat halaman. Silakan coba lagi.
      </div>
    `;
    }

    updateActiveNav(route) {
        // Remove active class from all nav links
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Add active class to current route
        const activeLink = document.querySelector(`[data-route="${route}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }

    executeScripts() {
        // Find and execute any script tags in the loaded content
        const scripts = this.contentArea.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');

            if (oldScript.src) {
                newScript.src = oldScript.src;
            } else {
                newScript.textContent = oldScript.textContent;
            }

            // Replace old script with new one to trigger execution
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }
}

// Initialize router when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded - Initializing router...');
    window.router = new SPARouter();
    console.log('Router instance created:', window.router);
});
