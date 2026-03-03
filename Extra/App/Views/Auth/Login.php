<!DOCTYPE html>
<html lang="es" class="light-style layout-wide customizer-hide" dir="ltr" data-skin="default" data-bs-theme="light" data-assets-path="/seramer-local/public/assets/" data-template="vertical-menu-template-no-customizer">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    
    <title>Iniciar Sesión - Sistema de Gestión Municipal</title>
    
    <meta name="description" content="Sistema de Gestión de Mercado Municipal" />
    
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
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/libs/@form-validation/form-validation.css" />
    
    <!-- Page CSS -->
    <link rel="stylesheet" href="/seramer-local/public/assets/vendor/css/pages/page-auth.css" />
    
    <!-- Helpers -->
    <script src="/seramer-local/public/assets/vendor/js/helpers.js"></script>
    <script src="/seramer-local/public/assets/js/config.js"></script>
</head>

<body>
    <!-- Content -->
    <div class="authentication-wrapper authentication-cover">
        
        
        <div class="authentication-inner row m-0">
            <!-- Left Section -->
            <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2" 
                style="background-image: url('/seramer-local/public/assets/img/new_logo.jpg'); background-repeat: no-repeat;background-size: cover; background-position: center;"></div>
            <!-- /Left Section -->
            
            <!-- Login -->
            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
                <div class="w-px-400 mx-auto pt-12 pt-lg-0">
                    <div style="display: flex;align-items: center;justify-content: center;margin-bottom: 2rem;">
                        <img src="/seramer-local/public/assets/img/logo.png" style="width: 8rem;height: 7.5rem;/* padding: 1rem; */box-shadow: 1px 0px 8px 0px #818181;border-radius: 50%;padding: .4rem;" alt="Logo" class="logo" />   
                    </div>
                    <h4 class="mb-1">Bienvenido a SERAMER! 👋</h4>
                    <p class="mb-5">Por favor inicia sesión en tu cuenta para comenzar</p>
                    
                    <?php if (\Core\Session::hasFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <?= \Core\Session::getFlash('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form id="formAuthentication" class="mb-5" action="/seramer-local/auth/processlogin" method="POST">
                        <?= \Core\Security::csrfField() ?>
                        
                        <div class="form-floating form-floating-outline mb-5 form-control-validation">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu usuario" autofocus required />
                            <label for="username">Usuario</label>
                        </div>
                        
                        <div class="mb-5">
                            <div class="form-password-toggle form-control-validation">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" required />
                                        <label for="password">Contraseña</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer">
                                        <i class="ri ri-eye-off-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary d-grid w-100" type="submit">Iniciar Sesión</button>
                    </form>
                    
                    <p class="text-center">
                        <span class="text-muted">Sistema de Gestión de Mercado Municipal</span>
                    </p>
                </div>
            </div>
            <!-- /Login -->
        </div>
    </div>
    <!-- / Content -->
    
    <!-- Core JS -->
    <script src="/seramer-local/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="/seramer-local/public/assets/vendor/js/bootstrap.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/seramer-local/public/assets/vendor/js/menu.js"></script>
    
    <!-- Vendors JS -->
    <script src="/seramer-local/public/assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="/seramer-local/public/assets/vendor/libs/@form-validation/auto-focus.js"></script>
    
    <!-- Main JS -->
    <script src="/seramer-local/public/assets/js/main.js"></script>
    
    <!-- Page JS -->
    <script src="/seramer-local/public/assets/js/pages-auth.js"></script>
</body>
</html>
