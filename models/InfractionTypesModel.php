<?php

require_once __DIR__ . '/../config/Database.php';

class InfractionTypesModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtiene todos los tipos de infracción con paginación y búsqueda.
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page, $limit, $search) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM infraction_types";
        
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE infraction_type_name LIKE ? OR description LIKE ? OR violated_article LIKE ?";
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm);
        }

        $sql .= " ORDER BY infraction_type_name ASC LIMIT " . $limit . " OFFSET " . $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Cuenta todos los tipos de infracción con un filtro de búsqueda opcional.
     * @param string $search
     * @return int
     */
    public function countAll($search) {
        $sql = "SELECT COUNT(infraction_type_id) FROM infraction_types";
        
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE infraction_type_name LIKE ? OR description LIKE ? OR violated_article LIKE ?";
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm);
        }

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Obtiene un solo tipo de infracción por su ID.
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $sql = "SELECT * FROM infraction_types WHERE infraction_type_id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Crea un nuevo tipo de infracción.
     * @param array $data
     * @return array
     */
    public function create($data) {
        $sql = "INSERT INTO infraction_types (infraction_type_name, description, violated_article, base_fine) 
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['infraction_type_name'],
            $data['description'] ?? null,
            $data['violated_article'] ?? null,
            $data['base_fine'] ?? null
        ];

        try {
            $this->db->executeQuery($sql, $params);
            return ['success' => true, 'message' => 'Tipo de infracción registrado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al registrar el tipo de infracción: ' . $e->getMessage()];
        }
    }

    /**
     * Actualiza un tipo de infracción existente.
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        $sql = "UPDATE infraction_types SET
                    infraction_type_name = ?,
                    description = ?,
                    violated_article = ?,
                    base_fine = ?
                WHERE infraction_type_id = ?";

        $params = [
            $data['infraction_type_name'],
            $data['description'] ?? null,
            $data['violated_article'] ?? null,
            $data['base_fine'] ?? null,
            $id
        ];

        try {
            $this->db->executeQuery($sql, $params);
            return ['success' => true, 'message' => 'Tipo de infracción actualizado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar el tipo de infracción: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina un tipo de infracción.
     * @param int $id
     * @return array
     */
    public function delete($id) {
        $sql = "DELETE FROM infraction_types WHERE infraction_type_id = ?";
        try {
            $this->db->executeQuery($sql, [$id]);
            return ['success' => true, 'message' => 'Tipo de infracción eliminado permanentemente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar el tipo de infracción. Es posible que esté asociado a otros registros.'];
        }
    }
}
