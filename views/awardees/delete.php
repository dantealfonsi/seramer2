<?php
// Procesar eliminación de adjudicatarios
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AwardeeController.php';

$controller = new AwardeeController();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = $_POST['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID de adjudicatario no válido'
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
