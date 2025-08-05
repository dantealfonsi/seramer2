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
            
            $query = "SELECT id_stall, stall_code FROM market_stalls";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $dat =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stallDict = [];
            foreach ($dat as $stall) {
                $stallDict[$stall['id_stall']] = $stall['stall_code'];
            }
            return $stallDict;
        } catch(PDOException $exception) {
            error_log("Error al obtener puestos del mercado: " . $exception->getMessage());
            return [];
        }
    }
}