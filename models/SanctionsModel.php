<?php
require_once __DIR__ . '/Model.php';

/**
 * Modelo para la gestión de Sanciones
 * * Interactúa directamente con la base de datos para
 * realizar operaciones CRUD en la tabla `sanctions`.
 */
class SanctionsModel extends Model {

    protected $table = "sanctions";
    private $status_logical_column = "status_logical";

    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene todos los registros de sanciones.
     * @return array
     */
public function index($filters = []) {
        try {
            $query = "SELECT s.*, 
                             i.infraction_description AS infraction_description,
                             it.infraction_type_name, 
                             st.severity_name AS severity_name,
                             a.id_number,
                             a.first_name,
                             a.last_name,
                             ms.stall_number
                      FROM " . $this->table . " s
                      JOIN infractions i ON s.infraction_id = i.infraction_id
                      JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id
                      JOIN awardees a ON i.awardee_id = a.id
                      LEFT JOIN market_stalls ms ON i.stall_id = ms.id
                      WHERE 1=1 "; // Cláusula base para empezar a añadir filtros
            
            $params = [];

            // --- APLICACIÓN DE FILTROS ---

            // 1. Filtrar por ID de Sanción
            if (!empty($filters['sanction_id'])) {
                $query .= " AND s.sanction_id = :sanction_id";
                $params[':sanction_id'] = $filters['sanction_id'];
            }

            // 2. Filtrar por Estado
            if (!empty($filters['sanction_status'])) {
                $query .= " AND s.sanction_status = :sanction_status";
                $params[':sanction_status'] = $filters['sanction_status'];
            }

            // 3. Filtrar por Fecha Desde (imposition_date)
            if (!empty($filters['date_from'])) {
                $query .= " AND s.imposition_date >= :date_from";
                // Asegurar que la fecha sea YYYY-MM-DD para la BD
                $params[':date_from'] = $filters['date_from']; 
            }

            // 4. Filtrar por Fecha Hasta (imposition_date)
            if (!empty($filters['date_to'])) {
                $query .= " AND s.imposition_date <= :date_to";
                $params[':date_to'] = $filters['date_to']; 
            }

            // 5. Filtrar por Cédula
            if (!empty($filters['awardee_cedula'])) {
                $query .= " AND a.id_number LIKE :awardee_cedula";
                $params[':awardee_cedula'] = '%' . $filters['awardee_cedula'] . '%';
            }

            // 6. Filtrar por Nombre
            if (!empty($filters['awardee_name'])) {
                $query .= " AND (a.first_name LIKE :awardee_name OR a.last_name LIKE :awardee_name OR CONCAT(a.first_name, ' ', a.last_name) LIKE :awardee_name)";
                $params[':awardee_name'] = '%' . $filters['awardee_name'] . '%';
            }

            // --- ORDENAMIENTO y EJECUCIÓN ---
            
            $query .= " ORDER BY s.imposition_date DESC";

            // NOTA: Se eliminaron LIMIT y OFFSET de esta versión para que DataTables pueda 
            // manejar toda la colección de datos filtrada en el cliente, si no usas Server-Side Processing.
            
            $stmt = $this->conn->prepare($query);
            
            // Enlazar parámetros
            foreach ($params as $key => &$val) {
                // Usamos bindParam para que pueda manejar cualquier tipo de dato
                $stmt->bindParam($key, $val); 
            }

            $stmt->execute();
            $sanctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Retornar un array estandarizado en caso de éxito
            return [
                'success' => true,
                'sanctions' => $sanctions
            ];

        } catch (PDOException $exception) {
            error_log("Error al obtener sanciones con filtros: " . $exception->getMessage());
            // Retornar un array estandarizado en caso de error
            return [
                'success' => false,
                'message' => 'Error de la base de datos al filtrar sanciones: ' . $exception->getMessage(),
                'sanctions' => []
            ];
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
        $query = "SELECT s.*, i.infraction_description as infraction_description, st.severity_name as severity_name
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
        $query = "SELECT s.*, i.infraction_description as infraction_name, st.severity_name as severity_name
                  FROM " . $this->table . " s
                  JOIN infractions i ON s.infraction_id = i.infraction_id
                  JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id";
        
        $params = [];
        if (!empty($search)) {
            $query .= " WHERE i.infraction_description LIKE :search OR st.severity_name LIKE :search";
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
            $query .= " WHERE i.infraction_description LIKE :search OR st.severity_name LIKE :search";
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
        $query = "SELECT sanction_type_id, severity_name FROM sanction_types ORDER BY severity_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== BILLING MODULE METHODS ==========

    /**
     * Get pending (unpaid) sanctions for a specific awardee.
     * @param int $awardeeId
     * @return array
     */
    public function getPendingSanctionsByAwardee($awardeeId) {
        try {
            $query = "SELECT s.*, 
                             i.infraction_id,
                             i.infraction_datetime,
                             i.infraction_description,
                             i.stall_id,
                             it.infraction_type_name,
                             st.severity_name,
                             st.description as sanction_type_description,
                             ms.stall_number
                      FROM " . $this->table . " s
                      JOIN infractions i ON s.infraction_id = i.infraction_id
                      JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id
                      LEFT JOIN market_stalls ms ON i.stall_id = ms.id
                      WHERE i.awardee_id = :awardee_id
                      AND s.sanction_status IN ('Imposed', 'Pending')
                      AND i.status_logical = 'active'
                      ORDER BY s.imposition_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':awardee_id', $awardeeId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log("Error getting pending sanctions: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Get paid sanctions for a specific awardee.
     * @param int $awardeeId
     * @return array
     */
    public function getPaidSanctionsByAwardee($awardeeId) {
        try {
            $query = "SELECT s.*, 
                             i.infraction_id,
                             i.infraction_datetime,
                             i.infraction_description,
                             it.infraction_type_name,
                             st.severity_name,
                             fp.payment_date,
                             fp.amount_paid,
                             fp.transaction_reference
                      FROM " . $this->table . " s
                      JOIN infractions i ON s.infraction_id = i.infraction_id
                      JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id
                      LEFT JOIN fine_payments fp ON s.sanction_id = fp.sanction_id
                      WHERE i.awardee_id = :awardee_id
                      AND s.sanction_status = 'Paid'
                      ORDER BY fp.payment_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':awardee_id', $awardeeId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log("Error getting paid sanctions: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Update sanction payment status.
     * @param int $sanctionId
     * @param string $status ('Paid', 'Imposed', 'Pending')
     * @return bool
     */
    public function updatePaymentStatus($sanctionId, $status) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET sanction_status = :status 
                      WHERE sanction_id = :sanction_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':sanction_id', $sanctionId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $exception) {
            error_log("Error updating sanction payment status: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Get sanction with full details (infraction, awardee, type).
     * @param int $sanctionId
     * @return array|null
     */
    public function getSanctionWithDetails($sanctionId) {
        try {
            $query = "SELECT s.*, 
                             i.infraction_id,
                             i.infraction_datetime,
                             i.infraction_description,
                             i.stall_id,
                             i.awardee_id,
                             it.infraction_type_name,
                             it.violated_article,
                             st.severity_name,
                             st.description as sanction_type_description,
                             a.first_name,
                             a.last_name,
                             a.id_number,
                             a.phone,
                             a.email,
                             ms.stall_number,
                             ms.location_description
                      FROM " . $this->table . " s
                      JOIN infractions i ON s.infraction_id = i.infraction_id
                      JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id
                      JOIN awardees a ON i.awardee_id = a.id
                      LEFT JOIN market_stalls ms ON i.stall_id = ms.id
                      WHERE s.sanction_id = :sanction_id
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':sanction_id', $sanctionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log("Error getting sanction details: " . $exception->getMessage());
            return null;
        }
    }

    /**
     * Get all delinquent sanctions (overdue and unpaid).
     * @param array $filters Optional filters (sector_id, zone_id, days_overdue)
     * @return array
     */
    public function getDelinquentSanctions($filters = []) {
        try {
            $query = "SELECT s.*, 
                             i.infraction_id,
                             i.infraction_datetime,
                             i.infraction_description,
                             i.awardee_id,
                             it.infraction_type_name,
                             st.severity_name,
                             a.first_name,
                             a.last_name,
                             a.id_number,
                             ms.stall_number,
                             sec.name as sector_name,
                             z.name as zone_name,
                             DATEDIFF(CURDATE(), s.imposition_date) as days_overdue
                      FROM " . $this->table . " s
                      JOIN infractions i ON s.infraction_id = i.infraction_id
                      JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      JOIN sanction_types st ON s.sanction_type_id = st.sanction_type_id
                      JOIN awardees a ON i.awardee_id = a.id
                      LEFT JOIN market_stalls ms ON i.stall_id = ms.id
                      LEFT JOIN sectors sec ON ms.sector_id = sec.id
                      LEFT JOIN zones z ON sec.zone_id = z.id
                      WHERE s.sanction_status IN ('Imposed', 'Pending')
                      AND i.status_logical = 'active'
                      HAVING days_overdue > 0";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['sector_id'])) {
                $query .= " AND sec.id = :sector_id";
                $params[':sector_id'] = $filters['sector_id'];
            }
            
            if (!empty($filters['zone_id'])) {
                $query .= " AND z.id = :zone_id";
                $params[':zone_id'] = $filters['zone_id'];
            }
            
            if (!empty($filters['min_days_overdue'])) {
                $query .= " AND days_overdue >= :min_days";
                $params[':min_days'] = $filters['min_days_overdue'];
            }
            
            $query .= " ORDER BY days_overdue DESC, s.fine_amount DESC";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log("Error getting delinquent sanctions: " . $exception->getMessage());
            return [];
        }
    }

    public function getAwardeesForFilter() {
        $query = "SELECT DISTINCT a.id, a.first_name, a.last_name, a.id_number 
                  FROM awardees a 
                  JOIN infractions i ON a.id = i.awardee_id 
                  JOIN sanctions s ON i.infraction_id = s.infraction_id 
                  ORDER BY a.first_name, a.last_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cancela todas las sanciones relacionadas con una infracción.
     * @param int $infractionId
     * @return bool
     */
    public function cancelSanctionByInfraction($infractionId) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET sanction_status = 'Cancelled' 
                      WHERE infraction_id = :infraction_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':infraction_id', $infractionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return true;
        } catch(PDOException $exception) {
            error_log("Error al cancelar sanción por infracción: " . $exception->getMessage());
            return false;
        }
    }
}
