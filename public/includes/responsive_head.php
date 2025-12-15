<!-- =============================================
     RESPONSIVE HEAD - Universal Meta & CSS
     Include di semua halaman untuk responsive design
     ============================================= -->
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

<!-- Responsive CSS -->
<link rel="stylesheet" href="/css/responsive.css">

<!-- Additional Responsive Styles -->
<style>
    /* Universal Responsive Fixes */
    * {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }
    
    html {
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }
    
    body {
        overflow-x: hidden;
        min-width: 320px;
    }
    
    img,
    svg,
    video,
    canvas,
    iframe {
        max-width: 100%;
        height: auto;
    }
    
    /* Prevent text overflow */
    h1, h2, h3, h4, h5, h6,
    p, span, div, a {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    /* Responsive Typography */
    @media (max-width: 575.98px) {
        html {
            font-size: 14px;
        }
        
        h1 { font-size: 1.75rem; }
        h2 { font-size: 1.5rem; }
        h3 { font-size: 1.25rem; }
        h4 { font-size: 1.1rem; }
        h5 { font-size: 1rem; }
        h6 { font-size: 0.9rem; }
    }
    
    /* Mobile Menu Toggle Button */
    .btn-toggle-mobile {
        display: none;
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 1060;
        background: var(--primary-blue, #0b63a8);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-toggle-mobile:hover {
        background: var(--dark-blue, #064b7a);
        transform: scale(1.05);
    }
    
    .btn-toggle-mobile:active {
        transform: scale(0.95);
    }
    
    @media (max-width: 767.98px) {
        .btn-toggle-mobile {
            display: block;
        }
    }
    
    /* Responsive Table Wrapper */
    .table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 767.98px) {
        .table-container {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
        
        .table-container table {
            margin-bottom: 0;
        }
    }
    
    /* Responsive Form Groups */
    @media (max-width: 575.98px) {
        .row > [class*="col-"] {
            margin-bottom: 1rem;
        }
        
        .row > [class*="col-"]:last-child {
            margin-bottom: 0;
        }
    }
    
    /* Responsive Buttons */
    @media (max-width: 575.98px) {
        .btn-group-responsive {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-group-responsive .btn {
            width: 100%;
        }
    }
    
    /* Toast/Notification Responsive */
    @media (max-width: 575.98px) {
        .toast-container {
            left: 50% !important;
            transform: translateX(-50%);
            width: 90% !important;
            max-width: 350px;
        }
    }
</style>
