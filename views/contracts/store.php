<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ContractController();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$result = $controller->store($_POST);

if ($result['success']) {
    header('Location: ' . ($result['redirect'] ?? 'index.php'));
} else {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => $result['message']
    ];
    header('Location: create.php');
}
exit;
