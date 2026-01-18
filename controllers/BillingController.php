<?php
require_once __DIR__ . '/../models/AwardeeModel.php';
require_once __DIR__ . '/../models/ContractModel.php';
require_once __DIR__ . '/../models/ContractPaymentModel.php';
require_once __DIR__ . '/../models/ContractPaymentInstallmentModel.php';
require_once __DIR__ . '/../models/SanctionsModel.php';
require_once __DIR__ . '/../models/FinePaymentModel.php';
require_once __DIR__ . '/../models/PaymentMethodModel.php';
require_once __DIR__ . '/../models/CashRegisterModel.php';
require_once __DIR__ . '/../models/DailyCashRegisterModel.php';
require_once __DIR__ . '/../models/BillingReportModel.php';
require_once __DIR__ . '/../models/FeePaymentModel.php';
require_once __DIR__ . '/../models/MarketStallModel.php';

class BillingController {
    private $awardeeModel;
    private $contractModel;
    private $paymentModel;
    private $installmentModel;
    private $sanctionsModel;
    private $finePaymentModel;
    private $feePaymentModel;
    private $paymentMethodModel;
    private $cashRegisterModel;
    private $dailyCashModel;
    private $billingReportModel;
    private $stallModel;
    
    public function __construct() {
        $this->awardeeModel = new AwardeeModel();
        $this->contractModel = new ContractModel();
        $this->paymentModel = new ContractPaymentModel();
        $this->installmentModel = new ContractPaymentInstallmentModel();
        $this->sanctionsModel = new SanctionsModel();
        $this->finePaymentModel = new FinePaymentModel();
        $this->feePaymentModel = new FeePaymentModel();
        $this->paymentMethodModel = new PaymentMethodModel();
        $this->cashRegisterModel = new CashRegisterModel();
        $this->dailyCashModel = new DailyCashRegisterModel();
        $this->billingReportModel = new BillingReportModel();
        $this->stallModel = new MarketStallModel();
    }
    
    /**
     * Unified search for debtors (by ID, name, or stall).
     * @param string $searchTerm
     * @param string $searchType 'id_number', 'name', 'stall'
     * @return array
     */
    public function searchDebtor($searchTerm, $searchType = 'id_number') {
        if (empty($searchTerm)) {
            return [
                'page_title' => 'Cuentas por Cobrar',
                'has_results' => false
            ];
        }
        
        $awardee = null;
        
        // Search based on type
        switch ($searchType) {
            case 'id_number':
                $awardee = $this->awardeeModel->getByIdNumber($searchTerm);
                break;
            
            case 'name':
                $results = $this->awardeeModel->search($searchTerm);
                $awardee = !empty($results) ? $results[0] : null;
                break;
            
            case 'stall':
                $stall = $this->stallModel->searchByStallNumber($searchTerm);
                if ($stall) {
                    // Get contract for this stall
                    $contract = $this->contractModel->getByStall($stall['id']);
                    if ($contract) {
                        $awardee = $this->awardeeModel->getById($contract['awardee_id']);
                    }
                }
                break;
        }
        
        if (!$awardee) {
            return [
                'page_title' => 'Cuentas por Cobrar',
                'has_results' => false,
                'error' => 'No se encontró el contribuyente'
            ];
        }
        
        // Get debt summary
        $debtSummary = $this->getDebtSummary($awardee['id']);
        
        return [
            'page_title' => 'Cuentas por Cobrar - ' . $awardee['first_name'] . ' ' . $awardee['last_name'],
            'has_results' => true,
            'awardee' => $awardee,
            'contracts' => $debtSummary['contracts'],
            'contract_payments' => $debtSummary['contract_payments'],
            'sanctions' => $debtSummary['sanctions'],
            'total_debt' => $debtSummary['total_debt'],
            'paymentMethods' => $this->paymentMethodModel->getActive()
        ];
    }
    
