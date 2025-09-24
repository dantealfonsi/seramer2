<?php
// Script de eliminación para sanciones

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/SanctionsController.php';

$sanctionsController = new SanctionsController();

// Manejar la petición POST para eliminar el registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    if ($id) {
        $result = $sanctionsController->delete($id);
        
        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => $result['message']
            ];
        }
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'ID de sanción no especificado para eliminar.'
        ];
    }
} else {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Método de solicitud no permitido.'
    ];
}

header('Location: index.php');
exit;
