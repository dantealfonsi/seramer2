<?php
require_once __DIR__ . '/../models/AwardeeModel.php';
require_once __DIR__ . '/../models/ContractModel.php';
require_once __DIR__ . '/../models/ContractPaymentModel.php';
require_once __DIR__ . '/../models/ContractPaymentInstallmentModel.php';
require_once __DIR__ . '/../models/PaymentMethodModel.php';
require_once __DIR__ . '/../models/CashRegisterModel.php';
require_once __DIR__ . '/../models/DailyCashRegisterModel.php';
require_once __DIR__ . '/../models/UserModel.php'; // For session user info if needed

class BillingController {
    private $awardeeModel;
    private $contractModel;
    private $paymentModel;
    private $installmentModel;
    private $paymentMethodModel;
    private $cashRegisterModel;
    private $dailyCashModel;
    
    public function __construct() {
        $this->awardeeModel = new AwardeeModel();
        $this->contractModel = new ContractModel();
        $this->paymentModel = new ContractPaymentModel();
        $this->installmentModel = new ContractPaymentInstallmentModel();
        $this->paymentMethodModel = new PaymentMethodModel();
        $this->cashRegisterModel = new CashRegisterModel();
        $this->dailyCashModel = new DailyCashRegisterModel();
    }
    
    public function getPaymentHistory($limit = 50) {
        $query = "SELECT cpi.*, 
                         pm.name as payment_method_name,
                         cp.payment_reference,
                         a.first_name, a.last_name, a.id_number
                  FROM contract_payment_installments cpi
                  JOIN payment_methods pm ON cpi.payment_method_id = pm.id
                  JOIN contract_payments cp ON cpi.contract_payment_id = cp.id
                  JOIN contracts c ON cp.contract_id = c.id
                  JOIN awardees a ON c.awardee_id = a.id
                  ORDER BY cpi.date DESC, cpi.id DESC
                  LIMIT :limit";
        
        // Quick access to DB via one of the models
        return $this->paymentModel->query($query, ['limit' => $limit]);
    }

    public function getDebtors() {
        // Find awardees with overdue payments
        // Logic: Contracts with payments where status = overdue OR (status = pending AND due_date < NOW)
        $query = "SELECT DISTINCT a.*, 
                         COUNT(cp.id) as overdue_count,
                         SUM(cp.amount_bs) as estimated_debt
                  FROM awardees a
                  JOIN contracts c ON a.id = c.awardee_id
                  JOIN contract_payments cp ON c.id = cp.contract_id
                  WHERE (cp.status = 'overdue' OR (cp.status = 'pending' AND cp.due_date < CURDATE()))
                  GROUP BY a.id
                  ORDER BY overdue_count DESC";
                  
        return $this->paymentModel->query($query);
    }
    
    public function search($idNumber = null) {
        if (empty($idNumber)) {
            return [
                'page_title' => 'Gestión de Cobros',
                'has_results' => false
            ];
        }
        
        $awardee = $this->awardeeModel->getByIdNumber($idNumber);
        
        if (!$awardee) {
            return [
                'page_title' => 'Gestión de Cobros',
                'has_results' => false,
                'error' => 'Adjudicatario no encontrado'
            ];
        }
        
        $contracts = $this->contractModel->getByAwardee($awardee['id']);
        $allPayments = $this->paymentModel->getAllPaymentsWithRateByAwardee($awardee['id']);
        $paymentMethods = $this->paymentMethodModel->getActive();
        
        return [
            'page_title' => 'Gestión de Cobros - ' . $awardee['first_name'] . ' ' . $awardee['last_name'],
            'has_results' => true,
            'awardee' => $awardee,
            'contracts' => $contracts,
            'allPayments' => $allPayments,
            'paymentMethods' => $paymentMethods
        ];
    }
    
