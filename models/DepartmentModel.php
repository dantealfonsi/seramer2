<?php
require_once __DIR__ . '/Model.php';

class DepartmentModel extends Model {
    protected $table = 'departments';

    /**
     * Obtener todos los departamentos con información del manager
     */
    public function getAllWithManager() {
        $sql = "SELECT d.*, s.first_name, s.last_name, s.id_number 
                FROM {$this->table} d
                LEFT JOIN staff s ON d.manager_id = s.id
                ORDER BY d.name";
        return $this->query($sql);
    }

    /**
     * Obtener lista de personal elegible para ser manager (staff activo)
     */
    public function getPotentialManagers() {
        // Asumiendo que cualquier staff activo puede ser manager
        // Podríamos filtrar por rol si fuera necesario, pero el usuario dijo "dependiendo el manager id"
        $sql = "SELECT id, first_name, last_name, id_number 
                FROM staff 
                WHERE status = 'active' 
                ORDER BY first_name, last_name";
        return $this->query($sql);
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, manager_id, shift_type, description) 
                VALUES (:name, :manager_id, :shift_type, :description)";
        
        $params = [
            ':name' => $data['name'],
            ':manager_id' => $data['manager_id'] ?: null,
            ':shift_type' => $data['shift_type'] ?? 'Day', // Default value?
            ':description' => $data['description'] ?? null
        ];
        
        return $this->execute($sql, $params);
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET name = :name, 
                    manager_id = :manager_id, 
                    shift_type = :shift_type,
                    description = :description
                WHERE id = :id";
        
        $params = [
            ':id' => $id,
            ':name' => $data['name'],
            ':manager_id' => $data['manager_id'] ?: null,
            ':shift_type' => $data['shift_type'] ?? null,
            ':description' => $data['description'] ?? null
        ];
        
        return $this->execute($sql, $params);
    }

    public function delete($id) {
        // Verificar si hay staff asignado a este departamento antes de borrar?
        // Por ahora simple delete
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->execute($sql, [':id' => $id]);
    }
}
