<?php
require_once __DIR__ . '/../config/Database.php';

class ConciliationReportsModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->conn = new Database();
        $this->db = $this->conn->getConnection();
    }
    
    /**
     * Obtiene una lista paginada y filtrada de informes de conciliación.
     * @param int $page El número de página actual.
     * @param int $limit El número de registros por página.
     * @param string $search Término de búsqueda para filtrar los resultados.
     * @return array
     */
    public function getAll($page, $limit, $search) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM conciliation_reports";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE result LIKE ?";
            $params[] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY report_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
   
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cuenta el total de informes de conciliación, con filtro opcional.
     * @param string $search Término de búsqueda para filtrar los resultados.
     * @return int
     */
    public function countAll($search) {
        $sql = "SELECT COUNT(*) FROM conciliation_reports";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE result LIKE ?";
            $params[] = '%' . $search . '%';
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Obtiene un solo informe de conciliación por su ID.
     * @param int $id El ID del informe.
     * @return array|false
     */
    public function getById($id) {
        $sql = "SELECT * FROM conciliation_reports WHERE report_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea un nuevo informe de conciliación.
     * @param array $data Los datos del informe.
     * @return array
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO conciliation_reports (citation_id, awardee_attendance, result, agreement_details) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['citation_id'],
                $data['awardee_attendance'],
                $data['result'],
                $data['agreement_details']
            ]);
            return ['success' => true, 'message' => 'Informe de conciliación creado con éxito.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al crear el informe: ' . $e->getMessage()];
        }
    }
    
    /**
     * Actualiza un informe de conciliación existente.
     * @param int $id El ID del informe.
     * @param array $data Los nuevos datos del informe.
     * @return array
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE conciliation_reports SET citation_id = ?, awardee_attendance = ?, result = ?, agreement_details = ? WHERE report_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['citation_id'],
                $data['awardee_attendance'],
                $data['result'],
                $data['agreement_details'],
                $id
            ]);
            return ['success' => true, 'message' => 'Informe de conciliación actualizado con éxito.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar el informe: ' . $e->getMessage()];
        }
    }
    
    /**
     * Elimina un informe de conciliación.
     * @param int $id El ID del informe.
     * @return array
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM conciliation_reports WHERE report_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Informe de conciliación eliminado con éxito.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar el informe: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtiene una lista de todas las citaciones para usarlas en un select.
     * @return array
     */
    public function getCitationsList() {
        // Asumiendo que existe una tabla 'citations'
        $stmt = $this->db->query("SELECT citation_id, location, citation_datetime FROM citations ORDER BY citation_datetime DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}