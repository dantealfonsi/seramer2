<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'seramermvc';
    private $username = 'root';
    private $password = '';
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

/**
     * Ejecuta una consulta SQL preparada.
     * @param string $query La consulta SQL.
     * @param array $params Los parámetros para la consulta.
     * @return PDOStatement|false Retorna la instancia de PDOStatement o false si falla.
     */
    public function executeQuery($query, $params = []) {
        try {
            // Aseguramos la conexión antes de intentar usarla
            $this->getConnection();
            
            // Si la conexión no se pudo establecer, salimos
            if (!$this->isConnected()) {
                throw new Exception("No hay una conexión a la base de datos disponible.");
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt;
            
        } catch (PDOException $exception) {
            // Registramos el error sin mostrarlo directamente al usuario
            error_log("Error al ejecutar la consulta: " . $exception->getMessage() . " - Query: " . $query);
            // Podrías mostrar un mensaje genérico al usuario
            echo "executeQuery: Ocurrió un error al procesar tu solicitud. Por favor, intenta de nuevo más tarde.";
            return [];
        } catch (Exception $e) {
            // Manejo de errores si getConnection() falló
            error_log($e->getMessage());
            echo "executeQuery: Ocurrió un error al procesar tu solicitud. Por favor, intenta de nuevo más tarde.";
            return [];
        }
    }

    /**
     * Ejecuta una consulta y retorna todos los resultados.
     * @param string $query La consulta SQL.
     * @param array $params Los parámetros para la consulta.
     * @return array Retorna un array con todos los resultados, o un array vacío en caso de error.
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return [];
    }

    /**
     * Ejecuta una consulta y retorna un único resultado.
     * @param string $query La consulta SQL.
     * @param array $params Los parámetros para la consulta.
     * @return mixed Retorna el primer resultado, o null si no hay resultados o falla.
     */
    public function fetchOne($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        if ($stmt) {
            // Con FETCH_ASSOC, esto devuelve un array asociativo o false
            return $stmt->fetch();
        }
        return null;
    }
}

?>
