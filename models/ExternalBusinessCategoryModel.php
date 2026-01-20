<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';

class ExternalBusinessCategoryModel extends Model {
    protected $table = 'external_business_categories';
    
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        return $this->query($query);
    }
    
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    public function create(array $data) {
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
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
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
    
    public function canDeleteCategory(int $id): array {
        $relations = [
            'contract_business_categories' => 'external_category_id'
        ];
        
        foreach ($relations as $table => $fk) {
             $count = $this->queryOne("SELECT COUNT(*) as c FROM $table WHERE $fk = :id", ['id' => $id]);
            if (($count['c'] ?? 0) > 0) {
                 return ['can_delete' => false, 'message' => "Tiene registros relacionados en $table"];
            }
        }
        return ['can_delete' => true, 'message' => ''];
    }
    
    public function deleteCategory(int $id): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $validation = $this->canDeleteCategory($id);
        if (!$validation['can_delete']) return false;
        
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('external_business_categories', $id, $old);
        }
        
        return $success;
    }
}
