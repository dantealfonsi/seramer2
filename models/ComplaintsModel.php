<?php

require_once __DIR__ . '/../config/Database.php';

class ComplaintsModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
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

    /**
     * Get all complaints with pagination and search.
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page, $limit, $search) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT c.*, p.stall_number AS position_name, contr.first_name AS contractor_name
                FROM complaints c
                LEFT JOIN market_stalls p ON c.stall_id = p.id
                LEFT JOIN awardees contr ON c.awardee_id = contr.id";
        
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE c.client_name LIKE ? OR c.complaint_description LIKE ? OR p.stall_number LIKE ? OR contr.first_name LIKE ?";
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        $sql .= " ORDER BY c.complaint_datetime DESC LIMIT " . $limit . " OFFSET " . $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Count all complaints with an optional search filter.
     * @param string $search
     * @return int
     */
    public function countAll($search) {
        $sql = "SELECT COUNT(c.complaint_id)
                FROM complaints c
                LEFT JOIN market_stalls p ON c.stall_id = p.id
                LEFT JOIN awardees contr ON c.awardee_id = contr.id";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE c.client_name LIKE ? OR c.complaint_description LIKE ? OR p.stall_number LIKE ? OR contr.first_name LIKE ?";
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get a single complaint by its ID.
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $sql = "SELECT * FROM complaints WHERE complaint_id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Create a new complaint.
     * @param array $data
     * @return array
     */
    public function create($data) {
        $sql = "INSERT INTO complaints (client_user_id, client_name, client_phone, client_email, complaint_description, stall_id, awardee_id, complaint_type, complaint_status, complaint_priority, internal_observations) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['client_user_id'] ?? null,
            $data['client_name'],
            $data['client_phone'] ?? null,
            $data['client_email'],
            $data['complaint_description'],
            $data['position_id'] ?? null,
            $data['contractor_id'] ?? null,
            $data['complaint_type'],
            $data['complaint_status'] ?? 'Received',
            $data['complaint_priority'] ?? 'Medium',
            $data['internal_observations'] ?? null
        ];

        try {
            $this->db->executeQuery($sql, $params);
            return ['success' => true, 'message' => 'Queja registrada exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al registrar la queja: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene todos los puestos de mercado.
     * @return array
     */    
    public function getMarketStall() {
        $sql = "SELECT id, stall_number FROM market_stalls";
        return $this->db->fetchAll($sql);
    }

    /**
     * Update an existing complaint.
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        $sql = "UPDATE complaints SET
                    client_name = ?,
                    client_phone = ?,
                    client_email = ?,
                    complaint_description = ?,
                    complaint_type = ?,
                    complaint_status = ?,
                    complaint_priority = ?,
                    internal_observations = ?
                WHERE complaint_id = ?";

        $params = [
            $data['client_name'],
            $data['client_phone'] ?? null,
            $data['client_email'],
            $data['complaint_description'],
            $data['complaint_type'],
            $data['complaint_status'],
            $data['complaint_priority'],
            $data['internal_observations'] ?? null,
            $id
        ];

        try {
            $this->db->executeQuery($sql, $params);
            return ['success' => true, 'message' => 'Queja actualizada exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar la queja: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a complaint.
     * @param int $id
     * @return array
     */
    public function delete($id) {
        // Aquí podrías agregar una verificación para no permitir eliminar si tiene dependencias.
        $sql = "DELETE FROM complaints WHERE complaint_id = ?";
        try {
            $this->db->executeQuery($sql, [$id]);
            return ['success' => true, 'message' => 'Queja eliminada permanentemente.'];
        } catch (Exception $e) {
            // Podría fallar por restricciones de clave foránea si las tuvieras
            return ['success' => false, 'message' => 'Error al eliminar la queja. Es posible que esté asociada a otros registros.'];
        }
    }
}