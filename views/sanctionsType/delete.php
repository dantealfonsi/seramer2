<?php
session_start();
require_once __DIR__ . '/../../controllers/SanctionTypesController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if (!$id) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'ID de tipo de sanción no válido.'
        ];
        header("Location: index.php");
        exit;
    }

    $sanctionTypesController = new SanctionTypesController();
    $result = $sanctionTypesController->delete($id);

    $_SESSION['flash_message'] = [
        'type' => $result['success'] ? 'success' : 'danger',
        'message' => $result['message']
    ];

    header("Location: index.php");
    exit;
}

header("Location: index.php");
exit;
