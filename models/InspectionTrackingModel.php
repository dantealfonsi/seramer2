<?php
// C:\xampp\htdocs\seramer2\models\InspectionTrackingModel.php

class InspectionTrackingModel {
    private $db;
    private $table_name = "inspection_updates"; 
    private $inspection_table = "scheduled_inspections";
    private $user_table = "users";
    private $staff_table = "staff"; // Nueva tabla añadida

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtiene todos los registros de seguimiento para una inspección específica.
     */
public function getTrackingByInspectionId($inspection_id) {
        $query = "SELECT 
                    t.update_id AS tracking_id, 
                    t.inspection_id, 
                    t.update_description,           
                    t.status_new,                   
                    t.update_date AS action_datetime, 
                    CONCAT(s.first_name, ' ', s.last_name) AS admin_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN " . $this->user_table . " u 
                    ON t.updated_by_user_id = u.id       
                  LEFT JOIN " . $this->staff_table . " s
                    ON u.staff_id = s.id                 
                  WHERE t.inspection_id = :inspection_id
                  ORDER BY t.update_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":inspection_id", $inspection_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro de seguimiento. (Se asume que los nombres de las columnas ya están correctos aquí)
     */
    public function create($data) {
        
        $query = "INSERT INTO " . $this->table_name . "
                  (report_id, inspection_id, updated_by_user_id, status_old, status_new, update_description, update_date)
                  VALUES
                  (:report_id, :inspection_id, :updated_by_user_id, :status_old, :status_new, :update_description, NOW())";

        $stmt = $this->db->prepare($query);

        // Limpieza de datos
        $report_id = htmlspecialchars(strip_tags($data['report_id'])); 
        $inspection_id = htmlspecialchars(strip_tags($data['inspection_id']));
        $updated_by_user_id = htmlspecialchars(strip_tags($data['admin_user_id']));
        $status_old = htmlspecialchars(strip_tags($data['current_status'])); 
        $status_new = htmlspecialchars(strip_tags($data['new_status'])); 
        $update_description = htmlspecialchars(strip_tags($data['update_description'])); 

        // Bind parameters
        $stmt->bindParam(":report_id", $report_id, PDO::PARAM_INT);
        $stmt->bindParam(":inspection_id", $inspection_id, PDO::PARAM_INT);
        $stmt->bindParam(":updated_by_user_id", $updated_by_user_id, PDO::PARAM_INT);
        $stmt->bindParam(":status_old", $status_old);
        $stmt->bindParam(":status_new", $status_new);
        $stmt->bindParam(":update_description", $update_description);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
    
    // El resto de los métodos (updateInspectionStatus, getOne, delete) no requieren cambios.
// En C:\xampp\htdocs\seramer2\models\InspectionTrackingModel.php

    /**
     * Actualiza el campo inspection_status en la tabla scheduled_inspections.
     */
    public function updateInspectionStatus($inspection_id, $new_status) {
        // Aseguramos que el estado sea uno de los válidos para evitar SQL Injection
        $valid_statuses = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
        if (!in_array($new_status, $valid_statuses)) {
            return false;
        }

        $query = "UPDATE " . $this->inspection_table . " 
                  SET inspection_status = :new_status
                  WHERE inspection_id = :inspection_id"; 
        
        $stmt = $this->db->prepare($query);
        
        $new_status_clean = htmlspecialchars(strip_tags($new_status));
        $inspection_id_clean = htmlspecialchars(strip_tags($inspection_id));

        $stmt->bindParam(":new_status", $new_status_clean);
        $stmt->bindParam(":inspection_id", $inspection_id_clean, PDO::PARAM_INT);

        // Si la ejecución falla, devuelve false
        return $stmt->execute();
    }
    
    public function getOne($tracking_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE update_id = :tracking_id LIMIT 0,1"; 
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tracking_id', $tracking_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function delete($tracking_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE update_id = :tracking_id"; 
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tracking_id', $tracking_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}