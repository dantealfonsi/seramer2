<?php

require_once __DIR__ . '/../config/Database.php';

class InfractionsModel {
    private $conn;
    private $table = 'infractions';
    
    // Propiedades de la infracción
    public $id_infraction;
    public $id_adjudicatory;
    public $id_stall;
    public $infraction_datetime;
    public $id_infraction_type;
    public $infraction_description;
    public $infraction_status;
    public $inspector_observations;
    public $status_logical; // Propiedad para la eliminación lógica

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
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
                             a.full_name_or_company_name as adjudicatory_name,
                             a.document_number as adjudicatory_document,
                             s.stall_code,
                             it.infraction_type_name
                      FROM " . $this->table . " i
                      LEFT JOIN adjudicatories a ON i.id_adjudicatory = a.id_adjudicatory
                      LEFT JOIN market_stalls s ON i.id_stall = s.id_stall
                      LEFT JOIN infraction_types it ON i.id_infraction_type = it.id_infraction_type
                      WHERE (a.full_name_or_company_name LIKE :search 
                             OR s.stall_code LIKE :search 
                             OR it.infraction_type_name LIKE :search)
                      AND i.status_logical = 'active'
                      ORDER BY i.infraction_datetime DESC
                      LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
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
            $searchParam = "%$search%";
            
            $query = "SELECT COUNT(i.id_infraction) as total 
                      FROM " . $this->table . " i
                      LEFT JOIN adjudicatories a ON i.id_adjudicatory = a.id_adjudicatory
                      LEFT JOIN market_stalls s ON i.id_stall = s.id_stall
                      LEFT JOIN infraction_types it ON i.id_infraction_type = it.id_infraction_type
                      WHERE (a.full_name_or_company_name LIKE :search 
                             OR s.stall_code LIKE :search 
                             OR it.infraction_type_name LIKE :search)
                      AND i.status_logical = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
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
            $query = "SELECT i.* FROM " . $this->table . " i WHERE i.id_infraction = :id AND i.status_logical = 'active'";
            
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
                             a.full_name_or_company_name as adjudicatory_name,
                             a.document_number as adjudicatory_document,
                             s.stall_code,
                             s.stall_type,
                             it.infraction_type_name,
                             it.description as infraction_type_description,
                             it.base_fine
                      FROM " . $this->table . " i
                      LEFT JOIN adjudicatories a ON i.id_adjudicatory = a.id_adjudicatory
                      LEFT JOIN market_stalls s ON i.id_stall = s.id_stall
                      LEFT JOIN infraction_types it ON i.id_infraction_type = it.id_infraction_type
                      WHERE i.id_infraction = :id AND i.status_logical = 'active'";

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
                        id_adjudicatory,
                        id_stall,
                        id_infraction_type,
                        infraction_description,
                        infraction_status,
                        inspector_observations,
                        infraction_datetime,
                        status_logical
                    ) VALUES (
                        :id_adjudicatory,
                        :id_stall,
                        :id_infraction_type,
                        :infraction_description,
                        :infraction_status,
                        :inspector_observations,
                        :infraction_datetime,
                        'active'
                    )";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitizar y enlazar parámetros
            $this->id_adjudicatory = htmlspecialchars(strip_tags($data['id_adjudicatory']));
            $this->id_stall = htmlspecialchars(strip_tags($data['id_stall']));
            $this->id_infraction_type = htmlspecialchars(strip_tags($data['id_infraction_type']));
            $this->infraction_description = htmlspecialchars(strip_tags($data['infraction_description']));
            $this->infraction_status = htmlspecialchars(strip_tags($data['infraction_status']));
            $this->inspector_observations = htmlspecialchars(strip_tags($data['inspector_observations']));
            $this->infraction_datetime = date('Y-m-d H:i:s'); // Usar la fecha y hora actual del servidor
            
            $stmt->bindParam(':id_adjudicatory', $this->id_adjudicatory);
            $stmt->bindParam(':id_stall', $this->id_stall);
            $stmt->bindParam(':id_infraction_type', $this->id_infraction_type);
            $stmt->bindParam(':infraction_description', $this->infraction_description);
            $stmt->bindParam(':infraction_status', $this->infraction_status);
            $stmt->bindParam(':inspector_observations', $this->inspector_observations);
            $stmt->bindParam(':infraction_datetime', $this->infraction_datetime);

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
                      SET id_adjudicatory = :id_adjudicatory,
                          id_stall = :id_stall,
                          id_infraction_type = :id_infraction_type,
                          infraction_description = :infraction_description,
                          infraction_status = :infraction_status,
                          inspector_observations = :inspector_observations
                      WHERE id_infraction = :id
                      AND status_logical = 'active'";
            
            $stmt = $this->conn->prepare($query);

            // Sanitizar y enlazar parámetros
            $this->id_adjudicatory = htmlspecialchars(strip_tags($data['id_adjudicatory']));
            $this->id_stall = htmlspecialchars(strip_tags($data['id_stall']));
            $this->id_infraction_type = htmlspecialchars(strip_tags($data['id_infraction_type']));
            $this->infraction_description = htmlspecialchars(strip_tags($data['infraction_description']));
            $this->infraction_status = htmlspecialchars(strip_tags($data['infraction_status']));
            $this->inspector_observations = htmlspecialchars(strip_tags($data['inspector_observations']));
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':id_adjudicatory', $this->id_adjudicatory);
            $stmt->bindParam(':id_stall', $this->id_stall);
            $stmt->bindParam(':id_infraction_type', $this->id_infraction_type);
            $stmt->bindParam(':infraction_description', $this->infraction_description);
            $stmt->bindParam(':infraction_status', $this->infraction_status);
            $stmt->bindParam(':inspector_observations', $this->inspector_observations);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    return [
                        'success' => true,
                        'message' => 'Infracción actualizada exitosamente.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'No se encontró la infracción o no se realizaron cambios.'
                    ];
                }
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
            $checkQuery = "SELECT id_infraction FROM " . $this->table . " WHERE id_infraction = :id AND status_logical = 'active'";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'La infracción no existe o ya ha sido eliminada.'
                ];
            }

            $query = "UPDATE " . $this->table . " SET status_logical = 'deleted' WHERE id_infraction = :id";
            
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
?>