    /**
     * Get complete debt summary for an awardee.
     * @param int $awardeeId
     * @return array
     */
    public function getDebtSummary($awardeeId) {
        // Get contracts
        $contracts = $this->contractModel->getByAwardee($awardeeId);
        
        // Get pending contract payments
        $contractPayments = $this->paymentModel->getAllPaymentsWithRateByAwardee($awardeeId);
        
        // Get pending sanctions (fines)
        $sanctions = $this->sanctionsModel->getPendingSanctionsByAwardee($awardeeId);
        
        // Calculate total debt
        $contractsDebt = 0;
        foreach ($contractPayments as $payment) {
            if ($payment['status'] !== 'paid') {
                $contractsDebt += (float)($payment['amount_bs'] ?? 0);
            }
        }
        
        $sanctionsDebt = 0;
        foreach ($sanctions as $sanction) {
            $sanctionsDebt += (float)$sanction['fine_amount'];
        }
        
        return [
            'contracts' => $contracts,
            'contract_payments' => $contractPayments,
            'sanctions' => $sanctions,
            'contracts_debt' => $contractsDebt,
            'sanctions_debt' => $sanctionsDebt,
            'total_debt' => $contractsDebt + $sanctionsDebt
        ];
    }
    
    /**
     * Process a payment (contract or fine).
     * @param array $paymentData
     * @return array
     */
    public function processPayment($paymentData) {
        $paymentType = $paymentData['payment_type'] ?? 'contract'; // 'contract' or 'fine'
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Verify cash register is assigned and active
        $cashRegister = $this->cashRegisterModel->getByAssignedUser($userId);
        if (!$cashRegister) {
            return ['success' => false, 'message' => 'No tiene una caja activa asignada para realizar cobros.'];
        }
        
        // Auto-get or create usage session for today
        $dailyCashRegisterId = $this->dailyCashModel->getOrCreateCurrentSession($cashRegister['id'], $userId);
        
        // Process based on type
        if ($paymentType === 'fine') {
            return $this->processFinePayment($paymentData, $dailyCashRegisterId);
        } else {
            return $this->processContractPayment($paymentData, $dailyCashRegisterId);
        }
    }
    
    /**
     * Process a fine payment.
     * @param array $paymentData
     * @param int $dailyCashRegisterId
     * @return array
     */
    private function processFinePayment($paymentData, $dailyCashRegisterId) {
        $sanctionId = (int)($paymentData['sanction_id'] ?? 0);
        $amount = (float)($paymentData['amount'] ?? 0);
        $paymentMethodId = (int)($paymentData['payment_method_id'] ?? 0);
        $transactionRef = $paymentData['transaction_reference'] ?? null;
        
        if (!$sanctionId || !$amount || !$paymentMethodId) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }
        
        // Get payment method name for payment_type
        $pm = $this->paymentMethodModel->getById($paymentMethodId);
        $paymentTypeName = $pm ? $pm['name'] : 'General';

        // Get sanction details
        $sanction = $this->sanctionsModel->getSanctionWithDetails($sanctionId);
        if (!$sanction) {
            return ['success' => false, 'message' => 'Sanción no encontrada'];
        }
        
        // Verify amount
        $fineAmount = (float)$sanction['fine_amount'];
        if ($amount > ($fineAmount + 0.01)) {
            return ['success' => false, 'message' => 'El monto excede el valor de la multa'];
        }
        
        // Create payment
        $result = $this->finePaymentModel->create([
            'sanction_id' => $sanctionId,
            'amount_paid' => $amount,
            'payment_method_id' => $paymentMethodId,
            'transaction_reference' => $transactionRef,
            'daily_cash_register_id' => $dailyCashRegisterId,
            'payment_type' => $paymentTypeName
        ]);
        
