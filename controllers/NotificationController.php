<?php
// Requerir los modelos necesarios
require_once __DIR__. '/../models/NotificationModel.php';

class NotificationController {

    private $notificationModel;

    public function __construct() {
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Acción para crear una nueva notificación.
     *
     * @param int|null $senderUserId ID del usuario que envía (null si es el sistema).
     * @param int $recipientUserId ID del usuario que recibe.
     * @param string $type Tipo de notificación.
     * @param string $subject Asunto de la notificación.
     * @param string $message Mensaje completo de la notificación.
     * @param int|null $complaintId ID de la queja asociada, si aplica.
     * @param int|null $alertId ID de la alerta asociada, si aplica.
     * @param int|null $infractionId ID de la infracción asociada, si aplica.
     * @return int|bool El ID de la notificación insertada o false en caso de error.
     */
    public function createNotification(
        ?int $senderUserId,
        int $recipientUserId,
        string $type,
        string $subject,
        string $message,
        ?int $complaintId = null,
        ?int $alertId = null,
        ?int $infractionId = null
    ): int|bool {
        return $this->notificationModel->insertNotification(
            $senderUserId,
            $recipientUserId,
            $type,
            $subject,
            $message,
            $complaintId,
            $alertId,
            $infractionId
        );
    }
    /**
     * Envía una notificación a todos los usuarios que pertenecen a un rol específico.
     *
     * @param string $roleName Nombre del rol.
     * @param string $message Mensaje de la notificación.
     * @param string $type Tipo de notificación (por defecto 'system_alert').
     * @param string $subject Asunto (opcional).
     * @return bool True si se procesó correctamente.
     */
    public function sendNotificationToRole(string $roleName, string $message, string $type = 'system_alert', string $subject = 'Notificación del Sistema'): bool {
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $users = $userModel->getUsersByRoleName($roleName);

        if (empty($users)) {
            return false;
        }

        foreach ($users as $userId) {
            $this->createNotification(
                null, // Sender (System)
                (int)$userId,
                $type,
                $subject,
                $message
            );
        }

        return true;
    }
}