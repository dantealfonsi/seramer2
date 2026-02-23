<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ContractController();
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'update_status':
        $result = $controller->bulkUpdateStatus($data);
        break;
    case 'update_payment_status':
        $result = $controller->bulkUpdatePaymentStatus($data);
        break;
    case 'delete':
        $result = $controller->bulkDelete($data['ids'] ?? []);
        break;
    default:
        $result = ['success' => false, 'message' => 'Acción no válida'];
}

header('Content-Type: application/json');
echo json_encode($result);
exit;
