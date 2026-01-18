<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
require_once __DIR__ . '/CashRegisterModel.php';

class DailyCashRegisterModel extends Model {
    protected $table = 'daily_cash_registers';
    
    private function writeLog(string $message): void {
        $logDir = __DIR__ . '/../logs/'; // Updated Base Path
        if (!is_dir($logDir)) {
             @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . 'cash_register_close.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        error_log($message);
    }
    
    
    /**
     * Get or Create a daily cash session for the given register and user for TODAY.
     */
    public function getOrCreateCurrentSession(int $cashRegisterId, int $userId): int {
        $today = date('Y-m-d');
        
        $query = "SELECT id FROM {$this->table} 
                  WHERE cash_register_id = :register_id 
                  AND user_id = :user_id 
                  AND open_date = :today 
                  LIMIT 1";
                  
        $existing = $this->queryOne($query, [
            'register_id' => $cashRegisterId,
            'user_id' => $userId,
            'today' => $today
        ]);
        
        if ($existing) {
            return (int)$existing['id'];
        }
        
        // Create new session
        $insertQuery = "INSERT INTO {$this->table} 
                        (cash_register_id, user_id, open_date, open_time, initial_amount, status) 
                        VALUES 
                        (:register_id, :user_id, :today, CURTIME(), 0, 'open')";
        
        $this->execute($insertQuery, [
            'register_id' => $cashRegisterId,
            'user_id' => $userId,
            'today' => $today
        ]);
        
        return $this->lastInsertId();
    }

    /**
     * Get report data for Daily Cash view
     */
    public function getDailyReport(array $filters = []): array {
        $params = [];
        $query = "SELECT dcr.id as session_id,
                         dcr.open_date,
                         dcr.initial_amount,
                         cr.name as register_name,
                         cr.status as register_status,
                         u.username,
                         CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as staff_name,
                         (
                            COALESCE((SELECT SUM(amount) FROM contract_payment_installments WHERE daily_cash_register_id = dcr.id), 0) +
                            COALESCE((SELECT SUM(amount_paid) FROM fine_payments WHERE daily_cash_register_id = dcr.id), 0)
                          ) as total_collected
                  FROM {$this->table} dcr
                  JOIN cash_registers cr ON dcr.cash_register_id = cr.id
                  LEFT JOIN users u ON dcr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  WHERE 1=1";

        if (!empty($filters['status'])) {
            $query .= " AND cr.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND dcr.open_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND dcr.open_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $query .= " ORDER BY dcr.open_date DESC, dcr.open_time DESC";

        return $this->query($query, $params);
    }

    public function openCash(int $cashRegisterId, int $userId, float $initialAmount) {
        try {
            $this->beginTransaction();
            
            if ($this->getOpenCashByRegister($cashRegisterId)) {
                $this->rollback();
                return false;
            }
            
            $cashRegisterModel = new CashRegisterModel();
            $cashRegister = $cashRegisterModel->getById($cashRegisterId);
            
            if (!$cashRegister || $cashRegister['user_id'] != $userId) {
                $this->rollback();
                return false;
            }
            
            $query = "INSERT INTO {$this->table} 
                    (cash_register_id, user_id, open_date, open_time, initial_amount, status) 
                    VALUES 
                    (:cash_register_id, :user_id, CURDATE(), CURTIME(), :initial_amount, 'open')";
            
            $success = $this->execute($query, [
                'cash_register_id' => $cashRegisterId,
                'user_id' => $userId,
                'initial_amount' => $initialAmount
            ]);
            
            if (!$success) {
                $this->rollback();
                return false;
            }
            
            $id = $this->lastInsertId();
            Audit::logInsert('daily_cash_registers', $id, [
                'cash_register_id' => $cashRegisterId,
                'user_id' => $userId,
                'initial_amount' => $initialAmount
            ]);
            
            $this->commit();
            return $id;
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error al abrir caja: " . $e->getMessage());
            return false;
        }
    }
    
    public function closeCash(int $dailyCashId, float $finalAmount): bool {
        try {
            $this->writeLog("=== INICIO CIERRE CAJA ===");
            
            $old = $this->getById($dailyCashId);
            if (!$old) return false;
            
            if ($old['status'] !== 'open') return false;
            
            $this->beginTransaction();
            
            $query = "UPDATE {$this->table} 
                      SET status = 'closed',
                          close_date = CURDATE(),
                          close_time = CURTIME(),
                          final_amount = :final_amount
                      WHERE id = :id AND status = 'open'";
            
            $success = $this->execute($query, [
                'final_amount' => $finalAmount,
                'id' => $dailyCashId
            ]);
            
            if (!$success) {
                $this->rollback();
                return false;
            }
            
            try {
                Audit::logUpdate('daily_cash_registers', $dailyCashId, $old, [
                    'final_amount' => $finalAmount,
                    'status' => 'closed'
                ]);
            } catch (\Exception $e) {
                $this->writeLog("ADVERTENCIA: Error en auditoría: " . $e->getMessage());
            }
            
            $this->commit();
            return true;
            
        } catch (\PDOException $e) {
            $this->rollback();
            $this->writeLog("ERROR PDOException: " . $e->getMessage());
            return false;
        }
    }
    
    public function isCashOpen(int $cashRegisterId, ?string $date = null): bool {
        $date = $date ?? date('Y-m-d');
        
        $query = "SELECT COUNT(*) as count
                  FROM {$this->table}
                  WHERE cash_register_id = :cash_register_id
                  AND status = 'open'
                  AND open_date = :date";
        
        $result = $this->queryOne($query, [
            'cash_register_id' => $cashRegisterId,
            'date' => $date
        ]);
        
        return (int) ($result['count'] ?? 0) > 0;
    }
    
    public function getOpenCashByRegister(int $cashRegisterId): ?array {
        $query = "SELECT dcr.*,
                         cr.name as cash_register_name,
                         u.username,
                         COALESCE(
                             NULLIF(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')), ' '),
                             u.username,
                             CONCAT('Usuario #', u.id)
                         ) as user_name
                  FROM {$this->table} dcr
                  INNER JOIN cash_registers cr ON dcr.cash_register_id = cr.id
                  LEFT JOIN users u ON dcr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  WHERE dcr.cash_register_id = :cash_register_id
                  AND dcr.status = 'open'
                  LIMIT 1";
        
        return $this->queryOne($query, ['cash_register_id' => $cashRegisterId]);
    }
    
    public function getCashHistory(int $cashRegisterId, int $limit = 30): array {
        $query = "SELECT dcr.*,
                         u.username,
                         COALESCE(
                             NULLIF(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')), ' '),
                             u.username,
                             CONCAT('Usuario #', u.id)
                         ) as user_name
                  FROM {$this->table} dcr
                  LEFT JOIN users u ON dcr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  WHERE dcr.cash_register_id = :cash_register_id
                  ORDER BY dcr.open_date DESC, dcr.open_time DESC
                  LIMIT " . (int)$limit;
        
        return $this->query($query, ['cash_register_id' => $cashRegisterId]);
    }
    
    public function getById(int $id): ?array {
        $query = "SELECT dcr.*,
                         cr.name as cash_register_name,
                         u.username,
                         COALESCE(
                             NULLIF(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')), ' '),
                             u.username,
                             CONCAT('Usuario #', u.id)
                         ) as user_name
                  FROM {$this->table} dcr
                  INNER JOIN cash_registers cr ON dcr.cash_register_id = cr.id
                  LEFT JOIN users u ON dcr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  WHERE dcr.id = :id
                  LIMIT 1";
        
        return $this->queryOne($query, ['id' => $id]);
    }
    
    public function getTotalInstallments(int $dailyCashId): float {
        // Sum contract installments
        $query1 = "SELECT COALESCE(SUM(cpi.amount), 0) as total
                   FROM contract_payment_installments cpi
                   WHERE cpi.daily_cash_register_id = :daily_cash_id";
        
        $res1 = $this->queryOne($query1, ['daily_cash_id' => $dailyCashId]);
        $total1 = (float) ($res1['total'] ?? 0);
        
        // Sum fine payments
        $query2 = "SELECT COALESCE(SUM(fp.amount_paid), 0) as total
                   FROM fine_payments fp
                   WHERE fp.daily_cash_register_id = :daily_cash_id";
        
        $res2 = $this->queryOne($query2, ['daily_cash_id' => $dailyCashId]);
        $total2 = (float) ($res2['total'] ?? 0);
        
        return $total1 + $total2;
    }
    
    public function getInstallmentsByDailyCash(int $dailyCashId): array {
        $query = "SELECT cpi.*,
                         pm.name as payment_method_name,
                         cp.payment_reference,
                         cp.id as payment_id,
                         c.id as contract_id,
                         CONCAT(COALESCE(a.first_name, ''), ' ', COALESCE(a.last_name, '')) as awardee_name,
                         a.id_number as awardee_id_number
                  FROM contract_payment_installments cpi
                  INNER JOIN payment_methods pm ON cpi.payment_method_id = pm.id
                  INNER JOIN contract_payments cp ON cpi.contract_payment_id = cp.id
                  INNER JOIN contracts c ON cp.contract_id = c.id
                  INNER JOIN awardees a ON c.awardee_id = a.id
                  WHERE cpi.daily_cash_register_id = :daily_cash_id
                  ORDER BY cpi.date DESC, cpi.id DESC";
        
        return $this->query($query, ['daily_cash_id' => $dailyCashId]);
    }
    
    public function hasOpenings(int $cashRegisterId): bool {
        $query = "SELECT COUNT(*) as count
                  FROM {$this->table}
                  WHERE cash_register_id = :cash_register_id";
        
        $result = $this->queryOne($query, ['cash_register_id' => $cashRegisterId]);
        return (int) ($result['count'] ?? 0) > 0;
    }
}
