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

    case 'fetch':
        // 1. Obtener lista reciente (Máximo 50)
        $notifications = $notificationModel->getUserNotifications($current_user_id, 50);

        // 2. Formatear
        $formatted_notifications = array_map(function($n) {
            $type = strtolower($n['notification_type']);

            // Determinar ID relacionado — prioridad: sanction > citation > complaint > alert > infraction
            $related_id = $n['sanction_id'] ?? $n['citation_id'] ?? $n['complaint_id'] ?? $n['alert_id'] ?? $n['infraction_id'] ?? 0;

            // PARCHE: Si el ID es 0, intentamos extraerlo del mensaje (ej: "Sanción #123")
            if ($related_id == 0 && preg_match('/#(\d+)/', $n['notification_message'], $matches)) {
                $related_id = $matches[1];
            }

            // Mapeo explícito de tipos de notificación → entidad del router
            $typeMap = [
                'sanction_new'           => 'sanction',
                'sanction_paid'          => 'sanction',
                'fine_payment_received'  => 'sanction',
                'fine_paid'              => 'sanction',
                'infraction_new'         => 'infraction',
                'infraction_update'      => 'infraction',
                'complaint_new'          => 'complaint',
                'complaint_update'       => 'complaint',
                'citation_new'           => 'citation',
                'citation_update'        => 'citation',
                'alert_new'              => 'alert',
                'alert_update'           => 'alert',
                'exchange_rate_update'   => null, // sin vista dedicada
            ];

            // Determinar entidad final
            if (isset($typeMap[$type])) {
                $entity_type = $typeMap[$type];
            } elseif (!empty($n['sanction_id'])) {
                $entity_type = 'sanction';
            } elseif (!empty($n['citation_id'])) {
                $entity_type = 'citation';
            } elseif (!empty($n['complaint_id'])) {
                $entity_type = 'complaint';
            } elseif (!empty($n['alert_id'])) {
                $entity_type = 'alert';
            } elseif (!empty($n['infraction_id'])) {
                $entity_type = 'infraction';
            } else {
                // Fallback: primera parte del tipo
                $parts = explode('_', $type);
                $entity_type = $parts[0];
            }

            $type = $entity_type ?? $type;
            
            // Construir enlace incluyendo notif_id
            $n['link'] = url('public/utils/notifications/details.php?id=' . $related_id . '&type=' . $type . '&notif_id=' . $n['notification_id']);
            
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