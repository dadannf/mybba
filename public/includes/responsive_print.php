<!-- =============================================
     PRINT STYLES INCLUDE
     For responsive print functionality
     ============================================= -->
<style media="print">
/* ==================================================
   RESPONSIVE PRINT STYLES
   Optimized for A4 paper and mobile print
   ================================================== */

/* Reset & Base */
@page {
    size: A4;
    margin: 15mm;
}

body {
    margin: 0;
    padding: 0;
    font-size: 10pt;
    line-height: 1.4;
    color: #000;
    background: #fff;
}

/* Hide non-printable elements */
.sidebar,
.topbar,
.overlay,
.btn-toggle,
.btn,
button,
.no-print,
.navbar,
.footer,
.breadcrumb,
.alert,
.badge:not(.print-show),
.dropdown,
.modal,
input[type="file"],
.pagination {
    display: none !important;
}

/* Layout */
.main-wrapper {
    margin-left: 0 !important;
    width: 100% !important;
}

.content,
.container,
.container-fluid {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
}

/* Cards */
.card {
    border: 1px solid #ddd !important;
    box-shadow: none !important;
    page-break-inside: avoid;
    margin-bottom: 10px;
}

.card-header {
    background: #f5f5f5 !important;
    border-bottom: 1px solid #ddd !important;
    padding: 8px !important;
    font-weight: bold;
}

.card-body {
    padding: 10px !important;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    page-break-inside: auto;
    font-size: 9pt;
}

tr {
    page-break-inside: avoid;
    page-break-after: auto;
}

thead {
    display: table-header-group;
}

tfoot {
    display: table-footer-group;
}

th, td {
    border: 1px solid #ddd !important;
    padding: 5px !important;
    text-align: left;
}

th {
    background: #f0f0f0 !important;
    font-weight: bold;
    color: #000 !important;
}

/* Typography */
h1 { font-size: 18pt; margin: 0 0 10px 0; }
h2 { font-size: 16pt; margin: 0 0 8px 0; }
h3 { font-size: 14pt; margin: 0 0 6px 0; }
h4 { font-size: 12pt; margin: 0 0 5px 0; }
h5, h6 { font-size: 10pt; margin: 0 0 4px 0; }

p { margin: 0 0 8px 0; }

/* Links */
a {
    color: #000;
    text-decoration: none;
}

a[href]:after {
    content: " (" attr(href) ")";
    font-size: 8pt;
    color: #666;
}

/* Images */
img {
    max-width: 100%;
    height: auto;
    page-break-inside: avoid;
}

/* Print-specific utilities */
.print-show {
    display: block !important;
}

.print-hide {
    display: none !important;
}

.page-break-before {
    page-break-before: always;
}

.page-break-after {
    page-break-after: always;
}

.page-break-avoid {
    page-break-inside: avoid;
}

/* Responsive for small paper */
@media print and (max-width: 210mm) {
    body {
        font-size: 9pt;
    }
    
    table {
        font-size: 8pt;
    }
    
    th, td {
        padding: 3px !important;
    }
    
    h1 { font-size: 16pt; }
    h2 { font-size: 14pt; }
    h3 { font-size: 12pt; }
}

/* Landscape orientation */
@media print and (orientation: landscape) {
    @page {
        size: A4 landscape;
    }
}

/* Colors - convert to grayscale for print */
* {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.text-primary, .bg-primary { color: #000 !important; background: #f0f0f0 !important; }
.text-success, .bg-success { color: #000 !important; background: #e8f5e9 !important; }
.text-warning, .bg-warning { color: #000 !important; background: #fff8e1 !important; }
.text-danger, .bg-danger { color: #000 !important; background: #ffebee !important; }
.text-info, .bg-info { color: #000 !important; background: #e3f2fd !important; }

/* Print watermark */
.print-watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 80pt;
    color: rgba(0, 0, 0, 0.05);
    z-index: -1;
}

/* Print header/footer */
.print-header,
.print-footer {
    width: 100%;
    text-align: center;
    padding: 10px 0;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    margin: 10px 0;
}

.print-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    font-size: 8pt;
    color: #666;
}

/* QR Code / Barcode */
.print-qrcode,
.print-barcode {
    page-break-inside: avoid;
    max-width: 150px;
    margin: 10px auto;
}
</style>

<!-- Print Optimization Script -->
<script>
// Print optimization
window.addEventListener('beforeprint', function() {
    console.log('🖨️ Preparing to print...');
    // Add any pre-print logic here
});

window.addEventListener('afterprint', function() {
    console.log('✅ Print complete');
    // Add any post-print logic here
});

// Auto-print function
function autoPrint() {
    window.print();
}

// Print with custom settings
function printPage(orientation = 'portrait') {
    const style = document.createElement('style');
    style.innerHTML = `@page { size: A4 ${orientation}; }`;
    document.head.appendChild(style);
    window.print();
    document.head.removeChild(style);
}
</script>
