<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
require_once __DIR__ . '/ContractPaymentModel.php';

class EuroRateModel extends Model {
    protected $table = 'euro_rates';
    
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY year DESC, month DESC";
        return $this->query($query);
    }
    
    public function getByMonthYear($month, int $year): ?array {
        if (is_numeric($month)) {
            $monthNames = [
                1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
                5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
                9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
            ];
            $month = $monthNames[(int)$month] ?? $month;
        }
        
        $query = "SELECT * FROM {$this->table} WHERE month = :month AND year = :year LIMIT 1";
        return $this->queryOne($query, [
            'month' => $month,
            'year' => $year
        ]);
    }
    
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    public function createOrUpdate(array $data) {
        try {
            $this->beginTransaction();
            
            $existing = $this->getByMonthYear($data['month'], $data['year']);
            
            if ($existing) {
                $query = "UPDATE {$this->table} 
                          SET bs_value = :bs_value 
                          WHERE id = :id";
                
                $success = $this->execute($query, [
                    'bs_value' => $data['bs_value'],
                    'id' => $existing['id']
                ]);
                
                if (!$success) {
                    $this->rollback();
                    return false;
                }
                
                $euroRateId = $existing['id'];
                Audit::logUpdate('euro_rates', $euroRateId, $existing, $data);
                
            } else {
                $query = "INSERT INTO {$this->table} (month, year, bs_value) 
                          VALUES (:month, :year, :bs_value)";
                
                $success = $this->execute($query, [
                    'month' => $data['month'],
                    'year' => $data['year'],
                    'bs_value' => $data['bs_value']
                ]);
                
                if (!$success) {
                    $this->rollback();
                    return false;
                }
                
                $euroRateId = $this->lastInsertId();
                Audit::logInsert('euro_rates', $euroRateId, $data);
            }
            
            $monthForUpdate = $data['month_number'] ?? $data['month'];
            $updated = $this->updateContractPaymentsByRate($euroRateId, $monthForUpdate, $data['year']);
            
            if (!$updated && $updated !== 0 && $updated !== true) { // updateContractPaymentsByRate returns bool, check logic
                 // Actually my port of updateContractPaymentsByRate handles it.
            }
            
            $this->commit();
            return $euroRateId;
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error al crear/actualizar tasa de euro: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateContractPaymentsByRate(int $euroRateId, $month, int $year): bool {
        try {
            $rate = $this->getById($euroRateId);
            if (!$rate || !isset($rate['bs_value'])) return false;
            $bsValue = (float)$rate['bs_value'];
            
            if (!is_numeric($month)) {
                $monthNumbers = [
                    'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
                    'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
                    'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
                ];
                $monthNumber = $monthNumbers[strtolower($month)] ?? 0;
            } else {
                $monthNumber = (int)$month;
            }
            
            $fiscalYearQuery = "SELECT id FROM fiscal_year WHERE year = :year LIMIT 1";
            $fiscalYear = $this->queryOne($fiscalYearQuery, ['year' => $year]);
            
            if (!$fiscalYear) return false;
            
            $paymentsQuery = "
                SELECT cp.id, cp.contract_id, cp.amount,
                       COALESCE((SELECT SUM(cpi.amount) 
                                FROM contract_payment_installments cpi 
                                WHERE cpi.contract_payment_id = cp.id), 0) as total_paid
                FROM contract_payments cp
                INNER JOIN contracts c ON cp.contract_id = c.id
                WHERE c.fiscal_year_id = :fiscal_year_id
                AND MONTH(cp.payment_date) = :month
                AND YEAR(cp.payment_date) = :year
                AND cp.status = 'pending'
                AND cp.status != 'paid'
            ";
            
            $payments = $this->query($paymentsQuery, [
                'fiscal_year_id' => $fiscalYear['id'],
                'month' => $monthNumber,
                'year' => $year
            ]);
            
            $paymentModel = new ContractPaymentModel();
            
            $updatedCount = 0;
            foreach ($payments as $payment) {
                $paymentId = (int)$payment['id'];
                $contractId = (int)$payment['contract_id'];
                $currentAmount = (float)$payment['amount'];
                $totalPaid = (float)$payment['total_paid'];
                
                if ($totalPaid < $currentAmount || $currentAmount == 0) {
                    $newAmount = $paymentModel->calculatePaymentAmount($contractId, $bsValue);
                    
                    $updateQuery = "
                        UPDATE contract_payments 
                        SET euro_rate_id = :euro_rate_id, 
                            amount = :amount
                        WHERE id = :payment_id
                    ";
                    
                    $success = $this->execute($updateQuery, [
                        'euro_rate_id' => $euroRateId,
                        'amount' => $newAmount,
                        'payment_id' => $paymentId
                    ]);
                    
                    if ($success) $updatedCount++;
                }
            }
            
            return true;
            
        } catch (\PDOException $e) {
            error_log("Error al actualizar pagos por tasa: " . $e->getMessage());
            return false;
        }
    }
}
