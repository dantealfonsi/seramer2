<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
require_once __DIR__ . '/DailyCashRegisterModel.php';

class CashRegisterModel extends Model {
    protected $table = 'cash_registers';
    
    public function getAll(): array {
        $query = "SELECT cr.*,
                         u.username,
                         u.email,
                         COALESCE(
                             NULLIF(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')), ' '),
                             u.username,
                             CONCAT('Usuario #', u.id)
                         ) as assigned_user_name
                  FROM {$this->table} cr
                  LEFT JOIN users u ON cr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  ORDER BY cr.name ASC";
        return $this->query($query);
    }
    
    public function getById(int $id): ?array {
        $query = "SELECT cr.*,
                         u.username,
                         u.email,
                         COALESCE(
                             NULLIF(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')), ' '),
                             u.username,
                             CONCAT('Usuario #', u.id)
                         ) as assigned_user_name
                  FROM {$this->table} cr
                  LEFT JOIN users u ON cr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  WHERE cr.id = :id
                  LIMIT 1";
        return $this->queryOne($query, ['id' => $id]);
    }
    
    public function getByAssignedUser(int $userId): ?array {
        $query = "SELECT cr.*
                  FROM {$this->table} cr
                  WHERE cr.user_id = :user_id
                  AND cr.status = 'active'
                  LIMIT 1";
        return $this->queryOne($query, ['user_id' => $userId]);
    }
    
    public function create(array $data) {
        $query = "INSERT INTO {$this->table} 
                  (name, user_id, status) 
                  VALUES 
                  (:name, :user_id, :status)";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'user_id' => $data['user_id'],
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('cash_registers', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        if (!$old) return false;
        
        $query = "UPDATE {$this->table} 
                  SET name = :name,
                      user_id = :user_id,
                      description = :description,
                      status = :status
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'name' => $data['name'],
            'user_id' => $data['user_id'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('cash_registers', $id, $old, $data);
        }
        
        return $success;
    }
    
    public function delete(int $id): bool {
        $dailyCashModel = new DailyCashRegisterModel();
        if ($dailyCashModel->hasOpenings($id)) {
            return false;
        }
        
        $old = $this->getById($id);
        if (!$old) return false;
        
        $success = $this->execute("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
        
        if ($success) {
            Audit::logDelete('cash_registers', $id, $old);
        }
        
        return $success;
    }
    
    public function canDelete(int $id, array $relations = []): array {
        $dailyCashModel = new DailyCashRegisterModel();
        if ($dailyCashModel->hasOpenings($id)) {
            return [
                'can_delete' => false,
                'message' => 'No se puede eliminar la caja porque tiene aperturas registradas'
            ];
        }
        return ['can_delete' => true, 'message' => 'La caja puede ser eliminada'];
    }
}
