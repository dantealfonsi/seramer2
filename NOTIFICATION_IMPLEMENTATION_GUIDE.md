# Implementación Completa - Sistema de Notificaciones SERAMER

## 📋 Resumen de Implementaciones

Este documento consolida TODAS las implementaciones necesarias para el sistema de notificaciones automáticas.

---

## ✅ YA IMPLEMENTADO

1. ✅ `UserModel::getUsersByDepartment()` - Obtener usuarios por departamento
2. ✅ `ComplaintsController::store()` - Notificación al crear queja → Fiscalización
3. ✅ `SanctionsController::create()` - Notificación al crear sanción → Cobranzas

---

## 🔧 IMPLEMENTACIONES PENDIENTES

### 1. Métodos Auxiliares en Modelos

#### A. SanctionsModel.php

Añadir al final de la clase, antes del cierre `}`:

```php
/**
 * Obtener el último ID insertado
 */
public function getLastInsertId() {
    return $this->conn->lastInsertId();
}

/**
 * Actualizar estado de una sanción
 */
public function updateStatus($sanctionId, $status) {
    try {
        $query = "UPDATE sanctions SET sanction_status = :status WHERE sanction_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $sanctionId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error actualizando estado de sanción: " . $e->getMessage());
        return false;
    }
}

/**
 * Perdonar sanción (cambiar a Waived)
 */
public function pardonSanction($sanctionId) {
    return $this->updateStatus($sanctionId, 'Waived');
}
```

#### B. InfractionsModel.php

Añadir al final de la clase:

```php
/**
 * Actualizar estado de una infracción
 */
public function updateStatus($infractionId, $status) {
    try {
        $query = "UPDATE infractions SET infraction_status = :status WHERE infraction_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $infractionId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error actualizando estado de infracción: " . $e->getMessage());
        return false;
    }
}
```

#### C. CitationsModel.php

Añadir al final de la clase:

```php
/**
 * Obtener citaciones programadas para hoy
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
```

---

### 2. ConciliationReportsController.php

Modificar el método `store()` o `create()` para manejar acuerdos:

```php
public function store($data) {
    // ... código existente de validación ...
    
    $result = $this->model->create($data);
    
    if ($result['success']) {
        // Si hay acuerdo alcanzado, perdonar infracción y sanción
        if (isset($data['result']) && $data['result'] === 'Agreement Reached') {
            $this->pardonInfractionAndSanction($data['citation_id']);
        }
        
        return ['success' => true, 'message' => 'Reporte de conciliación creado correctamente.'];
    }
    
    return $result;
}

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
        require_once __DIR__ . '/../models/InfractionsModel.php';
        $infractionsModel = new InfractionsModel();
        $infractionsModel->updateStatus($infractionId, 'Resolved');
        
        // Perdonar la sanción asociada (si existe)
        require_once __DIR__ . '/../models/SanctionsModel.php';
        $sanctionsModel = new SanctionsModel();
        
        // Buscar sanción asociada a esta infracción
        $sanction = $sanctionsModel->getBySanctionId($infractionId);
        if ($sanction) {
            $sanctionsModel->pardonSanction($sanction['sanction_id']);
        }
        
    } catch (Exception $e) {
        error_log("Error perdonando infracción y sanción: " . $e->getMessage());
    }
}
```

Añadir al inicio del archivo:

```php
require_once __DIR__ . '/../models/CitationsModel.php';
```

Y en el constructor:

```php
private $citationsModel;

public function __construct() {
    $this->model = new ConciliationReportsModel();
    $this->citationsModel = new CitationsModel();
}
```

---

### 3. Script CRON para Notificaciones de Citaciones

Crear archivo: `cron/citation_notifications.php`

```php
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
```

**Configurar CRON:**
```bash
# Ejecutar cada hora
0 * * * * php /c/xampp/htdocs/seramer2/cron/citation_notifications.php
```

---

### 4. BillingController.php - Notificación de Pago

Buscar el método que procesa pagos de multas y modificar:

```php
// Después de registrar el pago exitosamente
if ($paymentSuccess) {
    // Actualizar estado de la sanción
    require_once __DIR__ . '/../models/SanctionsModel.php';
    require_once __DIR__ . '/../models/InfractionsModel.php';
    
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
}
```

Añadir método privado:

```php
private function sendPaymentNotification($sanctionId, $infractionId) {
    try {
        require_once __DIR__ . '/../models/UserModel.php';
        require_once __DIR__ . '/../models/NotificationModel.php';
        
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
```

---

### 5. EuroRateController.php - Notificación de Cambio de Tasa

Modificar el método `create()` o `update()`:

```php
public function create($data) {
    // ... código existente ...
    
    $result = $this->model->create($data);
    
    if ($result['success']) {
        // Enviar notificación global
        $this->sendExchangeRateNotification($data['rate']);
        
        return ['success' => true, 'message' => 'Tasa de cambio actualizada correctamente.'];
    }
    
    return $result;
}

private function sendExchangeRateNotification($rate) {
    try {
        require_once __DIR__ . '/../models/NotificationModel.php';
        
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
```

---

### 6. views/complaints/view.php - Botón de Seguimiento

Buscar el botón "Volver" y añadir:

```php
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
```

---

## 📋 Checklist Final

### Modelos
- [ ] `SanctionsModel.php` - Añadir `getLastInsertId()`, `updateStatus()`, `pardonSanction()`
- [ ] `InfractionsModel.php` - Añadir `updateStatus()`
- [ ] `CitationsModel.php` - Añadir `getTodaysCitations()`, `cancelOverdueCitations()`

### Controladores
- [ ] `ConciliationReportsController.php` - Implementar perdón de infracción/sanción en acuerdos
- [ ] `BillingController.php` - Notificación de pago + actualización de estados
- [ ] `EuroRateController.php` - Notificación de cambio de tasa

### Scripts CRON
- [ ] Crear `cron/citation_notifications.php`
- [ ] Configurar tarea CRON (cada hora)

### Vistas
- [ ] `views/complaints/view.php` - Añadir botón de seguimiento

---

## 🧪 Pruebas Requeridas

1. ✅ Crear queja → Notificación a Fiscalización
2. ✅ Crear sanción → Notificación a Cobranzas
3. ⏳ Citación programada → Notificación el día de la cita
4. ⏳ Citación +3 horas → Auto-cancelación
5. ⏳ Acuerdo en conciliación → Infracción y sanción perdonadas
6. ⏳ Pagar multa → Notificación a Fiscalización + estados actualizados
7. ⏳ Cambiar tasa → Notificación global
8. ⏳ Ver queja → Botón de seguimiento visible
