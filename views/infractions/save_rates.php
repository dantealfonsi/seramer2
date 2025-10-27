<?php
session_start();
require_once __DIR__ . '/../../controllers/InfractionsController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tasas.php');
    exit;
}

// 1. Validar y limpiar entradas
$ut_value = filter_input(INPUT_POST, 'ut_value', FILTER_VALIDATE_FLOAT);
$euro_rate = filter_input(INPUT_POST, 'euro_bcv_rate', FILTER_VALIDATE_FLOAT);

if ($ut_value === false || $ut_value <= 0 || $euro_rate === false || $euro_rate <= 0) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Los valores de la UT y la Tasa del Euro deben ser números positivos válidos.'];
    header('Location: tasas.php');
    exit;
}

// 2. Inicializar controlador y guardar
$infractionsController = new InfractionsController();
// Asume que el controlador tiene un método para delegar al modelo
$saved = $infractionsController->saveOrUpdateEconomicIndicators($ut_value, $euro_rate); 

if ($saved) {
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Indicadores económicos actualizados correctamente para la fecha de hoy.'];
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Error al guardar los indicadores económicos en la base de datos.'];
}

header('Location: tasas.php');
exit;