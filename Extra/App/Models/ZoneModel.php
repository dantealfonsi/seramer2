<?php
/**
 * Modelo Zone
 * 
 * Gestiona las zonas del mercado
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class ZoneModel extends Model {
    protected string $table = 'zones';
    
    /**
     * Obtiene todas las zonas
     * 
     * @return array Lista de zonas
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        return $this->query($query);
    }
    
    /**
     * Obtiene una zona por ID
     * 
     * @param int $id ID de la zona
     * @return array|null Zona o null
     */
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    /**
     * Crea una nueva zona
     * 
     * @param array $data Datos de la zona
     * @return int|false ID de la zona creada o false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->table} 
                  (name, description) 
                  VALUES 
                  (:name, :description)";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('zones', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    /**
     * Actualiza una zona
     * 
     * @param int $id ID de la zona
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
                      description = :description
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('zones', $id, $old, $data);
        }
        
        return $success;
    }
    
    /**
     * Elimina una zona
     * 
     * @param int $id ID de la zona
     * @return bool True si tuvo éxito
     */
    public function deleteZone(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('zones', $id, $old);
        }
        
        return $success;
    }
}

