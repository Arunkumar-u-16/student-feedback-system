<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4361ee">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <?php if (isset($isDashboard) && $isDashboard): ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dashboard.css">
    <?php
endif; ?>
</head>
<body>
<?php if (!isset($noSidebar) || !$noSidebar): ?>
<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Page Content -->
    <div id="content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-link link-dark d-lg-none me-2 p-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                    <i class="fas fa-bars fs-4"></i>
                </button>
                <h4 class="mb-0"><?php echo $pageTitle ?? 'Dashboard'; ?></h4>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-link link-dark dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                    <div class="user-avatar-sm me-2 d-none d-md-flex">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <span class="d-none d-sm-inline"><?php echo $_SESSION['user_name']; ?></span>
                    <i class="fas fa-user-circle d-inline d-sm-none fs-5"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-lg">
                    <li><a class="dropdown-item py-2" href="#"><i class="fas fa-user-gear me-2 text-muted"></i>Account Settings</a></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="fas fa-palette me-2 text-muted"></i>Dark Mode</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger py-2" href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Mobile Offcanvas Sidebar -->
        <div class="offcanvas offcanvas-start border-0 shadow-lg" tabindex="-1" id="mobileSidebar" style="width: 280px;">
            <div class="offcanvas-header bg-primary text-white p-4">
                <h5 class="offcanvas-title fw-bold" id="offcanvasExampleLabel">
                    <i class="fas fa-graduation-cap me-2"></i>STUDENT FEEDBACK
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-0">
                <div class="mobile-sidebar-content">
                    <?php include 'sidebar.php'; ?>
                </div>
            </div>
        </div>
        
        <!-- Alerts Area -->
        <div class="mb-4">
            <?php display_flash_message(); ?>
        </div>
<?php
endif; ?>
