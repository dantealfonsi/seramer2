<?php
// C:\xampp\htdocs\seramer2\controllers\InspectionTrackingController.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/InspectionTrackingModel.php'; 

class InspectionTrackingController {
    private $model;
    private $db;
    
    // Tipos de acción permitidos (usados para determinar el estado_new)
    private $allowed_types = ['Schedule Update', 'Field Visit', 'Report Generation', 'Completion', 'Cancellation', 'Simple Note']; 
    
    // Reglas de transición (Estado Actual => [Estados Permitidos])
    private $transition_rules = [
        'Pending' => ['In Progress'],
        'In Progress' => ['Completed', 'Cancelled'],
        'Completed' => [], // Estados finales no pueden cambiar
        'Cancelled' => []  // Estados finales no pueden cambiar
    ];

    public function __construct() {
        $database = new Database(); 
        $this->db = $database->getConnection();
        $this->model = new InspectionTrackingModel($this->db); 
    }

    // -------------------------------------------------------------------
    // --- Métodos de CRUD -----------------------------------------------
    // -------------------------------------------------------------------

    public function index($inspection_id) {
        $records = $this->model->getTrackingByInspectionId($inspection_id);
        
        if ($records === false) {
            return ['success' => false, 'message' => 'Error al obtener registros de seguimiento.'];
        }
        
        return ['success' => true, 'tracking_records' => $records];
    }

    /**
     * Almacena un nuevo registro de seguimiento (CREATE) con validación de estado.
     */
    public function store($data) {
        $errors = $this->validate($data);

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        $current_status = $data['current_status'];
        $action_type = $data['action_type'];
        
        // 1. Detección del Estado Solicitado
        $new_status_requested = $this->getNewStatusFromActionType($action_type);
        
        $status_changed = false;
        $new_status_final = $current_status; // Por defecto, el estado no cambia
        
        // Solo procedemos a cambiar el estado si hay un estado solicitado y es diferente al actual
        if ($new_status_requested !== null && $new_status_requested !== $current_status) {
            
            // 2. Validar la Transición
            if (!$this->validateTransition($current_status, $new_status_requested)) {
                $allowed = implode(', ', $this->transition_rules[$current_status] ?? ['NINGUNO']);
                return ['success' => false, 'message' => "Transición de estado no permitida. El estado actual ('{$current_status}') solo puede pasar a: {$allowed}"];
            }
            
            // La transición es válida, establecemos el nuevo estado final
            $new_status_final = $new_status_requested;
        }

        // 3. CONCATENACIÓN: Unir Descripción y Resultado en un solo campo (update_description)
        $full_description = $data['action_description'];
        if (!empty($data['action_result'])) {
            $full_description .= "\n--- Resultado: " . $data['action_result']; 
        }

        // 4. Preparar datos finales para el modelo (usando nombres de campos del modelo/BD)
        $data_for_model = [
            'report_id' => $data['report_id'], 
            'inspection_id' => $data['inspection_id'],
            'admin_user_id' => $data['admin_user_id'], // Mapeará a updated_by_user_id
            'current_status' => $current_status,       // Mapeará a status_old
            'new_status' => $new_status_final,           // Mapeará a status_new (el estado final del log)
            'update_description' => $full_description  // Mapeará a update_description
        ];

        // 5. Realizar la inserción del Tracking (log)
        if ($this->model->create($data_for_model)) {
            
            // 6. ¡ACTUALIZACIÓN CRÍTICA DEL ESTADO PRINCIPAL!
            if ($new_status_final !== $current_status) {
                if ($this->model->updateInspectionStatus($data['inspection_id'], $new_status_final)) {
                    $status_changed = true;
                } else {
                    return ['success' => false, 'message' => 'Seguimiento creado, pero **falló la actualización del estado de la inspección principal** en scheduled_inspections.'];
                }
            }
            
            return [
                'success' => true, 
                'message' => 'Registro de seguimiento creado exitosamente.',
                'status_changed' => $status_changed,
                'new_status' => $new_status_final
            ];

        } else {
            return ['success' => false, 'message' => 'Error al crear el registro de seguimiento en la base de datos.'];
        }
    }

    /**
     * Elimina un registro de seguimiento por ID.
     */
    public function delete($tracking_id) {
        if (empty($tracking_id) || !is_numeric($tracking_id)) {
            return ['success' => false, 'message' => 'ID de seguimiento no válido.'];
        }
        
        // En el modelo, tracking_id es el alias de update_id
        if ($this->model->delete($tracking_id)) { 
            return ['success' => true, 'message' => 'Registro de seguimiento eliminado.'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar el registro de seguimiento.'];
        }
    }

    // -------------------------------------------------------------------
    // --- Métodos de Validación y Lógica de Estado ----------------------
    // -------------------------------------------------------------------

    /**
     * Valida los campos esenciales del formulario.
     */
    private function validate($data) {
        $errors = [];
        
        if (empty($data['inspection_id'])) {
            $errors[] = 'El ID de la inspección es requerido.';
        }
        if (empty($data['report_id'])) { 
            $errors[] = 'El ID del reporte es requerido.';
        }
        if (empty($data['admin_user_id'])) {
            $errors[] = 'El ID del usuario es requerido.';
        }
        if (empty($data['action_description'])) {
            $errors[] = 'La descripción de la acción es requerida.';
        }
        if (!in_array($data['action_type'], $this->allowed_types)) {
            $errors[] = 'Tipo de acción no válido.';
        }
        
        return $errors;
    }

    /**
     * Valida si la transición de estado es permitida según las reglas de negocio.
     */
    private function validateTransition($current_status, $new_status) {
        
        // 1. Si el nuevo estado es nulo (Simple Note) o igual al actual, siempre se permite.
        if ($new_status === null || $new_status === $current_status) {
            return true;
        }
        
        // 2. Si el estado actual no tiene transiciones permitidas (Completed, Cancelled), no se permite el cambio.
        if (!isset($this->transition_rules[$current_status]) || empty($this->transition_rules[$current_status])) {
            return false;
        }

        // 3. Si se solicita un cambio de estado, debe estar en la lista de permitidos.
        return in_array($new_status, $this->transition_rules[$current_status]);
    }

    /**
     * Mapea el tipo de acción a un posible nuevo estado.
     */
    private function getNewStatusFromActionType($action_type) {
        switch ($action_type) {
            case 'Schedule Update':
            case 'Field Visit':
            case 'Report Generation':
                return 'In Progress'; 
            case 'Completion':
                return 'Completed';
            case 'Cancellation':
                return 'Cancelled';
            case 'Simple Note':
            default:
                return null; // No hay cambio de estado
        }
    }
}