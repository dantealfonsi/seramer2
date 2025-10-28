<?php
require_once __DIR__ . '/../config/Database.php';

class NotificationModel {

    private $conn;

    public function __construct() {
        // Inicializar la conexión a la base de datos
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    /**
     * Inserta un nuevo registro en la tabla 'notifications'.
     *
     * @param int|null $senderUserId ID del usuario que envía (null si es el sistema).
     * @param int $recipientUserId ID del usuario que recibe.
     * @param string $type Tipo de notificación (ej: 'infraction_new', 'complaint_resolved', 'system_alert').
     * @param string $subject Asunto de la notificación.
     * @param string $message Mensaje completo de la notificación.
     * @param int|null $complaintId ID de la queja asociada, si aplica.
     * @param int|null $alertId ID de la alerta asociada, si aplica.
     * @param int|null $infractionId ID de la infracción asociada, si aplica.
     * @return int|bool El ID de la notificación insertada o false en caso de error.
     */
    public function insertNotification(
        ?int $senderUserId,
        int $recipientUserId,
        string $type,
        string $subject,
        string $message,
        ?int $complaintId = null,
        ?int $alertId = null,
        ?int $infractionId = null
    ): int|bool {

        $sql = "
            INSERT INTO notifications (
                sender_user_id,
                recipient_user_id,
                notification_type,
                notification_subject,
                notification_message,
                complaint_id,
                alert_id,
                infraction_id
            )
            VALUES (
                :sender_user_id,
                :recipient_user_id,
                :notification_type,
                :notification_subject,
                :notification_message,
                :complaint_id,
                :alert_id,
                :infraction_id
            );
        ";

        try {
            $stmt = $this->conn->prepare($sql);

            // 1. Vinculación de parámetros
            $stmt->bindParam(':sender_user_id', $senderUserId, $senderUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':recipient_user_id', $recipientUserId, PDO::PARAM_INT);
            $stmt->bindParam(':notification_type', $type, PDO::PARAM_STR);
            $stmt->bindParam(':notification_subject', $subject, PDO::PARAM_STR);
            $stmt->bindParam(':notification_message', $message, PDO::PARAM_STR);
            
            // 2. Manejo de IDs de entidades asociadas (pueden ser NULL)
            $stmt->bindParam(':complaint_id', $complaintId, $complaintId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':alert_id', $alertId, $alertId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':infraction_id', $infractionId, $infractionId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            // 3. Ejecutar
            if ($stmt->execute()) {
                // Devolver el ID de la notificación recién insertada
                return (int)$this->conn->lastInsertId();
            }

            return false;

        } catch (PDOException $e) {
            // Registrar el error
            error_log("Error al insertar notificación: " . $e->getMessage());
            return false;
        }
    }
}