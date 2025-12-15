<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    
    <!-- ✅ RESPONSIVE HEAD - WAJIB -->
    <?php include __DIR__ . '/../includes/responsive_head.php'; ?>
    
    <title>Responsive Test Page - Sistem Informasi BBA</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/dashboard-responsive.css">
    
    <style>
        .test-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .test-section h4 {
            color: #0b63a8;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #0b63a8;
        }
        
        .viewport-info {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            z-index: 9999;
        }
    </style>
</head>
<body class="has-sidebar">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header text-center p-3">
            <div class="profile-avatar">
                <i class="bi bi-person-circle" style="font-size: 2.5rem;"></i>
            </div>
            <h6 class="profile-name mt-2 mb-0">Test User</h6>
            <small class="profile-role text-muted">Administrator</small>
        </div>
        
        <nav class="nav flex-column mt-3">
            <a href="#" class="nav-link active">
                <i class="bi bi-house-door"></i>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-people"></i>
                <span class="nav-text">Data Siswa</span>
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-cash-coin"></i>
                <span class="nav-text">Keuangan</span>
            </a>
            <a href="#" class="nav-link">
                <i class="bi bi-info-circle"></i>
                <span class="nav-text">Informasi</span>
            </a>
        </nav>
    </aside>
    
    <!-- OVERLAY -->
    <div class="overlay"></div>
    
    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <!-- TOPBAR -->
        <nav class="topbar d-flex align-items-center">
            <button class="btn-toggle me-2" aria-label="Toggle Menu">
                <i class="bi bi-list"></i>
            </button>
            <span class="app-title">Responsive Test Page</span>
        </nav>
        
        <!-- CONTENT -->
        <main class="content">
            <!-- Alert Test -->
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Responsive System Active!</strong> Resize browser untuk test responsive.
            </div>
            
            <!-- Stats Cards Test -->
            <div class="test-section">
                <h4><i class="bi bi-bar-chart me-2"></i>Responsive Cards</h4>
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h6 class="card-title">Total Siswa</h6>
                                <h2 class="mb-0">350</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h6 class="card-title">Lunas</h6>
                                <h2 class="mb-0">280</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h6 class="card-title">Pending</h6>
                                <h2 class="mb-0">50</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h6 class="card-title">Belum Bayar</h6>
                                <h2 class="mb-0">20</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table Test -->
            <div class="test-section">
                <h4><i class="bi bi-table me-2"></i>Responsive Table</h4>
                <p class="text-muted">Table akan auto-scroll horizontal di mobile</p>
                
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>222111001</td>
                            <td>Ahmad Fadli</td>
                            <td>12</td>
                            <td>TKJ</td>
                            <td>ahmad@mail.com</td>
                            <td>08123456789</td>
                            <td><span class="badge bg-success">Aktif</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>222111002</td>
                            <td>Budi Santoso</td>
                            <td>11</td>
                            <td>TSM</td>
                            <td>budi@mail.com</td>
                            <td>08234567890</td>
                            <td><span class="badge bg-success">Aktif</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>222111003</td>
                            <td>Citra Dewi</td>
                            <td>10</td>
                            <td>TKJ</td>
                            <td>citra@mail.com</td>
                            <td>08345678901</td>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Form Test -->
            <div class="test-section">
                <h4><i class="bi bi-ui-checks me-2"></i>Responsive Form</h4>
                <p class="text-muted">Form akan stack vertical di mobile</p>
                
                <form>
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" placeholder="Masukkan nama">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="email@example.com">
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label">Kelas</label>
                            <select class="form-select">
                                <option>10</option>
                                <option>11</option>
                                <option>12</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label">Jurusan</label>
                            <select class="form-select">
                                <option>TKJ</option>
                                <option>TSM</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="form-label">No HP</label>
                            <input type="tel" class="form-control" placeholder="08xxx">
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </button>
                        <button type="button" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Button Test -->
            <div class="test-section">
                <h4><i class="bi bi-ui-radios me-2"></i>Responsive Buttons</h4>
                
                <h6>Button Group (akan stack vertical di mobile):</h6>
                <div class="btn-group mb-3" role="group">
                    <button class="btn btn-outline-primary">Button 1</button>
                    <button class="btn btn-outline-primary">Button 2</button>
                    <button class="btn btn-outline-primary">Button 3</button>
                </div>
                
                <h6>Flex Gap (akan stack vertical di mobile < 768px):</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success"><i class="bi bi-download"></i> Download</button>
                    <button class="btn btn-info"><i class="bi bi-printer"></i> Print</button>
                    <button class="btn btn-warning"><i class="bi bi-upload"></i> Upload</button>
                </div>
            </div>
            
            <!-- Modal Test -->
            <div class="test-section">
                <h4><i class="bi bi-window me-2"></i>Responsive Modal</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testModal">
                    <i class="bi bi-box-arrow-up-right"></i> Open Modal
                </button>
            </div>
            
            <!-- Viewport Info -->
            <div class="viewport-info">
                <div id="viewport-width">Width: <span id="vw">0</span>px</div>
                <div id="viewport-height">Height: <span id="vh">0</span>px</div>
                <div id="device-type">Type: <span id="dt">-</span></div>
            </div>
        </main>
    </div>
    
    <!-- TEST MODAL -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Responsive Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Modal ini akan fullscreen di mobile (< 576px)</p>
                    <p>Di tablet dan desktop, modal akan centered</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- ✅ RESPONSIVE SCRIPTS - WAJIB -->
    <?php include __DIR__ . '/../includes/responsive_scripts.php'; ?>
    
    <!-- ✅ RESPONSIVE MANAGER - WAJIB -->
    <?php include __DIR__ . '/../includes/responsive_manager.php'; ?>
    
    <!-- Viewport Info Update -->
    <script>
        function updateViewportInfo() {
            document.getElementById('vw').textContent = window.innerWidth;
            document.getElementById('vh').textContent = window.innerHeight;
            
            let type = 'Desktop';
            if (window.innerWidth < 576) type = 'Mobile (xs)';
            else if (window.innerWidth < 768) type = 'Mobile (sm)';
            else if (window.innerWidth < 992) type = 'Tablet (md)';
            else if (window.innerWidth < 1200) type = 'Desktop (lg)';
            else type = 'Desktop (xl)';
            
            document.getElementById('dt').textContent = type;
        }
        
        updateViewportInfo();
        window.addEventListener('resize', updateViewportInfo);
    </script>
</body>
</html>
