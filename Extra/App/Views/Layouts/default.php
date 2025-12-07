<!DOCTYPE html>
<html lang="es" class="light-style layout-navbar-fixed layout-menu-fixed customizer-hide" dir="ltr" data-skin="default" data-bs-theme="light" data-theme="theme-default" data-assets-path="/seramer-local/public/assets/" data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    
    <title><?= $title ?? 'Sistema' ?> - <?= $app['name'] ?></title>
    
    <meta name="description" content="<?= $app['name'] ?>" />
    <meta name="csrf-token" content="<?= \Core\Security::getCsrfToken() ?>" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/seramer-local/public/assets/img/favicon/favicon.ico" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/fonts/iconify-icons.css" />
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/css/demo.css" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/notyf/notyf.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/sweetalert2/sweetalert2.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/seramer-local/public/assets/css/custom-global.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/css/custom-datatable.css" />
    <link rel="stylesheet" href="/seramer-local/public/assets/css/custom-notifications.css" />
    
    <!-- Helpers -->
    <script src="/seramer-local/public/assets/vendor/js/helpers.js"></script>
    <script src="/seramer-local/public/assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            <!-- Menu -->
            <?php include __DIR__ . '/../Partials/Menu.php'; ?>
            <!-- / Menu -->
            
            <!-- Layout container -->
            <div class="layout-page">
                
                <!-- Navbar -->
                <?php include __DIR__ . '/../Partials/Navbar.php'; ?>
                <!-- / Navbar -->
                
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        
                        <!-- Contenido de la vista -->
                        <?= $content ?>
                        <!-- / Contenido de la vista -->
                        
                    </div>
                    <!-- / Content -->
                    
                    <!-- Footer -->
                    <?php include __DIR__ . '/../Partials/Footer.php'; ?>
                    <!-- / Footer -->
                    
                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
        
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        
        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
    
    <!-- Core JS -->
    <script src="/seramer-local/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="/seramer-local/public/assets/vendor/js/bootstrap.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/seramer-local/public/assets/vendor/js/menu.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/notyf/notyf.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/moment/moment.js"></script>
    
    <!-- Main JS -->
    <script src="/seramer-local/public/assets/js/main.js"></script>
    <script src="/seramer-local/public/assets/js/app-datatable.js"></script>
    
    <!-- Toast Notifications -->
    <script>
        const notyf = new Notyf({
            duration: 4000,
            position: {
                x: 'right',
                y: 'top',
            },
            dismissible: true,
            ripple: false,
            types: [
                {
                    type: 'success',
                    background: '#ffffff',
                    className: 'notyf__toast--success',
                    icon: {
                        className: 'ri ri-checkbox-circle-line',
                        tagName: 'i',
                        color: '#28c76f'
                    }
                },
                {
                    type: 'error',
                    background: '#ffffff',
                    className: 'notyf__toast--error',
                    icon: {
                        className: 'ri ri-close-circle-line',
                        tagName: 'i',
                        color: '#ea5455'
                    }
                },
                {
                    type: 'warning',
                    background: '#ffffff',
                    className: 'notyf__toast--warning',
                    icon: {
                        className: 'ri ri-error-warning-line',
                        tagName: 'i',
                        color: '#ff9f43'
                    }
                },
                {
                    type: 'info',
                    background: '#ffffff',
                    className: 'notyf__toast--info',
                    icon: {
                        className: 'ri ri-information-line',
                        tagName: 'i',
                        color: '#00cfe8'
                    }
                }
            ]
        });
        
        // Mostrar mensajes flash
        <?php if (\Core\Session::hasFlash('success')): ?>
        notyf.success('<?= addslashes(\Core\Session::getFlash('success')) ?>');
        <?php endif; ?>
        
        <?php if (\Core\Session::hasFlash('error')): ?>
        notyf.error('<?= addslashes(\Core\Session::getFlash('error')) ?>');
        <?php endif; ?>
        
        <?php if (\Core\Session::hasFlash('warning')): ?>
        notyf.open({type: 'warning', message: '<?= addslashes(\Core\Session::getFlash('warning')) ?>'});
        <?php endif; ?>
        
        <?php if (\Core\Session::hasFlash('info')): ?>
        notyf.open({type: 'info', message: '<?= addslashes(\Core\Session::getFlash('info')) ?>'});
        <?php endif; ?>
    </script>
    
    <!-- Page Scripts -->
    <?php if (isset($pageScripts)): ?>
        <?= $pageScripts ?>
    <?php endif; ?>
</body>
</html>

