<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';

class ZoneModel extends Model {
    protected $table = 'zones';
    
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        return $this->query($query);
    }
    
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    public function create(array $data) {
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
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
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
    
    public function deleteZone(int $id): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('zones', $id, $old);
        }
        
        return $success;
    }
}
