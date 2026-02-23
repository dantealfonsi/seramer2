<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
require_once __DIR__ . '/FiscalYearModel.php';
require_once __DIR__ . '/ContractModel.php';

class ContractPaymentModel extends Model {
    protected $table = 'contract_payments';
    
    /**
     * Genera los 12 pagos mensuales para un año fiscal (RF02 + RF03)
     * 
     * Genera automáticamente los 12 registros de pago (facturas) para
     * TODOS los contratos vigentes del año fiscal
     * 
     * @param int $fiscalYearId ID del año fiscal
     * @return bool True si tuvo éxito
     */
    public function generatePaymentsForFiscalYear(int $fiscalYearId): bool {
        try {
            // Obtener el año fiscal
            $fiscalYearModel = new FiscalYearModel();
            $fiscalYear = $fiscalYearModel->getById($fiscalYearId);
            
            if (!$fiscalYear) {
                return false;
            }
            
            // Obtener todos los contratos del año fiscal
            $contractModel = new ContractModel();
            $contracts = $contractModel->getByFiscalYear($fiscalYearId);
            
            if (empty($contracts)) {
                return true; // No hay contratos, pero no es un error
            }
            
            // Generar pagos para cada contrato
            foreach ($contracts as $contract) {
                $success = $this->generatePaymentsForContract($contract['id'], $fiscalYear);
                
                if (!$success) {
                    return false;
                }
            }
            
            return true;
            
        } catch (\PDOException $e) {
            error_log("Error al generar pagos para año fiscal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera los pagos mensuales para un contrato específico
     * Solo genera pagos desde la fecha de inicio hasta la fecha de fin del contrato
     * 
     * @param int $contractId ID del contrato
     * @param array $fiscalYear Datos del año fiscal
     * @param string|null $contractStartDate Fecha de inicio del contrato
     * @param string|null $contractEndDate Fecha de fin del contrato
     * @return bool True si tuvo éxito
     */
    public function generatePaymentsForContract(
        int $contractId, 
        array $fiscalYear, 
        ?string $contractStartDate = null, 
        ?string $contractEndDate = null
    ): bool {
        try {
            // Obtener el contrato con sus categorías
            $contractModel = new ContractModel();
            $contract = $contractModel->getById($contractId);
            
            if (!$contract) {
                return false;
            }
            
            // Determinar las fechas de inicio y fin
            $startDate = new \DateTime($contractStartDate ?? $contract['start_date'] ?? $fiscalYear['start_date']);
            $endDate = new \DateTime($contractEndDate ?? $contract['end_date'] ?? $fiscalYear['end_date']);
            
            // Generar pagos solo para los meses entre la fecha de inicio y la fecha de fin
            $currentDate = clone $startDate;
            $currentDate->modify('first day of this month'); 
            
            $paymentNumber = 1;
            
            while ($currentDate <= $endDate) {
                $year = (int) $currentDate->format('Y');
                $month = (int) $currentDate->format('m');
                
                // Día 15 de cada mes para el pago
                $paymentDate = sprintf('%04d-%02d-15', $year, $month);
                
                // Verificar que la fecha de pago no sea anterior a la fecha de inicio del contrato
                $paymentDateTime = new \DateTime($paymentDate);
                if ($paymentDateTime < $startDate) {
                    $paymentDate = $startDate->format('Y-m-d');
                }
                
                // Generar referencia de pago
                $paymentReference = $this->generatePaymentReference($contractId, $month, $year);
                
                $query = "INSERT INTO {$this->table} 
                          (contract_id, payment_reference, payment_date, euro_rate_id, amount, status) 
                          VALUES 
                          (:contract_id, :payment_reference, :payment_date, NULL, 0.00, 'pending')";
                
                $success = $this->execute($query, [
                    'contract_id' => $contractId,
                    'payment_reference' => $paymentReference,
                    'payment_date' => $paymentDate
                ]);
                
                if (!$success) {
                    return false;
                }
                
                $currentDate->modify('first day of next month');
                $paymentNumber++;
            }
            
            return true;
            
        } catch (\PDOException $e) {
            error_log("Error al generar pagos para contrato: " . $e->getMessage());
            return false;
        }
    }
    
    public function calculatePaymentAmount(int $contractId, float $euroRateValue): float {
        $query = "
            SELECT 
                COALESCE(ibc.payment_count, ebc.payment_count) as amount
            FROM contracts c
            LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
            LEFT JOIN internal_business_categories ibc ON cbc.internal_category_id = ibc.id
            LEFT JOIN external_business_categories ebc ON cbc.external_category_id = ebc.id
            WHERE c.id = :contract_id
            LIMIT 1
        ";
        
        $result = $this->queryOne($query, ['contract_id' => $contractId]);
        
        if (!$result || !$result['amount']) {
            return 0.00;
        }
        
        $amount = (float) $result['amount'];
        return round($amount * $euroRateValue, 2);
    }
    
    public function updatePaymentAmount(int $paymentId, int $euroRateId, float $euroRateValue): bool {
        $payment = $this->findById($paymentId);
        if (!$payment) return false;
        
        $amount = $this->calculatePaymentAmount($payment['contract_id'], $euroRateValue);
        
        $query = "UPDATE {$this->table} SET euro_rate_id = :euro_rate_id, amount = :amount WHERE id = :id";
        return $this->execute($query, [
            'euro_rate_id' => $euroRateId,
            'amount' => $amount,
            'id' => $paymentId
        ]);
    }
    
    private function generatePaymentReference(int $contractId, int $month, int $year): string {
        return sprintf('PAY-%04d-%02d-%06d', $year, $month, $contractId);
    }
    
    public function getByContract(int $contractId): array {
        $query = "SELECT cp.*,
                         er.bs_value as euro_rate_value,
                         GROUP_CONCAT(DISTINCT 
                            CASE 
                                WHEN ic.name IS NOT NULL THEN ic.name
                                WHEN ec.name IS NOT NULL THEN ec.name
                            END
                            SEPARATOR ', '
                         ) as categories_names,
                         (SELECT SUM(COALESCE(ic2.payment_count, 0) + COALESCE(ec2.payment_count, 0))
                          FROM contract_business_categories cbc2
                          LEFT JOIN internal_business_categories ic2 ON cbc2.internal_category_id = ic2.id
                          LEFT JOIN external_business_categories ec2 ON cbc2.external_category_id = ec2.id
                          WHERE cbc2.contract_id = cp.contract_id
                         ) as total_payment_count,
                         (SELECT SUM(COALESCE(ic2.payment_count, 0) + COALESCE(ec2.payment_count, 0))
                          FROM contract_business_categories cbc2
                          LEFT JOIN internal_business_categories ic2 ON cbc2.internal_category_id = ic2.id
                          LEFT JOIN external_business_categories ec2 ON cbc2.external_category_id = ec2.id
                          WHERE cbc2.contract_id = cp.contract_id
                         ) * COALESCE(er.bs_value, 0) as calculated_amount
                  FROM {$this->table} cp
                  LEFT JOIN contracts c ON cp.contract_id = c.id
                  LEFT JOIN euro_rates er ON cp.euro_rate_id = er.id
                  LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
                  LEFT JOIN internal_business_categories ic ON cbc.internal_category_id = ic.id
                  LEFT JOIN external_business_categories ec ON cbc.external_category_id = ec.id
                  WHERE cp.contract_id = :contract_id
                  GROUP BY cp.id
                  ORDER BY cp.payment_date ASC";
        return $this->query($query, ['contract_id' => $contractId]);
    }
    
    public function getById(int $id) {
        return $this->findById($id);
    }
    
    public function getPendingPaymentsWithRate(int $contractId): array {
        $query = "SELECT cp.*, er.bs_value as euro_rate_value
                  FROM {$this->table} cp
                  INNER JOIN euro_rates er ON cp.euro_rate_id = er.id
                  WHERE cp.contract_id = :contract_id
                  AND cp.status = 'pending'
                  AND cp.euro_rate_id IS NOT NULL
                  AND DATE_FORMAT(cp.payment_date, '%Y-%m') <= DATE_FORMAT(CURDATE(), '%Y-%m')
                  ORDER BY cp.payment_date ASC";
        return $this->query($query, ['contract_id' => $contractId]);
    }
    
    public function updateStatus(int $id, string $status): bool {
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        return $this->execute($query, ['status' => $status, 'id' => $id]);
    }
    
    public function getPaymentWithRateInfo(int $paymentId): ?array {
        $query = "SELECT cp.*, 
                         er.bs_value as euro_rate_value,
                         er.month as euro_rate_month,
                         er.year as euro_rate_year,
                         (SELECT SUM(COALESCE(ic.payment_count, 0) + COALESCE(ec.payment_count, 0))
                          FROM contract_business_categories cbc
                          LEFT JOIN internal_business_categories ic ON cbc.internal_category_id = ic.id
                          LEFT JOIN external_business_categories ec ON cbc.external_category_id = ec.id
                          WHERE cbc.contract_id = cp.contract_id
                         ) as amount_eur,
                         ((SELECT SUM(COALESCE(ic.payment_count, 0) + COALESCE(ec.payment_count, 0))
                          FROM contract_business_categories cbc
                          LEFT JOIN internal_business_categories ic ON cbc.internal_category_id = ic.id
                          LEFT JOIN external_business_categories ec ON cbc.external_category_id = ec.id
                          WHERE cbc.contract_id = cp.contract_id
                         ) * er.bs_value) as amount_bs,
                         COALESCE((SELECT SUM(cpi.amount) 
                                   FROM contract_payment_installments cpi 
                                   WHERE cpi.contract_payment_id = cp.id), 0) as total_paid
                  FROM {$this->table} cp
                  LEFT JOIN euro_rates er ON cp.euro_rate_id = er.id
                  WHERE cp.id = :payment_id
                  LIMIT 1";
        
        $result = $this->queryOne($query, ['payment_id' => $paymentId]);
        if ($result) {
            $result['remaining_balance'] = max(0, ($result['amount_bs'] ?? 0) - ($result['total_paid'] ?? 0));
        }
        return $result;
    }

    public function canDeletePayment(int $id): array {
        $query = "SELECT COUNT(*) as installments_count
                  FROM contract_payment_installments
                  WHERE contract_payment_id = :payment_id";
        $result = $this->queryOne($query, ['payment_id' => $id]);
        $count = (int) ($result['installments_count'] ?? 0);
        if ($count > 0) {
            return ['can_delete' => false, 'message' => "Tiene {$count} abonos."];
        }
        return ['can_delete' => true, 'message' => ''];
    }

    public function deletePayment(int $id): bool {
        $payment = $this->getById($id);
        if (!$payment) return false;
        $validation = $this->canDeletePayment($id);
        if (!$validation['can_delete']) return false;
        
        $query = "DELETE FROM contract_payments WHERE id = :id";
        $success = $this->execute($query, ['id' => $id]);
        if ($success) Audit::logDelete('contract_payments', $id, $payment);
        return $success;
    }

    public function updatePaymentStatus(int $id, string $status): bool {
        $validStatuses = ['pending', 'paid', 'cancelled', 'refunded'];
        if (!in_array($status, $validStatuses)) return false;
        $payment = $this->findById($id);
        if (!$payment) return false;
        
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $success = $this->execute($query, [':status' => $status, ':id' => $id]);
        if ($success) {
            Audit::logUpdate('contract_payments', $id, ['status' => $payment['status']], ['status' => $status]);
        }
        return $success;
    }
}
