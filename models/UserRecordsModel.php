<?php
// models/UserRecordsModel.php
require_once __DIR__ . '/../config/Database.php';

class UserRecordsModel {
    private $db;
    private $table = 'user_records';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtiene registros de actividad con información de usuario y departamento.
     * @param array $filters Opciones de filtrado (e.g., user_id, department_id, date_range).
     * @return array Registros de actividad.
     */
/**
     * Obtiene registros de actividad con información de usuario y departamento.
     * @param array $filters Opciones de filtrado (e.g., user_id, department_id, date_range).
     * @return array Registros de actividad.
     */
    public function getRecords($filters = []): array 
    {
        // La consulta une la tabla de records con la de usuarios y departamentos
        $query = "
            SELECT 
                ur.id, 
                ur.action, 
                u.username, 
                d.name AS department_name, 
                ur.created_at
            FROM 
                {$this->table} ur
            JOIN 
                users u ON ur.user_id = u.id
            LEFT JOIN 
                user_departments ud ON ur.user_id = ud.user_id AND ud.status = 'active'
            LEFT JOIN 
                departments d ON ud.department_id = d.id
        ";
        
        $binds = [];
        $conditions = [];

        // 1. Filtrar por Usuario Específico
        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $conditions[] = "ur.user_id = :user_id";
            $binds[':user_id'] = $filters['user_id'];
        }
        
        // 2. Filtrar por Rango de Fechas (usando created_at)
        if (isset($filters['start_date']) && !empty($filters['start_date'])) {
            // Buscamos registros CREADOS EN o DESPUÉS de la fecha de inicio
            $conditions[] = "ur.created_at >= :start_date_time";
            // Para incluir el día completo, se asume que la vista de fecha es 'YYYY-MM-DD'
            $binds[':start_date_time'] = $filters['start_date'] . ' 00:00:00'; 
        }
        
        if (isset($filters['end_date']) && !empty($filters['end_date'])) {
            // Buscamos registros CREADOS EN o ANTES de la fecha de fin
            $conditions[] = "ur.created_at <= :end_date_time";
            // Para incluir el día completo, se añade el último segundo del día
            $binds[':end_date_time'] = $filters['end_date'] . ' 23:59:59'; 
        }

        // --- CONSTRUCCIÓN DE LA CONSULTA ---
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $query .= " ORDER BY ur.created_at DESC"; // Ordenar por la más reciente

        // --- EJECUCIÓN ---
        
        // Asumiendo que $this->db tiene un método 'query' y 'bind' (PDO Wrapper)
        $this->db->query($query);
        
        foreach ($binds as $key => $value) {
            $this->db->bind($key, $value);
        }

        // Se usa un bloque try-catch simple si es posible, o se maneja el error
        // Asumiendo que $this->db->resultSet() devuelve un array vacío [] si no hay resultados.
        
        // El analizador estático ya no se quejará, pues hay un return al final del método.
        return $this->db->resultSet();
    }
}