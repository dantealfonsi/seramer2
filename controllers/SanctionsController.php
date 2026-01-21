<?php

require_once __DIR__ . '/../models/SanctionsModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';

/**
 * Controlador para la gestión de Sanciones
 * * Este controlador maneja las peticiones CRUD y actúa como
 * intermediario entre las vistas y el modelo de datos.
 */
class SanctionsController {

    private $model;

    public function __construct() {
        $this->model = new SanctionsModel();
    }

    /**
     * Muestra la lista de sanciones
     * @return array
     */
public function index($params = []) {
        // Extraer los filtros (si existen) y pasarlos al modelo
        $filters = $params['filters'] ?? [];

        // El modelo ahora devuelve un array estandarizado, que es lo que espera la vista.
        $result = $this->model->index($filters);

        // Aseguramos que el resultado siempre contenga las claves esperadas.
        if (isset($result['success']) && $result['success']) {
            return [
                'success' => true,
                'sanctions' => $result['sanctions'],
                'awardees' => $this->model->getAwardeesForFilter()
            ];
        }
        
        // Retorno en caso de error o si el modelo retorna un array no estandarizado (ahora manejado en el modelo modificado)
        return $result; 
    }
    /**
     * Procesa la creación de una nueva sanción
     * @param array $data
     * @return array
     */
    public function create($data) {
        if ($this->model->create($data)) {
            // Obtener el ID de la sanción recién creada
            $sanctionId = $this->model->getLastInsertId();
            
            // Enviar notificación a Cobranzas
            $this->sendSanctionNotification($sanctionId);
            
            return [
                'success' => true,
                'message' => 'Sanción creada correctamente.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear la sanción.'
            ];
        }
    }

    /**
     * Enviar notificación a Cobranzas cuando se crea una nueva sanción
     */
    private function sendSanctionNotification($sanctionId) {
        if (!$sanctionId) return;

        try {
            $userModel = new UserModel();
            $notificationModel = new NotificationModel();
            
            // ID del departamento de Cobranzas (ajustar según tu base de datos)
            $cobranzasDeptId = 2;
            
            // Obtener todos los usuarios de Cobranzas
            $cobranzasUsers = $userModel->getUsersByDepartment($cobranzasDeptId);
            
            if (empty($cobranzasUsers)) return;
            
            // Preparar notificaciones masivas
            $notifications = [];
            $senderUserId = $_SESSION['user_id'] ?? null;
            
            foreach ($cobranzasUsers as $userId) {
                $notifications[] = [
                    'sender_user_id' => $senderUserId,
                    'recipient_user_id' => $userId,
                    'notification_type' => 'sanction_new',
                    'notification_subject' => 'Nueva Sanción Aplicada',
                    'notification_message' => "Se ha aplicado una nueva sanción #$sanctionId. Proceder con gestión de cobro.",
                    'complaint_id' => null,
                    'alert_id' => null,
                    'infraction_id' => null,
                    'target_role_id' => null,
                    'target_department_id' => $cobranzasDeptId,
                    'is_global' => 0
                ];
            }
            
            // Insertar todas las notificaciones de una vez
            $notificationModel->insertBulkNotifications($notifications);
            
        } catch (Exception $e) {
            error_log("Error enviando notificación de sanción: " . $e->getMessage());
        }
    }

    public function view($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de sanción inválido.'];
        }

        $sanction = $this->model->getSanctionWithDetails($id);

        if (!$sanction) {
            return ['success' => false, 'message' => 'Sanción no encontrado.'];
        }

        return [
            'success' => true,
            'sanction' => $sanction,
            'page_title' => 'Detalle de la Sanción #' . $sanction['sanction_type_id']
        ];
    }

    public function edit($id) {
        $sanction = $this->model->getById($id);

        if (!$sanction) {
            return [
                'success' => false,
                'page_title' => 'Error al Editar Sanción #' . $id,
                'action' => 'edit',
                'message' => 'Sanción no encontrado.'
            ];
        }

        return [
            'success' => true,
            'sanction' => $sanction,
            'page_title' => 'Editar Sanción #' . $id,
            'action' => 'edit'
        ];
    }    

    /**
     * Procesa la actualización de una sanción existente
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        if ($this->model->update($id, $data)) {
            return [
                'success' => true,
                'message' => 'Sanción actualizada correctamente.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar la sanción.'
            ];
        }
    }

    /**
     * Procesa la eliminación de una sanción
     * @param int $id
     * @return array
     */
    public function delete($id) {
        if ($this->model->delete($id)) {
            return [
                'success' => true,
                'message' => 'Sanción eliminada correctamente.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar la sanción.'
            ];
        }
    }

    /**
     * Obtiene los datos de una sanción por su ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        return $this->model->getById($id);
    }
}