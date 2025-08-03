<?php
// Ejemplo de cómo usar el UserController para desactivar usuarios

// Incluir el controlador
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();

// Obtener ID del usuario
$user_id = $_GET['id'] ?? null;

// Usar el controlador para procesar la desactivación
$result = $userController->deactivate($user_id);

// Manejar el resultado
if (isset($result['redirect'])) {
    // Si hay una redirección definida, usarla
    header('Location: ' . $result['redirect']);
} else {
    // Redirección por defecto en caso de error
    $error_msg = !$result['success'] ? '&error=' . urlencode($result['message']) : '';
    header('Location: index.php' . $error_msg);
}
exit;
?>