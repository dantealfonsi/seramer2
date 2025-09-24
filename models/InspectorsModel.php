<?php
require_once __DIR__ . '/../config/Database.php';

class InspectorsModel {
    private $db;
    private $table = 'inspectors';

    public function __construct() {
        $this->db = new Database();
    }

    // Obtener todos los inspectores
    public function getAll() {
        $query = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY full_name";        
        $this->db->query($query);
        return $this->db->resultSet();
    }

    // Obtener un inspector por su ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE inspector_id = :id";
        $this->db->query($query);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Crear un nuevo inspector
    public function create($data) {
        $query = "INSERT INTO {$this->table} (inspector_code, full_name, phone_number, email, hire_date) VALUES (:inspector_code, :full_name, :phone_number, :email, :hire_date)";
        $this->db->query($query);
        $this->db->bind(':inspector_code', $data['inspector_code']);
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':phone_number', $data['phone_number']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':hire_date', $data['hire_date']);
        return $this->db->execute();
    }

    // Actualizar un inspector existente
    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET 
                    inspector_code = :inspector_code, 
                    full_name = :full_name, 
                    phone_number = :phone_number, 
                    email = :email, 
                    is_active = :is_active
                  WHERE inspector_id = :id";
        $this->db->query($query);
        $this->db->bind(':id', $id);
        $this->db->bind(':inspector_code', $data['inspector_code']);
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':phone_number', $data['phone_number']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':is_active', $data['is_active']);
        return $this->db->execute();
    }

    // Eliminar (desactivar) un inspector por su ID
    public function delete($id) {
        $query = "UPDATE {$this->table} SET is_active = FALSE WHERE inspector_id = :id";
        $this->db->query($query);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Métodos adicionales útiles
    public function getByUserId($inspector_code) {
        $query = "SELECT * FROM {$this->table} WHERE inspector_code = :inspector_code";
        $this->db->query($query);
        $this->db->bind(':inspector_code', $inspector_code);
        return $this->db->single();
    }
}