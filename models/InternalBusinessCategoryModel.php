<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';

class InternalBusinessCategoryModel extends Model {
    protected $table = 'internal_business_categories';
    
    public function getAll(array $filters = []): array {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['name'])) {
            $query .= " AND name LIKE :name";
            $params['name'] = "%{$filters['name']}%";
        }

        $query .= " ORDER BY name";
        return $this->query($query, $params);
    }
    
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    public function create(array $data) {
        $query = "INSERT INTO {$this->table} 
                  (name, payment_count) 
                  VALUES 
                  (:name, :payment_count)";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'payment_count' => $data['payment_count']
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('internal_business_categories', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $query = "UPDATE {$this->table} 
                  SET name = :name,
                      payment_count = :payment_count
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'payment_count' => $data['payment_count'],
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('internal_business_categories', $id, $old, $data);
        }
        
        return $success;
    }
    
    public function canDeleteCategory(int $id): array {
        $relations = [
            'contract_business_categories' => 'internal_category_id'
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
            Audit::logDelete('internal_business_categories', $id, $old);
        }
        
        return $success;
    }
}
