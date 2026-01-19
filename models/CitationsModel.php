<?php

// Requerir el archivo de configuración de la base de datos
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/NotificationModel.php';

class CitationsModel {
    private $db;
    private $conn;
    private $table = 'citations';

    public function __construct() {
        $this->conn = new Database();
        $this->db = $this->conn->getConnection();
    }

       // Nuevo método para actualizar el estado y la fecha de la citación
// Nuevo método para actualizar el estado y la fecha de la citación (Mantenido)
    public function updateStatusAndDate($id, $status, $new_datetime = null) {
        try {
            $sql = "UPDATE " . $this->table . " SET citation_status = :status";
            
            if ($new_datetime !== null) {
                $sql .= ", citation_datetime = :new_datetime";
            }
            
            $sql .= " WHERE citation_id = :id";
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            if ($new_datetime !== null) {
                $stmt->bindParam(':new_datetime', $new_datetime, PDO::PARAM_STR);
            }
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Error al actualizar el estado de la citación: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtiene citaciones. Si $limit es 0, trae todos los registros (para DataTables).
     * @param int $page La página actual (ignorado si $limit=0).
     * @param int $limit El número de registros por página (0 para todos).
     * @param string $search Un término de búsqueda opcional.
     * @return array Un arreglo de citaciones.
     */
    public function getAll($page, $limit, $search) {
        $sql = "
            SELECT 
                c.*, 
                c.infraction_id, 
                c.mediator_user_id,
                CONCAT(s.first_name, ' ', s.last_name) as mediator_full_name,
                ms.stall_number,
                CONCAT(a.first_name, ' ', a.last_name) as awardee_full_name,
                a.id_number as awardee_id_number,
                i.infraction_description
            FROM 
                " . $this->table . " c
            LEFT JOIN users u ON c.mediator_user_id = u.id
            LEFT JOIN staff s ON u.staff_id = s.id
            LEFT JOIN infractions i ON c.infraction_id = i.infraction_id
            LEFT JOIN market_stalls ms ON i.stall_id = ms.id
            LEFT JOIN awardees a ON i.awardee_id = a.id
        ";
        $params = [];
        $where = [];

        if (!empty($search)) {
            // Search by location, status, mediator, stall, awardee name, or ID number
            $where[] = "(c.location LIKE ? OR c.citation_status LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR ms.stall_number LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ? OR a.id_number LIKE ?)";
            for ($i = 0; $i < 8; $i++) {
                $params[] = "%$search%";
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY c.citation_datetime DESC";

        // *** CAMBIO CLAVE: Omitir LIMIT/OFFSET si el límite es 0 ***
        if ((int)$limit > 0) {
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit; // Se agrega el límite como parámetro
            $params[] = $offset; // Se agrega el offset como parámetro
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
             error_log("Error en CitationsModel::getAll: " . $e->getMessage());
             return []; // Devuelve un array vacío en caso de error
        }
    }

    /**
     * Cuenta el número total de citaciones. (Mantenido)
     */
    public function countAll($search) {
        $sql = "SELECT COUNT(*) FROM citations";
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE location LIKE ? OR citation_status LIKE ?";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // ... (El resto de los métodos getById, create, update, delete, getInfractionsList, getMediatorsList se mantienen iguales)
    
    public function getById($id) {
        $sql = "
            SELECT 
                c.*, 
                i.infraction_id,
                i.infraction_description,
                ms.id as stall_id,
                ms.stall_number,
                ms.location_description as stall_location,
                a.id as awardee_id,
                CONCAT(a.first_name, ' ', a.last_name) as awardee_full_name
            FROM 
                citations c
            LEFT JOIN infractions i ON c.infraction_id = i.infraction_id
            LEFT JOIN market_stalls ms ON i.stall_id = ms.id
            LEFT JOIN awardees a ON i.awardee_id = a.id
            WHERE c.citation_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

     /* Crea una nueva citación en la base de datos.
     * @param array $data Los datos de la citación a crear.
     * @return array El resultado de la operación.
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO citations (infraction_id, citation_datetime, location, mediator_user_id, citation_status) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['infraction_id'],
                $data['citation_datetime'],
                $data['location'],
                $data['mediator_user_id'],
                $data['citation_status']
            ]);
            return ['success' => true, 'message' => 'Citación creada exitosamente.'];
        } catch (PDOException $e) {
            error_log("Error al crear citación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la citación.'];
        }
    }

    /**
     * Actualiza una citación existente.
     * @param int $id El ID de la citación a actualizar.
     * @param array $data Los nuevos datos.
     * @return array El resultado de la operación.
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE citations SET 
                    infraction_id = ?, 
                    citation_datetime = ?, 
                    location = ?, 
                    mediator_user_id = ?, 
                    citation_status = ? 
                    WHERE citation_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['infraction_id'],
                $data['citation_datetime'],
                $data['location'],
                $data['mediator_user_id'],
                $data['citation_status'],
                $id
            ]);
            return ['success' => true, 'message' => 'Citación actualizada exitosamente.'];
        } catch (PDOException $e) {
            error_log("Error al actualizar citación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar la citación.'];
        }
    }

    /**
     * Elimina una citación.
     * @param int $id El ID de la citación a eliminar.
     * @return array El resultado de la operación.
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM citations WHERE citation_id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Citación eliminada exitosamente.'];
        } catch (PDOException $e) {
            error_log("Error al eliminar citación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar la citación.'];
        }
    }

    /**
     * Obtiene una lista de todas las infracciones.
     * @return array
     */
    public function getInfractionsList() {
        // Asumiendo que existe una tabla 'infractions'
        $stmt = $this->db->query("SELECT infraction_id, infraction_description FROM infractions ORDER BY infraction_description");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una lista de los usuarios con el rol de 'fiscalización'.
     * @return array
     */
    public function getMediatorsList() {
        // Obtenemos los usuarios que pertenecen al departamento de Fiscalización (ID 3)
        // Similar a UsersFiscModel::getUsersByFiscalizationDepartment
        $fiscalization_department_id = 3; 
        
        $sql = "
            SELECT
                u.id AS inspector_id, -- Usamos 'inspector_id' como alias para mantener compatibilidad con el controlador/vista si lo usan
                CONCAT(s.first_name, ' ', s.last_name) AS full_name
            FROM
                users u
            INNER JOIN 
                staff s ON u.staff_id = s.id
            WHERE
                s.department_id = :department_id
            ORDER BY 
                s.last_name, s.first_name
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':department_id', $fiscalization_department_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en CitationsModel::getMediatorsList: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica y actualiza automáticamente los estados de las citaciones según reglas de tiempo.
     */
    public function checkAndAutoUpdateStatuses() {
        $notificationModel = new NotificationModel();
        
        // 1. Scheduled -> In Process (Si fecha cita <= Ahora y está Programada)
        $sql = "SELECT citation_id, mediator_user_id FROM citations WHERE citation_status = 'Scheduled' AND citation_datetime <= NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $toProcess = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($toProcess as $cit) {
            $this->updateStatusAndDate($cit['citation_id'], 'In Process');
             // Notificar al mediador
            $notificationModel->insertNotification(
                null, // System
                $cit['mediator_user_id'],
                'citation_status_update',
                'Citación En Proceso',
                "La citación #{$cit['citation_id']} ha pasado a estado En Proceso."
            );
        }

        // 2. In Process -> Canceled (Si Ahora > citation_datetime + 3 days)
        $sql = "SELECT citation_id, mediator_user_id FROM citations WHERE citation_status = 'In Process' AND NOW() > DATE_ADD(citation_datetime, INTERVAL 3 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $toCancel = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($toCancel as $cit) {
            $this->updateStatusAndDate($cit['citation_id'], 'Canceled');
             // Notificar
            $notificationModel->insertNotification(
                null,
                $cit['mediator_user_id'],
                'citation_status_update',
                'Citación Cancelada',
                "La citación #{$cit['citation_id']} ha sido Cancelada automáticamente por inactividad tras 3 días."
            );
        }
    }
}