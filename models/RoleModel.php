<?php

require_once __DIR__ . '/../config/Database.php';

class RoleModel {
    private $conn;
    private $table = 'roles';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get all roles optionally filtered by department.
     */
    public function getAll($department_id = null) {
        try {
            $query = "SELECT r.*, d.name as department_name 
                      FROM {$this->table} r 
                      INNER JOIN departments d ON r.department_id = d.id";
            if ($department_id) {
                $query .= " WHERE r.department_id = :department_id";
            }
            $query .= " ORDER BY r.department_id, r.name";

            $stmt = $this->conn->prepare($query);
            if ($department_id) {
                $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting roles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single role by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT r.*, d.name as department_name 
                      FROM {$this->table} r 
                      INNER JOIN departments d ON r.department_id = d.id 
                      WHERE r.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting role: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new role
     */
    public function create($data) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (department_id, name, description, can_read, can_write, can_modify, can_delete, menu_json) 
                      VALUES (:department_id, :name, :description, :can_read, :can_write, :can_modify, :can_delete, :menu_json)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':department_id', $data['department_id'], PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':can_read', $data['can_read'], PDO::PARAM_INT);
            $stmt->bindParam(':can_write', $data['can_write'], PDO::PARAM_INT);
            $stmt->bindParam(':can_modify', $data['can_modify'], PDO::PARAM_INT);
            $stmt->bindParam(':can_delete', $data['can_delete'], PDO::PARAM_INT);
            $menu_json = isset($data['menu_json']) ? $data['menu_json'] : null;
            $stmt->bindParam(':menu_json', $menu_json);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating role: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing role
     */
    public function update($id, $data) {
        try {
            // Prevent changing permissions of 'admin'
            $currentRole = $this->getById($id);
            if ($currentRole && $currentRole['name'] === 'admin') {
                return false; // Cannot update admin
            }

            $query = "UPDATE {$this->table} 
                      SET name = :name, description = :description, 
                          can_read = :can_read, can_write = :can_write, 
                          can_modify = :can_modify, can_delete = :can_delete,
                          menu_json = :menu_json 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':can_read', $data['can_read'], PDO::PARAM_INT);
            $stmt->bindParam(':can_write', $data['can_write'], PDO::PARAM_INT);
            $stmt->bindParam(':can_modify', $data['can_modify'], PDO::PARAM_INT);
            $stmt->bindParam(':can_delete', $data['can_delete'], PDO::PARAM_INT);
            $menu_json = isset($data['menu_json']) ? $data['menu_json'] : null;
            $stmt->bindParam(':menu_json', $menu_json);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating role: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update permissions only (used by dept admin)
     */
    public function updatePermissions($id, $can_read, $can_write, $can_modify, $can_delete, $menu_json = null) {
        try {
            // Prevent changing admin properties
            $currentRole = $this->getById($id);
            if ($currentRole && $currentRole['name'] === 'admin') {
                return false;
            }

            $query = "UPDATE {$this->table} 
                      SET can_read = :can_read, can_write = :can_write, 
                          can_modify = :can_modify, can_delete = :can_delete,
                          menu_json = :menu_json 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':can_read', $can_read, PDO::PARAM_INT);
            $stmt->bindParam(':can_write', $can_write, PDO::PARAM_INT);
            $stmt->bindParam(':can_modify', $can_modify, PDO::PARAM_INT);
            $stmt->bindParam(':can_delete', $can_delete, PDO::PARAM_INT);
            $stmt->bindParam(':menu_json', $menu_json);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating role permissions: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a role
     */
    public function delete($id) {
        try {
            // Prevent deleting 'admin'
            $currentRole = $this->getById($id);
            if ($currentRole && $currentRole['name'] === 'admin') {
                return false;
            }

            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting role: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Get all departments for filtering
     */
    public function getDepartments() {
        try {
            $query = "SELECT id, name FROM departments ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting departments in RoleModel: " . $e->getMessage());
            return [];
        }
    }
}
