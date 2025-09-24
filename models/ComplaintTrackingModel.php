<?php

require_once __DIR__ . '/../config/database.php';

class ComplaintTrackingModel {
    private $conn;
    private $table = 'complaint_tracking';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene todos los registros de seguimiento para una queja específica.
     *
     * @param int $complaint_id El ID de la queja.
     * @return array Una lista de registros de seguimiento.
     */
    public function getAllByComplaintId($complaint_id) {
        try {
            $sql = "SELECT ct.*, u.username AS admin_name FROM " . $this->table . " ct JOIN users u ON ct.admin_user_id = u.id WHERE ct.complaint_id = :complaint_id ORDER BY ct.action_datetime DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':complaint_id', $complaint_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener registros de seguimiento: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un registro de seguimiento por su ID.
     *
     * @param int $id El ID del registro.
     * @return array|null El registro o null si no se encuentra.
     */
    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE tracking_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener el registro por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo registro de seguimiento.
     *
     * @param array $data Los datos del registro.
     * @return array Resultado de la operación.
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table . " (complaint_id, admin_user_id, action_type, action_description, action_result) VALUES (:complaint_id, :admin_user_id, :action_type, :action_description, :action_result)";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':complaint_id', $data['complaint_id'], PDO::PARAM_INT);
            $stmt->bindParam(':admin_user_id', $data['admin_user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':action_type', $data['action_type'], PDO::PARAM_STR);
            $stmt->bindParam(':action_description', $data['action_description'], PDO::PARAM_STR);
            $stmt->bindParam(':action_result', $data['action_result'], PDO::PARAM_STR);

            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Error al crear el registro de seguimiento: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Actualiza un registro de seguimiento existente.
     *
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos.
     * @return array Resultado de la operación.
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE " . $this->table . " SET admin_user_id = :admin_user_id, action_type = :action_type, action_description = :action_description, action_result = :action_result WHERE tracking_id = :id";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':admin_user_id', $data['admin_user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':action_type', $data['action_type'], PDO::PARAM_STR);
            $stmt->bindParam(':action_description', $data['action_description'], PDO::PARAM_STR);
            $stmt->bindParam(':action_result', $data['action_result'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Error al actualizar el registro de seguimiento: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Elimina un registro de seguimiento.
     *
     * @param int $id El ID del registro a eliminar.
     * @return array Resultado de la operación.
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM " . $this->table . " WHERE tracking_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Error al eliminar el registro de seguimiento: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}