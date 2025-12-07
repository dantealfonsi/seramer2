<?php
/**
 * Modelo MarketStall
 * 
 * Gestiona los locales del mercado (relacionados con sectores)
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class MarketStallModel extends Model {
    protected string $table = 'market_stalls';
    
    /**
     * Obtiene todos los locales con información de sector y zona
     * 
     * @return array Lista de locales
     */
    public function getAll(): array {
        $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name 
                  FROM {$this->table} ms
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  ORDER BY z.name, s.name, ms.stall_number";
        return $this->query($query);
    }
    
    /**
     * Obtiene un local por ID
     * 
     * @param int $id ID del local
     * @return array|null Local o null
     */
    public function getById(int $id): ?array {
        $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name, s.zone_id
                  FROM {$this->table} ms
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE ms.id = :id";
        return $this->queryOne($query, ['id' => $id]);
    }
    
    /**
     * Obtiene locales por sector
     * 
     * @param int $sectorId ID del sector
     * @return array Lista de locales con información de zona y sector
     */
    public function getBySector(int $sectorId): array {
        $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name 
                  FROM {$this->table} ms
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE ms.sector_id = :sector_id 
                  AND ms.id NOT IN (
                      SELECT DISTINCT cl.stall_id 
                      FROM contract_locations cl
                      INNER JOIN contracts c ON cl.contract_id = c.id
                      WHERE c.end_date >= CURDATE()
                  )
                  ORDER BY ms.stall_number";
        return $this->query($query, ['sector_id' => $sectorId]);
    }
    
    /**
     * Obtiene un local por número de local y sector
     * 
     * @param int $sectorId ID del sector
     * @param string $stallNumber Número del local
     * @return array|null Local o null si no existe
     */
    public function getByStallNumber(int $sectorId, string $stallNumber): ?array {
        $query = "SELECT * FROM {$this->table} 
                  WHERE sector_id = :sector_id AND stall_number = :stall_number 
                  LIMIT 1";
        $result = $this->query($query, [
            'sector_id' => $sectorId,
            'stall_number' => $stallNumber
        ]);
        return $result[0] ?? null;
    }
    
    /**
     * Obtiene locales disponibles (no asignados a contratos activos)
     * 
     * @return array Lista de locales disponibles
     */
    public function getAvailable(): array {
        $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name 
                  FROM {$this->table} ms
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE ms.id NOT IN (
                      SELECT DISTINCT cl.stall_id 
                      FROM contract_locations cl
                      INNER JOIN contracts c ON cl.contract_id = c.id
                      WHERE c.end_date >= CURDATE()
                  )
                  ORDER BY z.name, s.name, ms.stall_number";
        return $this->query($query);
    }
    
    /**
     * Crea un nuevo local
     * 
     * @param array $data Datos del local
     * @return int|false ID del local creado o false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->table} 
                  (sector_id, stall_number, location_description) 
                  VALUES 
                  (:sector_id, :stall_number, :location_description)";
        
        $success = $this->execute($query, [
            'sector_id' => $data['sector_id'],
            'stall_number' => $data['stall_number'],
            'location_description' => $data['location_description'] ?? null
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('market_stalls', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    /**
     * Actualiza un local
     * 
     * @param int $id ID del local
     * @param array $data Datos a actualizar
     * @return bool True si tuvo éxito
     */
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $query = "UPDATE {$this->table} 
                  SET sector_id = :sector_id,
                      stall_number = :stall_number,
                      location_description = :location_description
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'sector_id' => $data['sector_id'],
            'stall_number' => $data['stall_number'],
            'location_description' => $data['location_description'] ?? null,
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('market_stalls', $id, $old, $data);
        }
        
        return $success;
    }
    
    /**
     * Elimina un local
     * 
     * @param int $id ID del local
     * @return bool True si tuvo éxito
     */
    public function deleteStall(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('market_stalls', $id, $old);
        }
        
        return $success;
    }
}
