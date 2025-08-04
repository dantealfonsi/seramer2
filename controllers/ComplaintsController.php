<?php

require_once __DIR__ . '/../models/ComplaintsModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/app.php';

class ComplaintsController {
    private $complaintsModel;
    
    public function __construct() {
        $this->complaintsModel = new ComplaintsModel();
    }

    /**
     * Display a list of complaints with filters and pagination.
     */
    public function index($params = []) {
        AuthMiddleware::requireUserManagementAccess(); // O el permiso que necesites        
        
        $page = isset($params['page']) ? (int)$params['page'] : 1;        
        $limit = 10;        
        $search = isset($params['search']) ? trim($params['search']) : '';                        
        $complaints = $this->complaintsModel->getAll($page, $limit, $search);          
        $total = (int)$this->complaintsModel->countAll($search);
        $totalPages = (int)ceil($total / $limit);
                
        return [
            'complaints' => $complaints,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Quejas y Reclamos',
            'has_search' => !empty($search)
        ];
    }

    /**
     * Display a specific complaint.
     */
    public function view($id) {
        AuthMiddleware::requireUserManagementAccess();
        
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
        AuthMiddleware::requireUserManagementAccess();

        $marketStalls = $this->complaintsModel->getMarketStall(); 
            
        return [
            'page_title' => 'Registrar Nueva Queja',
            'market_stalls' => $marketStalls,
            'action' => 'create'
            // 'positions' => $positions
        ];
    }

    /**
     * Process the creation of a new complaint.
     */
    public function store($data) {
        AuthMiddleware::requireUserManagementAccess();
        
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
        AuthMiddleware::requireUserManagementAccess();
        
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
            'page_title' => 'Editar Queja #' . $id,
            'action' => 'edit'
        ];
    }

    /**
     * Process the update of a complaint.
     */
    public function update($id, $data) {
        AuthMiddleware::requireUserManagementAccess();
        
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
        AuthMiddleware::requireUserManagementAccess();
        
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

    /**
     * Validate complaint data.
     * @param array $data
     * @return array
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

        if (empty($data['complaint_type'])) {
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
}