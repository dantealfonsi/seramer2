<?php

require_once __DIR__ . '/../config/Database.php';

class InfractionsModel {
    private $conn;
    private $table = 'infractions';
    
    // Propiedades de la infracción
    public $infraction_id;
    public $awardee_id;
    public $stall_id;
    public $infraction_datetime;
    public $infraction_type_id;
    public $infraction_description;
    public $infraction_status;
    public $inspector_observations;
    public $proof; // Nuevo campo para la evidencia
    public $status_logical;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createEconomicIndicatorsTable(): void {        
        $sql = "
            CREATE TABLE IF NOT EXISTS `economic_indicators` (
                `indicator_id` INT(11) NOT NULL AUTO_INCREMENT,
                `ut_value` DECIMAL(18, 6) NOT NULL COMMENT 'Valor de la Unidad Tributaria (UT)',
                `euro_bcv_rate` DECIMAL(18, 6) NOT NULL COMMENT 'Tasa del Euro según BCV (Moneda de Mayor Valor - Art. 105)',
                `effective_date` DATE NOT NULL COMMENT 'Fecha desde la que son vigentes estos valores',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`indicator_id`) USING BTREE,
                -- Aseguramos que solo haya una UT y Tasa por día de vigencia
                UNIQUE INDEX `idx_effective_date` (`effective_date`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;
        ";

        try {            
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log("FATAL ERROR: No se pudo crear la tabla : " . $e->getMessage());
            die("Error crítico al inicializar la base de datos.");
        }
    }    

    /**
     * Devuelve la tasa de la Unidad Tributaria (UT) y del Euro (BCV) más reciente 
     * basándose en la fecha efectiva (effective_date).
     * * @return array|null Un array asociativo con 'ut_value' y 'euro_bcv_rate', o null si no hay registros.
     */
    public function getLatestEconomicIndicators(): ?array {
        // La consulta busca el registro con la fecha efectiva más reciente
        $sql = "
            SELECT 
                ut_value, 
                euro_bcv_rate
            FROM 
                economic_indicators
            ORDER BY 
                effective_date DESC
            LIMIT 1;
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            // Obtener la fila como array asociativo
            $indicator = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si se encontró una fila, devolverla; de lo contrario, devolver null
            return $indicator ?: null;

        } catch (PDOException $e) {
            // Registrar el error pero no detener la aplicación
            error_log("Error al obtener el indicador económico más reciente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Inserta o actualiza la UT y la Tasa del Euro para la fecha actual.
     * Si el effective_date ya existe, actualiza los valores.
     * @param float $utValue
     * @param float $euroRate
     * @return bool True si la operación fue exitosa, false en caso contrario.
     */
    public function saveOrUpdateEconomicIndicators(float $utValue, float $euroRate): bool {
        // Usamos la fecha actual como la fecha de vigencia (effective_date)
        $today = date('Y-m-d'); 
        
        $sql = "
            INSERT INTO economic_indicators (ut_value, euro_bcv_rate, effective_date)
            VALUES (:ut_value, :euro_rate, :effective_date)
            ON DUPLICATE KEY UPDATE
                ut_value = VALUES(ut_value),
                euro_bcv_rate = VALUES(euro_bcv_rate),
                created_at = CURRENT_TIMESTAMP; -- Actualizar el timestamp de modificación
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':ut_value' => $utValue,
                ':euro_rate' => $euroRate,
                ':effective_date' => $today
            ]);
        } catch (PDOException $e) {
            error_log("Error al guardar/actualizar indicadores económicos: " . $e->getMessage());
            return false;
        }
    }    

