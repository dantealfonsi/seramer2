<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
// Dependencies will need to be required here or in the methods that use them to avoid circular deps? 
// Ideally unrelated models are autloaded or required once.
// Since we don't have autoload, we might need manual requires.
require_once __DIR__ . '/FiscalYearModel.php';
require_once __DIR__ . '/ContractPaymentModel.php';
require_once __DIR__ . '/EuroRateModel.php';


class ContractModel extends Model {
    protected $table = 'contracts';
    
    public function getAll(): array {
        $query = "SELECT c.*,
                         CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                         a.id_number as awardee_id_number,
                         fy.year as fiscal_year,
                         COUNT(DISTINCT cbc.id) as categories_count,
                         COUNT(DISTINCT cl.id) as locations_count,
                         GROUP_CONCAT(DISTINCT 
                            CASE 
                                WHEN ic.name IS NOT NULL THEN CONCAT('INT:', ic.name)
                                WHEN ec.name IS NOT NULL THEN CONCAT('EXT:', ec.name)
                            END
                            ORDER BY ic.name, ec.name
                            SEPARATOR '||'
                         ) as categories_list,
                         GROUP_CONCAT(DISTINCT 
                            CONCAT(ms.stall_number, ' (', z.name, '-', s.name, ')')
                            ORDER BY z.name, s.name, ms.stall_number
                            SEPARATOR '||'
                         ) as locations_list
                  FROM {$this->table} c
                  LEFT JOIN awardees a ON c.awardee_id = a.id
                  LEFT JOIN fiscal_year fy ON c.fiscal_year_id = fy.id
                  LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
                  LEFT JOIN internal_business_categories ic ON cbc.internal_category_id = ic.id
                  LEFT JOIN external_business_categories ec ON cbc.external_category_id = ec.id
                  LEFT JOIN contract_locations cl ON c.id = cl.contract_id
                  LEFT JOIN market_stalls ms ON cl.stall_id = ms.id
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  GROUP BY c.id
                  ORDER BY c.id DESC";
        return $this->query($query);
    }
    
    public function getById(int $id) {
        $query = "SELECT c.*, 
                         a.first_name as awardee_first_name,
                         a.last_name as awardee_last_name,
                         a.id_number as awardee_id_number,
                         fy.year as fiscal_year,
                         fy.start_date as fiscal_start_date,
                         fy.end_date as fiscal_end_date
                  FROM {$this->table} c
                  LEFT JOIN awardees a ON c.awardee_id = a.id
                  LEFT JOIN fiscal_year fy ON c.fiscal_year_id = fy.id
                  WHERE c.id = :id
                  LIMIT 1";
        
        return $this->queryOne($query, ['id' => $id]);
    }
    
    public function getByAwardee(int $awardeeId): array {
        $query = "SELECT c.*, 
                         fy.year as fiscal_year,
                         fy.start_date as fiscal_start_date,
                         fy.end_date as fiscal_end_date
                  FROM {$this->table} c
                  LEFT JOIN fiscal_year fy ON c.fiscal_year_id = fy.id
                  WHERE c.awardee_id = :awardee_id
                  ORDER BY c.id DESC";
        
        return $this->query($query, ['awardee_id' => $awardeeId]);
    }
    
    public function getByFiscalYear(int $fiscalYearId): array {
        $query = "SELECT c.*, 
                         CONCAT(a.first_name, ' ', a.last_name) as awardee_name
                  FROM {$this->table} c
                  LEFT JOIN awardees a ON c.awardee_id = a.id
                  WHERE c.fiscal_year_id = :fiscal_year_id
                  ORDER BY c.id DESC";
        
        return $this->query($query, ['fiscal_year_id' => $fiscalYearId]);
    }
    