    public function handleAjax($action, $params = []) {
        switch ($action) {
            case 'register_payment':
                return $this->registerPayment($params);
            case 'get_installments':
                return $this->getInstallments($params);
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }
    
    private function registerPayment($params) {
        $paymentId = (int) ($params['payment_id'] ?? 0);
        $amount = (float) ($params['amount'] ?? 0);
        $paymentMethodId = (int) ($params['payment_method_id'] ?? 0);
        $concept = $params['concept'] ?? 'Pago de mensualidad';
        $userId = $_SESSION['user_id'] ?? 0; // Assuming session is started
        
        if (empty($paymentId) || empty($amount) || empty($paymentMethodId)) {
            return ['success' => false, 'message' => 'Todos los campos son requeridos'];
        }
        
        // 1. Verify Payment
        $payment = $this->paymentModel->getById($paymentId);
        if (!$payment) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        
        if (empty($payment['euro_rate_id'])) {
             return ['success' => false, 'message' => 'El pago no tiene una tasa de euro asignada.'];
        }
        
        $paymentWithRate = $this->paymentModel->getPaymentWithRateInfo($paymentId);
        if (!$paymentWithRate) {
             return ['success' => false, 'message' => 'Error al obtener información del pago'];
        }
        
        // 2. Check Balance
        $totalAmount = (float) ($paymentWithRate['amount_bs'] ?? 0);
        $totalPaid = $this->installmentModel->getTotalPaid($paymentId);
        $remainingBalance = max(0, $totalAmount - $totalPaid);
        
        // Allow a small epsilon for float comparison logic, but stricter
        if ($amount > ($remainingBalance + 0.01)) {
             return ['success' => false, 'message' => 'El monto excede el saldo restante (' . number_format($remainingBalance, 2) . ')'];
        }
        
        // 3. Check Cash Register
        $cashRegister = $this->cashRegisterModel->getByAssignedUser($userId);
        if (!$cashRegister) {
             return ['success' => false, 'message' => 'No tiene una caja asignada.'];
        }
        
        $openCash = $this->dailyCashModel->getOpenCashByRegister($cashRegister['id']);
        if (!$openCash) {
             return ['success' => false, 'message' => 'Debe abrir su caja antes de registrar cobros.'];
        }
        
        // 4. Create Installment
        $installmentId = $this->installmentModel->create([
            'contract_payment_id' => $paymentId,
            'payment_method_id' => $paymentMethodId,
            'amount' => $amount,
            'concept' => $concept,
            'date' => date('Y-m-d'),
            'daily_cash_register_id' => $openCash['id']
        ]);
        
        if ($installmentId) {
            // Recalculate and Update Status
             $newTotalPaid = $this->installmentModel->getTotalPaid($paymentId);
             $newRemainingBalance = max(0, $totalAmount - $newTotalPaid);
             
             if ($newRemainingBalance <= 0.01) {
                 $this->paymentModel->updateStatus($paymentId, 'paid');
                 $status = 'paid';
                 $msg = 'Pago completado.';
             } else {
                 $this->paymentModel->updateStatus($paymentId, 'pending');
                 $status = 'pending';
                 $msg = 'Abono registrado. Pendiente: ' . number_format($newRemainingBalance, 2);
             }
             
             return [
                 'success' => true,
                 'message' => $msg,
                 'payment_status' => $status,
                 'remaining_balance' => $newRemainingBalance,
                 'total_paid' => $newTotalPaid
             ];
        }
        
        return ['success' => false, 'message' => 'Error al registrar el pago'];
    }
    
    private function getInstallments($params) {
        $paymentId = (int) ($params['payment_id'] ?? 0);
        if (!$paymentId) {
             return ['success' => false, 'message' => 'ID de pago requerido'];
        }
        
        $installments = $this->installmentModel->getByPayment($paymentId);
        return [
            'success' => true,
            'installments' => $installments
        ];
    }
    
    public function viewInvoice(int $paymentId) {
        $payment = $this->paymentModel->getPaymentWithRateInfo($paymentId);
        if (!$payment) return null;
        
        $contract = $this->contractModel->getById($payment['contract_id']);
        $awardee = $this->awardeeModel->getById($contract['awardee_id']);
        $installments = $this->installmentModel->getByPayment($paymentId);
        
        return [
            'page_title' => 'Factura #' . $payment['payment_reference'],
            'payment' => $payment,
            'contract' => $contract,
            'awardee' => $awardee,
            'installments' => $installments
        ];
    }
}
