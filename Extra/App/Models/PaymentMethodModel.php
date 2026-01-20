<?php
/**
 * Modelo PaymentMethod
 * 
 * Gestiona los métodos de pago aceptados en el sistema
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class PaymentMethodModel extends Model {
    protected string $table = 'payment_methods';
    
    /**
     * Obtiene todos los métodos de pago
     * 
     * @return array Lista de métodos de pago
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        return $this->query($query);
    }
    
    /**
     * Obtiene solo los métodos de pago activos
     * 
     * @return array Lista de métodos de pago activos
     */
    public function getActive(): array {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name";
        return $this->query($query);
    }
    
    /**
     * Obtiene un método de pago por ID
     * 
     * @param int $id ID del método de pago
     * @return array|null Método de pago o null
     */
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    /**
     * Verifica si un método de pago con ese nombre ya existe
     * 
     * @param string $name Nombre del método de pago
     * @param int|null $excludeId ID a excluir (para edición)
     * @return bool True si existe
     */
    public function existsByName(string $name, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name";
        $params = ['name' => $name];
        
        if ($excludeId !== null) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = $this->query($query, $params);
        return $result[0]['count'] > 0;
    }
    
    /**
     * Crea un nuevo método de pago
     * 
     * @param array $data Datos del método de pago
     * @return int|false ID del método de pago creado o false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->table} 
                  (name, is_active) 
                  VALUES 
                  (:name, :is_active)";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? 1
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('payment_methods', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    /**
     * Actualiza un método de pago
     * 
     * @param int $id ID del método de pago
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
                      is_active = :is_active
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? 1,
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('payment_methods', $id, $old, $data);
        }
        
        return $success;
    }
    
    /**
     * Verifica si un método de pago puede ser eliminado
     * 
     * @param int $id ID del método de pago
     * @return array ['can_delete' => bool, 'relations' => array, 'message' => string]
     */
    public function canDeleteMethod(int $id): array {
        $relations = [
            'contract_payment_installments' => 'payment_method_id'
        ];
        
        return $this->canDelete($id, $relations);
    }
    
    /**
     * Elimina un método de pago
     * 
     * @param int $id ID del método de pago
     * @return bool True si tuvo éxito
     */
    public function deleteMethod(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        // Verificar que no tenga relaciones
        $validation = $this->canDeleteMethod($id);
        if (!$validation['can_delete']) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('payment_methods', $id, $old);
        }
        
        return $success;
    }
    
    /**
     * Cambia el estado (activo/inactivo) de un método de pago
     * 
     * @param int $id ID del método de pago
     * @param bool $isActive Estado a establecer
     * @return bool True si tuvo éxito
     */
    public function toggleActive(int $id, bool $isActive): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $query = "UPDATE {$this->table} SET is_active = :is_active WHERE id = :id";
        
        $success = $this->execute($query, [
            'is_active' => $isActive ? 1 : 0,
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('payment_methods', $id, $old, ['is_active' => $isActive]);
        }
        
        return $success;
    }
}