        return $result;
    }
    
    /**
     * Process a contract payment.
     * @param array $paymentData
     * @param int $dailyCashRegisterId
     * @return array
     */
    private function processContractPayment($paymentData, $dailyCashRegisterId) {
        $paymentId = (int)($paymentData['payment_id'] ?? 0);
        $amount = (float)($paymentData['amount'] ?? 0);
        $paymentMethodId = (int)($paymentData['payment_method_id'] ?? 0);
        $concept = $paymentData['concept'] ?? 'Pago de mensualidad';
        $transactionRef = $paymentData['transaction_reference'] ?? null;

        if (!$paymentId || !$amount || !$paymentMethodId) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }
        
        // Get payment method name
        $pm = $this->paymentMethodModel->getById($paymentMethodId);
        $paymentTypeName = $pm ? $pm['name'] : 'Mensualidad';

        // Verify payment exists
        $payment = $this->paymentModel->getById($paymentId);
        if (!$payment) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        
        // Get payment with rate info
        $paymentWithRate = $this->paymentModel->getPaymentWithRateInfo($paymentId);
        if (!$paymentWithRate) {
            return ['success' => false, 'message' => 'Error al obtener información del pago'];
        }
        
        // Check balance
        $totalAmount = (float)($paymentWithRate['amount_bs'] ?? 0);
        $totalPaid = $this->installmentModel->getTotalPaid($paymentId);
        $remainingBalance = max(0, $totalAmount - $totalPaid);
        
        if ($amount > ($remainingBalance + 0.01)) {
            return ['success' => false, 'message' => 'El monto excede el saldo restante (' . number_format($remainingBalance, 2) . ')'];
        }
        
        // Create installment (for system tracking and status updating)
        $installmentId = $this->installmentModel->create([
            'contract_payment_id' => $paymentId,
            'payment_method_id' => $paymentMethodId,
            'amount' => $amount,
            'concept' => $concept,
            'date' => date('Y-m-d'),
            'daily_cash_register_id' => $dailyCashRegisterId
        ]);
        
        if ($installmentId) {
            // ALSO create in fee_payments as requested by the user
            $this->feePaymentModel->create([
                'contract_id' => $payment['contract_id'],
                'period_month' => $payment['payment_date'], // The date of the contract_payment record represents the month
                'amount_paid' => $amount,
                'payment_type' => $paymentTypeName,
                'payment_status' => 'Paid',
                'transaction_reference' => $transactionRef,
                'payment_method_id' => $paymentMethodId,
                'daily_cash_register_id' => $dailyCashRegisterId
            ]);

            // Update payment status
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
    
    /**
     * Get payment history.
     * @param int $limit
     * @return array
     */
    public function getPaymentHistory($limit = 100) {
        // Combine both contract and fine payments
        $contractPayments = $this->getContractPaymentHistory($limit);
        $finePayments = $this->finePaymentModel->getPaymentHistory($limit);
        
        // Merge and sort by date
        $allPayments = array_merge($contractPayments, $finePayments);
        
        usort($allPayments, function($a, $b) {
            $dateA = strtotime($a['date'] ?? $a['payment_date']);
            $dateB = strtotime($b['date'] ?? $b['payment_date']);
            return $dateB - $dateA;
        });
        
        return array_slice($allPayments, 0, $limit);
    }
    
    /**
     * Get contract payment history.
     * @param int $limit
     * @return array
     */
    private function getContractPaymentHistory($limit) {
        $query = "SELECT cpi.*, 
                         pm.name as payment_method_name,
                         cp.payment_reference,
                         a.first_name, a.last_name, a.id_number,
                         'contract' as payment_type
                  FROM contract_payment_installments cpi
                  JOIN payment_methods pm ON cpi.payment_method_id = pm.id
                  JOIN contract_payments cp ON cpi.contract_payment_id = cp.id
                  JOIN contracts c ON cp.contract_id = c.id
                  JOIN awardees a ON c.awardee_id = a.id
                  ORDER BY cpi.date DESC, cpi.id DESC
                  LIMIT :limit";
        
        return $this->paymentModel->query($query, ['limit' => $limit]);
    }
    
    /**
     * Get debtors list.
     * @return array
     */
    public function getDebtors() {
        return $this->billingReportModel->getDelinquentAccounts();
    }
    
    /**
     * Handle AJAX requests.
     * @param string $action
     * @param array $params
     * @return array
     */
    public function handleAjax($action, $params = []) {
        switch ($action) {
            case 'register_payment':
                return $this->processPayment($params);
            case 'get_debt_summary':
                $awardeeId = (int)($params['awardee_id'] ?? 0);
                return $this->getDebtSummary($awardeeId);
            case 'search_debtor':
                return $this->searchDebtor($params['search_term'] ?? '', $params['search_type'] ?? 'id_number');
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }
}
