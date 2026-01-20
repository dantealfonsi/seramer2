<?php
require_once __DIR__ . '/../../controllers/BillingController.php';

// Ensure JSON response
header('Content-Type: application/json');

$controller = new BillingController();
$action = $_GET['action'] ?? '';
$params = $_POST;

echo json_encode($controller->handleAjax($action, $params));
exit;
