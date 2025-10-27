<?php
require_once __DIR__ . '/../config/Database.php';

class UsersFiscModel {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene una lista de usuarios que pertenecen al departamento de Fiscalización (ID 3).
     *
     * @return array La lista de usuarios o un array vacío en caso de error o no haber resultados.
     */
    public function getUsersByFiscalizationDepartment(): array {
        // ID del departamento de Fiscalización
        $fiscalization_department_id = 3; 
        
        // La consulta une users con staff y filtra por el department_id
        $query = "
            SELECT
                u.id AS user_id,
                u.username,
                u.email,
                u.status AS user_status,
                s.id_number,
                s.first_name,
                s.last_name
            FROM
                " . $this->table . " u
            INNER JOIN 
                staff s ON u.staff_id = s.id
            WHERE
                s.department_id = :department_id
            ORDER BY 
                s.last_name, s.first_name
        ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':department_id', $fiscalization_department_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en getUsersByFiscalizationDepartment: " . $e->getMessage());
            return []; // Devolver array vacío en caso de error
        }
    }
}