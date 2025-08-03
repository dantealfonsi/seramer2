<?php
// Procesar eliminación de infracciones

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InfractionsController.php';

$infractionsController = new InfractionsController();

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Obtener el ID de la infracción a eliminar
$id = $_POST['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID de infracción no válido'
    ];
    header('Location: index.php');
    exit;
}

// Procesar la eliminación
$result = $infractionsController->delete($id);

// Los mensajes flash ya se configuran en el controlador
// Redirigir al listado
header('Location: index.php');
exit;
?>