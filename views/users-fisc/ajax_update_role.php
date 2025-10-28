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
$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$roleId = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);

if ($action === 'update_role' && $userId && $roleId) {
    $usersController = new UsersFiscController();
    $usersController->updateRoleAjax($userId, $roleId); // El controlador maneja la salida JSON
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Petición inválida o datos incompletos.']);
}
?>