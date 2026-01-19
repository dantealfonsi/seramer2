<?php
session_start();
require_once __DIR__ . '/../../controllers/InfractionsController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tasas.php');
    exit;
}

// 1. Validar y limpiar entradas (Solo UT, el Euro es automático)
$ut_value = filter_input(INPUT_POST, 'ut_value', FILTER_VALIDATE_FLOAT);

if ($ut_value === false || $ut_value <= 0) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'El valor de la UT debe ser un número positivo válido.'];
    header('Location: tasas.php');
    exit;
}

// 2. Inicializar controlador y guardar
$infractionsController = new InfractionsController();
// Actualizar UT y sincronizar Euro automáticamente
$saved = $infractionsController->updateUTAndSyncEuro($ut_value); 

if ($saved) {
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Indicadores económicos actualizados correctamente para la fecha de hoy.'];
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Error al guardar los indicadores económicos en la base de datos.'];
}

header('Location: tasas.php');
exit;