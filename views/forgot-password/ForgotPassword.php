<?php
// Incluir la configuración y el controlador
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/UserController.php';

session_start();

// Crear una instancia del controlador
$controller = new UserController();

// Procesar la solicitud si es un POST
$data = [];
//$data = $controller->forgotPassword($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    
    if(isset($_POST['code']) && ($_POST['code'] === $_POST['token'])){
        header('Location: ResetPassword.php?email=' . $_POST['email']. '&token=' . $_POST['code']);
        exit();
    } else {
        $data = $controller->forgotPassword( $_POST);
    }

    //$data = $controller->forgotPassword($_POST);
} else {
    // Si no es un POST, simplemente mostramos el formulario
    $data = ['message' => 'Por favor, ingresa tu correo electrónico para recuperar tu contraseña.'];
}

// Redireccionar si la solicitud fue exitosa
if (isset($data['success']) && $data['success']) {
    $_SESSION['message'] = $data['message'];
    $_SESSION['message_type'] = 'success';
    header('Location: ../auth/login.php');
    exit();
}
?>

<!doctype html>
<html lang="es" class="layout-wide customizer-hide" dir="ltr" data-skin="default" data-bs-theme="light" data-assets-path="<?php echo ASSETS_URL; ?>/" data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Recuperar Contraseña - <?php echo PROJECT_NAME; ?></title>

    <link rel="icon" type="image/x-icon" href="<?php echo img('favicon/favicon.ico'); ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo vendor('fonts/remixicon/remixicon.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('libs/node-waves/node-waves.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('libs/pickr/pickr-themes.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('css/core.css'); ?>" />
    <link rel="stylesheet" href="<?php echo css('demo.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('libs/@form-validation/form-validation.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('css/pages/page-auth.css'); ?>" />
    <script src="<?php echo vendor('js/helpers.js'); ?>"></script>
    <script src="<?php echo vendor('js/template-customizer.js'); ?>"></script>
    <script src="<?php echo js('config.js'); ?>"></script>
</head>
<body>
    <div class="authentication-wrapper authentication-cover">
        <div class="authentication-inner row m-0">
            <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2">
                <img src="<?php echo img('illustrations/auth-login-illustration-light.png'); ?>" class="auth-cover-illustration w-100" alt="auth-illustration" data-app-light-img="illustrations/auth-login-illustration-light.png" data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
                <img alt="mask" src="<?php echo img('illustrations/auth-basic-login-mask-light.png'); ?>" class="authentication-image d-none d-lg-block" data-app-light-img="illustrations/auth-basic-login-mask-light.png" data-app-dark-img="illustrations/auth-basic-login-mask-dark.png" />
            </div>
            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
                <div class="w-px-400 mx-auto pt-12 pt-lg-0">
                    <h4 class="mb-1">¿Olvidaste tu contraseña? 🔒</h4>
                    <p class="mb-5">Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.</p>

                    <?php if (!empty($data['message'])): ?>
                        <div class="alert alert-<?php echo isset($data['success']) && $data['success'] ? 'success' : 'danger'; ?>" role="alert">
                            <?php echo htmlspecialchars($data['message']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($data['code'])): ?>
                        <div class="alert alert-<?php echo isset($data['success']) && $data['success'] ? 'success' : 'info'; ?>" role="alert">
                            Codigo Temp: <?php echo htmlspecialchars($data['code']); ?>
                        </div>
                    <?php endif; ?>                    

                    <form id="formForgotPassword" class="mb-5" action="" method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($data['code'] ?? ''); ?>" />
                        <div class="form-floating form-floating-outline mb-5">
                            <input type="email" class="form-control" id="email" name="email" <?php if(isset($data['email'])) echo  "value='".htmlspecialchars($data['email'])."' readonly "; ?>placeholder="Ingresa tu correo electrónico" autofocus />
                            <label for="email">Correo Electrónico</label>                            
                        </div>
                        <?php if(isset($data['email'])){?>
                        <div class="form-floating form-floating-outline mb-5">
                            <input type="text" class="form-control" id="code" name="code" placeholder="Ingresa Codigo" />
                            <label for="email">Codigo Recuperacion</label>                            
                        </div>                            
                        <?php
                        } ?>                    
                        <button class="btn btn-primary d-grid w-100" type="submit">Enviar Codigo de Recuperación</button>
                    </form>

                    <div class="text-center">
                        <a href="../auth/login.php" class="d-flex align-items-center justify-content-center">
                            <i class="ri-arrow-left-s-line ri-24px me-1"></i>
                            Volver al inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
            </div>
    </div>

    <script src="<?php echo vendor('libs/jquery/jquery.js'); ?>"></script>
    <script src="<?php echo vendor('libs/popper/popper.js'); ?>"></script>
    <script src="<?php echo vendor('js/bootstrap.js'); ?>"></script>
    <script src="<?php echo vendor('libs/node-waves/node-waves.js'); ?>"></script>
    <script src="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.js'); ?>"></script>
    <script src="<?php echo vendor('js/menu.js'); ?>"></script>
    <script src="<?php echo js('main.js'); ?>"></script>
    <script src="<?php echo js('pages-auth.js'); ?>"></script>
</body>
</html>