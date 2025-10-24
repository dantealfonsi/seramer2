<?php
// Incluye tu clase Database
require_once __DIR__ . '/../../config/Database.php';

// === Inicialización ===
// Instancia la clase Database
$db = new Database();

// **IMPORTANTE**: Obtén la ID del usuario logueado. ¡AJUSTA ESTO A TU SESIÓN!
// EJEMPLO REAL: $current_user_id = $_SESSION['user_id'] ?? null;
// Para la demo, mantenemos el ID 1.
$current_user_id = 1; 

// Comprobación básica de autenticación
if (!$current_user_id) {
    http_response_code(401); // No autorizado
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit();
}

// === Lógica de Routing ===
if (!isset($_GET['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Acción no especificada.']);
    exit();
}

$action = $_GET['action'];
header('Content-Type: application/json');

switch ($action) {
    case 'count':
        // ===================================
        // A. Obtener Contador de Notificaciones
        // ===================================
        $query = "
            SELECT COUNT(notification_id) AS count 
            FROM notifications 
            WHERE recipient_user_id = ? 
            AND read_status = 0
        ";
        
        $result = $db->fetchOne($query, [$current_user_id]);
        
        // El método fetchOne retorna null si no hay resultado.
        $count = $result ? (int)$result['count'] : 0;
        
        echo json_encode(['count' => $count]);
        break;

    case 'fetch_and_mark':
        // ===================================
        // B. Obtener Lista y Marcar Leídas
        // ===================================
        try {
            // Acceder a la conexión PDO para la transacción
            $pdo = $db->getConnection();
            $pdo->beginTransaction();

            // 1. Marcar todas las notificaciones no leídas como leídas (read_status = 1)
            $query_update = "
                UPDATE notifications 
                SET read_status = 1 
                WHERE recipient_user_id = ? 
                AND read_status = 0
            ";
            $db->executeQuery($query_update, [$current_user_id]);

            // 2. Obtener la lista de notificaciones (15 más recientes)
            $query_select = "
                SELECT 
                    notification_id, 
                    notification_date, 
                    notification_subject,
                    notification_message,
                    read_status,
                    notification_type,
                    COALESCE(complaint_id, alert_id, infraction_id) as related_id 
                FROM notifications 
                WHERE recipient_user_id = ? 
                ORDER BY notification_date DESC 
                LIMIT 15
            ";
            $notifications = $db->fetchAll($query_select, [$current_user_id]);

            $pdo->commit();

            // 3. Formatear y generar enlaces (lógica de presentación)
            $formatted_notifications = array_map(function($n) {
                $type = strtolower($n['notification_type']);
                $n['link'] = '/details.php?id=' . $n['related_id'] . '&type=' . $type;
                $n['date_friendly'] = (new DateTime($n['notification_date']))->format('d M H:i');
                return $n;
            }, $notifications);

            echo json_encode(['notifications' => $formatted_notifications]);

        } catch (\PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // En caso de error, siempre cerramos la conexión si es necesario
            $db->closeConnection();
            http_response_code(500);
            echo json_encode(['error' => 'Error de Transacción de BD: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción desconocida.']);
        break;
}
?>