<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';

class MarketStallModel extends Model {
    protected $table = 'market_stalls';
    
    public function getAll(array $filters = []): array {
        $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name 
                  FROM {$this->table} ms
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE 1=1";
        $params = [];

        if (!empty($filters['stall_number'])) {
            $query .= " AND ms.stall_number LIKE :stall_number";
            $params['stall_number'] = "%{$filters['stall_number']}%";
        }

        if (!empty($filters['sector_id'])) {
            $query .= " AND ms.sector_id = :sector_id";
            $params['sector_id'] = $filters['sector_id'];
        }

        if (!empty($filters['zone_id'])) {
            $query .= " AND s.zone_id = :zone_id";
            $params['zone_id'] = $filters['zone_id'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND ms.status = :status";
            $params['status'] = $filters['status'];
        }

        $query .= " ORDER BY z.name, s.name, ms.stall_number";
        return $this->query($query, $params);
    }
    
    public function getById(int $id): ?array {
        $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name, s.zone_id
                  FROM {$this->table} ms
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE ms.id = :id";
        return $this->queryOne($query, ['id' => $id]);
    }
    
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
    
    public function getByStallNumber(int $sectorId, string $stallNumber): ?array {
        $query = "SELECT * FROM {$this->table} 
                  WHERE sector_id = :sector_id AND stall_number = :stall_number 
                  LIMIT 1";
        $result = $this->query($query, [
            'sector_id' => $sectorId,
            'stall_number' => $stallNumber
        ]);
        return $result[0] ?? null; // Since fetchAll returns array of rows, we need the first one or null
        // Wait, Model::queryOne returns a single row array or false/null. 
        // My Model::queryOne returns fetchOne() which returns associative array.
        // So checking the implementation of Model::queryOne in Model.php...
        // it calls fetchOne() which returns "mixed". Usually assoc array or false.
        // But here I'm using $this->query() which returns fetchAll().
        // So $result[0] is correct if I used query(). But if I use queryOne for single result is better.
        // Let's refactor to iterate to queryOne in getByStallNumber if possible, or keep as is.
        // The original code used query and returned [0]??null.
        // I will stick to queryOne if I can, but let's just fix it to be safe.
        // Using queryOne directly:
        return $this->queryOne("SELECT * FROM {$this->table} WHERE sector_id = :sector_id AND stall_number = :stall_number LIMIT 1", [
            'sector_id' => $sectorId,
            'stall_number' => $stallNumber
        ]) ?: null;
    }
    
    /**
     * Search stall by number only (without sector).
     * @param string $stallNumber
     * @return array|null
     */
    public function searchByStallNumber(string $stallNumber): ?array {
        $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name 
                  FROM {$this->table} ms
                  LEFT JOIN sectors s ON ms.sector_id = s.id
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE ms.stall_number = :stall_number 
                  LIMIT 1";
        return $this->queryOne($query, ['stall_number' => $stallNumber]) ?: null;
    }
    
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
    
    public function create(array $data) {
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
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
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
    
    public function deleteStall(int $id): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('market_stalls', $id, $old);
        }
        
        return $success;
    }
}
