<?php
// Incluir la configuración al inicio de cada vista
require_once __DIR__ . '/../../config/app.php';
// Implementar Control de Acceso Estricto por Departamento
require_once __DIR__ . '/../../helpers/access_control.php';
?>
<!doctype html>

<html lang="es" class="layout-navbar-fixed layout-compact layout-menu-fixed" dir="ltr" data-skin="default"
    data-bs-theme="light" data-assets-path="../../public/assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title><?php echo PROJECT_NAME; ?></title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo img('favicon/favicon.ico'); ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo vendor('fonts/remixicon/remixicon.css'); ?>" />
    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css -->
    <link rel="stylesheet" href="<?php echo vendor('css/core.css'); ?>" />
    <link rel="stylesheet" href="<?php echo css('demo.css'); ?>" />
    <!-- SERAMER: Sistema Neumórfico Integral (v2.0) -->
    <link rel="stylesheet" href="<?php echo css('neumorph-system.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('libs/node-waves/node-waves.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('libs/pickr/pickr-themes.css'); ?>" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.css'); ?>" />
    <link rel="stylesheet" href="<?php echo vendor('libs/sweetalert2/sweetalert2.css'); ?>" />
    <!-- endbuild -->
    <script src="<?php echo vendor('js/template-customizer.js'); ?>"></script>
    <!-- Helpers -->
    <script src="<?php echo vendor('js/helpers.js'); ?>"></script>
    <!--? Config: Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file. -->
    <script src="<?php echo js('config.js'); ?>"></script>
    <script src="<?php echo js('validation.js'); ?>"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">