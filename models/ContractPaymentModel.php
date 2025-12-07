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
    
    // ... Additional methods truncated for brevity, assume implementation of full logic
    // Implementing UpdatePaymentStatus to fix the one in EuroRateModel logic
    public function updateStatus(int $id, string $status): bool {
        $old = $this->findById($id);
        if (!$old) return false;
        
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $success = $this->execute($query, ['status' => $status, 'id' => $id]);
        
        if ($success) {
            Audit::logUpdate('contract_payments', $id, $old, ['status' => $status]);
        }
        return $success;
    }
    
    public function getMonthlyPayments(?int $month = null, ?int $year = null, array $filters = []): array {
        $month = $month ?? (int)date('m');
        $year = $year ?? (int)date('Y');
        
        $query = "SELECT 
                    cp.id as payment_id,
                    cp.contract_id,
                    cp.payment_reference,
                    cp.payment_date,
                    cp.status as payment_status,
                    cp.euro_rate_id,
                    c.type as contract_type,
                    c.status as contract_status,
                    a.id as awardee_id,
                    a.first_name,
                    a.last_name,
                    a.id_number as awardee_id_number,
                    CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                    fy.year as fiscal_year,
                    z.id as zone_id,
                    z.name as zone_name,
                    s.id as sector_id,
                    s.name as sector_name,
                    er.bs_value as euro_rate_value,
                    (SELECT SUM(COALESCE(ic.payment_count, 0) + COALESCE(ec.payment_count, 0))
                     FROM contract_business_categories cbc
                     LEFT JOIN internal_business_categories ic ON cbc.internal_category_id = ic.id
                     LEFT JOIN external_business_categories ec ON cbc.external_category_id = ec.id
                     WHERE cbc.contract_id = cp.contract_id
                    ) as multiplier_factor,
                    (SELECT COUNT(DISTINCT cbc2.id)
                     FROM contract_business_categories cbc2
                     WHERE cbc2.contract_id = cp.contract_id
                    ) as total_categories,
                    (SELECT COUNT(DISTINCT cl.stall_id)
                     FROM contract_locations cl
                     WHERE cl.contract_id = cp.contract_id
                    ) as total_locations,
                    ((SELECT SUM(COALESCE(ic2.payment_count, 0) + COALESCE(ec2.payment_count, 0))
                      FROM contract_business_categories cbc3
                      LEFT JOIN internal_business_categories ic2 ON cbc3.internal_category_id = ic2.id
                      LEFT JOIN external_business_categories ec2 ON cbc3.external_category_id = ec2.id
                      WHERE cbc3.contract_id = cp.contract_id
                     ) * COALESCE(er.bs_value, 0)) as calculated_amount
                  FROM {$this->table} cp
                  INNER JOIN contracts c ON cp.contract_id = c.id
                  INNER JOIN awardees a ON c.awardee_id = a.id
                  LEFT JOIN fiscal_year fy ON c.fiscal_year_id = fy.id
                  LEFT JOIN euro_rates er ON cp.euro_rate_id = er.id
                  LEFT JOIN contract_locations cl ON c.id = cl.contract_id
                  LEFT JOIN market_stalls ms ON cl.stall_id = ms.id
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE DATE_FORMAT(cp.payment_date, '%Y-%m') = :month_year
                    AND c.status != 'canceled'";
        
        $params = ['month_year' => sprintf('%04d-%02d', $year, $month)];
        
        if (!empty($filters['contract_type'])) {
            $query .= " AND c.type = :contract_type";
            $params['contract_type'] = $filters['contract_type'];
        }
        
        if (!empty($filters['zone_id'])) {
            $query .= " AND z.id = :zone_id";
            $params['zone_id'] = $filters['zone_id'];
        }
        
        if (!empty($filters['sector_id'])) {
            $query .= " AND s.id = :sector_id";
            $params['sector_id'] = $filters['sector_id'];
        }
        
        if (!empty($filters['show_delinquent']) && $filters['show_delinquent'] == '1') {
            $query .= " AND cp.status = 'pending' AND cp.payment_date < CURDATE()";
        }
        
        $query .= " GROUP BY cp.id, cp.contract_id, cp.payment_reference, cp.payment_date, cp.status, c.type, c.status,
                    a.id, a.first_name, a.last_name, a.id_number, fy.year, z.id, z.name, s.id, s.name,
                    er.bs_value
                  ORDER BY cp.payment_date ASC, a.last_name ASC";
        
        return $this->query($query, $params);
    }
}
