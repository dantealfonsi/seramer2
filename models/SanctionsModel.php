<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Modelo para la gestión de Sanciones
 * * Interactúa directamente con la base de datos para
 * realizar operaciones CRUD en la tabla `sanctions`.
 */
class SanctionsModel {

    private $conn;
    private $table = "sanctions";
    private $status_logical_column = "status_logical";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Obtiene todos los registros de sanciones.
     * @return array
     */
     public function index($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $query = "SELECT s.*, 
                             i.infraction_description AS infraction_description, 
                             st.sanction_type_name AS sanction_type_name
                      FROM " . $this->table . " s
                      JOIN infractions i ON s.infraction_id = i.infraction_id
                      JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id
                      ";
            
            $params = [];
            if (!empty($search)) {
                $query .= " AND (i.infraction_description LIKE :search OR st.sanction_type_name LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY s.imposition_date DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log("Error al obtener sanciones: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Crea un nuevo registro de sanción.
     * @param array $data
     * @return bool
     */
public function create($data) {
        try {
            // Revisa si los campos obligatorios existen en el array $data
            $required_fields = ['infraction_id', 'sanction_type_id', 'fine_amount', 'effect_start_date', 'imposed_by_user_id'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Falta el campo obligatorio: '{$field}'."
                    ];
                }
            }

            $query = "INSERT INTO " . $this->table . " SET
                      infraction_id = :infraction_id,
                      sanction_type_id = :sanction_type_id,
                      fine_amount = :fine_amount,
                      fine_currency = :fine_currency,
                      effect_start_date = :effect_start_date,
                      effect_end_date = :effect_end_date,
                      sanction_status = :sanction_status,
                      sanction_observations = :sanction_observations,
                      is_repeat_offense = :is_repeat_offense,
                      imposed_by_user_id = :imposed_by_user_id";

            $stmt = $this->conn->prepare($query);

            // Enlazar los parámetros. Usamos bindValue por seguridad.
            $stmt->bindValue(':infraction_id', $data['infraction_id'], PDO::PARAM_INT);
            $stmt->bindValue(':sanction_type_id', $data['sanction_type_id'], PDO::PARAM_INT);
            $stmt->bindValue(':fine_amount', $data['fine_amount']);
            $stmt->bindValue(':fine_currency', $data['fine_currency']);
            $stmt->bindValue(':effect_start_date', $data['effect_start_date']);
            $stmt->bindValue(':effect_end_date', $data['effect_end_date']);
            $stmt->bindValue(':sanction_status', $data['sanction_status']);
            $stmt->bindValue(':sanction_observations', $data['sanction_observations'] ?? null); // Usar null si el campo es opcional
            $stmt->bindValue(':is_repeat_offense', $data['is_repeat_offense'], PDO::PARAM_INT);
            $stmt->bindValue(':imposed_by_user_id', $data['imposed_by_user_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Sanción creada correctamente.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo crear la sanción.'
                ];
            }
        } catch (PDOException $exception) {
            // Captura y registra el error de la base de datos
            error_log("Error al crear sanción: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error de la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Actualiza un registro de sanción.
     * @param int $id
     * @param array $data
     * @return bool
     */
  public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET
                      infraction_id = :infraction_id,
                      sanction_type_id = :sanction_type_id,
                      fine_amount = :fine_amount,
                      fine_currency = :fine_currency,
                      effect_start_date = :effect_start_date,
                      effect_end_date = :effect_end_date,
                      sanction_status = :sanction_status,
                      sanction_observations = :sanction_observations,
                      is_repeat_offense = :is_repeat_offense,
                      imposed_by_user_id = :imposed_by_user_id
                      WHERE sanction_id = :id";
    
            $stmt = $this->conn->prepare($query);
    
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':infraction_id', $data['infraction_id'], PDO::PARAM_INT);
            $stmt->bindValue(':sanction_type_id', $data['sanction_type_id'], PDO::PARAM_INT);
            $stmt->bindValue(':fine_amount', $data['fine_amount']);
            $stmt->bindValue(':fine_currency', $data['fine_currency']);
            $stmt->bindValue(':effect_start_date', $data['effect_start_date']);
            $stmt->bindValue(':effect_end_date', $data['effect_end_date']);
            $stmt->bindValue(':sanction_status', $data['sanction_status']);
            $stmt->bindValue(':sanction_observations', $data['sanction_observations'] ?? null);
            $stmt->bindValue(':is_repeat_offense', $data['is_repeat_offense'], PDO::PARAM_INT);
            $stmt->bindValue(':imposed_by_user_id', $data['imposed_by_user_id'], PDO::PARAM_INT);
    
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Sanción actualizada correctamente.'];
            }
        } catch (PDOException $exception) {
            error_log("Error al actualizar sanción: " . $exception->getMessage());
            return ['success' => false, 'message' => 'Error de la base de datos: ' . $exception->getMessage()];
        }
        return ['success' => false, 'message' => 'No se pudo actualizar la sanción.'];
    }

    public function getById($id) {
        $query = "SELECT s.*, i.infraction_description as infraction_description, st.sanction_type_name as sanction_type_name
                  FROM " . $this->table . " s
                  JOIN infractions i ON s.infraction_id = i.infraction_id
                  JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id
                  WHERE s.sanction_id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll($page, $limit, $search) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT s.*, i.infraction_description as infraction_name, st.sanction_type_name as sanction_type_name
                  FROM " . $this->table . " s
                  JOIN infractions i ON s.infraction_id = i.infraction_id
                  JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id";
        
        $params = [];
        if (!empty($search)) {
            $query .= " WHERE i.infraction_description LIKE :search OR st.sanction_type_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
    
        $query .= " ORDER BY s.sanction_id DESC LIMIT :limit OFFSET :offset";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    

    /**
     * Elimina lógicamente un registro de sanción.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $query = "UPDATE " . $this->table . " SET " . $this->status_logical_column . " = 'inactive' WHERE sanction_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $exception) {
            error_log("Error al eliminar sanción: " . $exception->getMessage());
        }
        return false;
    }

    public function countAll($search) {
        $query = "SELECT COUNT(*) as total_count FROM " . $this->table;
        $params = [];
        if (!empty($search)) {
            $query .= " JOIN infractions i ON " . $this->table . ".infraction_id = i.infraction_id";
            $query .= " JOIN sanction_types st ON " . $this->table . ".sanction_type_id = st.sanction_type_id";
            $query .= " WHERE i.infraction_description LIKE :search OR st.sanction_type_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_count'];
    } 
    
    public function getInfractionDropdown() {
        $query = "SELECT infraction_id, infraction_description FROM infractions ORDER BY infraction_description ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSanctionTypeDropdown() {
        $query = "SELECT sanction_type_id, sanction_type_name FROM sanction_types ORDER BY sanction_type_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

}
