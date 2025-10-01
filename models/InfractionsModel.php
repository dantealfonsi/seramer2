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

    public function getInfractionTypeById($id)
    {
        // Asumiendo que tienes una conexión PDO en $this->db
        $stmt = $this->conn->prepare("SELECT * FROM infraction_types WHERE infraction_type_id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Helper para construir dinámicamente las cláusulas WHERE y los parámetros bind.
     * Esto asegura que getAll() y countAll() usen exactamente los mismos filtros.
     * @param array $filters Array de filtros recibidos del controlador (incluye 'search', 'infraction_date', etc.)
     * @return array ['whereSQL' => 'WHERE ...', 'bindParams' => [':param' => value]]
     */
    private function buildFilterConditions(array $filters): array {
        $whereClauses = ["i.status_logical = 'active'"];
        $bindParams = [];

        // 1. Filtrado de búsqueda general (por nombre, puesto, tipo)
        if (!empty($filters['search'])) {
            $searchParam = "%{$filters['search']}%";
            // Utilizamos un solo placeholder (:search) en la query, pero se bindeará 3 veces.
            // Para simplificar el bind, vamos a usar placeholders únicos:
            $whereClauses[] = "(a.first_name LIKE :search_name 
                                OR s.stall_number LIKE :search_stall 
                                OR it.infraction_type_name LIKE :search_type)";
            
            $bindParams[':search_name'] = $searchParam;
            $bindParams[':search_stall'] = $searchParam;
            $bindParams[':search_type'] = $searchParam;
        }

        // 2. Filtrado por fecha de infracción (infraction_date)
        if (!empty($filters['infraction_date'])) {
            $whereClauses[] = "DATE(i.infraction_datetime) = :infraction_date";
            $bindParams[':infraction_date'] = $filters['infraction_date'];
        }

        // 3. Filtrado por estado (infraction_status)
        if (!empty($filters['infraction_status'])) {
            $whereClauses[] = "i.infraction_status = :infraction_status";
            $bindParams[':infraction_status'] = $filters['infraction_status'];
        }

        // 4. Filtrado por ID de tipo de infracción (infraction_type_id)
        if (!empty($filters['infraction_type_id'])) {
            $whereClauses[] = "i.infraction_type_id = :infraction_type_id";
            $bindParams[':infraction_type_id'] = (int)$filters['infraction_type_id'];
        }

        // 5. Filtrado por ID de puesto (stall_id)
        if (!empty($filters['stall_id'])) {
            $whereClauses[] = "i.stall_id = :stall_id";
            $bindParams[':stall_id'] = (int)$filters['stall_id'];
        }

        // 6. Filtrado por ID de adjudicatario (awardee_id)
        if (!empty($filters['awardee_id'])) {
            $whereClauses[] = "i.awardee_id = :awardee_id";
            $bindParams[':awardee_id'] = (int)$filters['awardee_id'];
        }
        
        // Construir la cláusula WHERE final
        $whereSQL = "WHERE " . implode(" AND ", $whereClauses);

        return ['whereSQL' => $whereSQL, 'bindParams' => $bindParams];
    }

    public function getAll($page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Usamos el helper para obtener los filtros y parámetros
            $conditions = $this->buildFilterConditions($filters);
            $bindParams = $conditions['bindParams'];
            $whereSQL = $conditions['whereSQL'];            

            $query = "SELECT i.*, 
                             a.first_name as adjudicatory_name,
                             a.id_number as adjudicatory_document,
                             s.stall_number,
                             it.infraction_type_name
                      FROM " . $this->table . " i
                      LEFT JOIN awardees a ON i.awardee_id = a.id
                      LEFT JOIN market_stalls s ON i.stall_id = s.id
                      LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      {$whereSQL}
                      ORDER BY i.infraction_datetime DESC
                      LIMIT :limit OFFSET :offset";

            
            $stmt = $this->conn->prepare($query); 
           
            // Bindear los parámetros de los filtros
            foreach ($bindParams as $key => $value) {
                // Determine el tipo de parámetro, asumiendo que los IDs son INT y el resto STR
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
 
            // Bindear los parámetros de paginación
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
     * Obtiene el total de registros de infracciones, aplicando los mismos filtros.
     */
    public function countAll($filters = []) {
        try {
            // Usamos el helper para obtener los filtros y parámetros
            $conditions = $this->buildFilterConditions($filters);
            $bindParams = $conditions['bindParams'];
            $whereSQL = $conditions['whereSQL'];

            // Consulta de conteo, utiliza los mismos JOINS y WHERE
            $query = "SELECT COUNT(*) as total_records
                      FROM " . $this->table . " i
                      LEFT JOIN awardees a ON i.awardee_id = a.id
                      LEFT JOIN market_stalls s ON i.stall_id = s.id
                      LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      {$whereSQL}";
            
            $stmt = $this->conn->prepare($query); 
            
            // Bindear los parámetros de los filtros
            foreach ($bindParams as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['total_records'];

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
                             it.base_fine,
                             sc.sanction_id,
                             sc.fine_amount,
                             sc.sanction_status,
                             sc.fine_currency
                     FROM " . $this->table . " i
                     LEFT JOIN awardees a ON i.awardee_id = a.id
                     LEFT JOIN market_stalls s ON i.stall_id = s.id
                     LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id 
                     LEFT JOIN sanctions sc ON i.infraction_id = sc.infraction_id
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