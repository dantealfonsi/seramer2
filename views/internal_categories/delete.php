<?php
// Procesar eliminación de rubros internos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/InternalCategoryController.php';

$controller = new InternalCategoryController();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = $_POST['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID de rubro no válido'
    ];
    header('Location: index.php');
    exit;
}

$result = $controller->delete($id);

$_SESSION['flash_message'] = [
    'type' => $result['success'] ? 'success' : 'danger',
    'message' => $result['message']
];

header('Location: index.php');
exit;
?>
