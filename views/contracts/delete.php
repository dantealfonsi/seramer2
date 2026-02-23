<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ContractController();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$result = $controller->delete((int)$id);

echo json_encode($result);
exit;
