<?php
// models/ReportModel.php
require_once __DIR__ . '/../config/Database.php';

class ReportModel {

    private $reportsDir = 'reports/';
    private $conn;

    public function __construct() {
        // Inicializar la conexión a la base de datos
        $database = new Database();
        $this->conn = $database->getConnection();
        // Asegurarse de que el directorio de reportes exista
        if (!is_dir($this->reportsDir)) {
            mkdir($this->reportsDir, 0755, true);
        }
    }

    /**
     * Obtiene los detalles para un reporte específico basado en una configuración.
     * @param int $id El ID del registro principal.
     * @param array $config El array de configuración cargado desde el archivo .sys.
     * @return array|false Un array con los datos o false si hay un error.
     */
    public function getDetails($id, $config) {
        if (!isset($config['sql']) || empty($config['sql'])) {
            error_log("La clave 'sql' no está definida en la configuración del reporte.");
            return false;
        }

        // Validación para asegurar que la conexión existen
        if (!$this->conn) {
            error_log("La conexión a la base de datos no está disponible. Entrando en modo simulación.");
            // Modo simulación si no hay DB, para que siga funcionando la demo.
            $sampleData = [];
            foreach ($config['fields'] as $field) {
                // Llenar con datos de ejemplo más realistas
                $sampleData[$field] = ucfirst(str_replace('_', ' ', $field)) . ' de Ejemplo';
            }
            $sampleData['infraction_id'] = $id; // Asigna el ID real
            return $sampleData;
        }
        
        try {
            $query = $config['sql'];
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener detalles de la infracción: " . $exception->getMessage());
            return false;
        }
    }
    
    /**
     * Devuelve una lista de los campos que se pueden usar en la plantilla.
     * @param array $config El array de configuración cargado desde el archivo .sys.
     * @return array
     */
    public function getAvailableFields($config) {
        if (isset($config['fields']) && is_array($config['fields'])) {
            return $config['fields'];
        }
        // Devuelve un array vacío si la configuración no es válida
        error_log("La clave 'fields' no está definida o no es un array en la configuración.");
        return [];
    }

    /**
     * Lee y decodifica el archivo de configuración .sys para un reporte.
     * @param string $reportFilename El nombre del archivo .rep (ej: print_infraction.rep).
     * @return array|null El array de configuración o null si hay un error.
     */
    public function getReportConfig($reportFilename) {
        // 1. Obtiene el nombre base del archivo (sin extensión)
        $baseName = pathinfo($reportFilename, PATHINFO_FILENAME);
        
        // 2. Construye la ruta al archivo .sys
        $configPath = $this->reportsDir . $baseName . '.sys';

        // 3. Verifica si el archivo existe
        if (!file_exists($configPath)) {
            error_log("Archivo de configuración no encontrado: " . $configPath);
            return null;
        }

        // 4. Lee el contenido del archivo
        $jsonContent = file_get_contents($configPath);
        
        // 5. Decodifica el JSON a un array asociativo de PHP
        $config = json_decode($jsonContent, true);

        // 6. Verifica si hubo errores al decodificar el JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error de JSON en el archivo de configuración " . $configPath . ": " . json_last_error_msg());
            return null;
        }

        // 7. Devuelve la configuración como un array
        return $config;
    }

    /**
     * Obtiene la lista de archivos .rep del directorio de reportes.
     * @return array
     */
    public function getReportFiles() {
        $allFiles = scandir($this->reportsDir);
        $repFiles = array_filter($allFiles, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'rep';
        });
        return array_values($repFiles); // Reiniciar índices del array
    }

    /**
     * Lee y devuelve el contenido de un archivo de reporte.
     * @param string $filename El nombre del archivo.
     * @return string|false El contenido del archivo o false si falla.
     */
    public function getReportContent($filename) {
        $path = $this->reportsDir . basename($filename); // basename para seguridad
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return false;
    }

    /**
     * Guarda contenido en un archivo de reporte.
     * @param string $filename El nombre del archivo.
     * @param string $content El contenido a guardar.
     * @return bool True si se guardó, false si falló.
     */
    public function saveReportContent($filename, $content) {
        $path = $this->reportsDir . basename($filename);
        // file_put_contents devuelve el número de bytes escritos o false en caso de error.
        return file_put_contents($path, $content) !== false;
    }
}
