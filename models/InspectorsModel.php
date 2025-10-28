<?php
require_once __DIR__ . '/../config/Database.php';

class InspectorsModel {
    private $db;
    private $table = 'inspectors';

    public function __construct() {
        $this->db = new Database();
    }

    // Obtener todos los inspectores, con opción de filtrado
public function getAll($filters = []) {
        
        $query = "SELECT * FROM {$this->table}"; // Consulta base sin WHERE inicial
        $binds = [];
        $conditions = [];

        // --- Lógica de Manejo de Estado (is_active) ---
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            // Si el filtro está presente (incluso si es '0' o '1'), lo aplicamos.
            $conditions[] = "is_active = :is_active";
            $binds[':is_active'] = (int)$filters['is_active']; // Aseguramos que sea entero (0 o 1)
        } else {
            // Si el filtro NO está presente, aplicamos la condición por defecto: SOLO ACTIVOS
            $conditions[] = "is_active = TRUE";
        }
        // ----------------------------------------------
        
        // 1. Filtro de Búsqueda General (search)
        if (isset($filters['search']) && !empty(trim($filters['search']))) {
            $searchTerm = '%' . trim($filters['search']) . '%';
            $conditions[] = "(inspector_code LIKE :search OR full_name LIKE :search OR email LIKE :search OR phone_number LIKE :search)";
            $binds[':search'] = $searchTerm;
        }

        // 2. Filtro de Código de Inspector
        if (isset($filters['inspector_code']) && !empty(trim($filters['inspector_code']))) {
            $conditions[] = "inspector_code = :inspector_code";
            $binds[':inspector_code'] = $filters['inspector_code'];
        }
        
        // 3. Filtro de Nombre
        if (isset($filters['full_name']) && !empty(trim($filters['full_name']))) {
            $conditions[] = "full_name LIKE :full_name_term";
            $binds[':full_name_term'] = '%' . trim($filters['full_name']) . '%';
        }
        
        // 4. Filtro de Correo Electrónico
        if (isset($filters['email']) && !empty(trim($filters['email']))) {
            $conditions[] = "email = :email";
            $binds[':email'] = $filters['email'];
        }

        // Añadir todas las condiciones al WHERE
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $query .= " ORDER BY full_name";
        
        $this->db->query($query);
        
        // Asignar los valores a las sentencias preparadas
        foreach ($binds as $key => $value) {
            $this->db->bind($key, $value);
        }

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