<?php
// models/InfractionTypesModel.php

require_once __DIR__ . '/../config/Database.php';

class InfractionTypesModel {
    private $conn;
    private $table = 'infraction_types';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $searchParam = "%$search%";
            
            $query = "SELECT it.*, 
                             COUNT(i.id_infraction) as infractions_count 
                      FROM " . $this->table . " it
                      LEFT JOIN infractions i ON it.id_infraction_type = i.id_infraction_type
                      WHERE it.infraction_type_name LIKE :search
                      GROUP BY it.id_infraction_type
                      ORDER BY it.infraction_type_name ASC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener tipos de infracción: " . $exception->getMessage());
            return [];
        }
    }
}