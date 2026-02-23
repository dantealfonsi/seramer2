<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';

class SectorModel extends Model {
    protected $table = 'sectors';
    
    public function getAll(array $filters = []): array {
        $query = "SELECT s.*, z.name as zone_name 
                  FROM {$this->table} s
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE 1=1";
        $params = [];

        if (!empty($filters['name'])) {
            $query .= " AND s.name LIKE :name";
            $params['name'] = "%{$filters['name']}%";
        }

        if (!empty($filters['zone_id'])) {
            $query .= " AND s.zone_id = :zone_id";
            $params['zone_id'] = $filters['zone_id'];
        }

        $query .= " ORDER BY z.name, s.name";
        return $this->query($query, $params);
    }
    
    public function getById(int $id): ?array {
        $query = "SELECT s.*, z.name as zone_name 
                  FROM {$this->table} s
                  LEFT JOIN zones z ON s.zone_id = z.id
                  WHERE s.id = :id";
        return $this->queryOne($query, ['id' => $id]);
    }
    
    public function getByZone(int $zoneId): array {
        $query = "SELECT * FROM {$this->table} WHERE zone_id = :zone_id ORDER BY name";
        return $this->query($query, ['zone_id' => $zoneId]);
    }
    
    public function create(array $data) {
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
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
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
    
    public function deleteSector(int $id): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('sectors', $id, $old);
        }
        
        return $success;
    }
}
