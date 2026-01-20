<?php
require_once __DIR__ . '/../config/Database.php';

class NotificationModel {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Inserta un nuevo registro en la tabla 'notifications'.
     */
    public function insertNotification(
        ?int $senderUserId,
        ?int $recipientUserId,
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
                infraction_id,
                notification_datetime,
                read_status
            )
            VALUES (
                :sender_user_id,
                :recipient_user_id,
                :notification_type,
                :notification_subject,
                :notification_message,
                :complaint_id,
                :alert_id,
                :infraction_id,
                NOW(),
                0
            );
        ";

        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(':sender_user_id', $senderUserId, $senderUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':recipient_user_id', $recipientUserId, $recipientUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':notification_type', $type, PDO::PARAM_STR);
            $stmt->bindValue(':notification_subject', $subject, PDO::PARAM_STR);
            $stmt->bindValue(':notification_message', $message, PDO::PARAM_STR);
            
            $stmt->bindValue(':complaint_id', $complaintId, $complaintId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':alert_id', $alertId, $alertId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':infraction_id', $infractionId, $infractionId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->conn->lastInsertId();
            }

            return false;

        } catch (PDOException $e) {
            error_log("Error al insertar notificación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserta múltiples notificaciones masivamente (Optimizado).
     * @param array $notificationsData Array de arrays asociativos con datos para cada notificación.
     * @return bool
     */
    public function insertBulkNotifications(array $notificationsData): bool {
        if (empty($notificationsData)) {
            return false;
        }

        // Preparamos la parte de VALUES para la inserción múltiple
        // (sender, recipient, type, subject, message, complaint, alert, infraction, target_role, target_dept, is_global, date, read)
        $values = [];
        $params = [];
        $i = 0;

        foreach ($notificationsData as $data) {
            $values[] = "(:sender_$i, :recipient_$i, :type_$i, :subject_$i, :message_$i, :complaint_$i, :alert_$i, :infraction_$i, :role_$i, :dept_$i, :global_$i, NOW(), 0)";
            
            $params[":sender_$i"] = $data['sender_user_id'] ?? null;
            $params[":recipient_$i"] = $data['recipient_user_id'] ?? null;
            $params[":type_$i"] = $data['notification_type'];
            $params[":subject_$i"] = $data['notification_subject'];
            $params[":message_$i"] = $data['notification_message'];
            
            $params[":complaint_$i"] = $data['complaint_id'] ?? null;
            $params[":alert_$i"] = $data['alert_id'] ?? null;
            $params[":infraction_$i"] = $data['infraction_id'] ?? null;

            // Extra metadata columns
            $params[":role_$i"] = $data['target_role_id'] ?? null;
            $params[":dept_$i"] = $data['target_department_id'] ?? null;
            $params[":global_$i"] = $data['is_global'] ?? 0;

            $i++;
        }

        $sql = "INSERT INTO notifications (
            sender_user_id, recipient_user_id, notification_type, notification_subject, notification_message, 
            complaint_id, alert_id, infraction_id, target_role_id, target_department_id, is_global, notification_datetime, read_status
        ) VALUES " . implode(', ', $values);

        try {
            $stmt = $this->conn->prepare($sql);
            
            // Bind manual
            foreach ($params as $key => $val) {
                // Determine types roughly
                if (is_int($val)) $type = PDO::PARAM_INT;
                elseif (is_bool($val)) $type = PDO::PARAM_BOOL;
                elseif (is_null($val)) $type = PDO::PARAM_NULL;
                else $type = PDO::PARAM_STR;
                
                $stmt->bindValue($key, $val, $type);
            }

            return $stmt->execute();

        } catch (PDOException $e) {
             error_log("Error en insertBulkNotifications: " . $e->getMessage());
             return false;
        }
    }

    public function getUnreadCount(int $userId): int {
        $sql = "SELECT COUNT(notification_id) as count FROM notifications WHERE recipient_user_id = :uid AND read_status = 0";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['count'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function markAllAsRead(int $userId): bool {
        $sql = "UPDATE notifications SET read_status = 1 WHERE recipient_user_id = :uid AND read_status = 0";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUserNotifications(int $userId, int $limit = 15): array {
        $sql = "SELECT * FROM notifications WHERE recipient_user_id = :uid ORDER BY notification_datetime DESC LIMIT :limit";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}