    public function create(array $data, array $categories = [], array $locations = []) {
        try {
            $this->beginTransaction();
            
            $query = "INSERT INTO {$this->table} 
                      (awardee_id, fiscal_year_id, start_date, end_date, type, contract_mode) 
                      VALUES 
                      (:awardee_id, :fiscal_year_id, :start_date, :end_date, :type, :contract_mode)";
            
            $success = $this->execute($query, [
                'awardee_id' => $data['awardee_id'],
                'fiscal_year_id' => $data['fiscal_year_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'type' => $data['type'],
                'contract_mode' => $data['contract_mode']
            ]);
            
            if (!$success) {
                $this->rollback();
                return false;
            }
            
            $contractId = $this->lastInsertId();
            
            // Audit LOG
            Audit::logInsert('contracts', $contractId, $data);
            
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $this->addBusinessCategory($contractId, $category);
                }
            }
            
            if (!empty($locations)) {
                foreach ($locations as $locationId) {
                    $this->addLocation($contractId, $locationId);
                }
            }
            
            $fiscalYearModel = new FiscalYearModel();
            $fiscalYear = $fiscalYearModel->getById($data['fiscal_year_id']);
            
            if ($fiscalYear) {
                $paymentModel = new ContractPaymentModel();
                $paymentModel->generatePaymentsForContract(
                    $contractId, 
                    $fiscalYear, 
                    $data['start_date'], 
                    $data['end_date']
                );
                
                $this->assignRatesToPayments($contractId);
            }
            
            $this->commit();
            return $contractId;
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error al crear contrato: " . $e->getMessage());
            return false;
        }
    }
    
    public function update(int $id, array $data): bool {
        $old = $this->findById($id);
        if (!$old) return false;
        
        $query = "UPDATE {$this->table} 
                  SET awardee_id = :awardee_id,
                      fiscal_year_id = :fiscal_year_id,
                      start_date = :start_date,
                      end_date = :end_date,
                      type = :type,
                      contract_mode = :contract_mode
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'awardee_id' => $data['awardee_id'],
            'fiscal_year_id' => $data['fiscal_year_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'type' => $data['type'],
            'contract_mode' => $data['contract_mode'],
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('contracts', $id, $old, $data);
        }
        
        return $success;
    }
    
    public function addBusinessCategory(int $contractId, array $category): bool {
        $query = "INSERT INTO contract_business_categories 
                  (contract_id, external_category_id, internal_category_id, type) 
                  VALUES 
                  (:contract_id, :external_category_id, :internal_category_id, :type)";
        
        return $this->execute($query, [
            'contract_id' => $contractId,
            'external_category_id' => $category['type'] === 'external' ? $category['id'] : null,
            'internal_category_id' => $category['type'] === 'internal' ? $category['id'] : null,
            'type' => $category['type']
        ]);
    }
    
    public function addLocation(int $contractId, int $stallId): bool {
        $query = "INSERT INTO contract_locations (contract_id, stall_id) 
                  VALUES (:contract_id, :stall_id)";
        
        return $this->execute($query, [
            'contract_id' => $contractId,
            'stall_id' => $stallId
        ]);
    }
    
    public function removeLocation(int $contractId, int $stallId): bool {
        $query = "DELETE FROM contract_locations 
                  WHERE contract_id = :contract_id AND stall_id = :stall_id";
        
        return $this->execute($query, [
            'contract_id' => $contractId,
            'stall_id' => $stallId
        ]);
    }
    
    public function addCategory(int $contractId, string $categoryType, int $categoryId): bool {
        if ($categoryType === 'internal') {
            $query = "INSERT INTO contract_business_categories 
                      (contract_id, internal_category_id, external_category_id) 
                      VALUES (:contract_id, :category_id, NULL)";
        } else {
            $query = "INSERT INTO contract_business_categories 
                      (contract_id, internal_category_id, external_category_id) 
                      VALUES (:contract_id, NULL, :category_id)";
        }
        
        return $this->execute($query, [
            'contract_id' => $contractId,
            'category_id' => $categoryId
        ]);
    }
    
    public function removeCategory(int $contractId, string $categoryType, int $categoryId): bool {
        if ($categoryType === 'internal') {
            $query = "DELETE FROM contract_business_categories 
                      WHERE contract_id = :contract_id 
                      AND internal_category_id = :category_id";
        } else {
            $query = "DELETE FROM contract_business_categories 
                      WHERE contract_id = :contract_id 
                      AND external_category_id = :category_id";
        }
        
        return $this->execute($query, [
            'contract_id' => $contractId,
            'category_id' => $categoryId
        ]);
    }
    
    public function canAssignLocationToAwardee(int $awardeeId, int $stallId, int $excludeContractId = 0): array {
        $query = "SELECT c.id, c.start_date, c.end_date, ms.stall_number, z.name as zone_name, s.name as sector_name
                  FROM {$this->table} c
                  INNER JOIN contract_locations cl ON c.id = cl.contract_id
                  INNER JOIN market_stalls ms ON cl.stall_id = ms.id
                  INNER JOIN sectors s ON ms.sector_id = s.id
                  INNER JOIN zones z ON s.zone_id = z.id
                  WHERE c.awardee_id = :awardee_id 
                  AND cl.stall_id = :stall_id
                  AND c.id != :exclude_contract_id
                  AND c.end_date >= CURDATE()
                  LIMIT 1";
        
        $result = $this->queryOne($query, [
            'awardee_id' => $awardeeId,
            'stall_id' => $stallId,
            'exclude_contract_id' => $excludeContractId
        ]);
        
        if ($result) {
            return [
                'can_assign' => false,
                'message' => "Este adjudicatario ya tiene asignado el Local {$result['stall_number']} " .
                            "({$result['zone_name']} - {$result['sector_name']}) en otro contrato activo " .
                            "(Contrato #{$result['id']}, vigente hasta " . 
                            date('d/m/Y', strtotime($result['end_date'])) . "). " .
                            "No se puede asignar el mismo local en múltiples contratos del mismo adjudicatario."
            ];
        }
        
        return [
            'can_assign' => true,
            'message' => 'El local puede ser asignado'
        ];
    }
    
    public function updateDebtStatus(int $contractId, int $daysToMoroso = 30, int $daysToIncobrable = 90): bool {
        $query = "SELECT MIN(DATEDIFF(CURDATE(), payment_date)) as days_overdue
                  FROM contract_payments
                  WHERE contract_id = :contract_id
                  AND status = 'pending'
                  AND payment_date < CURDATE()";
        
        $result = $this->queryOne($query, ['contract_id' => $contractId]);
        $daysOverdue = $result['days_overdue'] ?? 0;
        
        // Determinar el nuevo estatus (simulacion por ahora)
        return true; 
    }
    
    public function getCategories(int $contractId): array {
        $query = "SELECT cbc.*,
                         ibc.name as internal_category_name,
                         ebc.name as external_category_name,
                         CASE 
                            WHEN cbc.internal_category_id IS NOT NULL THEN 'internal'
                            WHEN cbc.external_category_id IS NOT NULL THEN 'external'
                         END as type
                  FROM contract_business_categories cbc
                  LEFT JOIN internal_business_categories ibc ON cbc.internal_category_id = ibc.id
                  LEFT JOIN external_business_categories ebc ON cbc.external_category_id = ebc.id
                  WHERE cbc.contract_id = :contract_id";
        
        return $this->query($query, ['contract_id' => $contractId]);
    }
    
    public function getLocations(int $contractId): array {
        $query = "SELECT cl.*, ms.stall_number, ms.location_description,
                         s.name as sector_name, z.name as zone_name
                  FROM contract_locations cl
                  LEFT JOIN market_stalls ms ON cl.stall_id = ms.id
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE cl.contract_id = :contract_id";
        
        return $this->query($query, ['contract_id' => $contractId]);
    }
    
    public function canDeleteContract(int $id): array {
        $query = "SELECT COUNT(*) as installments_count
                  FROM contract_payment_installments cpi
                  INNER JOIN contract_payments cp ON cpi.contract_payment_id = cp.id
                  WHERE cp.contract_id = :contract_id";
        
        $result = $this->queryOne($query, ['contract_id' => $id]);
        $installmentsCount = (int) ($result['installments_count'] ?? 0);
        
        if ($installmentsCount > 0) {
            return [
                'can_delete' => false,
                'message' => "No se puede eliminar el contrato porque tiene {$installmentsCount} abono(s) registrado(s) en sus pagos. " .
                            "Debe eliminar primero los abonos."
            ];
        }
        
        return [
            'can_delete' => true,
            'message' => 'El contrato puede ser eliminado'
        ];
    }
    
    public function deleteContract(int $id): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $validation = $this->canDeleteContract($id);
        if (!$validation['can_delete']) {
            error_log("No se puede eliminar contrato {$id}: " . $validation['message']);
            return false;
        }
        
        try {
            $this->beginTransaction();
            
            $query = "DELETE FROM contract_business_categories WHERE contract_id = :contract_id";
            $this->execute($query, ['contract_id' => $id]);
            
            $query = "DELETE FROM contract_locations WHERE contract_id = :contract_id";
            $this->execute($query, ['contract_id' => $id]);
            
            $query = "DELETE FROM contract_payments WHERE contract_id = :contract_id";
            $this->execute($query, ['contract_id' => $id]);
            
            // Assuming default delete method is not available in Model base, need to implement or use query
            // Using query directly
            $query = "DELETE FROM contracts WHERE id = :id";
            $success = $this->execute($query, ['id' => $id]);
            
            if (!$success) {
                $this->rollback();
                return false;
            }
            
            Audit::logDelete('contracts', $id, $old);
            
            $this->commit();
            return true;
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error al eliminar contrato: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateStatus(int $id, string $status): bool {
        $validStatuses = ['active', 'renewed', 'canceled'];
        if (!in_array($status, $validStatuses)) return false;
        
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        return $this->execute($query, ['id' => $id, 'status' => $status]);
    }
    
    public function updatePaymentStatus(int $id, string $statusPayment): bool {
        $validStatuses = ['up to date', 'delinquent', 'unable to pay'];
        if (!in_array($statusPayment, $validStatuses)) return false;
        
        $query = "UPDATE {$this->table} SET status_payment = :status_payment WHERE id = :id";
        return $this->execute($query, ['id' => $id, 'status_payment' => $statusPayment]);
    }
    
    public function getMetrics(): array {
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                    COUNT(CASE WHEN status = 'canceled' THEN 1 END) as canceled
                  FROM {$this->table}";
        
        $result = $this->queryOne($query);
        
        return [
            'total' => (int) ($result['total'] ?? 0),
            'active' => (int) ($result['active'] ?? 0),
            'canceled' => (int) ($result['canceled'] ?? 0)
        ];
    }
    
    private function assignRatesToPayments(int $contractId): void {
        $paymentModel = new ContractPaymentModel();
        $euroRateModel = new EuroRateModel();
        
        $payments = $paymentModel->getByContract($contractId);
        
        foreach ($payments as $payment) {
            if (!empty($payment['euro_rate_id'])) continue;
            
            $paymentDate = new \DateTime($payment['payment_date']);
            $month = (int) $paymentDate->format('m');
            $year = (int) $paymentDate->format('Y');
            
            $rate = $euroRateModel->getByMonthYear($month, $year);
            
            if ($rate) {
                $paymentModel->updatePaymentAmount(
                    $payment['id'],
                    $rate['id'],
                    (float) $rate['bs_value']
                );
            }
        }
    }
}