    public function getAwardeesList() {
        try {
            $query = "SELECT id, CONCAT(first_name, ' ', last_name) as full_name, id_number, phone FROM awardees ORDER BY first_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener adjudicatarios: " . $exception->getMessage());
            return [];
        }
    }

    public function getStallsList() {
        try {
            // Direct mapping using awardee_id column in market_stalls
            $query = "SELECT 
                        s.id, 
                        s.sector_id, 
                        s.stall_number, 
                        s.location_description, 
                        s.awardee_id,
                        CONCAT(a.first_name, ' ', a.last_name) as awardee_full_name
                      FROM market_stalls s
                      LEFT JOIN awardees a ON s.awardee_id = a.id
                      ORDER BY s.stall_number";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener puestos de mercado: " . $exception->getMessage());
            return [];
        }
    }

    public function getInfractionTypesList() {
        try {
            $query = "SELECT * FROM infraction_types ORDER BY infraction_type_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener tipos de infracción: " . $exception->getMessage());
            return [];
        }
    }

    public function getInfractionTypeById($id)
    {
        // Asumiendo que tienes una conexión PDO en $this->db
        $stmt = $this->conn->prepare("SELECT * FROM infraction_types WHERE infraction_type_id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param array $filters Array de filtros recibidos del controlador (incluye 'search', 'infraction_date', etc.)
     * @return array ['whereSQL' => 'WHERE ...', 'bindParams' => [':param' => value]]
     */
    private function buildFilterConditions(array $filters): array {
        $whereClauses = ["i.status_logical = 'active'"];
        $bindParams = [];

        // 1. Filtrado de búsqueda general (por nombre, puesto, tipo)
        if (!empty($filters['search'])) {
            $searchParam = "%{$filters['search']}%";
            // Utilizamos un solo placeholder (:search) en la query, pero se bindeará 3 veces.
            // Para simplificar el bind, vamos a usar placeholders únicos:
            $whereClauses[] = "(a.first_name LIKE :search_name 
                                OR s.stall_number LIKE :search_stall 
                                OR it.infraction_type_name LIKE :search_type)";
            
            $bindParams[':search_name'] = $searchParam;
            $bindParams[':search_stall'] = $searchParam;
            $bindParams[':search_type'] = $searchParam;
        }

        // 2. Filtrado por fecha de infracción (infraction_date)
        if (!empty($filters['infraction_date'])) {
            $whereClauses[] = "DATE(i.infraction_datetime) = :infraction_date";
            $bindParams[':infraction_date'] = $filters['infraction_date'];
        }

        // 3. Filtrado por estado (infraction_status)
        if (!empty($filters['infraction_status'])) {
            $whereClauses[] = "i.infraction_status = :infraction_status";
            $bindParams[':infraction_status'] = $filters['infraction_status'];
        }

        // 4. Filtrado por ID de tipo de infracción (infraction_type_id)
        if (!empty($filters['infraction_type_id'])) {
            $whereClauses[] = "i.infraction_type_id = :infraction_type_id";
            $bindParams[':infraction_type_id'] = (int)$filters['infraction_type_id'];
        }

        // 5. Filtrado por ID de puesto (stall_id)
        if (!empty($filters['stall_id'])) {
            $whereClauses[] = "i.stall_id = :stall_id";
            $bindParams[':stall_id'] = (int)$filters['stall_id'];
        }

        // 6. Filtrado por ID de adjudicatario (awardee_id)
        if (!empty($filters['awardee_id'])) {
            $whereClauses[] = "i.awardee_id = :awardee_id";
            $bindParams[':awardee_id'] = (int)$filters['awardee_id'];
        }
        
        // Construir la cláusula WHERE final
        $whereSQL = "WHERE " . implode(" AND ", $whereClauses);

        return ['whereSQL' => $whereSQL, 'bindParams' => $bindParams];
    }

    public function getAll($page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Usamos el helper para obtener los filtros y parámetros
            $conditions = $this->buildFilterConditions($filters);
            $bindParams = $conditions['bindParams'];
            $whereSQL = $conditions['whereSQL'];            

            $query = "SELECT i.*, 
                             a.id as id_adjudicatory,
                             CONCAT(a.first_name, ' ', a.last_name) as adjudicatory_name,
                             a.id_number as adjudicatory_document,
                             s.stall_number,
                             it.infraction_type_name
                      FROM " . $this->table . " i
                      LEFT JOIN awardees a ON i.awardee_id = a.id
                      LEFT JOIN market_stalls s ON i.stall_id = s.id
                      LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      {$whereSQL}
                      ORDER BY i.infraction_datetime DESC
                      LIMIT :limit OFFSET :offset";

            
            $stmt = $this->conn->prepare($query); 
           
            // Bindear los parámetros de los filtros
            foreach ($bindParams as $key => $value) {
                // Determine el tipo de parámetro, asumiendo que los IDs son INT y el resto STR
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
 
            // Bindear los parámetros de paginación
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $exception) {
            error_log("Error al obtener infracciones: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el número de infracciones de un tipo específico (infraction_type_id) 
     * para un adjudicatario dado, limitándose al año actual.
     *
     * @param PDO $pdo La conexión PDO a la base de datos.
     * @param int $awardeeId El ID del adjudicatario.
     * @param int $infractionTypeId El ID del tipo de infracción a contar.
     * @return int El total de infracciones de ese tipo en el año actual.
     */
    function contarTipoInfraccionEspecificoAnual(
        int $awardeeId, 
        int $infractionTypeId
    ): int 
    {
        // Obtener el año actual en formato 'YYYY'
        $currentYear = date('Y');

        // Consulta SQL optimizada para obtener solo el conteo
        $sql = "
            SELECT
                COUNT(infraction_id) AS total_count
            FROM
                infractions
            WHERE
                awardee_id = :awardee_id
                AND infraction_type_id = :infraction_type_id
                -- Filtrar por el año actual
                AND YEAR(infraction_datetime) = :current_year;
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            
            // Ejecutar la consulta, vinculando todos los parámetros
            $stmt->execute([
                ':awardee_id' => $awardeeId,
                ':infraction_type_id' => $infractionTypeId,
                ':current_year' => $currentYear
            ]);

            // Obtener el resultado (será una única columna con el conteo)
            $totalCount = $stmt->fetchColumn();

            return (int) $totalCount;

        } catch (PDOException $e) {
            error_log("Error al contar infracción específica: " . $e->getMessage());
            return 0; // Devolver 0 en caso de error
        }
    }

    /**
     * Calcula el monto de una multa en Bolívares (Bs.) basada en la gravedad
     * y en la legislación que compara la UT y la Moneda de Mayor Valor (Euro BCV.M).
     *
     * @param string $gravedad 'leve', 'moderada' o 'grave'.
     * @param float $tasa_euro_bcv_venta Tasa de venta del Euro publicada por el BCV.
     * @param float $ut_seniat Valor actual de la Unidad Tributaria (SENIAT).
     * @return float El monto final de la multa en Bolívares.
     */
    function calcularMultaMunicipal(
        int $nivel_gravedad, 
        float $tasa_euro_bcv_venta, 
        float $ut_seniat
    ): float {

        // 1. Definición de Parámetros de la Sanción (Según Ordenanza de referencia)
        $sanciones = [
            'leve' => [
                'rango_ut' => 10,  // Usamos el máximo del rango típico (5-10 UT)
                'multiplicador_bcvm' => 0.55 // Ejemplo: 0.55 * BCV.M
            ],
            'moderada' => [
                'rango_ut' => 50,  // Usamos el máximo del rango típico (10-50 UT)
                'multiplicador_bcvm' => 2.78 // Ejemplo: 2.78 * BCV.M
            ],
            'grave' => [
                'rango_ut' => 500, // Usamos el máximo de tu ejemplo
                'multiplicador_bcvm' => 27.89 // El multiplicador (27,89)
            ]
        ];

        $enumGravedad = ['leve', 'moderada', 'grave'];
        $gravedad = strtolower($enumGravedad[$nivel_gravedad - 1] ?? 'leve');

        if (!isset($sanciones[$gravedad])) {
            // En caso de que se pase una gravedad no válida
            throw new InvalidArgumentException("Gravedad de sanción no válida. Use 'leve', 'moderada' o 'grave'.");
        }

        $sancion = $sanciones[$gravedad];

        // --- CÁLCULO 1: Basado en la Unidad Tributaria (UT) ---
        $monto_ut = $sancion['rango_ut'] * $ut_seniat;

        // --- CÁLCULO 2: Basado en la Moneda de Mayor Valor (BCV.M) ---
        // En este caso, el Euro (€) es la Moneda de Mayor Valor.
        $monto_bcvm = $sancion['multiplicador_bcvm'] * $tasa_euro_bcv_venta;

        // --- CÁLCULO FINAL: Se aplica el monto mayor de los dos ---
        $multa_final_bs = max($monto_ut, $monto_bcvm);

        // Redondear el monto a dos decimales para el pago en Bolívares
        return round($multa_final_bs, 2);
    }   
    
    
    /**
     * Cuenta el número de infracciones de cada tipo (infraction_type_id) 
     * para un adjudicatario (awardee) dado, limitándose al año actual.
     *
     * @param PDO $pdo La conexión PDO a la base de datos.
     * @param int $awardeeId El ID del adjudicatario cuyas infracciones se van a contar.
     * @return array Un array asociativo con el conteo por tipo de infracción, 
     * ejemplo: [5 => 3, 8 => 1].
     */
    function contarInfraccionesPorTipoAnual(int $awardeeId): array 
    {
        // Obtener el año actual en formato 'YYYY'
        $currentYear = date('Y');

        // La consulta SQL
        $sql = "
            SELECT
                infraction_type_id,
                COUNT(infraction_id) AS total_infracciones
            FROM
                infractions
            WHERE
                awardee_id = :awardee_id
                -- Filtrar por el año actual de la columna infraction_datetime
                AND YEAR(infraction_datetime) = :current_year 
                -- Opcional: Solo contar las infracciones que han sido sancionadas/confirmadas
                -- AND infraction_status = 'Sanctioned' 
            GROUP BY
                infraction_type_id
            ORDER BY
                total_infracciones DESC;
        ";

        try {
            // 1. Preparar la consulta
            $stmt = $this->conn->prepare($sql);
            
            // 2. Ejecutar la consulta, vinculando los parámetros
            $stmt->execute([
                ':awardee_id' => $awardeeId,
                ':current_year' => $currentYear
            ]);

            // 3. Obtener los resultados
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Transformar los resultados en el formato deseado (infraction_type_id => count)
            $counts = [];
            foreach ($results as $row) {
                // Usamos el ID del tipo de infracción como clave
                $counts[(int) $row['infraction_type_id']] = (int) $row['total_infracciones'];
            }

            return $counts;

        } catch (PDOException $e) {
            // Manejo de errores
            error_log("Error al contar infracciones anuales: " . $e->getMessage());
            return []; // Devuelve un array vacío en caso de error
        }
    }

    /**
     * Cuenta el número de sanciones por nivel de severidad ('leve', 'moderada', 'grave') 
     * para un adjudicatario (awardee) dado.
     *
     * @param int $awardeeId El ID del adjudicatario cuyas sanciones se van a contar.
     * @return array Un array asociativo con los conteos, ejemplo: ['leve' => 3, 'moderada' => 1, 'grave' => 0].
     */
    function contarSancionesPorSeveridad(int $awardeeId): array 
    {
        // La consulta SQL une las tres tablas necesarias:
        // 1. 'infractions' para obtener el 'awardee_id'.
        // 2. 'sanctions' para enlazar la infracción con el tipo de sanción.
        // 3. 'sanction_types' para obtener el 'severity_name'.
        $sql = "
            SELECT
                st.severity_name,
                COUNT(s.sanction_id) AS total_sanciones
            FROM
                sanctions s
            JOIN
                infractions i ON s.infraction_id = i.infraction_id
            JOIN
                sanction_types st ON s.sanction_type_id = st.sanction_type_id
            WHERE
                i.awardee_id = :awardee_id
            GROUP BY
                st.severity_name
            ORDER BY
                FIELD(st.severity_name, 'leve', 'moderada', 'grave');
        ";

        try {
            // 1. Preparar la consulta para evitar inyección SQL
            $stmt = $this->conn->prepare($sql);
            
            // 2. Ejecutar la consulta, vinculando el parámetro
            $stmt->execute([':awardee_id' => $awardeeId]);

            // 3. Obtener los resultados
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Transformar los resultados en el formato deseado
            $counts = [];
            $totalSanciones = 0; // Para el conteo total

            foreach ($results as $row) {
                $severity = $row['severity_name'];
                $count = (int) $row['total_sanciones'];
                $counts[$severity] = $count;
                $totalSanciones += $count;
            }

            // 5. Asegurar que las tres categorías ('leve', 'moderada', 'grave') existan en el array,
            //    incluso si el conteo es 0, para tener un array consistente.
            $finalCounts = [
                'leve'     => $counts['leve'] ?? 0,
                'moderada' => $counts['moderada'] ?? 0,
                'grave'    => $counts['grave'] ?? 0,
                // Opcional: Incluir el conteo total
                'total'    => $totalSanciones
            ];

            return $finalCounts;

        } catch (PDOException $e) {
            // Manejo básico de errores de la base de datos
            // En un entorno de producción, es mejor registrar el error y devolver un array vacío o lanzar una excepción.
            error_log("Error al contar sanciones: " . $e->getMessage());
            return [
                'leve'     => 0,
                'moderada' => 0,
                'grave'    => 0,
                'total'    => 0
            ];
        }
    }

    /**
     * Obtiene el total de registros de infracciones, aplicando los mismos filtros.
     */
    public function countAll($filters = []) {
        try {
            // Usamos el helper para obtener los filtros y parámetros
            $conditions = $this->buildFilterConditions($filters);
            $bindParams = $conditions['bindParams'];
            $whereSQL = $conditions['whereSQL'];

            // Consulta de conteo, utiliza los mismos JOINS y WHERE
            $query = "SELECT COUNT(*) as total_records
                      FROM " . $this->table . " i
                      LEFT JOIN awardees a ON i.awardee_id = a.id
                      LEFT JOIN market_stalls s ON i.stall_id = s.id
                      LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      {$whereSQL}";
            
            $stmt = $this->conn->prepare($query); 
            
            // Bindear los parámetros de los filtros
            foreach ($bindParams as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['total_records'];

        } catch(PDOException $exception) {
            error_log("Error al contar infracciones: " . $exception->getMessage());
            return 0;
        }
    }


    
    /**
     * Obtener una infracción por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT i.* FROM " . $this->table . " i WHERE i.infraction_id = :id AND i.status_logical = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener infracción: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Obtener una infracción con todos sus detalles de tablas relacionadas
     * @param int $id
     * @return array|false
     */
    public function getInfractionDetails($id) {
        try {
            $query = "SELECT i.*, 
                             a.id as id_adjudicatory,
                             a.id as id_adjudicatory,
                             CONCAT(a.first_name, ' ', a.last_name) as adjudicatory_name,
                             a.id_number as adjudicatory_document,
                             s.stall_number,
                             s.location_description,
                             it.infraction_type_name,
                             it.description as infraction_type_description,
                             sc.sanction_id,
                             sc.fine_amount,
                             sc.sanction_status,
                             sc.fine_currency
                     FROM infractions i
                     LEFT JOIN awardees a ON i.awardee_id = a.id
                     LEFT JOIN market_stalls s ON i.stall_id = s.id
                     LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id 
                     LEFT JOIN sanctions sc ON i.infraction_id = sc.infraction_id
                     WHERE i.infraction_id = :id AND i.status_logical = 'active'";

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
     * Crear una nueva infracción
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " (
                         awardee_id,
                         stall_id,
                         infraction_type_id,
                         infraction_description,
                         infraction_status,
                         inspector_observations,
                         infraction_datetime,
                         proof,
                         status_logical
                     ) VALUES (
                         :awardee_id,
                         :stall_id,
                         :infraction_type_id,
                         :infraction_description,
                         :infraction_status,
                         :inspector_observations,
                         :infraction_datetime,
                         :proof,
                         'active'
                     )";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitizar y enlazar parámetros
            $this->awardee_id = htmlspecialchars(strip_tags($data['awardee_id']));
            $this->stall_id = htmlspecialchars(strip_tags($data['stall_id']));
            $this->infraction_type_id = htmlspecialchars(strip_tags($data['infraction_type_id']));
            $this->infraction_description = htmlspecialchars(strip_tags($data['infraction_description']));
            $this->infraction_status = htmlspecialchars(strip_tags($data['infraction_status']));
            $this->inspector_observations = htmlspecialchars(strip_tags($data['inspector_observations']));
            $this->infraction_datetime = date('Y-m-d H:i:s');
            $this->proof = $data['proof']; // No sanitizar, ya que es el nombre de archivo

            $stmt->bindParam(':awardee_id', $this->awardee_id);
            $stmt->bindParam(':stall_id', $this->stall_id);
            $stmt->bindParam(':infraction_type_id', $this->infraction_type_id);
            $stmt->bindParam(':infraction_description', $this->infraction_description);
            $stmt->bindParam(':infraction_status', $this->infraction_status);
            $stmt->bindParam(':inspector_observations', $this->inspector_observations);
            $stmt->bindParam(':infraction_datetime', $this->infraction_datetime);
            $stmt->bindParam(':proof', $this->proof);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Infracción creada exitosamente.',
                    'id' => $this->conn->lastInsertId()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al crear la infracción.'
            ];

        } catch(PDOException $exception) {
            error_log("Error al crear infracción: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Actualizar una infracción existente
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET awardee_id = :awardee_id,
                         stall_id = :stall_id,
                         infraction_type_id = :infraction_type_id,
                         infraction_description = :infraction_description,
                         infraction_status = :infraction_status,
                         inspector_observations = :inspector_observations,
                         proof = :proof
                     WHERE infraction_id = :id
                     AND status_logical = 'active'";
            
            $stmt = $this->conn->prepare($query);

            // Sanitizar y enlazar parámetros
            $this->awardee_id = htmlspecialchars(strip_tags($data['awardee_id']));
            $this->stall_id = htmlspecialchars(strip_tags($data['stall_id']));
            $this->infraction_type_id = htmlspecialchars(strip_tags($data['infraction_type_id']));
            $this->infraction_description = htmlspecialchars(strip_tags($data['infraction_description']));
            $this->infraction_status = htmlspecialchars(strip_tags($data['infraction_status']));
            $this->inspector_observations = htmlspecialchars(strip_tags($data['inspector_observations']));
            $this->proof = $data['proof'];
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':awardee_id', $this->awardee_id);
            $stmt->bindParam(':stall_id', $this->stall_id);
            $stmt->bindParam(':infraction_type_id', $this->infraction_type_id);
            $stmt->bindParam(':infraction_description', $this->infraction_description);
            $stmt->bindParam(':infraction_status', $this->infraction_status);
            $stmt->bindParam(':inspector_observations', $this->inspector_observations);
            $stmt->bindParam(':proof', $this->proof);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Infracción actualizada exitosamente.'
                ];               
            } 
            
            return [
                'success' => false,
                'message' => 'Error al actualizar la infracción.'
            ];

        } catch(PDOException $exception) {
            error_log("Error al actualizar infracción: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Eliminar lógicamente una infracción
     * @param int $id
     * @return array
     */
    public function logicalDelete($id) {
        try {
            // Verificar si la infracción existe y no está ya eliminada
            $checkQuery = "SELECT infraction_id FROM " . $this->table . " WHERE infraction_id = :id AND status_logical = 'active'";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'La infracción no existe o ya ha sido eliminada.'
                ];
            }

            $query = "UPDATE " . $this->table . " SET status_logical = 'deleted' WHERE infraction_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Infracción eliminada lógicamente de forma exitosa.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al eliminar lógicamente la infracción.'
            ];
        } catch(PDOException $exception) {
            error_log("Error al eliminar lógicamente infracción: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    public function countInfractionsByMode($startDate, $endDate, $mode = 'day') {
        // 1. Definir la lógica de agrupación y la etiqueta (label)
        $grouping = '';
        $labelSelect = '';
        
        switch ($mode) {
            case 'month':
                // Agrupa por Mes y Año
                $grouping = 'DATE_FORMAT(infraction_datetime, "%Y-%m")'; 
                $labelSelect = 'DATE_FORMAT(infraction_datetime, "%m/%Y") AS label';
                break;
            case 'week':
                // Agrupa por el número de semana del año (Usando 3 para ISO 8601: Lunes es el primer día)
                $grouping = 'YEAR(infraction_datetime), WEEK(infraction_datetime, 3)';
                $labelSelect = 'CONCAT("Semana ", WEEK(infraction_datetime, 3), " - ", YEAR(infraction_datetime)) AS label';
                break;
            case 'day':
            default:
                // Agrupa por Día
                $grouping = 'DATE(infraction_datetime)';
                $labelSelect = 'DATE_FORMAT(infraction_datetime, "%d/%m/%Y") AS label';
                break;
        }

        // 2. Construir la consulta SQL
        $sql = "
            SELECT 
                {$labelSelect},
                COUNT(infraction_id) AS count
            FROM 
                {$this->table} -- Usamos la propiedad $this->table
            WHERE 
                status_logical = 'active'
                AND DATE(infraction_datetime) BETWEEN :start_date AND :end_date
            GROUP BY 
                {$grouping}
            ORDER BY 
                infraction_datetime ASC
        ";

        try {
            // 3. Preparar y ejecutar la consulta usando $this->conn
            $stmt = $this->conn->prepare($sql);
            
            // Asignar parámetros
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            
            $stmt->execute();
            
            // Devolver los resultados
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Manejo de errores
            error_log("Error al contar infracciones por modo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las infracciones activas (No canceladas) de un adjudicatario.
     * @param int $awardeeId
     * @return array
     */
    public function getInfractionsByAwardee($awardeeId) {
        try {
            $query = "SELECT 
                        i.infraction_id, 
                        i.infraction_description, 
                        i.infraction_datetime, 
                        it.infraction_type_name
                      FROM " . $this->table . " i
                      LEFT JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                      WHERE i.awardee_id = :awardee_id 
                      AND i.status_logical = 'active'
                      AND i.infraction_status != 'Cancelled'
                      ORDER BY i.infraction_datetime DESC";
                      
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':awardee_id', $awardeeId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener infracciones por adjudicatario: " . $exception->getMessage());
            return [];
        }
    }
    
    /**
     * Cancela una infracción estableciendo su estado a 'Cancelled'.
     * @param int $infractionId
     * @return bool
     */
    public function cancelInfraction($infractionId) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET infraction_status = 'Cancelled' 
                      WHERE infraction_id = :infraction_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':infraction_id', $infractionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return true;
        } catch(PDOException $exception) {
            error_log("Error al cancelar infracción: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado de una infracción
     * @param int $infractionId
     * @param string $status
     * @return bool
     */
    public function updateStatus($infractionId, $status) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET infraction_status = :status 
                      WHERE infraction_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $infractionId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando estado de infracción: " . $e->getMessage());
            return false;
        }
    }

}