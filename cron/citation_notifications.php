<?php
/**
 * Script CRON para enviar notificaciones de citaciones programadas
 * Ejecutar cada hora: 0 * * * * php /path/to/citation_notifications.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/CitationsModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';

// Obtener citaciones de hoy
$citationsModel = new CitationsModel();
$todaysCitations = $citationsModel->getTodaysCitations();

if (!empty($todaysCitations)) {
    $userModel = new UserModel();
    $notificationModel = new NotificationModel();
    $fiscalizacionDeptId = 3;
    
    // Obtener usuarios de Fiscalización
    $fiscalizacionUsers = $userModel->getUsersByDepartment($fiscalizacionDeptId);
    
    foreach ($todaysCitations as $citation) {
        $citationTime = date('H:i', strtotime($citation['citation_datetime']));
        
        // Preparar notificaciones
        $notifications = [];
        foreach ($fiscalizacionUsers as $userId) {
            $notifications[] = [
                'sender_user_id' => null,
                'recipient_user_id' => $userId,
                'notification_type' => 'citation_reminder',
                'notification_subject' => 'Citación Programada para Hoy',
                'notification_message' => "Recordatorio: Citación #{$citation['citation_id']} programada para hoy a las {$citationTime}. Infracción #{$citation['infraction_id']}.",
                'complaint_id' => null,
                'alert_id' => null,
                'infraction_id' => $citation['infraction_id'],
                'target_role_id' => null,
                'target_department_id' => $fiscalizacionDeptId,
                'is_global' => 0
            ];
        }
        
        $notificationModel->insertBulkNotifications($notifications);
    }
    
    echo "Notificaciones enviadas para " . count($todaysCitations) . " citaciones.\n";
}

// Cancelar citaciones vencidas (más de 3 horas)
$cancelled = $citationsModel->cancelOverdueCitations();
if ($cancelled) {
    echo "Citaciones vencidas canceladas.\n";
}
