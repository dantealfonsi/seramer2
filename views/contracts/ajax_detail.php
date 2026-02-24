<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ContractController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add_location':
        $result = $controller->addLocation($_POST);
        break;
    case 'remove_location':
        $result = $controller->removeLocation($_POST);
        break;
    case 'add_category':
        $result = $controller->addCategory($_POST);
        break;
    case 'remove_category':
        $result = $controller->removeCategory($_POST);
        break;
    case 'delete_payment':
        $result = $controller->deletePayment((int)$_POST['payment_id']);
        break;
    case 'bulk_delete_payments':
        $result = $controller->bulkDeletePayments($_POST['ids'] ?? []);
        break;
    case 'bulk_update_payment_status':
        $result = $controller->bulkUpdateIndividualPaymentStatus($_POST);
        break;
    default:
        $result = ['success' => false, 'message' => 'Acción no válida'];
}

header('Content-Type: application/json');
echo json_encode($result);
exit;
