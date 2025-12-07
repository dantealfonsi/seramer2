<?php
/**
 * Modelo Sector
 * 
 * Gestiona los sectores del mercado (relacionados con zonas)
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class SectorModel extends Model {
    protected string $table = 'sectors';
    
    /**
     * Obtiene todos los sectores con información de su zona
     * 
     * @return array Lista de sectores
     */
    public function getAll(): array {
        $query = "SELECT s.*, z.name as zone_name 
                  FROM {$this->table} s
                  LEFT JOIN zones z ON s.zone_id = z.id
                  ORDER BY z.name, s.name";
        return $this->query($query);
    }
    
    /**
     * Obtiene un sector por ID
     * 
     * @param int $id ID del sector
     * @return array|null Sector o null
     */
    public function getById(int $id): ?array {
        $query = "SELECT s.*, z.name as zone_name 
                  FROM {$this->table} s
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE s.id = :id";
        return $this->queryOne($query, ['id' => $id]);
    }
    
    /**
     * Obtiene sectores por zona
     * 
     * @param int $zoneId ID de la zona
     * @return array Lista de sectores
     */
    public function getByZone(int $zoneId): array {
        $query = "SELECT * FROM {$this->table} WHERE zone_id = :zone_id ORDER BY name";
        return $this->query($query, ['zone_id' => $zoneId]);
    }
    
    /**
     * Crea un nuevo sector
     * 
     * @param array $data Datos del sector
     * @return int|false ID del sector creado o false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->table} 
                  (zone_id, name, description) 
                  VALUES 
                  (:zone_id, :name, :description)";
        
        $success = $this->execute($query, [
            'zone_id' => $data['zone_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('sectors', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    /**
     * Actualiza un sector
     * 
     * @param int $id ID del sector
     * @param array $data Datos a actualizar
     * @return bool True si tuvo éxito
     */
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $query = "UPDATE {$this->table} 
                  SET zone_id = :zone_id,
                      name = :name,
                      description = :description
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'zone_id' => $data['zone_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('sectors', $id, $old, $data);
        }
        
        return $success;
    }
    
    /**
     * Elimina un sector
     * 
     * @param int $id ID del sector
     * @return bool True si tuvo éxito
     */
    public function deleteSector(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('sectors', $id, $old);
        }
        
        return $success;
    }
}

