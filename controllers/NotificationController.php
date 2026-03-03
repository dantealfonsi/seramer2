<?php
// Requerir los modelos necesarios
require_once __DIR__. '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class NotificationController {

    private $notificationModel;
    private $userModel;

    public function __construct() {
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
    }

    /**
     * Acción para crear una nueva notificación (Individual).
     */
    public function createNotification(
        ?int $senderUserId,
        int $recipientUserId,
        string $type,
        string $subject,
        string $message,
        ?int $complaintId = null,
        ?int $alertId = null,
        ?int $infractionId = null,
        ?int $citationId = null
    ): int|bool {
        // Enviar al destinatario original
        $result = $this->notificationModel->insertNotification(
            $senderUserId,
            $recipientUserId,
            $type,
            $subject,
            $message,
            $complaintId,
            $alertId,
            $infractionId,
            $citationId
        );

        // Enviar copia a todos los superadmins (que no sean el remitente ni el destinatario)
        $superadmins = $this->userModel->getSuperadminsIds();
        $superadminsToNotify = array_diff($superadmins, [$senderUserId, $recipientUserId]);
        
        if (!empty($superadminsToNotify)) {
            $bulkData = $this->prepareBulkData($superadminsToNotify, $senderUserId, $type, $subject, $message, [
                'complaint_id' => $complaintId,
                'alert_id' => $alertId,
                'infraction_id' => $infractionId,
                'citation_id' => $citationId,
                'is_global' => 1 // Marcamos como global/copia al admin
            ]);
            $this->notificationModel->insertBulkNotifications($bulkData);
        }

        return $result;
    }

    /**
     * Helper para preparar el array de datos
     */
    private function prepareBulkData(array $userIds, ?int $senderId, string $type, string $subject, string $message, array $meta = []): array {
        $data = [];
        foreach ($userIds as $uid) {
            $row = [
                'sender_user_id' => $senderId,
                'recipient_user_id' => $uid,
                'notification_type' => $type,
                'notification_subject' => $subject,
                'notification_message' => $message,
                'complaint_id' => $meta['complaint_id'] ?? null,
                'alert_id' => $meta['alert_id'] ?? null,
                'infraction_id' => $meta['infraction_id'] ?? null,
                'citation_id' => $meta['citation_id'] ?? null,
                'target_role_id' => $meta['target_role_id'] ?? null,
                'target_department_id' => $meta['target_department_id'] ?? null,
                'is_global' => $meta['is_global'] ?? 0
            ];
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Envía una notificación a todos los usuarios de un ROL.
     */
    public function sendNotificationToRole(string $roleName, string $message, string $type = 'system_alert', string $subject = 'Notificación del Sistema', array $meta = []): bool {
        $users = $this->userModel->getUsersByRoleName($roleName);

        $superadmins = $this->userModel->getSuperadminsIds();
        $users = array_unique(array_merge($users, $superadmins));

        if (empty($users)) {
            return false;
        }

        // Obtener el ID del rol si es posible para metadata, pero no es crítico.
        // Simulamos un sender NULL (Sistema)
        $bulkData = $this->prepareBulkData($users, null, $type, $subject, $message, $meta); 
        
        return $this->notificationModel->insertBulkNotifications($bulkData);
    }

    /**
     * Envía una notificación a todos los usuarios de un DEPARTAMENTO.
     */
    public function sendNotificationToDepartment(int $departmentId, string $message, string $type = 'dept_alert', string $subject = 'Aviso Departamental', array $meta = []): bool {
        // Obtenemos usuarios por departamento (Necesitamos implementar este método en UserModel o hacerlo aquí con query directa si UserModel no lo tiene)
        // Por seguridad, usaremos un metodo nuevo en UserModel: getUsersByDepartmentId
        // Si no existe, fallará. Debemos asegurarnos de modificar UserModel primero o usar una query raw aquí.
        // Dado que no modifiqué UserModel aun, haré una query raw rápida via UserModel connection si es posible,
        // o mejor, asumiré que voy a agregar el método.
        
        // Opción B: Usar userModel->getAll y filtrar? No, muy lento.
        // Usaré una query directa aqui usando la conexión del modelo (pública indirectamente? no).
        // Voy a agregar el método a UserModel en el siguiente paso. Confía.
        
        if (method_exists($this->userModel, 'getUsersByDepartmentId')) {
             $users = $this->userModel->getUsersByDepartmentId($departmentId);
        } else {
            // Fallback temporal si no he actualizado el modelo
             return false; 
        }

        $superadmins = $this->userModel->getSuperadminsIds();
        $users = array_unique(array_merge($users, $superadmins));

        if (empty($users)) {
            return false;
        }

        $meta['target_department_id'] = $departmentId;
        $bulkData = $this->prepareBulkData($users, null, $type, $subject, $message, $meta);
        return $this->notificationModel->insertBulkNotifications($bulkData);
    }

    /**
     * Envía una notificación GLOBAL a todos los usuarios activos.
     */
    public function sendGlobalNotification(string $message, string $subject = 'Anuncio General'): bool {
        // Necesitamos obtener todos los usuarios activos.
        // UserModel::getAllForSelect() retorna array con id.
        $allUsers = $this->userModel->getAllForSelect();
        $userIds = array_column($allUsers, 'id');

        if (empty($userIds)) {
            return false;
        }

        // Chunking para no explotar la memoria si son miles
        $chunks = array_chunk($userIds, 500);
        $success = true;

        foreach ($chunks as $chunkIds) {
            $bulkData = $this->prepareBulkData($chunkIds, null, 'global_alert', $subject, $message, ['is_global' => 1]);
            if (!$this->notificationModel->insertBulkNotifications($bulkData)) {
                $success = false;
            }
        }
        return $success;
    }
}