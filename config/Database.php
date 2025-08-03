<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'seramermvc';
    private $username = 'root';
    private $password = 'a10882990';
    private $charset = 'utf8mb4';
    private $conn;

    /**
     * Obtener la conexión a la base de datos
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configurar PDO para mostrar errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Cerrar la conexión a la base de datos
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Verificar si la conexión está activa
     * @return bool
     */
    public function isConnected() {
        return $this->conn !== null;
    }

    /**
     * Obtener información de la conexión
     * @return array
     */
    public function getConnectionInfo() {
        return [
            'host' => $this->host,
            'database' => $this->db_name,
            'username' => $this->username,
            'charset' => $this->charset
        ];
    }

    //new method to execute a query
    public function executeQuery($query, $params = []) {
        if ($this->conn === null) {
            $this->getConnection();
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $exception) {
            echo "Error al ejecutar la consulta: " . $exception->getMessage();
            return false;
        }
    }

    //new method to fetch all results from a query
    public function fetchAll($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return [];
    }
    
    //new method to fetch a single result from a query
    public function fetchOne($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return null;
    }
}

?>
