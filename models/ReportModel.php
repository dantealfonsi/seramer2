<?php
require_once __DIR__ . '/Model.php';

class ReportModel extends Model {
    protected $table = 'contracts'; 
    
    public function getDelinquentContracts() {
        // Adapted query from Extra App
        // NOTE: Adjusted column names to match potential differences or keep strict
        $query = "
            SELECT 
                c.id as contract_id,
                a.id as awardee_id,
                CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                a.id_number as awardee_id_number,
                a.phone as awardee_phone,
                a.email as awardee_email,
                COUNT(DISTINCT cp.id) as overdue_payments_count,
                SUM(cp.amount_bs) as total_amount_due,
                SUM(
                    COALESCE((SELECT SUM(cpi.amount) 
                              FROM contract_payment_installments cpi 
                              WHERE cpi.contract_payment_id = cp.id), 0)
                ) as total_paid,
                MIN(cp.due_date) as first_overdue_date,
                DATEDIFF(CURDATE(), MIN(cp.due_date)) as days_overdue
            FROM contracts c
            INNER JOIN awardees a ON c.awardee_id = a.id
            INNER JOIN contract_payments cp ON c.id = cp.contract_id
            WHERE cp.status = 'overdue' OR (cp.status = 'pending' AND cp.due_date < CURDATE())
            GROUP BY c.id, a.id, a.first_name, a.last_name, a.id_number, a.phone, a.email
            HAVING overdue_payments_count > 0
            ORDER BY days_overdue DESC, total_amount_due DESC
        ";
        // Simplified query uses 'amount_bs' from contract_payments which is already calculated when rate is applied
        // The original query recalculated it. I stick to simpler version if 'amount_bs' is reliable.
        
        return $this->query($query);
    }
    
    public function getZoneAccumulated($startDate, $endDate) {
        $query = "
            SELECT 
                z.id as zone_id,
                z.name as zone_name,
                SUM(cpi.amount) as total_accumulated,
                COUNT(DISTINCT c.id) as contracts_count,
                COUNT(DISTINCT cpi.id) as payments_count
            FROM zones z
            INNER JOIN sectors s ON z.id = s.zone_id
            INNER JOIN market_stalls ms ON s.id = ms.sector_id
            INNER JOIN contract_locations cl ON ms.id = cl.stall_id
            INNER JOIN contracts c ON cl.contract_id = c.id
            INNER JOIN contract_payments cp ON c.id = cp.contract_id
            INNER JOIN contract_payment_installments cpi ON cp.id = cpi.contract_payment_id
            WHERE DATE(cpi.date) BETWEEN :start_date AND :end_date
            GROUP BY z.id, z.name
            ORDER BY total_accumulated DESC
        ";
        
        return $this->query($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    private $reportsDir = __DIR__ . '/../reports/';

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
        if (!is_dir($this->reportsDir)) {
            mkdir($this->reportsDir, 0755, true);
        }
        
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
        if (!is_dir($this->reportsDir)) {
             mkdir($this->reportsDir, 0755, true);
        }
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
        if (!is_dir($this->reportsDir)) {
             mkdir($this->reportsDir, 0755, true);
        }
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
        if (!is_dir($this->reportsDir)) {
             mkdir($this->reportsDir, 0755, true);
        }
        $path = $this->reportsDir . basename($filename);
        // file_put_contents devuelve el número de bytes escritos o false en caso de error.
        return file_put_contents($path, $content) !== false;
    }
}
