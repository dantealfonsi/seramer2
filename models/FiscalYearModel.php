<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
require_once __DIR__ . '/ContractPaymentModel.php';

class FiscalYearModel extends Model {
    protected $table = 'fiscal_year';
    
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY year DESC";
        return $this->query($query);
    }
    
    public function getActive(): ?array {
        $query = "SELECT * FROM {$this->table} WHERE status = 'active' LIMIT 1";
        return $this->queryOne($query);
    }
    
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    public function create(array $data) {
        try {
            $this->beginTransaction();
            
            $this->execute(
                "UPDATE {$this->table} SET status = 'inactive' WHERE status = 'active'"
            );
            
            $query = "INSERT INTO {$this->table} (start_date, end_date, year, status) 
                      VALUES (:start_date, :end_date, :year, 'active')";
            
            $success = $this->execute($query, [
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'year' => $data['year']
            ]);
            
            if (!$success) {
                $this->rollback();
                return false;
            }
            
            $fiscalYearId = $this->lastInsertId();
            
            Audit::logInsert('fiscal_year', $fiscalYearId, $data);
            
            $contractPaymentModel = new ContractPaymentModel();
            $generated = $contractPaymentModel->generatePaymentsForFiscalYear($fiscalYearId);
            
            if (!$generated) {
                $this->rollback();
                return false;
            }
            
            $this->commit();
            return $fiscalYearId;
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error al crear año fiscal: " . $e->getMessage());
            return false;
        }
    }
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $query = "UPDATE {$this->table} 
                  SET start_date = :start_date, 
                      end_date = :end_date, 
                      year = :year,
                      status = :status
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'year' => $data['year'],
            'status' => $data['status'] ?? $old['status'],
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('fiscal_year', $id, $old, $data);
        }
        
        return $success;
    }
    
    public function changeStatus(int $id, string $status): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        if ($status === 'active') {
            $this->execute(
                "UPDATE {$this->table} SET status = 'inactive' WHERE status = 'active' AND id != :id",
                ['id' => $id]
            );
        }
        
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $success = $this->execute($query, [
            'status' => $status,
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('fiscal_year', $id, $old, ['status' => $status]);
        }
        
        return $success;
    }
    
    public function yearExists(int $year, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE year = :year";
        $params = ['year' => $year];
        
        if ($excludeId !== null) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($query, $params);
        return ($result['count'] ?? 0) > 0;
    }
    
    public function canDeleteFiscalYear(int $id): array {
        // Mock implementation of canDelete since Base Model doesn't have it yet? 
        // Or assumes base model has it. Base Model I wrote does NOT have 'canDelete'. 
        // I need to implement it here or remove the check if not critical, or replicate logic.
        // Reading `FiscalYearModel` original, it uses `$this->canDelete($id, $relations)`.
        // This suggests `Core\Model` had a `canDelete` helper.
        // I will implement a simple check here manually.
        
        $relations = [
            'contracts' => 'fiscal_year_id',
            'euro_rates' => 'fiscal_year_id'
        ];
        
        foreach ($relations as $table => $fk) {
            $count = $this->queryOne("SELECT COUNT(*) as c FROM $table WHERE $fk = :id", ['id' => $id]);
            if (($count['c'] ?? 0) > 0) {
                return ['can_delete' => false, 'message' => "Tiene registros relacionados en $table"];
            }
        }
        
        return ['can_delete' => true, 'message' => ''];
    }
    
    public function deleteFiscalYear(int $id): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $validation = $this->canDeleteFiscalYear($id);
        if (!$validation['can_delete']) return false;
        
        // delete method in Base Model? No. I need to implement delete query.
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('fiscal_year', $id, $old);
        }
        
        return $success;
    }
}
