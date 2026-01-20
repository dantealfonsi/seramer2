<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';

class AwardeeModel extends Model {
    protected $table = 'awardees';
    
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY last_name, first_name";
        return $this->query($query);
    }
    
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    public function getByIdNumber(string $idNumber): ?array {
        $query = "SELECT * FROM {$this->table} WHERE id_number = :id_number LIMIT 1";
        return $this->queryOne($query, ['id_number' => $idNumber]) ?: null;
    }
    
    public function search(string $search): array {
        $query = "SELECT * FROM {$this->table} 
                  WHERE CONCAT(first_name, ' ', last_name) LIKE :search
                  OR id_number LIKE :search
                  ORDER BY last_name, first_name";
        
        return $this->query($query, ['search' => "%{$search}%"]);
    }
    
    public function create(array $data) {
        if ($this->idNumberExists($data['id_number'])) {
            return false;
        }
        
        $query = "INSERT INTO {$this->table} 
                  (first_name, middle_name, last_name, second_last_name, id_number, phone, email, address) 
                  VALUES 
                  (:first_name, :middle_name, :last_name, :second_last_name, :id_number, :phone, :email, :address)";
        
        $success = $this->execute($query, [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'second_last_name' => $data['second_last_name'] ?? null,
            'id_number' => $data['id_number'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('awardees', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        
        if (!$old) return false;
        
        if (isset($data['id_number']) && $this->idNumberExists($data['id_number'], $id)) {
            return false;
        }
        
        $query = "UPDATE {$this->table} 
                  SET first_name = :first_name,
                      middle_name = :middle_name,
                      last_name = :last_name,
                      second_last_name = :second_last_name,
                      id_number = :id_number,
                      phone = :phone,
                      email = :email,
                      address = :address
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'second_last_name' => $data['second_last_name'] ?? null,
            'id_number' => $data['id_number'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('awardees', $id, $old, $data);
        }
        
        return $success;
    }
    
    public function canDeleteAwardee(int $id): array {
        $relations = [
            'contracts' => 'awardee_id'
        ];
        
        // Manual implementation of check logic since base helper isn't there yet
        foreach ($relations as $table => $fk) {
             $count = $this->queryOne("SELECT COUNT(*) as c FROM $table WHERE $fk = :id", ['id' => $id]);
            if (($count['c'] ?? 0) > 0) {
                 return ['can_delete' => false, 'message' => "Tiene registros relacionados en $table"];
            }
        }
        
        return ['can_delete' => true, 'message' => ''];
    }
    
    public function deleteAwardee(int $id): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $validation = $this->canDeleteAwardee($id);
        if (!$validation['can_delete']) return false;
        
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('awardees', $id, $old);
        }
        
        return $success;
    }
    
    public function idNumberExists(string $idNumber, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE id_number = :id_number";
        $params = ['id_number' => $idNumber];
        
        if ($excludeId !== null) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($query, $params);
        return ($result['count'] ?? 0) > 0;
    }
    
    public static function getFullName(array $awardee): string {
        $parts = [
            $awardee['first_name'],
            $awardee['middle_name'] ?? '',
            $awardee['last_name'],
            $awardee['second_last_name'] ?? ''
        ];
        
        return trim(implode(' ', array_filter($parts)));
    }
}
