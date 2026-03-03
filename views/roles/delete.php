<?php
require_once __DIR__ . '/../../controllers/RolesController.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php?error=invalid_role");
    exit;
}

$rolesController = new RolesController();
$result = $rolesController->delete($id);

if ($result['success']) {
    header("Location: index.php?success=role_deleted");
} else {
    // Si hay error (ej: intentando eliminar admin) pasamos el mensaje codificado o lo mapeamos
    header("Location: index.php?error=" . urlencode($result['message']));
}
exit;
