<?php
// models/AdjudicatoriesModel.php

require_once __DIR__ . '/../config/Database.php';

class AdjudicatoriesModel {
    private $conn;
    private $table = 'adjudicatories';

    public function __construct() {
        // Suponiendo que la conexión PDO está en una clase Database
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $searchParam = "%$search%";
            
            $query = "SELECT a.*, 
                             COUNT(i.id_infraction) as infractions_count 
                      FROM " . $this->table . " a 
                      LEFT JOIN infractions i ON a.id_adjudicatory = i.id_adjudicatory
                      WHERE a.full_name_or_company_name LIKE :search
                      GROUP BY a.id_adjudicatory
                      ORDER BY a.full_name_or_company_name ASC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener adjudicatarios: " . $exception->getMessage());
            return [];
        }
    }
}