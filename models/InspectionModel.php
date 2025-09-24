<?php

require_once __DIR__ . '/../config/Database.php';

class InspectionModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all inspection reports with pagination and search.
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page, $limit, $search) {
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
            $sql .= " WHERE mi.full_name LIKE ? OR ai.full_name LIKE ? OR s.stall_number LIKE ? OR a.full_name_or_company_name LIKE ?";
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        $sql .= " ORDER BY ir.creation_date DESC LIMIT " . $limit . " OFFSET " . $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Count all inspection reports with an optional search filter.
     * @param string $search
     * @return int
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
            $sql .= " WHERE mi.full_name LIKE ? OR ai.full_name LIKE ? OR s.stall_number LIKE ? OR a.full_name_or_company_name LIKE ?";
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get a single inspection report by its ID.
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
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
    
    /**
     * Create a new inspection report.
     * @param array $data
     * @return array
     */
    public function create($data) {
        // 1. Array for 'scheduled_inspections' table
        $scheduled_inspection_data = [
            'scheduled_date' => $data['scheduled_date'],
            'inspection_type' => $data['inspection_type'],
            'assigned_responsible_id' => $data['assigned_responsible_id'],
            'inspection_status' => $data['inspection_status'] ?? 'Pending',
            'observations' => $data['observations'] ?? null,
        ];

        // SQL to insert into scheduled_inspections
        $sql_scheduled = "INSERT INTO scheduled_inspections (scheduled_date, inspection_type, assigned_responsible_id, inspection_status, observations) 
                        VALUES (?, ?, ?, ?, ?)";
        
        // Parameters for the scheduled_inspections insertion
        $params_scheduled = [
            $scheduled_inspection_data['scheduled_date'],
            $scheduled_inspection_data['inspection_type'],
            $scheduled_inspection_data['assigned_responsible_id'],
            $scheduled_inspection_data['inspection_status'],
            $scheduled_inspection_data['observations'],
        ];

        try {
            // Execute the first query
            $this->db->executeQuery($sql_scheduled, $params_scheduled);

            // Get the ID of the new scheduled inspection
            $scheduled_inspection_id = $this->db->getConnection()->lastInsertId();

            // 2. Array for 'inspection_reports' table
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

            // SQL to insert into inspection_reports
            $sql_report = "INSERT INTO inspection_reports (scheduled_inspection_id, main_inspector_id, assistant_inspector_id, stall_id, awardee_id, general_observations, inspector_signature_url, assistant_signature_url) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Parameters for the inspection_reports insertion
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

            // Execute the second query
            $this->db->executeQuery($sql_report, $params_report);

            return ['success' => true, 'message' => 'Reporte e inspección registrados exitosamente.'];

        } catch (Exception $e) {
            // If any insertion fails, return a general error
            return ['success' => false, 'message' => 'Error al registrar los datos: ' . $e->getMessage()];
        }
    }

    /**
     * Update an existing inspection report.
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
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

    /**
     * Delete an inspection report.
     * @param int $id
     * @return array
     */
    public function delete($id) {
        $sql = "DELETE FROM inspection_reports WHERE report_id = ?";
        try {
            $this->db->executeQuery($sql, [$id]);
            return ['success' => true, 'message' => 'Reporte de inspección eliminado permanentemente.'];
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
        $sql = "SELECT id, first_name FROM awardees ORDER BY first_name";
        return $this->db->fetchAll($sql);
    }

    public function getUsers() {
        $sql = "SELECT id, username FROM users WHERE status = 'active' ORDER BY username";
        return $this->db->fetchAll($sql);
    }
}