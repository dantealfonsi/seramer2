<?php
/**
 * Modelo ActivityLog
 * 
 * Gestiona el historial de actividades de usuarios
 * Consulta la tabla audit_log existente
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;

class ActivityLogModel extends Model {
    protected string $table = 'audit_log';
    
    /**
     * Obtiene todas las actividades con información del usuario
     * 
     * @param array $filters Filtros opcionales (user_id, action, table_affected, date_from, date_to)
     * @param int $limit Límite de resultados
     * @param int $offset Offset para paginación
     * @return array Lista de actividades
     */
    public function getAll(array $filters = [], int $limit = 50, int $offset = 0): array {
        try {
            $query = "SELECT al.id,
                            al.user_id,
                            al.action,
                            al.table_affected,
                            al.record_id,
                            al.old_values,
                            al.new_values,
                            al.ip_address,
                            al.user_agent,
                            al.created_at,
                            COALESCE(u.username, 'N/A') as username,
                            COALESCE(u.email, '') as email,
                            CASE 
                                WHEN s.first_name IS NOT NULL AND s.last_name IS NOT NULL 
                                    AND TRIM(CONCAT(s.first_name, ' ', s.last_name)) != '' 
                                THEN CONCAT(s.first_name, ' ', s.last_name)
                                WHEN u.username IS NOT NULL AND u.username != '' THEN u.username
                                ELSE CONCAT('Usuario #', al.user_id)
                            END as user_name
                    FROM audit_log al
                    LEFT JOIN users u ON al.user_id = u.id
                    LEFT JOIN staff s ON u.staff_id = s.id
                    WHERE 1=1";
            
            $params = [];
            
            // Aplicar filtros
            if (!empty($filters['user_id'])) {
                $query .= " AND al.user_id = :user_id";
                $params['user_id'] = (int) $filters['user_id'];
            }
            
            if (!empty($filters['action'])) {
                $query .= " AND al.action = :action";
                $params['action'] = $filters['action'];
            }
            
            if (!empty($filters['table_affected'])) {
                $query .= " AND al.table_affected = :table_affected";
                $params['table_affected'] = $filters['table_affected'];
            }
            
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(al.created_at) >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(al.created_at) <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            $query .= " ORDER BY al.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            
            $results = $this->query($query, $params);
            
            // Si no hay resultados, intentar consulta simple sin JOIN para debug
            if (empty($results) && empty($filters)) {
                $simpleQuery = "SELECT * FROM audit_log ORDER BY created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
                $simpleResults = $this->query($simpleQuery);
                if (!empty($simpleResults)) {
                    // El problema está en el JOIN, usar datos simples
                    foreach ($simpleResults as &$row) {
                        $row['username'] = 'N/A';
                        $row['email'] = '';
                        $row['user_name'] = 'Usuario #' . $row['user_id'];
                    }
                    return $simpleResults;
                }
            }
            
            return $results;
        } catch (\Exception $e) {
            error_log("Error en ActivityLogModel::getAll: " . $e->getMessage());
            error_log("Query: " . $query ?? 'N/A');
            return [];
        }
    }
    
    /**
     * Obtiene el total de registros con los filtros aplicados
     * 
     * @param array $filters Filtros opcionales
     * @return int Total de registros
     */
    public function getTotal(array $filters = []): int {
        $query = "SELECT COUNT(*) as total
                  FROM audit_log al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        // Aplicar filtros (mismos que getAll)
        if (!empty($filters['user_id'])) {
            $query .= " AND al.user_id = :user_id";
            $params['user_id'] = (int) $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $query .= " AND al.action = :action";
            $params['action'] = $filters['action'];
        }
        
        if (!empty($filters['table_affected'])) {
            $query .= " AND al.table_affected = :table_affected";
            $params['table_affected'] = $filters['table_affected'];
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(al.created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(al.created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $result = $this->queryOne($query, $params);
        return (int) ($result['total'] ?? 0);
    }
    
    /**
     * Obtiene las acciones únicas disponibles
     * 
     * @return array Lista de acciones
     */
    public function getDistinctActions(): array {
        $query = "SELECT DISTINCT action FROM audit_log ORDER BY action ASC";
        $results = $this->query($query);
        return array_column($results, 'action');
    }
    
    /**
     * Obtiene las tablas únicas disponibles
     * 
     * @return array Lista de tablas
     */
    public function getDistinctTables(): array {
        $query = "SELECT DISTINCT table_affected FROM audit_log ORDER BY table_affected ASC";
        $results = $this->query($query);
        return array_column($results, 'table_affected');
    }
}

