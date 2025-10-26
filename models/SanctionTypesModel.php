<?php

require_once __DIR__ . '/../config/Database.php';

class SanctionTypesModel {
    private $conn;
    private $table = 'sanction_types';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene todos los tipos de sanción con paginación y búsqueda.
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '') {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM " . $this->table;

        if (!empty($search)) {
            $query .= " WHERE severity_name LIKE :search OR description LIKE :search";
        }
        
        $query .= " ORDER BY severity_name ASC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            if (!empty($search)) {
                $searchTerm = "%$search%";
                $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log("Error en getAll: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Cuenta todos los tipos de sanción para la paginación.
     * @param string $search
     * @return int
     */
    public function countAll($search = '') {
        $query = "SELECT COUNT(*) FROM " . $this->table;

        if (!empty($search)) {
            $query .= " WHERE severity_name LIKE :search OR description LIKE :search";
        }

        try {
            $stmt = $this->conn->prepare($query);

            if (!empty($search)) {
                $searchTerm = "%$search%";
                $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $exception) {
            error_log("Error en countAll: " . $exception->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene un tipo de sanción por ID.
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE sanction_type_id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log("Error en getById: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo tipo de sanción.
     * @param array $data
     * @return array
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (severity_name, description) VALUES (:sanction_type_name, :description)";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':sanction_type_name', $data['sanction_type_name']);
            $stmt->bindParam(':description', $data['description']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tipo de sanción creado exitosamente.'];
            }
        } catch (PDOException $exception) {
            error_log("Error en create: " . $exception->getMessage());
        }
        
        return ['success' => false, 'message' => 'Error al crear el tipo de sanción.'];
    }

    /**
     * Actualiza un tipo de sanción existente.
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET severity_name = :sanction_type_name, description = :description WHERE sanction_type_id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':sanction_type_name', $data['sanction_type_name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tipo de sanción actualizado exitosamente.'];
            }
        } catch (PDOException $exception) {
            error_log("Error en update: " . $exception->getMessage());
        }
        
        return ['success' => false, 'message' => 'Error al actualizar el tipo de sanción.'];
    }

    /**
     * Elimina un tipo de sanción.
     * @param int $id
     * @return array
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE sanction_type_id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tipo de sanción eliminado exitosamente.'];
            }
        } catch (PDOException $exception) {
            error_log("Error en delete: " . $exception->getMessage());
        }
        
        return ['success' => false, 'message' => 'Error al eliminar el tipo de sanción.'];
    }
}
