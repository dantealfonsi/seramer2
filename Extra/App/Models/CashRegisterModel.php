<?php
/**
 * Modelo CashRegister
 * 
 * Gestiona las cajas (entidades) asignadas a usuarios
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class CashRegisterModel extends Model {
    protected string $table = 'cash_registers';
    
    /**
     * Obtiene todas las cajas con información del usuario asignado
     * 
     * @return array Lista de cajas
     */
    public function getAll(): array {
        $query = "SELECT cr.*,
                         u.username,
                         u.email,
                         COALESCE(
                             NULLIF(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')), ' '),
                             u.username,
                             CONCAT('Usuario #', u.id)
                         ) as assigned_user_name
                  FROM {$this->table} cr
                  LEFT JOIN users u ON cr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  ORDER BY cr.name ASC";
        return $this->query($query);
    }
    
    /**
     * Obtiene una caja por ID
     * 
     * @param int $id ID de la caja
     * @return array|null Caja o null
     */
    public function getById(int $id): ?array {
        $query = "SELECT cr.*,
                         u.username,
                         u.email,
                         COALESCE(
                             NULLIF(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')), ' '),
                             u.username,
                             CONCAT('Usuario #', u.id)
                         ) as assigned_user_name
                  FROM {$this->table} cr
                  LEFT JOIN users u ON cr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  WHERE cr.id = :id
                  LIMIT 1";
        return $this->queryOne($query, ['id' => $id]);
    }
    
    /**
     * Obtiene la caja asignada a un usuario
     * 
     * @param int $userId ID del usuario
     * @return array|null Caja o null
     */
    public function getByAssignedUser(int $userId): ?array {
        $query = "SELECT cr.*
                  FROM {$this->table} cr
                  WHERE cr.user_id = :user_id
                  AND cr.status = 'active'
                  LIMIT 1";
        return $this->queryOne($query, ['user_id' => $userId]);
    }
    
    /**
     * Crea una nueva caja
     * 
     * @param array $data Datos de la caja
     * @return int|false ID de la caja creada o false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->table} 
                  (name, user_id, status) 
                  VALUES 
                  (:name, :user_id, :status)";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'user_id' => $data['user_id'],
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('cash_registers', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    /**
     * Actualiza una caja
     * 
     * @param int $id ID de la caja
     * @param array $data Datos a actualizar
     * @return bool True si tuvo éxito
     */
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $query = "UPDATE {$this->table} 
                  SET name = :name,
                      user_id = :user_id,
                      description = :description,
                      status = :status
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'user_id' => $data['user_id'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('cash_registers', $id, $old, $data);
        }
        
        return $success;
    }
    
    /**
     * Elimina una caja
     * Solo si no tiene aperturas registradas
     * 
     * @param int $id ID de la caja
     * @return bool True si tuvo éxito
     */
    public function delete(int $id): bool {
        // Verificar si tiene aperturas
        $dailyCashModel = new DailyCashRegisterModel();
        $hasOpenings = $dailyCashModel->hasOpenings($id);
        
        if ($hasOpenings) {
            return false;
        }
        
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('cash_registers', $id, $old);
        }
        
        return $success;
    }
    
    /**
     * Verifica si una caja puede ser eliminada
     * 
     * @param int $id ID de la caja
     * @param array $relations Relaciones a verificar (no usado en este caso)
     * @return array ['can_delete' => bool, 'message' => string]
     */
    public function canDelete(int $id, array $relations = []): array {
        $dailyCashModel = new DailyCashRegisterModel();
        $hasOpenings = $dailyCashModel->hasOpenings($id);
        
        if ($hasOpenings) {
            return [
                'can_delete' => false,
                'message' => 'No se puede eliminar la caja porque tiene aperturas registradas'
            ];
        }
        
        return [
            'can_delete' => true,
            'message' => 'La caja puede ser eliminada'
        ];
    }
}

