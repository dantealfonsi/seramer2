<?php 
// Incluir la configuración al inicio de cada vista
require_once '../../config/app.php';
?>
<!doctype html>

<html
  lang="en"
  class="layout-wide customizer-hide"
  dir="ltr"
  data-skin="default"
  data-bs-theme="light"
  data-assets-path="<?php echo ASSETS_URL; ?>/"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title><?php echo PROJECT_NAME; ?></title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo img('favicon/favicon.ico'); ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="<?php echo vendor('fonts/iconify-icons.css'); ?>" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css -->

    <link rel="stylesheet" href="<?php echo vendor('libs/node-waves/node-waves.css'); ?>" />

    <link rel="stylesheet" href="<?php echo vendor('libs/pickr/pickr-themes.css'); ?>" />

    <link rel="stylesheet" href="<?php echo vendor('css/core.css'); ?>" />
    <link rel="stylesheet" href="<?php echo css('demo.css'); ?>" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.css'); ?>" />

    <!-- endbuild -->

    <!-- Vendor -->
    <link rel="stylesheet" href="<?php echo vendor('libs/@form-validation/form-validation.css'); ?>" />

    <!-- Page -->
    <link rel="stylesheet" href="<?php echo css('page-auth.css'); ?>" />

    <!-- Helpers -->
    <script src="<?php echo vendor('js/helpers.js'); ?>"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file, Requires customizer for theme customizer and set ONLY color & fonts as per themes  -->
    <script src="<?php echo js('config.js'); ?>"></script>
  </head>

  <body>
    <!-- Content -->

    <div class="position-relative">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-auto">
          <!-- Login -->
          <div class="card p-7">
            <!-- Logo -->
            <div class="app-brand justify-content-center mt-5">
              <a href="<?php echo url('index.php'); ?>" class="app-brand-link gap-3">
                <span class="app-brand-logo demo">
                  <img src="<?php echo img('logo/logo.png'); ?>" alt="<?php echo PROJECT_NAME; ?>" width="32" height="32">
                </span>
                <span class="app-brand-text demo menu-text fw-semibold ms-2"><?php echo PROJECT_NAME; ?></span>
              </a>
            </div>
            <!-- /Logo -->

            <div class="card-body mt-1">
              <h4 class="mb-1">Bienvenido a <?php echo PROJECT_NAME; ?>! 👋🏻</h4>
              <p class="mb-5">Inicia sesión en tu cuenta y comienza la aventura</p>

              <form id="formAuthentication" class="mb-5" action="<?php echo url('controller/AuthController.php'); ?>" method="POST">
                <div class="form-floating form-floating-outline mb-5">
                  <input
                    type="text"
                    class="form-control"
                    id="email"
                    name="email-username"
                    placeholder="Ingresa tu email o nombre de usuario"
                    autofocus />
                  <label for="email">Email o Usuario</label>
                </div>
                <div class="mb-5">
                  <div class="form-password-toggle">
                    <div class="input-group input-group-merge">
                      <div class="form-floating form-floating-outline">
                        <input
                          type="password"
                          id="password"
                          class="form-control"
                          name="password"
                          placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                          aria-describedby="password" />
                        <label for="password">Contraseña</label>
                      </div>
                      <span class="input-group-text cursor-pointer"><i class="ri-eye-off-line ri-20px"></i></span>
                    </div>
                  </div>
                </div>
                <div class="mb-5 pb-2 d-flex justify-content-between pt-2 align-items-center">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me"> Recordarme </label>
                  </div>
                  <a href="<?php echo url('views/auth/forgot-password.php'); ?>" class="float-end mb-1">
                    <span>¿Olvidaste tu contraseña?</span>
                  </a>
                </div>
                <div class="mb-5">
                  <button class="btn btn-primary d-grid w-100" type="submit">Iniciar Sesión</button>
                </div>
              </form>

              <p class="text-center mb-5">
                <span>¿Nuevo en nuestra plataforma?</span>
                <a href="<?php echo url('views/auth/register.php'); ?>">
                  <span>Crear una cuenta</span>
                </a>
              </p>
            </div>
          </div>
          <!-- /Login -->
          <img
            alt="mask"
            src="<?php echo img('illustrations/auth-login-illustration-light.png'); ?>"
            class="authentication-image d-none d-lg-block"
            data-app-light-img="<?php echo img('illustrations/auth-login-illustration-light.png'); ?>"
            data-app-dark-img="<?php echo img('illustrations/auth-login-illustration-dark.png'); ?>" />
        </div>
      </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="<?php echo vendor('libs/jquery/jquery.js'); ?>"></script>
    <script src="<?php echo vendor('libs/popper/popper.js'); ?>"></script>
    <script src="<?php echo vendor('js/bootstrap.js'); ?>"></script>
    <script src="<?php echo vendor('libs/node-waves/node-waves.js'); ?>"></script>
    <script src="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.js'); ?>"></script>
    <script src="<?php echo vendor('libs/hammer/hammer.js'); ?>"></script>
    <script src="<?php echo vendor('libs/i18n/i18n.js'); ?>"></script>
    <script src="<?php echo vendor('libs/typeahead-js/typeahead.js'); ?>"></script>
    <script src="<?php echo vendor('js/menu.js'); ?>"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="<?php echo vendor('libs/@form-validation/popular.js'); ?>"></script>
    <script src="<?php echo vendor('libs/@form-validation/bootstrap5.js'); ?>"></script>
    <script src="<?php echo vendor('libs/@form-validation/auto-focus.js'); ?>"></script>

    <!-- Main JS -->
    <script src="<?php echo js('main.js'); ?>"></script>

    <!-- Page JS -->
    <script src="<?php echo js('pages-auth.js'); ?>"></script>
    
    <?php if (DEBUG_MODE): ?>
    <!-- Scripts de desarrollo -->
    <script>
        console.log('🚀 Modo desarrollo activado');
        console.log('📍 Base URL:', '<?php echo BASE_URL; ?>');
        console.log('🎨 Assets URL:', '<?php echo ASSETS_URL; ?>');
        console.log('📱 Proyecto:', '<?php echo PROJECT_NAME; ?> v<?php echo PROJECT_VERSION; ?>');
    </script>
    <?php endif; ?>
    
  </body>
</html>