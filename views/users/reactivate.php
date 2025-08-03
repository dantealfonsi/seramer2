<?php
// Verificar acceso - permitir RRHH y jefes de departamento
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

require_once __DIR__ . '/../../models/UserModel.php';

$userModel = new UserModel();

// Verificar roles
$is_manager = AuthMiddleware::isManager();
$is_rrhh = AuthMiddleware::hasAccessToDepartment('Recursos Humanos');

// Obtener ID del usuario
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header('Location: index.php?error=invalid_user');
    exit;
}

// Obtener datos del usuario
$user = $userModel->getUserWithStaffDetails($user_id);

if (!$user) {
    header('Location: index.php?error=user_not_found');
    exit;
}

// Verificar permisos: RRHH puede reactivar cualquiera, jefes solo de su departamento
if ($is_manager && !$is_rrhh) {
    if ($user['department_id'] != $is_manager['id']) {
        header('Location: index.php?error=no_permission');
        exit;
    }
}

// Verificar que el usuario esté inactivo
if ($user['status'] == 'active') {
    header('Location: edit.php?id=' . $user_id . '&error=already_active');
    exit;
}

// Procesar reactivación
$result = $userModel->reactivateUser($user_id);

if ($result) {
    header('Location: edit.php?id=' . $user_id . '&success=user_reactivated');
} else {
    header('Location: edit.php?id=' . $user_id . '&error=reactivation_failed');
}
exit;
?>