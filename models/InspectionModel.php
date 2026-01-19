<?php

require_once __DIR__ . '/../config/Database.php';

class InspectionModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // =======================================================================
    // NUEVO MÉTODO PARA FILTROS AVANZADOS (USADO POR DATATABLES/VISTA)
    // =======================================================================

    /**
     * Get a list of inspection reports based on advanced filters.
     * This method retrieves the complete dataset filtered by server-side criteria.
     * @param array $filters Associative array of filters (e.g., 'search', 'inspection_status').
     * @return array
     */
    public function getFilteredReports(array $filters) {
        $sql = "SELECT 
                    ir.report_id, 
                    s.stall_number, 
                    si.inspection_type AS inspection_type_name, 
                    si.scheduled_date AS scheduled_datetime, 
                    mi.full_name AS inspector_name, 
                    si.inspection_status,
                    ir.scheduled_inspection_id,
                    ir.stall_id
                FROM 
                    inspection_reports ir
                LEFT JOIN 
                    scheduled_inspections si ON ir.scheduled_inspection_id = si.inspection_id
                LEFT JOIN 
                    inspectors mi ON ir.main_inspector_id = mi.inspector_id
                LEFT JOIN 
                    market_stalls s ON ir.stall_id = s.id
                LEFT JOIN 
                    awardees a ON ir.awardee_id = a.id";

        $whereClauses = [];
        $params = [];

        // 1. FILTRO DE BÚSQUEDA GENERAL (Search Input)
        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $whereClauses[] = " (mi.full_name LIKE ? OR s.stall_number LIKE ? OR si.inspection_type LIKE ? OR si.inspection_status LIKE ?) ";
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        // 2. FILTRO POR ESTADO (inspection_status)
        if (!empty($filters['inspection_status'])) {
            $whereClauses[] = " si.inspection_status = ? ";
            $params[] = $filters['inspection_status'];
        }

        // 3. FILTRO POR TIPO DE INSPECCIÓN (inspection_type_id)
        // Nota: Asumo que 'inspection_type_id' es el nombre que usaste en la vista para el filtro, 
        // pero la columna en 'scheduled_inspections' se llama 'inspection_type' y es un string. 
        // Si es un ID, ajusta el LEFT JOIN y la columna. 
        // Si es un string (como 'Inicial', 'Rutina'), la lógica es:
        if (!empty($filters['inspection_type_id'])) {
             // Si el tipo es un ID: 
             // $whereClauses[] = " si.inspection_type_id = ? "; 
             // $params[] = $filters['inspection_type_id'];
             
             // Asumiendo que $filters['inspection_type_id'] contiene el nombre del tipo (string):
             $whereClauses[] = " si.inspection_type = ? "; 
             $params[] = $filters['inspection_type_id']; 
        }

        // 4. FILTRO POR FECHA (inspection_date)
        if (!empty($filters['inspection_date'])) {
            // Utilizamos DATE() para comparar solo la parte de la fecha
            $whereClauses[] = " DATE(si.scheduled_date) = ? ";
            $params[] = $filters['inspection_date'];
        }
        
        // 5. FILTRO POR PUESTO (stall_id)
        if (!empty($filters['stall_id'])) {
            $whereClauses[] = " ir.stall_id = ? ";
            $params[] = $filters['stall_id'];
        }

        // 6. FILTRO POR INSPECTOR (inspector_id)
        // Esto asume que el ID de inspector en el filtro es para el 'main_inspector'
        if (!empty($filters['inspector_id'])) {
            $whereClauses[] = " ir.main_inspector_id = ? ";
            $params[] = $filters['inspector_id'];
        }
        
        // APLICAR LAS CLÁUSULAS WHERE
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // ORDENAMIENTO (Importante para DataTables inicial)
        $sql .= " ORDER BY ir.report_id DESC";

        // Devolvemos todos los resultados filtrados
        return $this->db->fetchAll($sql, $params);
    }
    
    // =======================================================================
    // MÉTODOS EXISTENTES (Modificados/Optimizados)
    // =======================================================================

    /**
     * Get all inspection reports with pagination and search.
     * **Nota:** Este método ya no se utiliza en el Controlador para DataTables. 
     * Se mantiene por si se requiere en otro contexto.
     */
    public function getAll($page, $limit, $search) {
        // ... Lógica original ...
        $offset = ($page - 1) * $limit;
        $sql = "SELECT ir.*, si.scheduled_date, mi.full_name AS main_inspector_name, ai.full_name AS assistant_inspector_name, s.stall_number AS stall_name, a.first_name AS awardee_name
                 FROM inspection_reports ir
                 LEFT JOIN scheduled_inspections si ON ir.scheduled_inspection_id = si.inspection_id
                 LEFT JOIN inspectors mi ON ir.main_inspector_id = mi.inspector_id
                 LEFT JOIN inspectors ai ON ir.assistant_inspector_id = ai.inspector_id
                 LEFT JOIN market_stalls s ON ir.stall_id = s.id
                 LEFT JOIN awardees a ON ir.awardee_id = a.id";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE mi.full_name LIKE ? OR ai.full_name LIKE ? OR s.stall_number LIKE ? OR a.first_name LIKE ?"; // Ajusté el campo de búsqueda del adjudicatario
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        $sql .= " ORDER BY ir.creation_date DESC LIMIT " . $limit . " OFFSET " . $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Count all inspection reports with an optional search filter.
     * **Nota:** Este método ya no se utiliza en el Controlador para DataTables.
     */
    public function countAll($search) {
        $sql = "SELECT COUNT(ir.report_id)
                 FROM inspection_reports ir
                 LEFT JOIN inspectors mi ON ir.main_inspector_id = mi.inspector_id
                 LEFT JOIN inspectors ai ON ir.assistant_inspector_id = ai.inspector_id
                 LEFT JOIN market_stalls s ON ir.stall_id = s.id
                 LEFT JOIN awardees a ON ir.awardee_id = a.id";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE mi.full_name LIKE ? OR ai.full_name LIKE ? OR s.stall_number LIKE ? OR a.first_name LIKE ?"; // Ajusté el campo de búsqueda del adjudicatario
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get a single inspection report by its ID.
     */
    public function getById($id) {
        // ... (Lógica original, se mantiene) ...
        $sql = "SELECT 
                        ir.*, 
                        si.scheduled_date, 
                        si.inspection_type, 
                        si.inspection_status, 
                        si.observations,
                        mi.stall_number AS stall_code,
                        aw.first_name AS awardee_name,
                        ip.full_name AS main_inspector_name,
                        au.full_name AS assistant_inspector_name
                    FROM 
                        inspection_reports ir
                    INNER JOIN 
                        scheduled_inspections si ON ir.scheduled_inspection_id = si.inspection_id
                    INNER JOIN 
                        market_stalls mi ON ir.stall_id = mi.id  
                    INNER JOIN 
                        awardees aw ON ir.awardee_id = aw.id  
                    INNER JOIN 
                        inspectors ip ON ir.main_inspector_id = ip.inspector_id  
                    INNER JOIN 
                        inspectors au ON ir.assistant_inspector_id = au.inspector_id        
                    WHERE 
                        ir.report_id = ?";
        
        return $this->db->fetchOne($sql, [$id]);
    }
    
    // ... (create, update, delete se mantienen iguales) ...
    public function create($data) {
        // ... Lógica original ...
        $scheduled_inspection_data = [
            'scheduled_date' => $data['scheduled_date'],
            'inspection_type' => $data['inspection_type'],
            'assigned_responsible_id' => $data['assigned_responsible_id'],
            'inspection_status' => $data['inspection_status'] ?? 'Pending',
            'observations' => $data['observations'] ?? null,
        ];
        $sql_scheduled = "INSERT INTO scheduled_inspections (scheduled_date, inspection_type, assigned_responsible_id, inspection_status, observations) 
                         VALUES (?, ?, ?, ?, ?)";
        $params_scheduled = [
            $scheduled_inspection_data['scheduled_date'],
            $scheduled_inspection_data['inspection_type'],
            $scheduled_inspection_data['assigned_responsible_id'],
            $scheduled_inspection_data['inspection_status'],
            $scheduled_inspection_data['observations'],
        ];

        try {
            $this->db->executeQuery($sql_scheduled, $params_scheduled);
            $scheduled_inspection_id = $this->db->getConnection()->lastInsertId();
            
            $inspection_report_data = [
                'scheduled_inspection_id' => $scheduled_inspection_id,
                'main_inspector_id' => $data['main_inspector_id'],
                'assistant_inspector_id' => $data['assistant_inspector_id'] ?? null,
                'stall_id' => $data['stall_id'],
                'awardee_id' => $data['awardee_id'],
                'general_observations' => $data['general_observations'] ?? null,
                'inspector_signature_url' => $data['inspector_signature_url'] ?? null,
                'assistant_signature_url' => $data['assistant_signature_url'] ?? null,
            ];
            $sql_report = "INSERT INTO inspection_reports (scheduled_inspection_id, main_inspector_id, assistant_inspector_id, stall_id, awardee_id, general_observations, inspector_signature_url, assistant_signature_url) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params_report = [
                $inspection_report_data['scheduled_inspection_id'],
                $inspection_report_data['main_inspector_id'],
                $inspection_report_data['assistant_inspector_id'],
                $inspection_report_data['stall_id'],
                $inspection_report_data['awardee_id'],
                $inspection_report_data['general_observations'],
                $inspection_report_data['inspector_signature_url'],
                $inspection_report_data['assistant_signature_url'],
            ];

            $this->db->executeQuery($sql_report, $params_report);
            return ['success' => true, 'message' => 'Reporte de inspección registrados exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al registrar los datos: ' . $e->getMessage()];
        }
    }
    public function update($id, $data) {
        // ... Lógica original ...
        $sql = "UPDATE inspection_reports SET
                     scheduled_inspection_id = ?,
                     main_inspector_id = ?,
                     assistant_inspector_id = ?,
                     stall_id = ?,
                     awardee_id = ?,
                     general_observations = ?,
                     inspector_signature_url = ?,
                     assistant_signature_url = ?
                 WHERE report_id = ?";
        $params = [
            $data['scheduled_inspection_id'] ?? null,
            $data['main_inspector_id'],
            $data['assistant_inspector_id'] ?? null,
            $data['stall_id'],
            $data['awardee_id'],
            $data['general_observations'] ?? null,
            $data['inspector_signature_url'] ?? null,
            $data['assistant_signature_url'] ?? null,
            $id
        ];

        $sql_scheduled = "UPDATE scheduled_inspections SET
                     scheduled_date = ?,
                     inspection_status = ?
                 WHERE inspection_id = ?";
        $params_scheduled = [
            $data['scheduled_date'],
            $data['inspection_status'],
            $data['scheduled_inspection_id']
        ];

        try {
            $this->db->executeQuery($sql, $params);
            $this->db->executeQuery($sql_scheduled, $params_scheduled);
            return ['success' => true, 'message' => 'Reporte de inspección actualizado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar el reporte: ' . $e->getMessage()];
        }
    }
    public function delete($id) {
        $sql_report = "SELECT scheduled_inspection_id FROM inspection_reports WHERE report_id = ?";
        $scheduled_id = $this->db->fetchOne($sql_report, [$id]);
        
        $sql_delete_report = "DELETE FROM inspection_reports WHERE report_id = ?";
        $sql_delete_scheduled = "DELETE FROM scheduled_inspections WHERE inspection_id = ?";

        try {
            // Eliminar el reporte
            $this->db->executeQuery($sql_delete_report, [$id]);
            
            // Eliminar la inspección programada asociada (si existe)
            if ($scheduled_id) {
                $this->db->executeQuery($sql_delete_scheduled, [$scheduled_id]);
            }
            
            return ['success' => true, 'message' => 'Reporte de inspección y registro programado asociado eliminados permanentemente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar el reporte. Es posible que esté asociado a otros registros.'];
        }
    }

    // Métodos adicionales para obtener datos para los select en el controlador
    public function getInspectors() {
        $sql = "SELECT inspector_id, full_name FROM inspectors WHERE is_active = TRUE ORDER BY full_name";
        return $this->db->fetchAll($sql);
    }
    
    public function getStalls() {
        $sql = "SELECT id, stall_number FROM market_stalls ORDER BY stall_number";
        return $this->db->fetchAll($sql);
    }

    public function getAwardees() {
        $sql = "SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM awardees ORDER BY first_name";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtiene un mapeo de todos los puestos con su adjudicatario asignado.
     * @return array
     */
    public function getStallAwardeeMapping() {
        $sql = "SELECT 
                    s.id as stall_id, 
                    a.id as awardee_id, 
                    CONCAT(a.first_name, ' ', a.last_name) as awardee_name
                FROM 
                    market_stalls s
                LEFT JOIN 
                    awardees a ON s.awardee_id = a.id
                WHERE 
                    s.awardee_id IS NOT NULL";
        
        $results = $this->db->fetchAll($sql);
        $mapping = [];
        foreach ($results as $row) {
            $mapping[$row['stall_id']] = [
                'id' => $row['awardee_id'],
                'name' => $row['awardee_name']
            ];
        }
        return $mapping;
    }

    public function getUsers() {
        $sql = "SELECT id, username FROM users WHERE status = 'active' ORDER BY username";
        return $this->db->fetchAll($sql);
    }

    /**
     * Registra un cambio de estado en la línea de tiempo del reporte.
     * Este método se llama desde el método update() del controlador.
     */
    public function logStatusUpdate(
        $reportId, 
        $oldStatus, 
        $newStatus, 
        $description = null, 
        $userId = null
    ) {
        $sql = "INSERT INTO inspection_updates 
                    (report_id, status_old, status_new, update_description, updated_by_user_id)
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $reportId, 
            $oldStatus, 
            $newStatus, 
            $description, 
            $userId
        ];
        
        try {
            $this->db->executeQuery($sql, $params);
            return ['success' => true];
        } catch (Exception $e) {
            // Manejo de errores
            return ['success' => false, 'message' => 'Error al registrar el log: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene todos los eventos de la línea de tiempo para un reporte específico.
     * Incluye el nombre del usuario que realizó la actualización.
     * @param int $reportId
     * @return array
     */
    public function getReportTimeline($reportId) {
        $sql = "SELECT 
                    iu.*,
                    u.username AS updated_by_username,
                    u.full_name AS updated_by_name -- Asumiendo que la tabla users tiene un campo full_name
                FROM 
                    inspection_updates iu
                LEFT JOIN 
                    users u ON iu.updated_by_user_id = u.id
                WHERE 
                    iu.report_id = ?
                ORDER BY 
                    iu.update_date ASC"; // Orden cronológico ascendente
        
        return $this->db->fetchAll($sql, [$reportId]);
    }
    
}