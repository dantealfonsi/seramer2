<?php
require_once __DIR__ . '/../config/Database.php';

class Model {
    protected $db;
    protected $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Executes a query and returns all results as an array.
     */
    protected function query(string $sql, array $params = []): array {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Executes a query and returns a single result row.
     */
    protected function queryOne(string $sql, array $params = []) {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Executes a query (INSERT, UPDATE, DELETE) and returns true on success.
     */
    protected function execute(string $sql, array $params = []): bool {
        $stmt = $this->db->executeQuery($sql, $params);
        return $stmt !== false;
    }

    protected function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    protected function commit() {
        return $this->conn->commit();
    }

    protected function rollback() {
        return $this->conn->rollback();
    }

    protected function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    protected function findById($id) {
        $table = isset($this->table) ? $this->table : '';
        if (!$table) return null;
        return $this->queryOne("SELECT * FROM {$table} WHERE id = :id", ['id' => $id]);
    }
}
