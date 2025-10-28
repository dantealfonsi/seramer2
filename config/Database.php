<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'seramermvc';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';

    /**
     * @var PDO
     * Almacena la única instancia de la conexión PDO.
     */
    private $conn;
    
    /**
     * @var PDOStatement
     * Almacena la última declaración preparada.
     */
    private $stmt;

    /**
     * El constructor establece la conexión a la base de datos
     * y la almacena en $this->conn.
     */
    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Un manejo de errores más robusto podría registrar el error y mostrar un mensaje genérico.
            die('Connection failed: ' . $e->getMessage());
        }
    }
    
    // --- Métodos de Conexión ---
    
    /**
     * Obtiene la conexión PDO ya establecida por el constructor.
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Cierra la conexión a la base de datos (libera el objeto PDO).
     */
    public function closeConnection() {
        $this->conn = null;
    }
    
    /**
     * Verifica si la conexión está activa.
     * @return bool
     */
    public function isConnected() {
        return $this->conn !== null;
    }

    // --- Métodos de Consulta ---

    /**
     * Prepara una consulta SQL.
     * @param string $sql La consulta SQL.
     */
    public function query($sql) {
        $this->stmt = $this->conn->prepare($sql);
    }

    /**
     * Vincula valores a la consulta preparada.
     * @param mixed $param Parámetro a vincular.
     * @param mixed $value Valor a vincular.
     * @param int|null $type Tipo de dato PDO.
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Ejecuta la consulta preparada.
     * @return bool
     */
    public function execute() {
        return $this->stmt->execute();
    }

    /**
     * Retorna todos los resultados de una consulta.
     * @return array
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Retorna un solo resultado de una consulta.
     * @return mixed
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    /**
     * Método mejorado para ejecutar consultas directamente.
     * Retorna la declaración PDOStatement para que puedas usar lastInsertId().
     * @param string $query
     * @param array $params
     * @return PDOStatement|false
     */
    public function executeQuery($query, $params = []) {
        try {
            $this->stmt = $this->conn->prepare($query);
            $this->stmt->execute($params);
            return $this->stmt;
        } catch (PDOException $e) {
            error_log("Error al ejecutar la consulta: " . $e->getMessage() . " - Query: " . $query);
            // Podrías lanzar una excepción o retornar false.
            return false;
        }
    }

    /**
     * Ejecuta una consulta y retorna todos los resultados.
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Ejecuta una consulta y retorna un solo resultado.
     * @param string $query
     * @param array $params
     * @return mixed
     */
    public function fetchOne($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetch() : null;
    }
}