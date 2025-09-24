<?php

require_once __DIR__ . '/../config/Database.php';

class InfractionsModel {
    private $conn;
    private $table = 'infractions';
    
    // Propiedades de la infracción
    public $infraction_id;
    public $awardee_id;
    public $stall_id;
    public $infraction_datetime;
    public $infraction_type_id;
    public $infraction_description;
    public $infraction_status;
    public $inspector_observations;
    public $proof; // Nuevo campo para la evidencia
    public $status_logical;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAwardeesList() {
        try {
            $query = "SELECT id, first_name, last_name, id_number,phone FROM awardees ORDER BY first_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener adjudicatarios: " . $exception->getMessage());
            return [];
        }
    }

    public function getStallsList() {
        try {
            $query = "SELECT id, sector_id, stall_number, location_description FROM market_stalls ORDER BY stall_number";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener puestos de mercado: " . $exception->getMessage());
            return [];
        }
    }

    public function getInfractionTypesList() {
        try {
            $query = "SELECT infraction_type_id, infraction_type_name FROM infraction_types ORDER BY infraction_type_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener tipos de infracción: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las infracciones
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $searchParam = "%$search%";

            $query = "SELECT i.*, 
                            a.first_name as adjudicatory_name,
                            a.id_number as adjudicatory_document,
                            s.stall_number,
                            it.infraction_type_name
                    FROM " . $this->table . " i
                    LEFT JOIN awardees a ON i.awardee_id = a.id
                    LEFT JOIN market_stalls s ON i.stall_id = s.id
                    LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                    WHERE (a.first_name LIKE :search1 
                            OR s.stall_number LIKE :search2 
                            OR it.infraction_type_name LIKE :search3)
                    AND i.status_logical = 'active'
                    ORDER BY i.infraction_datetime DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query); 
            
            $stmt->bindValue(':search1', $searchParam, PDO::PARAM_STR);
            $stmt->bindValue(':search2', $searchParam, PDO::PARAM_STR);
            $stmt->bindValue(':search3', $searchParam, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener infracciones: " . $exception->getMessage());
            return [];
        }
    }
    /**
     * Contar el total de infracciones (solo activas)
     * @param string $search
     * @return int
     */
    public function countAll($search = '') {
        try {
            $query = "SELECT COUNT(*) AS total FROM " . $this->table . " i
                    LEFT JOIN awardees a ON i.awardee_id = a.id
                    LEFT JOIN market_stalls s ON i.stall_id = s.id
                    LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                    WHERE (a.first_name LIKE :search 
                            OR s.stall_number LIKE :search 
                            OR it.infraction_type_name LIKE :search)
                    AND i.status_logical = 'active'";

            $stmt = $this->conn->prepare($query);
            $searchParam = "%$search%";

            // It's better to explicitly bind with bindValue for clarity
            $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);

            // This line will execute the query and capture any errors
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
            // Return the count
            return (int)$result['total'];
            
        } catch(PDOException $exception) {
            error_log("Error al contar infracciones: " . $exception->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener una infracción por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT i.* FROM " . $this->table . " i WHERE i.infraction_id = :id AND i.status_logical = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener infracción: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Obtener una infracción con todos sus detalles de tablas relacionadas
     * @param int $id
     * @return array|false
     */
    public function getInfractionDetails($id) {
        try {
            $query = "SELECT i.*, 
                             a.first_name as adjudicatory_name,
                             a.id_number as adjudicatory_document,
                             s.stall_number,
                             s.location_description,
                             it.infraction_type_name,
                             it.description as infraction_type_description,
                             it.base_fine
                     FROM " . $this->table . " i
                     LEFT JOIN awardees a ON i.awardee_id = a.id
                     LEFT JOIN market_stalls s ON i.stall_id = s.id
                     LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                     WHERE i.infraction_id = :id AND i.status_logical = 'active'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener detalles de la infracción: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Crear una nueva infracción
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " (
                         awardee_id,
                         stall_id,
                         infraction_type_id,
                         infraction_description,
                         infraction_status,
                         inspector_observations,
                         infraction_datetime,
                         proof,
                         status_logical
                     ) VALUES (
                         :awardee_id,
                         :stall_id,
                         :infraction_type_id,
                         :infraction_description,
                         :infraction_status,
                         :inspector_observations,
                         :infraction_datetime,
                         :proof,
                         'active'
                     )";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitizar y enlazar parámetros
            $this->awardee_id = htmlspecialchars(strip_tags($data['awardee_id']));
            $this->stall_id = htmlspecialchars(strip_tags($data['stall_id']));
            $this->infraction_type_id = htmlspecialchars(strip_tags($data['infraction_type_id']));
            $this->infraction_description = htmlspecialchars(strip_tags($data['infraction_description']));
            $this->infraction_status = htmlspecialchars(strip_tags($data['infraction_status']));
            $this->inspector_observations = htmlspecialchars(strip_tags($data['inspector_observations']));
            $this->infraction_datetime = date('Y-m-d H:i:s');
            $this->proof = $data['proof']; // No sanitizar, ya que es el nombre de archivo

            $stmt->bindParam(':awardee_id', $this->awardee_id);
            $stmt->bindParam(':stall_id', $this->stall_id);
            $stmt->bindParam(':infraction_type_id', $this->infraction_type_id);
            $stmt->bindParam(':infraction_description', $this->infraction_description);
            $stmt->bindParam(':infraction_status', $this->infraction_status);
            $stmt->bindParam(':inspector_observations', $this->inspector_observations);
            $stmt->bindParam(':infraction_datetime', $this->infraction_datetime);
            $stmt->bindParam(':proof', $this->proof);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Infracción creada exitosamente.',
                    'id' => $this->conn->lastInsertId()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al crear la infracción.'
            ];

        } catch(PDOException $exception) {
            error_log("Error al crear infracción: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Actualizar una infracción existente
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET awardee_id = :awardee_id,
                         stall_id = :stall_id,
                         infraction_type_id = :infraction_type_id,
                         infraction_description = :infraction_description,
                         infraction_status = :infraction_status,
                         inspector_observations = :inspector_observations,
                         proof = :proof
                     WHERE infraction_id = :id
                     AND status_logical = 'active'";
            
            $stmt = $this->conn->prepare($query);

            // Sanitizar y enlazar parámetros
            $this->awardee_id = htmlspecialchars(strip_tags($data['awardee_id']));
            $this->stall_id = htmlspecialchars(strip_tags($data['stall_id']));
            $this->infraction_type_id = htmlspecialchars(strip_tags($data['infraction_type_id']));
            $this->infraction_description = htmlspecialchars(strip_tags($data['infraction_description']));
            $this->infraction_status = htmlspecialchars(strip_tags($data['infraction_status']));
            $this->inspector_observations = htmlspecialchars(strip_tags($data['inspector_observations']));
            $this->proof = $data['proof'];
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':awardee_id', $this->awardee_id);
            $stmt->bindParam(':stall_id', $this->stall_id);
            $stmt->bindParam(':infraction_type_id', $this->infraction_type_id);
            $stmt->bindParam(':infraction_description', $this->infraction_description);
            $stmt->bindParam(':infraction_status', $this->infraction_status);
            $stmt->bindParam(':inspector_observations', $this->inspector_observations);
            $stmt->bindParam(':proof', $this->proof);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Infracción actualizada exitosamente.'
                ];               
            } 
            
            return [
                'success' => false,
                'message' => 'Error al actualizar la infracción.'
            ];

        } catch(PDOException $exception) {
            error_log("Error al actualizar infracción: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Eliminar lógicamente una infracción
     * @param int $id
     * @return array
     */
    public function logicalDelete($id) {
        try {
            // Verificar si la infracción existe y no está ya eliminada
            $checkQuery = "SELECT infraction_id FROM " . $this->table . " WHERE infraction_id = :id AND status_logical = 'active'";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'La infracción no existe o ya ha sido eliminada.'
                ];
            }

            $query = "UPDATE " . $this->table . " SET status_logical = 'deleted' WHERE infraction_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Infracción eliminada lógicamente de forma exitosa.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al eliminar lógicamente la infracción.'
            ];
        } catch(PDOException $exception) {
            error_log("Error al eliminar lógicamente infracción: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }
}