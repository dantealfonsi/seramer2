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
    <!-- SERAMER: Sistema Neumórfico Integral -->
    <link rel="stylesheet" href="<?php echo css('neumorph-system.css'); ?>" />
    
    <link rel="stylesheet" href="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('libs/@form-validation/form-validation.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('css/pages/page-auth.css'); ?>" />
    <script src="<?php echo vendor('js/helpers.js'); ?>"></script>
    <script src="<?php echo vendor('js/template-customizer.js'); ?>"></script>
    <script src="<?php echo js('config.js'); ?>"></script>
</head>
<body class="page-forgot-password">
    <div class="authentication-wrapper authentication-cover">
        <div class="authentication-inner row m-0">
            <!-- Left Section (Text) -->
            <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2" style="background-color: #1e2a3a;">
                <div class="text-center p-5">
                    <h1 class="display-3 fw-bold text-white mb-4" style="letter-spacing: -1px; text-transform: uppercase;">REESTABLEZCA SU<br>CONTRASEÑA</h1>
                    <div style="width: 80px; height: 4px; background: var(--metro-primary); margin: 0 auto 2rem;"></div>
                    <p class="lead text-white opacity-75" style="font-size: 1.25rem;">Proceso de recuperación de acceso al sistema SERAMER.</p>
                </div>
            </div>
            <!-- /Left Section -->

            <!-- Form Section -->
            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
                <div class="w-px-400 mx-auto pt-12 pt-lg-0">
                    <!-- Logo centered -->
                    <div style="display: flex;align-items: center;justify-content: center;margin-bottom: 2rem;">
                        <img src="<?php echo img('logo.png'); ?>" style="width: 7rem; height: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 50%; padding: .4rem; background: white;" alt="Logo" class="logo" />   
                    </div>

                    <h4 class="mb-1 fw-bold" style="color: var(--metro-primary);">¿Olvidaste tu contraseña?</h4>
                    <p class="mb-5 text-muted">Ingresa tu correo electrónico y te enviaremos un código para restablecerla.</p>

                    <?php if (!empty($data['message'])): ?>
                        <div class="alert alert-<?php echo isset($data['success']) && $data['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($data['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form id="formForgotPassword" class="mb-4" action="" method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($data['code'] ?? ''); ?>" />
                        
                        <div class="form-floating form-floating-outline mb-4">
                            <input type="email" class="form-control" id="email" name="email" 
                                <?php if(isset($data['email'])) echo "value='".htmlspecialchars($data['email'])."' readonly "; ?>
                                placeholder="Ingresa tu correo electrónico" autofocus required />
                            <label for="email">Correo Electrónico</label>                            
                        </div>

                        <?php if (!empty($data['code'])): ?>
                        <!-- Test Log Card -->
                        <div class="card bg-label-info border-info mb-4 alert alert-dismissible fade show" role="alert" style="border-style: dashed !important; background-color: rgba(41, 128, 185, 0.1) !important;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-info"><i class="ri-terminal-box-line"></i></span>
                                </div>
                                <div>
                                    <small class="d-block text-info fw-bold mb-1">TEST LOG / DEPURE</small>
                                    <span class="mb-0 text-dark">Tu código es: <code class="fw-bold fs-5 text-primary"><?php echo htmlspecialchars($data['code']); ?></code></span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="top: 0.5rem; right: 0.5rem;"></button>
                        </div>
                        <?php endif; ?>

                        <?php if(isset($data['email'])): ?>
                        <div class="form-floating form-floating-outline mb-4">
                            <input type="text" class="form-control" id="code" name="code" placeholder="Ingresa Código" required />
                            <label for="code">Código de Recuperación</label>                            
                        </div>                            
                        <?php endif; ?>                    

                        <button class="btn btn-primary d-grid w-100 py-3 fw-bold" type="submit">
                            <?php echo isset($data['email']) ? 'Validar Código' : 'Enviar Código de Recuperación'; ?>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="../auth/login.php" class="d-flex align-items-center justify-content-center text-primary fw-semibold">
                            <i class="ri-arrow-left-s-line ri-20px me-1"></i>
                            Volver al inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Form Section -->
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