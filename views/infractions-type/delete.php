<?php
session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InfractionTypesController.php';

// Verificar si la solicitud es de tipo POST y si el método es DELETE (simulado)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['_method'] ?? null) !== 'DELETE') {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Método de solicitud no válido.'
    ];
    header('Location: index.php');
    exit;
}

$infractionTypesController = new InfractionTypesController();

// Obtener el ID del formulario
$id = $_POST['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'No se especificó un tipo de infracción para eliminar.'
    ];
    header('Location: index.php');
    exit;
}

$result = $infractionTypesController->delete($id);

if ($result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Tipo de infracción eliminado correctamente.'
    ];
} else {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => $result['message']
    ];
}

header('Location: index.php');
exit;
