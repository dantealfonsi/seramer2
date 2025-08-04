<?php
// models/MarketStallsModel.php

require_once __DIR__ . '/../config/Database.php';

class MarketStallsModel {
    private $conn;
    private $table = 'market_stalls';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $searchParam = "%$search%";
            
            $query = "SELECT ms.*, 
                             COUNT(i.id_infraction) as infractions_count 
                      FROM " . $this->table . " ms 
                      LEFT JOIN infractions i ON ms.id_stall = i.id_stall
                      WHERE ms.stall_code LIKE :search
                      GROUP BY ms.id_stall
                      ORDER BY ms.stall_code ASC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener puestos del mercado: " . $exception->getMessage());
            return [];
        }
    }
}