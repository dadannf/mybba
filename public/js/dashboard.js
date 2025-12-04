/* ==================================================
   dashboard.js
   Komentar (bahasa Indonesia) untuk membatasi antar komponen:
   - Inisialisasi elemen
   - Fungsi setCollapsed untuk mengatur state sidebar
   - Event handler toggle (desktop/mobile)
   - Overlay handling (klik di luar / klik overlay)
   - Resize handler untuk menyesuaikan state
   ================================================== */

document.addEventListener('DOMContentLoaded', function () {
    // ----- Inisialisasi elemen -----
    const toggle = document.getElementById('sidebarToggle');
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    // Kunci localStorage untuk menyimpan preferensi pengguna
    const STATE_KEY = 'bbs_admin_sidebar_collapsed';

    // ----- Fungsi bantu: setCollapsed -----
    // Mengatur kelas pada <body>, teks tombol dan atribut aria
    function setCollapsed(collapsed) {
        if (collapsed) {
            body.classList.add('sidebar-collapsed');
            if (window.innerWidth <= 767) body.classList.remove('sidebar-open');
            toggle.textContent = '☰'; // saat disusutkan, tampilkan burger
            localStorage.setItem(STATE_KEY, '1');
            toggle.setAttribute('aria-expanded', 'false');
        } else {
            body.classList.remove('sidebar-collapsed');
            toggle.textContent = '✖'; // saat terbuka, tampilkan X
            localStorage.setItem(STATE_KEY, '0');
            toggle.setAttribute('aria-expanded', 'true');
        }
    }

    // ----- Inisialisasi state dari localStorage -----
    const stored = localStorage.getItem(STATE_KEY);
    if (stored === '1') setCollapsed(true); else setCollapsed(false);

    // ----- Event: klik toggle -----
    toggle.addEventListener('click', function (e) {
        // Mobile: buka/tutup sidebar sebagai overlay
        if (window.innerWidth <= 767) {
            if (body.classList.contains('sidebar-open')) {
                body.classList.remove('sidebar-open');
                if (overlay) overlay.classList.add('d-none');
                toggle.textContent = '☰';
                toggle.setAttribute('aria-expanded', 'false');
            } else {
                body.classList.add('sidebar-open');
                if (overlay) overlay.classList.remove('d-none');
                toggle.textContent = '✖';
                toggle.setAttribute('aria-expanded', 'true');
            }
            return;
        }

        // Desktop: collapse/expand
        const collapsed = body.classList.toggle('sidebar-collapsed');
        setCollapsed(collapsed);
    });

    // ----- Tutup overlay bila klik di luar sidebar (mobile) -----
    document.addEventListener('click', function (e) {
        if (window.innerWidth > 767) return; // hanya mobile
        if (!body.classList.contains('sidebar-open')) return;
        if (sidebar.contains(e.target) || e.target === toggle) return;
        body.classList.remove('sidebar-open');
        if (overlay) overlay.classList.add('d-none');
        toggle.textContent = '☰';
        toggle.setAttribute('aria-expanded', 'false');
    });

    // ----- Tutup sidebar bila overlay diklik -----
    if (overlay) {
        overlay.addEventListener('click', function () {
            if (body.classList.contains('sidebar-open')) {
                body.classList.remove('sidebar-open');
                overlay.classList.add('d-none');
                toggle.textContent = '☰';
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ----- Sesuaikan saat window di-resize -----
    window.addEventListener('resize', function () {
        if (window.innerWidth <= 767) {
            // pada layar kecil, pastikan sidebar tidak collapsed
            body.classList.remove('sidebar-collapsed');
            toggle.textContent = '☰';
        } else {
            // restore dari penyimpanan
            const s = localStorage.getItem(STATE_KEY);
            if (s === '1') setCollapsed(true); else setCollapsed(false);
        }
    });
});
