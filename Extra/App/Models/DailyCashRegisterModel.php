<?php
/**
 * Modelo DailyCashRegister
 * 
 * Gestiona las aperturas y cierres diarios de caja
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;
use Config\App;

class DailyCashRegisterModel extends Model {
    protected string $table = 'daily_cash_registers';
    
    /**
     * Escribe un mensaje en el archivo de log de cierre de caja
     * 
     * @param string $message Mensaje a escribir
     * @return void
     */
    private function writeLog(string $message): void {
        $logDir = App::BASE_PATH . 'logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . 'cash_register_close.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // También escribir al error_log estándar
        error_log($message);
    }
    
    /**
     * Abre una caja
     * 
     * @param int $cashRegisterId ID de la caja
     * @param int $userId ID del usuario que abre
     * @param float $initialAmount Monto inicial
     * @return int|false ID del registro creado o false
     */
    public function openCash(int $cashRegisterId, int $userId, float $initialAmount): int|false {
        try {
            $this->beginTransaction();
            
            // Verificar que no haya otra caja abierta para este cash_register_id
            $openCash = $this->getOpenCashByRegister($cashRegisterId);
            if ($openCash) {
                $this->rollback();
                return false;
            }
            
            // Verificar que el usuario sea el asignado a la caja
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
    
    /**
     * Cierra una caja
     * 
     * @param int $dailyCashId ID del registro de caja diaria
     * @param float $finalAmount Monto final
     * @return bool True si tuvo éxito
     */
    public function closeCash(int $dailyCashId, float $finalAmount): bool {
        try {
            $this->writeLog("=== INICIO CIERRE CAJA ===");
            $this->writeLog("ID: {$dailyCashId}, Monto: {$finalAmount}");
            
            // Obtener el registro antes de iniciar la transacción
            $old = $this->getById($dailyCashId);
            
            if (!$old) {
                $this->writeLog("ERROR: Registro no encontrado. ID: {$dailyCashId}");
                return false;
            }
            
            $this->writeLog("Registro encontrado - Status: {$old['status']}, Cash Register ID: {$old['cash_register_id']}, User ID: {$old['user_id']}");
            
            if ($old['status'] !== 'open') {
                $this->writeLog("ERROR: Caja ya cerrada. Status actual: {$old['status']}");
                return false;
            }
            
            if (!$this->beginTransaction()) {
                $this->writeLog("ERROR: No se pudo iniciar la transacción");
                return false;
            }
            
            $this->writeLog("Transacción iniciada");
            
            // Actualizar primero el status para evitar problemas con la constraint UNIQUE
            // La constraint es (cash_register_id, status), así que necesitamos cambiar el status primero
            $query = "UPDATE {$this->table} 
                      SET status = 'closed',
                          close_date = CURDATE(),
                          close_time = CURTIME(),
                          final_amount = :final_amount
                      WHERE id = :id AND status = 'open'";
            
            $this->writeLog("Ejecutando query: " . str_replace(["\n", "\r", "  "], " ", $query));
            $this->writeLog("Parámetros - final_amount: {$finalAmount}, id: {$dailyCashId}");
            
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([
                'final_amount' => $finalAmount,
                'id' => $dailyCashId
            ]);
            
            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                $this->rollback();
                $this->writeLog("ERROR en execute: " . json_encode($errorInfo, JSON_UNESCAPED_UNICODE));
                return false;
            }
            
            $rowsAffected = $stmt->rowCount();
            $this->writeLog("Filas afectadas: {$rowsAffected}");
            
            if ($rowsAffected === 0) {
                // Verificar el estado actual para diagnosticar
                $checkQuery = "SELECT id, status, cash_register_id, user_id, open_date, close_date FROM {$this->table} WHERE id = :id";
                $checkResult = $this->queryOne($checkQuery, ['id' => $dailyCashId]);
                $this->rollback();
                $this->writeLog("ERROR: No se afectaron filas. Estado actual: " . json_encode($checkResult, JSON_UNESCAPED_UNICODE));
                return false;
            }
            
            $this->writeLog("UPDATE exitoso, registrando auditoría...");
            
            // Registrar en auditoría (puede fallar pero no debe impedir el cierre)
            try {
                Audit::logUpdate('daily_cash_registers', $dailyCashId, $old, [
                    'final_amount' => $finalAmount,
                    'status' => 'closed'
                ]);
                $this->writeLog("Auditoría registrada");
            } catch (\Exception $e) {
                $this->writeLog("ADVERTENCIA: Error en auditoría: " . $e->getMessage());
                // No fallar el cierre por un error de auditoría
            }
            
            if (!$this->commit()) {
                $this->writeLog("ERROR: No se pudo hacer commit");
                $this->rollback();
                return false;
            }
            
            $this->writeLog("=== CIERRE CAJA EXITOSO ===");
            $this->writeLog("===========================================");
            return true;
            
        } catch (\PDOException $e) {
            if (isset($this->db) && $this->db->inTransaction()) {
                $this->rollback();
            }
            $this->writeLog("ERROR PDOException: " . $e->getMessage());
            $this->writeLog("Código: " . $e->getCode());
            $this->writeLog("Stack trace: " . $e->getTraceAsString());
            $this->writeLog("===========================================");
            return false;
        } catch (\Exception $e) {
            if (isset($this->db) && $this->db->inTransaction()) {
                $this->rollback();
            }
            $this->writeLog("ERROR Exception: " . $e->getMessage());
            $this->writeLog("Stack trace: " . $e->getTraceAsString());
            $this->writeLog("===========================================");
            return false;
        }
    }
    
    /**
     * Verifica si una caja está abierta
     * 
     * @param int $cashRegisterId ID de la caja
     * @param string|null $date Fecha a verificar (null = fecha actual)
     * @return bool True si está abierta
     */
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
    
    /**
     * Obtiene la caja abierta de un registro de caja
     * 
     * @param int $cashRegisterId ID de la caja
     * @return array|null Registro de caja abierta o null
     */
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
    
    /**
     * Obtiene el historial de aperturas/cierres de una caja
     * 
     * @param int $cashRegisterId ID de la caja
     * @param int $limit Límite de resultados
     * @return array Lista de registros
     */
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
                  LIMIT :limit";
        
        return $this->query($query, [
            'cash_register_id' => $cashRegisterId,
            'limit' => $limit
        ]);
    }
    
    /**
     * Obtiene un registro por ID
     * 
     * @param int $id ID del registro
     * @return array|null Registro o null
     */
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
    
    /**
     * Calcula el total de abonos registrados en una caja abierta
     * 
     * @param int $dailyCashId ID del registro de caja diaria
     * @return float Total de abonos
     */
    public function getTotalInstallments(int $dailyCashId): float {
        $query = "SELECT COALESCE(SUM(cpi.amount), 0) as total
                  FROM contract_payment_installments cpi
                  WHERE cpi.daily_cash_register_id = :daily_cash_id";
        
        $result = $this->queryOne($query, ['daily_cash_id' => $dailyCashId]);
        return (float) ($result['total'] ?? 0);
    }
    
    /**
     * Obtiene todos los movimientos (abonos) realizados en una caja diaria
     * 
     * @param int $dailyCashId ID del registro de caja diaria
     * @return array Lista de abonos con información del pago y método de pago
     */
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
    
    /**
     * Verifica si una caja tiene aperturas registradas
     * 
     * @param int $cashRegisterId ID de la caja
     * @return bool True si tiene aperturas
     */
    public function hasOpenings(int $cashRegisterId): bool {
        $query = "SELECT COUNT(*) as count
                  FROM {$this->table}
                  WHERE cash_register_id = :cash_register_id";
        
        $result = $this->queryOne($query, ['cash_register_id' => $cashRegisterId]);
        return (int) ($result['count'] ?? 0) > 0;
    }
}

