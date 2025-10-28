<?php

require_once __DIR__ . '/../models/ComplaintsModel.php';
require_once __DIR__ . '/../config/app.php';

class ComplaintsController {
    private $complaintsModel;
    
    public function __construct() {
        $this->complaintsModel = new ComplaintsModel();
    }

    /**
     * Display a list of complaints with filters (for DataTables).
     */
    public function getStallsList() {
        return $this->complaintsModel->getStallsList();
    }

public function index($params = []) {
        // --- INICIO DE LA MODIFICACIÓN ---
        
        // 1. Extraer el sub-array 'filters' de $params
        $filters = $params['filters'] ?? [];
        
        // 2. Extraer los filtros específicos con los nombres definidos en la VISTA
        $search = $filters['search'] ?? ''; // Búsqueda global (si se usa)
        $type_filter = $filters['complaint_type'] ?? '';
        $priority_filter = $filters['complaint_priority'] ?? '';
        $status_filter = $filters['complaint_status'] ?? '';
        
        // Creamos un array de filtros limpio para pasar al modelo
        // Usaremos nombres cortos en el array que pasamos al Model
        $model_filters = [
            'search' => $search,
            'type' => $type_filter,      // Nuevo filtro
            'status' => $status_filter,
            'priority' => $priority_filter
        ];
        
        // Filtramos para quitar valores vacíos antes de enviar al modelo (buena práctica)
        $active_model_filters = array_filter($model_filters, fn($value) => $value !== null && $value !== '');

        // Llamamos al método getAll modificado, pasando solo los filtros activos
        $complaints = $this->complaintsModel->getAll($active_model_filters);
        
        // --- FIN DE LA MODIFICACIÓN ---

        // Definiciones de filtros (útil para la vista)
        $allowed_status = ['Received', 'In Process', 'Resolved', 'Closed'];
        $allowed_priority = ['Low', 'Medium', 'High', 'Urgent'];
        $allowed_type = ['Suggestion', 'Claim', 'Question'];
        
        return [
            'success' => true,
            'complaints' => $complaints,
            'page_title' => 'Gestión de Quejas y Reclamos',
            
            // Retornamos los valores de filtro para que la vista los use
            'search' => $search,
            'status_filter' => $status_filter,
            'priority_filter' => $priority_filter,
            'type_filter' => $type_filter, // Retornar el nuevo filtro para la vista
            
            // Listas de opciones para los desplegables de la vista
            'allowed_status' => $allowed_status,
            'allowed_priority' => $allowed_priority,
            'allowed_type' => $allowed_type // Añadir el nuevo filtro
        ];
    }
    
    // ... el resto del controlador se mantiene igual ...

    /**
     * Validate complaint data.
     * Añadí la validación del nuevo campo 'complaint_type'
     */
    private function validateComplaintData($data) {
        $errors = [];
        
        if (empty(trim($data['client_name']))) {
            $errors[] = 'El nombre del cliente es obligatorio.';
        }
        
        if (empty($data['client_email'])) {
            $errors[] = 'El email del cliente es obligatorio.';
        } elseif (!filter_var($data['client_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del email no es válido.';
        }
        
        if (empty(trim($data['complaint_description']))) {
            $errors[] = 'La descripción de la queja es obligatoria.';
        }

        // VALIDACIÓN DE TIPO (NUEVO)
        $allowed_type = ['Suggestion', 'Claim', 'Question'];
        if (isset($data['complaint_type']) && !in_array($data['complaint_type'], $allowed_type)) {
            $errors[] = 'El tipo de queja no es válido.';
        } elseif (empty($data['complaint_type'])) {
            $errors[] = 'El tipo de queja es obligatorio.';
        }

        // Validar que los valores de status y priority sean los permitidos
        $allowed_status = ['Received', 'In Process', 'Resolved', 'Closed'];
        if (isset($data['complaint_status']) && !in_array($data['complaint_status'], $allowed_status)) {
            $errors[] = 'El estado de la queja no es válido.';
        }

        $allowed_priority = ['Low', 'Medium', 'High', 'Urgent'];
        if (isset($data['complaint_priority']) && !in_array($data['complaint_priority'], $allowed_priority)) {
            $errors[] = 'La prioridad de la queja no es válida.';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Errores de validación', 'errors' => $errors];
        }
        
        return ['success' => true];
    }

    /**
     * Display a specific complaint.
     */
    public function view($id) {        
        if (!$id || !is_numeric($id)) {
            // Manejar error
        }
        
        $complaint = $this->complaintsModel->getById($id);
        
        if (!$complaint) {
            // Manejar error de "no encontrado"
        }
        
        return [
            'success' => true,
            'complaint' => $complaint,
            'page_title' => 'Detalle de Queja #' . $complaint['complaint_id']
        ];
    }

    /**
     * Show form to create a new complaint.
     */
    public function create() {
        return [
            'page_title' => 'Registrar Nueva Queja',
            'market_stalls' => $this->complaintsModel->getMarketStall(),
            'awardees' => $this->complaintsModel->getAwardeesList(),            
            'action' => 'create'
            // 'positions' => $positions
        ];
    }

    /**
     * Process the creation of a new complaint.
     */
    public function store($data) {        
        $validation = $this->validateComplaintData($data);
        if (!$validation['success']) {
            return $validation; // Devuelve errores para mostrar en el formulario
        }
        
        $result = $this->complaintsModel->create($data);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        if ($result['success']) {
            return ['success' => true, 'redirect' => 'index.php', 'message' => $result['message']];
        }
        return $result;
    }

    /**
     * Show form to edit a complaint.
     */
    public function edit($id) {        
        $complaint = $this->complaintsModel->getById($id);
        
        if (!$complaint) {
            return [
                'success' => false,
                'page_title' => 'Error al Editar Queja #' . $id,
                'action' => 'edit'
            ];
        }

        return [
            'success' => true,
            'complaint' => $complaint,
            'awardees' => $this->complaintsModel->getAwardeesList(),  
            'page_title' => 'Editar Queja #' . $id,
            'action' => 'edit'
        ];
    }

    /**
     * Process the update of a complaint.
     */
    public function update($id, $data) {        
        $validation = $this->validateComplaintData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->complaintsModel->update($id, $data);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        if ($result['success']) {
            return ['success' => true, 'redirect' => 'index.php'];
        }
        return $result;
    }

    /**
     * Delete a complaint.
     */
    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de queja inválido'];
        }
        
        $result = $this->complaintsModel->delete($id);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        // La redirección se hará en la vista que procesa el delete.
        return $result;
    }

}