<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
require_once __DIR__ . '/FiscalYearModel.php';
require_once __DIR__ . '/ContractModel.php';
// euro rate model is used but not necessarily for class structure here, but used in code

class ContractPaymentModel extends Model {
    protected $table = 'contract_payments';
    
    public function generatePaymentsForFiscalYear(int $fiscalYearId): bool {
        try {
            $fiscalYearModel = new FiscalYearModel();
            $fiscalYear = $fiscalYearModel->getById($fiscalYearId);
            
            if (!$fiscalYear) return false;
            
            $contractModel = new ContractModel();
            $contracts = $contractModel->getByFiscalYear($fiscalYearId);
            
            if (empty($contracts)) return true;
            
            foreach ($contracts as $contract) {
                $success = $this->generatePaymentsForContract($contract['id'], $fiscalYear);
                if (!$success) return false;
            }
            
            return true;
        } catch (\PDOException $e) {
            error_log("Error al generar pagos para año fiscal: " . $e->getMessage());
            return false;
        }
    }
    
    public function generatePaymentsForContract(
        int $contractId, 
        array $fiscalYear, 
        ?string $contractStartDate = null, 
        ?string $contractEndDate = null
    ): bool {
        try {
            $contractModel = new ContractModel();
            $contract = $contractModel->getById($contractId);
            
            if (!$contract) return false;
            
            $paymentCount = $this->getPaymentCountForContract($contractId);
            
            if (!$paymentCount) {
                error_log("No se pudo obtener payment_count para contrato {$contractId}");
                return false;
            }
            
            $startDate = new \DateTime($contractStartDate ?? $contract['start_date'] ?? $fiscalYear['start_date']);
            $endDate = new \DateTime($contractEndDate ?? $contract['end_date'] ?? $fiscalYear['end_date']);
            
            $currentDate = clone $startDate;
            $currentDate->modify('first day of this month');
            
            while ($currentDate <= $endDate) {
                $year = (int) $currentDate->format('Y');
                $month = (int) $currentDate->format('m');
                
                $paymentDate = sprintf('%04d-%02d-15', $year, $month);
                $paymentDateTime = new \DateTime($paymentDate);
                if ($paymentDateTime < $startDate) {
                    $paymentDate = $startDate->format('Y-m-d');
                }
                
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
                
                if (!$success) return false;
                
                $currentDate->modify('first day of next month');
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
        
        if (!$result || !$result['amount']) return 0.00;
        
        $amount = (float) $result['amount'];
        return round($amount * $euroRateValue, 2);
    }
    
    public function updatePaymentAmount(int $paymentId, int $euroRateId, float $euroRateValue): bool {
        $payment = $this->findById($paymentId);
        if (!$payment) return false;
        
        $amount = $this->calculatePaymentAmount($payment['contract_id'], $euroRateValue);
        
        $query = "UPDATE {$this->table} 
                  SET euro_rate_id = :euro_rate_id, amount = :amount 
                  WHERE id = :id";
        
        return $this->execute($query, [
            'euro_rate_id' => $euroRateId,
            'amount' => $amount,
            'id' => $paymentId
        ]);
    }
    
    private function getPaymentCountForContract(int $contractId): ?float {
        $query = "
            SELECT COALESCE(ibc.payment_count, ebc.payment_count) as payment_count
            FROM contracts c
            LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
            LEFT JOIN internal_business_categories ibc ON cbc.internal_category_id = ibc.id
            LEFT JOIN external_business_categories ebc ON cbc.external_category_id = ebc.id
            WHERE c.id = :contract_id
            LIMIT 1
        ";
        
        $result = $this->queryOne($query, ['contract_id' => $contractId]);
        
        return $result ? (float) $result['payment_count'] : null;
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
                  GROUP BY cp.id, cp.contract_id, cp.payment_reference, cp.euro_rate_id, cp.payment_date, 
                           cp.amount, cp.status, er.bs_value
                  ORDER BY cp.payment_date ASC";
        return $this->query($query, ['contract_id' => $contractId]);
    }
    
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    public function getAllPaymentsWithRateByAwardee(int $awardeeId): array {
        $query = "SELECT 
                    cp.*,
                    er.bs_value as rate_amount,
                    MONTH(cp.payment_date) as month_num,
                    YEAR(cp.payment_date) as year,
                    (SELECT SUM(COALESCE(ic.payment_count, 0) + COALESCE(ec.payment_count, 0))
                     FROM contract_business_categories cbc
                     LEFT JOIN internal_business_categories ic ON cbc.internal_category_id = ic.id
                     LEFT JOIN external_business_categories ec ON cbc.external_category_id = ec.id
                     WHERE cbc.contract_id = cp.contract_id
                    ) as amount_euro,
                    ((SELECT SUM(COALESCE(ic2.payment_count, 0) + COALESCE(ec2.payment_count, 0))
                      FROM contract_business_categories cbc2
                      LEFT JOIN internal_business_categories ic2 ON cbc2.internal_category_id = ic2.id
                      LEFT JOIN external_business_categories ec2 ON cbc2.external_category_id = ec2.id
                      WHERE cbc2.contract_id = cp.contract_id
                     ) * COALESCE(er.bs_value, 0)) as amount_bs
                  FROM {$this->table} cp
                  INNER JOIN contracts c ON cp.contract_id = c.id
                  LEFT JOIN euro_rates er ON cp.euro_rate_id = er.id
                  WHERE c.awardee_id = :awardee_id
                  ORDER BY cp.payment_date DESC";
        
        $results = $this->query($query, ['awardee_id' => $awardeeId]);
        
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        foreach ($results as &$row) {
            $row['month_name'] = $months[(int)$row['month_num']] ?? 'N/A';
        }
        
        return $results;
    }

    public function getPaymentWithRateInfo(int $id): ?array {
        $query = "SELECT 
                    cp.*,
                    er.bs_value as rate_amount,
                    MONTH(cp.payment_date) as month_num,
                    YEAR(cp.payment_date) as year,
                    (SELECT SUM(COALESCE(ic.payment_count, 0) + COALESCE(ec.payment_count, 0))
                     FROM contract_business_categories cbc
                     LEFT JOIN internal_business_categories ic ON cbc.internal_category_id = ic.id
                     LEFT JOIN external_business_categories ec ON cbc.external_category_id = ec.id
                     WHERE cbc.contract_id = cp.contract_id
                    ) as amount_euro,
                    ((SELECT SUM(COALESCE(ic2.payment_count, 0) + COALESCE(ec2.payment_count, 0))
                      FROM contract_business_categories cbc2
                      LEFT JOIN internal_business_categories ic2 ON cbc2.internal_category_id = ic2.id
                      LEFT JOIN external_business_categories ec2 ON cbc2.external_category_id = ec2.id
                      WHERE cbc2.contract_id = cp.contract_id
                     ) * COALESCE(er.bs_value, 0)) as amount_bs
                  FROM {$this->table} cp
                  LEFT JOIN euro_rates er ON cp.euro_rate_id = er.id
                  WHERE cp.id = :id
                  LIMIT 1";
        
        $row = $this->queryOne($query, ['id' => $id]);
        
        if ($row) {
            $months = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $row['month_name'] = $months[(int)$row['month_num']] ?? 'N/A';
        }
        
        return $row;
    }

    public function updateStatus(int $id, string $status): bool {
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        return $this->execute($query, ['id' => $id, 'status' => $status]);
    }
}
