<?php
// ajax_update_role.php

require_once __DIR__ . '/../../controllers/UsersFiscController.php';

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit;
}

// 1. Obtener y validar datos de la petición
$action = filter_input(INPUT_POST, 'action', FILTER_DEFAULT);

$controller = new UsersFiscController();

if ($action === 'update_role') {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $roleId = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);    

    if ($userId && $roleId) {
        $usersController = new UsersFiscController();
        $usersController->updateRoleAjax($userId, $roleId); // El controlador maneja la salida JSON
    } else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de usuario o rol inválido.']);
    }
} elseif ($action === 'update_permissions') {
    $roleId = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
    // Usar FILTER_DEFAULT + htmlspecialchars para la máscara, luego validación más estricta en el modelo.
    $mask = filter_input(INPUT_POST, 'permissions_mask', FILTER_DEFAULT); 

    if ($roleId && $mask) {
        $controller->updatePermissionsAjax($roleId, $mask); // El controlador maneja la salida JSON
    } else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parámetros incompletos o inválidos.']);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Petición inválida o datos incompletos.']);
}

?>