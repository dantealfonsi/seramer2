<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';

class PaymentMethodModel extends Model {
    protected $table = 'payment_methods';
    
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        return $this->query($query);
    }
    
    public function getActive(): array {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name";
        return $this->query($query);
    }
    
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    public function existsByName(string $name, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = :name";
        $params = ['name' => $name];
        if ($excludeId !== null) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $result = $this->queryOne($query, $params);
        return ($result['count'] ?? 0) > 0;
    }
    
    public function create(array $data) {
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
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
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
    
    public function canDeleteMethod(int $id): array {
        $relations = [
            'contract_payment_installments' => 'payment_method_id'
        ];
        foreach ($relations as $table => $fk) {
             $count = $this->queryOne("SELECT COUNT(*) as c FROM $table WHERE $fk = :id", ['id' => $id]);
            if (($count['c'] ?? 0) > 0) {
                 return ['can_delete' => false, 'message' => "Tiene registros relacionados en $table"];
            }
        }
        return ['can_delete' => true, 'message' => ''];
    }
    
    public function deleteMethod(int $id): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $validation = $this->canDeleteMethod($id);
        if (!$validation['can_delete']) return false;
        
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('payment_methods', $id, $old);
        }
        
        return $success;
    }
    
    public function toggleActive(int $id, bool $isActive): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
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
