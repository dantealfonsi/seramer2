<?php
/**
 * Modelo EuroRate
 * 
 * Gestiona las tasas de cambio del Euro
 * Implementa RF04: Gestión de Tasa de Euro Mensual
 * Implementa RF05: Actualización Masiva de Facturas por Tasa
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class EuroRateModel extends Model {
    protected string $table = 'euro_rates';
    
    /**
     * Obtiene todas las tasas de euro
     * 
     * @return array Lista de tasas
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY year DESC, month DESC";
        return $this->query($query);
    }
    
    /**
     * Obtiene una tasa por mes y año
     * 
     * @param string|int $month Mes (nombre en minúsculas o número 1-12)
     * @param int $year Año
     * @return array|null Tasa o null
     */
    public function getByMonthYear(string|int $month, int $year): ?array {
        // Convertir número a nombre si es necesario
        if (is_numeric($month)) {
            $monthNames = [
                1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
                5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
                9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
            ];
            $month = $monthNames[(int)$month];
        }
        
        $query = "SELECT * FROM {$this->table} WHERE month = :month AND year = :year LIMIT 1";
        return $this->queryOne($query, [
            'month' => $month,
            'year' => $year
        ]);
    }
    
    /**
     * Obtiene una tasa por ID
     * 
     * @param int $id ID de la tasa
     * @return array|null Tasa o null
     */
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    /**
     * Crea o actualiza una tasa de euro (RF04 + RF05)
     * 
     * Al establecer una tasa:
     * 1. Crea o actualiza el registro en euro_rates
     * 2. Actualiza automáticamente todas las facturas del mes correspondiente (RF05)
     * 
     * @param array $data Datos de la tasa (month, year, bs_value)
     * @return int|false ID de la tasa o false en caso de error
     */
    public function createOrUpdate(array $data): int|false {
        try {
            $this->beginTransaction();
            
            // Verificar si ya existe una tasa para ese mes/año
            $existing = $this->getByMonthYear($data['month'], $data['year']);
            
            if ($existing) {
                // Actualizar tasa existente
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
                
                // Registrar en auditoría
                Audit::logUpdate('euro_rates', $euroRateId, $existing, $data);
                
            } else {
                // Crear nueva tasa
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
                
                // Registrar en auditoría
                Audit::logInsert('euro_rates', $euroRateId, $data);
            }
            
            // RF05: Actualizar masivamente las facturas del mes correspondiente
            // Usar month_number si está disponible, sino usar month
            $monthForUpdate = $data['month_number'] ?? $data['month'];
            $updated = $this->updateContractPaymentsByRate($euroRateId, $monthForUpdate, $data['year']);
            
            if (!$updated) {
                $this->rollback();
                return false;
            }
            
            $this->commit();
            return $euroRateId;
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error al crear/actualizar tasa de euro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza masivamente las facturas por tasa (RF05)
     * 
     * Actualiza el campo euro_rate_id y recalcula el monto (amount) de TODOS los contract_payments 
     * que correspondan al mes y año de la tasa, que pertenezcan a contratos vigentes
     * y que NO hayan sido pagados completamente
     * 
     * @param int $euroRateId ID de la tasa
     * @param string|int $month Mes (nombre o número)
     * @param int $year Año
     * @return bool True si tuvo éxito
     */
    private function updateContractPaymentsByRate(int $euroRateId, string|int $month, int $year): bool {
        try {
            // Obtener el valor de la tasa de euro actualizado
            $rate = $this->getById($euroRateId);
            if (!$rate || !isset($rate['bs_value'])) {
                return false;
            }
            $bsValue = (float)$rate['bs_value'];
            
            // Convertir nombre de mes a número si es necesario
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
            
            // Obtener el año fiscal correspondiente
            $fiscalYearQuery = "SELECT id FROM fiscal_year WHERE year = :year LIMIT 1";
            $fiscalYear = $this->queryOne($fiscalYearQuery, ['year' => $year]);
            
            if (!$fiscalYear) {
                return false;
            }
            
            // Obtener todos los pagos del mes/año correspondiente que no hayan sido pagados completamente
            // Solo pagos pendientes que no hayan sido completamente pagados
            // Excluir explícitamente facturas con status = 'paid'
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
            
            // Usar el modelo ContractPaymentModel para calcular los montos
            $paymentModel = new \App\Models\ContractPaymentModel();
            
            $updatedCount = 0;
            foreach ($payments as $payment) {
                $paymentId = (int)$payment['id'];
                $contractId = (int)$payment['contract_id'];
                $currentAmount = (float)$payment['amount'];
                $totalPaid = (float)$payment['total_paid'];
                
                // Solo actualizar si el pago NO está completamente pagado
                // Si total_paid es menor que el monto actual (o el monto es 0), significa que aún hay saldo pendiente
                if ($totalPaid < $currentAmount || $currentAmount == 0) {
                    // Calcular el nuevo monto usando la nueva tasa
                    $newAmount = $paymentModel->calculatePaymentAmount($contractId, $bsValue);
                    
                    // Actualizar el pago con la nueva tasa y el nuevo monto
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
                    
                    if ($success) {
                        $updatedCount++;
                    }
                }
            }
            
            return $updatedCount > 0 || count($payments) == 0; // Retorna true si se actualizó al menos uno o no había nada que actualizar
            
        } catch (\PDOException $e) {
            error_log("Error al actualizar pagos por tasa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si una tasa puede ser eliminada
     * 
     * @param int $id ID de la tasa
     * @return array ['can_delete' => bool, 'relations' => array, 'message' => string]
     */
    public function canDeleteRate(int $id): array {
        $relations = [
            'contract_payments' => 'euro_rate_id'
        ];
        
        return $this->canDelete($id, $relations);
    }
    
    /**
     * Elimina una tasa de euro
     * 
     * @param int $id ID de la tasa
     * @return bool True si tuvo éxito
     */
    public function deleteRate(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        // Verificar que no tenga relaciones
        $validation = $this->canDeleteRate($id);
        if (!$validation['can_delete']) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('euro_rates', $id, $old);
        }
        
        return $success;
    }
    
    /**
     * Obtiene las tasas de un año específico
     * 
     * @param int $year Año
     * @return array Lista de tasas del año
     */
    public function getByYear(int $year): array {
        $query = "SELECT * FROM {$this->table} WHERE year = :year ORDER BY month ASC";
        return $this->query($query, ['year' => $year]);
    }
    
    /**
     * Verifica si existe una tasa para un mes/año
     * 
     * @param string|int $month Mes (nombre o número)
     * @param int $year Año
     * @return bool True si existe
     */
    public function rateExists(string|int $month, int $year): bool {
        $result = $this->getByMonthYear($month, $year);
        return $result !== null;
    }
}

