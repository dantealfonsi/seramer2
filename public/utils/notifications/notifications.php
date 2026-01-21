<?php
// CORS Headers para evitar bloqueos si hay mismatch de puertos/dominios
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluye la configuración global y el Modelo
require_once __DIR__ . '/../../../config/app.php';
require_once __DIR__ . '/../../../models/NotificationModel.php';

// Verificación de autenticación
$current_user_id = $_SESSION['user_id'] ?? null;

// Si no hay sesión, intentar ID 1 para desarrollo (solo si DEBUG es true o similar, pero dejaremos el fallback del usuario original por ahora)
if (!$current_user_id) {
    // Fallback original del usuario:
    $current_user_id = 1; 
}

if (!$current_user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit();
}

$notificationModel = new NotificationModel();
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'count':
        $count = $notificationModel->getUnreadCount($current_user_id);
        echo json_encode(['count' => $count]);
        break;

    case 'fetch_and_mark':
        // 1. Marcar todas como leídas
        $notificationModel->markAllAsRead($current_user_id);

        // 2. Obtener lista reciente
        $notifications = $notificationModel->getUserNotifications($current_user_id, 15);

        // 3. Formatear
        $formatted_notifications = array_map(function($n) {
            $type = strtolower($n['notification_type']);
            // Determinar ID relacionado
            $related_id = $n['citation_id'] ?? $n['complaint_id'] ?? $n['alert_id'] ?? $n['infraction_id'] ?? 0;
            
            // Si es una citación, forzar el tipo para el router si el notification_type no empieza por 'citation'
            // (por si acaso se usan otros tipos que deban ir a citaciones)
            if (!empty($n['citation_id'])) {
                $type = 'citation';
            }
            
            // Construir enlace
            $n['link'] = url('public/utils/notifications/details.php?id=' . $related_id . '&type=' . $type);
            
            // Formatear fecha
            try {
                $n['date_friendly'] = (new DateTime($n['notification_datetime']))->format('d M H:i');
            } catch (Exception $e) {
                $n['date_friendly'] = $n['notification_datetime'];
            }
            return $n;
        }, $notifications);

        echo json_encode([
            'notifications' => $formatted_notifications,
            'current_user_id' => $current_user_id
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción desconocida.']);
        break;
}
?>