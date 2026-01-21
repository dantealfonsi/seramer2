<?php
/**
 * SCRIPT DE IMPLEMENTACIÓN COMPLETA - SISTEMA DE NOTIFICACIONES
 * 
 * Este script contiene TODAS las implementaciones restantes del sistema de notificaciones.
 * Copie y pegue cada sección en el archivo correspondiente.
 * 
 * ORDEN DE IMPLEMENTACIÓN:
 * 1. Modelos (InfractionsModel, CitationsModel)
 * 2. Controladores (ConciliationReportsController, BillingController, EuroRateController)
 * 3. Vista (complaints/view.php)
 * 4. Script CRON
 */

// ============================================================================
// 1. INFRACTIONSMODEL.PHP
// ============================================================================
// Añadir ANTES del cierre de la clase (antes del último })

/**
 * Actualizar estado de una infracción
 * @param int $infractionId
 * @param string $status
 * @return bool
 */
public function updateStatus($infractionId, $status) {
    try {
        $query = "UPDATE " . $this->table . " 
                  SET infraction_status = :status 
                  WHERE infraction_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $infractionId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error actualizando estado de infracción: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// 2. CITATIONSMODEL.PHP
// ============================================================================
// Añadir ANTES del cierre de la clase (antes del último })

/**
 * Obtener citaciones programadas para hoy
 * @return array
 */
public function getTodaysCitations() {
    try {
        $query = "SELECT c.*, i.awardee_id, i.infraction_id
                  FROM citations c
                  INNER JOIN infractions i ON c.infraction_id = i.infraction_id
                  WHERE DATE(c.citation_datetime) = CURDATE()
                  AND c.citation_status = 'Scheduled'
                  ORDER BY c.citation_datetime";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error obteniendo citaciones de hoy: " . $e->getMessage());
        return [];
    }
}

/**
 * Cancelar citaciones que pasaron 3 horas
 * @return bool
 */
public function cancelOverdueCitations() {
    try {
        $query = "UPDATE citations 
                  SET citation_status = 'Cancelled'
                  WHERE citation_status = 'Scheduled'
                  AND citation_datetime < DATE_SUB(NOW(), INTERVAL 3 HOUR)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error cancelando citaciones vencidas: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// 3. CONCILIATIONREPORTSCONTROLLER.PHP
// ============================================================================
// Añadir al INICIO del archivo (después de los require_once existentes):

require_once __DIR__ . '/../models/CitationsModel.php';
require_once __DIR__ . '/../models/SanctionsModel.php';
require_once __DIR__ . '/../models/InfractionsModel.php';

// Modificar el constructor para añadir:

private $citationsModel;

public function __construct() {
    $this->model = new ConciliationReportsModel();
    $this->citationsModel = new CitationsModel();
}

// Modificar el método store() o create() para añadir DESPUÉS de crear el reporte:

if ($result['success']) {
    // Si hay acuerdo alcanzado, perdonar infracción y sanción
    if (isset($data['result']) && $data['result'] === 'Agreement Reached') {
        $this->pardonInfractionAndSanction($data['citation_id']);
    }
    
    return ['success' => true, 'message' => 'Reporte de conciliación creado correctamente.'];
}

// Añadir este método privado al final de la clase:

/**
 * Perdonar infracción y sanción cuando hay acuerdo
 */
private function pardonInfractionAndSanction($citationId) {
    try {
        // Obtener la citación con su infracción
        $citation = $this->citationsModel->getById($citationId);
        if (!$citation) return;
        
        $infractionId = $citation['infraction_id'];
        
        // Actualizar estado de la infracción a "Resolved"
        $infractionsModel = new InfractionsModel();
        $infractionsModel->updateStatus($infractionId, 'Resolved');
        
        // Perdonar la sanción asociada (si existe)
        $sanctionsModel = new SanctionsModel();
        $sanction = $sanctionsModel->getByInfractionId($infractionId);
        
        if ($sanction) {
            $sanctionsModel->pardonSanction($sanction['sanction_id']);
        }
        
    } catch (Exception $e) {
        error_log("Error perdonando infracción y sanción: " . $e->getMessage());
    }
}

// ============================================================================
// 4. BILLINGCONTROLLER.PHP
// ============================================================================
// Buscar el método que procesa pagos de multas y añadir DESPUÉS de registrar el pago:

// Después de: if ($paymentSuccess) {

require_once __DIR__ . '/../models/SanctionsModel.php';
require_once __DIR__ . '/../models/InfractionsModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

$sanctionsModel = new SanctionsModel();
$sanctionsModel->updateStatus($sanctionId, 'Paid');

// Obtener infracción asociada
$sanction = $sanctionsModel->getById($sanctionId);
if ($sanction && $sanction['infraction_id']) {
    $infractionId = $sanction['infraction_id'];
    
    // Actualizar estado de infracción
    $infractionsModel = new InfractionsModel();
    $infractionsModel->updateStatus($infractionId, 'Resolved');
    
    // Enviar notificación a Fiscalización
    $this->sendPaymentNotification($sanctionId, $infractionId);
}

// Añadir este método privado al final de la clase BillingController:

/**
 * Enviar notificación a Fiscalización cuando se paga una multa
 */
private function sendPaymentNotification($sanctionId, $infractionId) {
    try {
        $userModel = new UserModel();
        $notificationModel = new NotificationModel();
        
        $fiscalizacionDeptId = 3;
        $fiscalizacionUsers = $userModel->getUsersByDepartment($fiscalizacionDeptId);
        
        if (empty($fiscalizacionUsers)) return;
        
        $notifications = [];
        $senderUserId = $_SESSION['user_id'] ?? null;
        
        foreach ($fiscalizacionUsers as $userId) {
            $notifications[] = [
                'sender_user_id' => $senderUserId,
                'recipient_user_id' => $userId,
                'notification_type' => 'fine_payment_received',
                'notification_subject' => 'Pago de Multa Recibido',
                'notification_message' => "Se ha recibido el pago de la sanción #$sanctionId. La infracción #$infractionId ha sido resuelta.",
                'complaint_id' => null,
                'alert_id' => null,
                'infraction_id' => $infractionId,
                'target_role_id' => null,
                'target_department_id' => $fiscalizacionDeptId,
                'is_global' => 0
            ];
        }
        
        $notificationModel->insertBulkNotifications($notifications);
        
    } catch (Exception $e) {
        error_log("Error enviando notificación de pago: " . $e->getMessage());
    }
}

// ============================================================================
// 5. EURORATECONTROLLER.PHP
// ============================================================================
// Añadir al INICIO del archivo:

require_once __DIR__ . '/../models/NotificationModel.php';

// Modificar el método create() o update() para añadir DESPUÉS de crear/actualizar:

if ($result['success']) {
    // Enviar notificación global
    $this->sendExchangeRateNotification($data['rate']);
    
    return ['success' => true, 'message' => 'Tasa de cambio actualizada correctamente.'];
}

// Añadir este método privado al final de la clase:

/**
 * Enviar notificación global cuando cambia la tasa de cambio
 */
private function sendExchangeRateNotification($rate) {
    try {
        $notificationModel = new NotificationModel();
        
        // Notificación global
        $notification = [
            'sender_user_id' => $_SESSION['user_id'] ?? null,
            'recipient_user_id' => null,
            'notification_type' => 'exchange_rate_update',
            'notification_subject' => 'Actualización de Tasa de Cambio',
            'notification_message' => "La tasa de cambio del Euro ha sido actualizada a $rate Bs.",
            'complaint_id' => null,
            'alert_id' => null,
            'infraction_id' => null,
            'target_role_id' => null,
            'target_department_id' => null,
            'is_global' => 1
        ];
        
        $notificationModel->insertBulkNotifications([$notification]);
        
    } catch (Exception $e) {
        error_log("Error enviando notificación de tasa: " . $e->getMessage());
    }
}

// ============================================================================
// 6. VIEWS/COMPLAINTS/VIEW.PHP
// ============================================================================
// Buscar el botón "Volver" y REEMPLAZAR con:

<div class="d-flex gap-2 mt-3">
    <a href="index.php" class="btn btn-secondary">
        <i class="ri-arrow-left-line"></i> Volver
    </a>
    
    <!-- Botón de seguimiento de inspección -->
    <a href="../inspections/index.php?complaint_id=<?php echo htmlspecialchars($complaint['complaint_id']); ?>" 
       class="btn btn-info">
        <i class="ri-search-eye-line"></i> Seguimiento de Inspección
    </a>
</div>

// ============================================================================
// 7. CRON/CITATION_NOTIFICATIONS.PHP (CREAR NUEVO ARCHIVO)
// ============================================================================

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

// ============================================================================
// 8. CONFIGURAR CRON (WINDOWS)
// ============================================================================
// Abrir "Programador de tareas" de Windows y crear una tarea que ejecute:
// php C:\xampp\htdocs\seramer2\cron\citation_notifications.php
// Configurar para ejecutar cada hora

// ============================================================================
// FIN DEL SCRIPT
// ============================================================================
