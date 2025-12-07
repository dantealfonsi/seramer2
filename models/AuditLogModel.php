<?php
require_once __DIR__ . '/Model.php';

class AuditLogModel extends Model {
    protected $table = 'audit_log';

    /**
     * Registrar una actividad en el log
     */
    public function log($user_id, $action, $table_affected, $record_id, $old_values, $new_values) {
        $sql = "INSERT INTO audit_log (user_id, action, table_affected, record_id, old_values, new_values, created_at, ip_address, user_agent) 
                VALUES (:user_id, :action, :table_affected, :record_id, :old_values, :new_values, NOW(), :ip, :ua)";
        
        $params = [
            'user_id' => $user_id,
            'action' => $action,
            'table_affected' => $table_affected,
            'record_id' => $record_id,
            'old_values' => $old_values,
            'new_values' => $new_values,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Obtiene todas las actividades con información del usuario
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
            
            return $this->query($query, $params);
        } catch (Exception $e) {
            error_log("Error en AuditLogModel::getAll: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotal(array $filters = []): int {
        $query = "SELECT COUNT(*) as total FROM audit_log al WHERE 1=1";
        $params = [];
        
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
}
