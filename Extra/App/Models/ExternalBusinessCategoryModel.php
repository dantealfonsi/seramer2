<?php
/**
 * Modelo ExternalBusinessCategory
 * 
 * Gestiona los rubros (categorías) de negocios externos del mercado
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class ExternalBusinessCategoryModel extends Model {
    protected string $table = 'external_business_categories';
    
    /**
     * Obtiene todas las categorías
     * 
     * @return array Lista de categorías
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        return $this->query($query);
    }
    
    /**
     * Obtiene una categoría por ID
     * 
     * @param int $id ID de la categoría
     * @return array|null Categoría o null
     */
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    /**
     * Crea una nueva categoría
     * 
     * @param array $data Datos de la categoría
     * @return int|false ID de la categoría creada o false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->table} 
                  (name, installation_type, payment_count) 
                  VALUES 
                  (:name, :installation_type, :payment_count)";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'installation_type' => $data['installation_type'] ?? null,
            'payment_count' => $data['payment_count']
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('external_business_categories', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    /**
     * Actualiza una categoría
     * 
     * @param int $id ID de la categoría
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
                      installation_type = :installation_type,
                      payment_count = :payment_count
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'installation_type' => $data['installation_type'] ?? null,
            'payment_count' => $data['payment_count'],
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('external_business_categories', $id, $old, $data);
        }
        
        return $success;
    }
    
    /**
     * Verifica si una categoría puede ser eliminada
     * 
     * @param int $id ID de la categoría
     * @return array ['can_delete' => bool, 'relations' => array, 'message' => string]
     */
    public function canDeleteCategory(int $id): array {
        $relations = [
            'contract_business_categories' => 'external_category_id'
        ];
        
        return $this->canDelete($id, $relations);
    }
    
    /**
     * Elimina una categoría
     * 
     * @param int $id ID de la categoría
     * @return bool True si tuvo éxito
     */
    public function deleteCategory(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        // Verificar que no tenga relaciones
        $validation = $this->canDeleteCategory($id);
        if (!$validation['can_delete']) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('external_business_categories', $id, $old);
        }
        
        return $success;
    }
}
