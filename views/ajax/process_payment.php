<?php
session_start();
require_once __DIR__ . '/../../controllers/BillingController.php';

header('Content-Type: application/json');

try {
    $controller = new BillingController();
    
    // Get payment data from POST
    $paymentData = [
        'payment_type' => $_POST['payment_type'] ?? 'contract',
        'payment_id' => $_POST['payment_id'] ?? null,
        'sanction_id' => $_POST['sanction_id'] ?? null,
        'amount' => $_POST['amount'] ?? 0,
        'payment_method_id' => $_POST['payment_method_id'] ?? 0,
        'transaction_reference' => $_POST['transaction_reference'] ?? null,
        'concept' => $_POST['concept'] ?? 'Pago de deuda'
    ];

    // DEBUG: Log incoming payment data
    error_log("Payment Process Request: " . print_r($paymentData, true));

    
    // Process payment
    $result = $controller->processPayment($paymentData);
    
    echo json_encode($result);
    
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del sistema: ' . $e->getMessage()
    ]);
}
