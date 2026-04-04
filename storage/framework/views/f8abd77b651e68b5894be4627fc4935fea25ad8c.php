<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'MuliSystem_AIDA - Gestion de Micro-Crédits'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #17a2b8;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            display: flex;
            align-items: center;
        }
        
        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            cursor: pointer;
            padding: 5px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        
        .menu-toggle:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logout-btn {
            background: none;
            border: none;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .logout-btn:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            height: calc(100vh - 60px);
            box-shadow: 2px 0 15px rgba(0,0,0,0.2);
            position: fixed;
            width: 280px;
            left: 0;
            transition: left 0.3s ease;
            z-index: 100;
            color: white;
            top: 60px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .sidebar.show {
            left: 0;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            flex-shrink: 0;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .sidebar-logo i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .sidebar-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sidebar-subtitle {
            font-size: 0.9rem;
            color: #bdc3c7;
            margin: 5px 0 0 0;
        }
        
        .sidebar-nav {
            padding: 20px 0;
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1;
            -webkit-overflow-scrolling: touch;
        }
        
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-nav::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Menu déroulant Rapport */
        .report-menu-header {
            cursor: pointer;
            user-select: none;
            position: relative;
        }
        
        .report-menu-header::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 25px;
            transition: transform 0.3s ease;
        }
        
        .report-menu-header.expanded::after {
            transform: rotate(180deg);
        }
        
        .report-menu-items {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .report-menu-header.expanded + .report-menu-items {
            max-height: 500px;
        }
        
        /* Menu déroulant Trésorerie */
        .treasury-menu-header {
            cursor: pointer;
            user-select: none;
            position: relative;
        }
        
        .treasury-menu-header::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 25px;
            transition: transform 0.3s ease;
        }
        
        .treasury-menu-header.expanded::after {
            transform: rotate(180deg);
        }
        
        .treasury-menu-items {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .treasury-menu-header.expanded + .treasury-menu-items {
            max-height: 500px;
        }
        
        /* Menu déroulant Admin */
        .admin-menu-header {
            cursor: pointer;
            user-select: none;
            position: relative;
        }
        
        .admin-menu-header::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 25px;
            transition: transform 0.3s ease;
        }
        
        .admin-menu-header.expanded::after {
            transform: rotate(180deg);
        }
        
        .admin-menu-items {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .admin-menu-header.expanded + .admin-menu-items {
            max-height: 500px;
        }
        
        .sidebar .nav-link {
            color: #bdc3c7;
            padding: 15px 25px;
            border-radius: 0;
            margin-bottom: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
            font-weight: 500;
            text-decoration: none;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(52, 152, 219, 0.2) 0%, rgba(41, 128, 185, 0.1) 100%);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            font-size: 1.1rem;
            margin-right: 12px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .main-content.sidebar-open {
            margin-left: 280px;
        }
        
        .content-wrapper {
            padding: 100px 20px 20px 20px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: none;
        }
        
        .stats-card .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stats-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0 5px 0;
        }
        
        .stats-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 10px 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        /* Tables */
        .table {
            border-radius: 10px;
            overflow: hidden;
            background: white;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }
        
        /* Badges */
        .badge {
            border-radius: 20px;
            padding: 8px 12px;
            font-weight: 500;
        }
        
        /* Mobile Responsive */
        .menu-toggle {
            display: none;
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .sidebar {
                width: 100%;
                left: -100%;
                top: 0;
                height: 100vh;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.sidebar-open {
                margin-left: 0;
            }
            
            .content-wrapper {
                padding: 80px 15px 15px 15px;
            }
        }
        
        @media (max-height: 600px) {
            .sidebar-header {
                padding: 15px 20px;
            }
            
            .sidebar-nav {
                padding: 10px 0;
            }
            
            .sidebar .nav-link {
                padding: 10px 25px;
            }
        }
        
        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
        
        .footer p {
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="header-title">MuliSystem_AIDA</h1>
        </div>
        <div class="header-right">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span>Bienvenue : <?php echo e(Auth::user()->fullName); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </button>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-university"></i>
            </div>
            <p class="sidebar-subtitle">MuliSystem_AIDA</p>
        </div>
        <nav class="sidebar-nav">
            <?php echo $__env->make('layouts.menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="content-wrapper">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>All rights reserved © AllSolutions 2025</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('show');
            mainContent.classList.toggle('sidebar-open');
        }

        // Initialiser le menu selon la taille d'écran
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            console.log('DOM loaded, window width:', window.innerWidth);
            console.log('Sidebar element:', sidebar);
            console.log('Main content element:', mainContent);
            
            // Sur les grands écrans, le menu est toujours visible
            if (window.innerWidth > 768) {
                sidebar.classList.add('show');
                mainContent.classList.add('sidebar-open');
                console.log('Sidebar opened for large screen');
            } else {
                console.log('Sidebar closed for small screen');
            }
        });

        // Gérer le redimensionnement de la fenêtre
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth > 768) {
                // Grand écran : menu toujours visible
                sidebar.classList.add('show');
                mainContent.classList.add('sidebar-open');
            } else {
                // Petit écran : menu caché par défaut
                sidebar.classList.remove('show');
                mainContent.classList.remove('sidebar-open');
            }
        });

        // Auto-hide disabled - all messages remain visible
        // setTimeout(function() {
        //     const alerts = document.querySelectorAll('.alert-success, .alert-danger');
        //     alerts.forEach(function(alert) {
        //         const bsAlert = new bootstrap.Alert(alert);
        //         bsAlert.close();
        //     });
        // }, 5000);

        // Désactiver tous les comportements automatiques de Bootstrap sur les alertes
        document.addEventListener('DOMContentLoaded', function() {
            // Empêcher la fermeture automatique des alertes
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                // Supprimer les attributs data-bs-dismiss pour les messages informatifs
                if (alert.classList.contains('alert-info') || alert.classList.contains('alert-warning')) {
                    const closeButton = alert.querySelector('.btn-close');
                    if (closeButton) {
                        closeButton.remove();
                    }
                }
                
                // Désactiver l'auto-hide de Bootstrap
                alert.setAttribute('data-bs-autohide', 'false');
            });
        });

        // Toggle menu Rapport
        function toggleReportMenu() {
            const menuHeader = document.querySelector('.report-menu-header');
            if (menuHeader) {
                menuHeader.classList.toggle('expanded');
            }
        }
        
        // Toggle menu Trésorerie
        function toggleTreasuryMenu() {
            const menuHeader = document.querySelector('.treasury-menu-header');
            if (menuHeader) {
                menuHeader.classList.toggle('expanded');
            }
        }
        
        // Toggle menu Admin
        function toggleAdminMenu() {
            const menuHeader = document.querySelector('.admin-menu-header');
            if (menuHeader) {
                menuHeader.classList.toggle('expanded');
            }
        }
        
        // Ouvrir les menus si une route correspondante est active
        document.addEventListener('DOMContentLoaded', function() {
            const isReportRoute = window.location.pathname.includes('/reports/');
            if (isReportRoute) {
                const menuHeader = document.querySelector('.report-menu-header');
                if (menuHeader) {
                    menuHeader.classList.add('expanded');
                }
            }
            
            const isCashflowRoute = window.location.pathname.includes('/cashflow/');
            if (isCashflowRoute) {
                const menuHeader = document.querySelector('.treasury-menu-header');
                if (menuHeader) {
                    menuHeader.classList.add('expanded');
                }
            }
            
            const isAdminRoute = window.location.pathname.includes('/admin/');
            if (isAdminRoute) {
                const menuHeader = document.querySelector('.admin-menu-header');
                if (menuHeader) {
                    menuHeader.classList.add('expanded');
                }
            }
        });

        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                },
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']]
            });
        });
    </script>
    
    
    <?php echo $__env->yieldContent('scripts'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\laragon\www\mulisys_aida\resources\views/layouts/app.blade.php ENDPATH**/ ?>