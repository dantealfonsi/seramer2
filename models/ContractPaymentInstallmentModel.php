<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
require_once __DIR__ . '/ContractPaymentModel.php';

class ContractPaymentInstallmentModel extends Model {
    protected $table = 'contract_payment_installments';
    
    public function getByPayment(int $contractPaymentId): array {
        $query = "SELECT cpi.*, pm.name as payment_method_name
                  FROM {$this->table} cpi
                  LEFT JOIN payment_methods pm ON cpi.payment_method_id = pm.id
                  WHERE cpi.contract_payment_id = :contract_payment_id
                  ORDER BY cpi.date DESC";
        
        return $this->query($query, ['contract_payment_id' => $contractPaymentId]);
    }
    
    public function create(array $data) {
        try {
            $this->beginTransaction();
            
            if (empty($data['daily_cash_register_id'])) {
                error_log("Error: daily_cash_register_id es requerido para registrar un abono");
                $this->rollback();
                return false;
            }
            
            $query = "INSERT INTO {$this->table} 
                      (contract_payment_id, payment_method_id, date, amount, concept, daily_cash_register_id) 
                      VALUES 
                      (:contract_payment_id, :payment_method_id, :date, :amount, :concept, :daily_cash_register_id)";
            
            $success = $this->execute($query, [
                'contract_payment_id' => $data['contract_payment_id'],
                'payment_method_id' => $data['payment_method_id'],
                'date' => $data['date'] ?? date('Y-m-d'),
                'amount' => $data['amount'],
                'concept' => $data['concept'] ?? 'Pago de mensualidad',
                'daily_cash_register_id' => $data['daily_cash_register_id']
            ]);
            
            if (!$success) {
                $this->rollback();
                return false;
            }
            
            $installmentId = $this->lastInsertId();
            
            Audit::logInsert('contract_payment_installments', $installmentId, $data);
            
            $this->checkAndUpdatePaymentStatus($data['contract_payment_id']);
            
            $this->commit();
            return $installmentId;
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error al crear abono: " . $e->getMessage());
            return false;
        }
    }
    
    private function checkAndUpdatePaymentStatus(int $contractPaymentId): bool {
        $paymentModel = new ContractPaymentModel();
        $paymentWithRate = $paymentModel->getPaymentWithRateInfo($contractPaymentId);
        
        if (!$paymentWithRate || empty($paymentWithRate['amount_bs'])) {
            return false;
        }
        
        $totalAmountBs = (float) $paymentWithRate['amount_bs'];
        
        $installmentsQuery = "SELECT SUM(amount) as total_paid 
                              FROM {$this->table} 
                              WHERE contract_payment_id = :contract_payment_id";
        
        $result = $this->queryOne($installmentsQuery, ['contract_payment_id' => $contractPaymentId]);
        $totalPaid = (float) ($result['total_paid'] ?? 0);
        
        if ($totalPaid >= ($totalAmountBs - 0.01)) {
            $updateQuery = "UPDATE contract_payments 
                            SET status = 'paid' 
                            WHERE id = :id";
            
            return $this->execute($updateQuery, ['id' => $contractPaymentId]);
        }
        
        return true;
    }
    
    public function getTotalPaid(int $contractPaymentId): float {
        $query = "SELECT SUM(amount) as total_paid 
                  FROM {$this->table} 
                  WHERE contract_payment_id = :contract_payment_id";
        
        $result = $this->queryOne($query, ['contract_payment_id' => $contractPaymentId]);
        return (float) ($result['total_paid'] ?? 0);
    }
    
    public function getRemainingBalance(int $contractPaymentId): float {
        $paymentModel = new ContractPaymentModel();
        $paymentWithRate = $paymentModel->getPaymentWithRateInfo($contractPaymentId);
        
        if (!$paymentWithRate || empty($paymentWithRate['amount_bs'])) {
            return 0;
        }
        
        $totalAmountBs = (float) $paymentWithRate['amount_bs'];
        $totalPaid = $this->getTotalPaid($contractPaymentId);
        
        return max(0, $totalAmountBs - $totalPaid);
    }
}
