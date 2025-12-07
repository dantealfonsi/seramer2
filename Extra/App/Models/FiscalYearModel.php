<?php
/**
 * Modelo FiscalYear
 * 
 * Gestiona los años fiscales del sistema
 * Implementa RF01: Gestión de Años Fiscales
 * Implementa RF02: Generación Inicial de Pagos/Facturas por Año Fiscal
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class FiscalYearModel extends Model {
    protected string $table = 'fiscal_year';
    
    /**
     * Obtiene todos los años fiscales
     * 
     * @return array Lista de años fiscales
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY year DESC";
        return $this->query($query);
    }
    
    /**
     * Obtiene el año fiscal activo
     * 
     * @return array|null Año fiscal activo o null
     */
    public function getActive(): ?array {
        $query = "SELECT * FROM {$this->table} WHERE status = 'active' LIMIT 1";
        return $this->queryOne($query);
    }
    
    /**
     * Obtiene un año fiscal por ID
     * 
     * @param int $id ID del año fiscal
     * @return array|null Año fiscal o null
     */
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    /**
     * Crea un nuevo año fiscal (RF01)
     * 
     * Al crear un año fiscal:
     * 1. Desactiva el año fiscal anterior
     * 2. Crea el nuevo año fiscal como activo
     * 3. Genera los 12 pagos para todos los contratos vigentes (RF02)
     * 
     * @param array $data Datos del año fiscal (start_date, end_date, year)
     * @return int|false ID del año fiscal creado o false en caso de error
     */
    public function create(array $data): int|false {
        try {
            $this->beginTransaction();
            
            // Desactivar el año fiscal anterior
            $this->execute(
                "UPDATE {$this->table} SET status = 'inactive' WHERE status = 'active'"
            );
            
            // Insertar el nuevo año fiscal
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
            
            // Registrar en auditoría
            Audit::logInsert('fiscal_year', $fiscalYearId, $data);
            
            // Generar los pagos para todos los contratos vigentes (RF02)
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
    
    /**
     * Actualiza un año fiscal
     * 
     * @param int $id ID del año fiscal
     * @param array $data Datos a actualizar
     * @return bool True si tuvo éxito
     */
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
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
    
    /**
     * Cambia el estado de un año fiscal
     * 
     * @param int $id ID del año fiscal
     * @param string $status Estado (active/inactive)
     * @return bool True si tuvo éxito
     */
    public function changeStatus(int $id, string $status): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        // Si se va a activar este año fiscal, desactivar los demás
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
    
    /**
     * Verifica si un año ya existe
     * 
     * @param int $year Año
     * @param int|null $excludeId ID a excluir de la búsqueda
     * @return bool True si existe
     */
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
    
    /**
     * Verifica si un año fiscal puede ser eliminado
     * 
     * @param int $id ID del año fiscal
     * @return array ['can_delete' => bool, 'relations' => array, 'message' => string]
     */
    public function canDeleteFiscalYear(int $id): array {
        $relations = [
            'contracts' => 'fiscal_year_id',
            'euro_rates' => 'fiscal_year_id'
        ];
        
        return $this->canDelete($id, $relations);
    }
    
    /**
     * Elimina un año fiscal
     * 
     * @param int $id ID del año fiscal
     * @return bool True si tuvo éxito
     */
    public function deleteFiscalYear(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        // Verificar que no tenga relaciones
        $validation = $this->canDeleteFiscalYear($id);
        if (!$validation['can_delete']) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('fiscal_year', $id, $old);
        }
        
        return $success;
    }
}

