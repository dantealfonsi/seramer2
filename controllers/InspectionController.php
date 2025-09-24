<?php

require_once __DIR__ . '/../models/InspectionModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/app.php';

class InspectionController {
    private $inspectionModel;
    
    public function __construct() {
        $this->inspectionModel = new InspectionModel();
    }

    /**
     * Display a list of inspection reports with filters and pagination.
     */
    public function index($params = []) {
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 10;
        $search = isset($params['search']) ? trim($params['search']) : '';
        $reports = $this->inspectionModel->getAll($page, $limit, $search);
        $total = (int)$this->inspectionModel->countAll($search);
        $totalPages = (int)ceil($total / $limit);
        
        return [
            'reports' => $reports,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Reportes de Inspección',
            'has_search' => !empty($search)
        ];
    }

    /**
     * Display a specific inspection report.
     */
    public function view($id) {
        if (!$id || !is_numeric($id)) {
            // Manejar error de ID inválido
        }
        
        $report = $this->inspectionModel->getById($id);
        
        if (!$report) {
            // Manejar error de "no encontrado"
        }
        
        return [
            'success' => true,
            'report' => $report,
            'page_title' => 'Detalle de Reporte #' . $report['report_id']
        ];
    }

    /**
     * Show form to create a new inspection report.
     */
    public function create() {
        // Asumiendo que necesitas obtener listas para los select, como inspectores, puestos, etc.
        $inspectors = $this->inspectionModel->getInspectors();
        $stalls = $this->inspectionModel->getStalls();
        $awardees = $this->inspectionModel->getAwardees();
        $users = $this->inspectionModel->getUsers();
        
        return [
            'page_title' => 'Registrar Nuevo Reporte de Inspección',
            'inspectors' => $inspectors,
            'stalls' => $stalls,
            'users' => $users,
            'awardees' => $awardees,
            'action' => 'create'
        ];
    }

    /**
     * Process the creation of a new inspection report.
     */
    public function store($data) {
        $validation = $this->validateReportData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->inspectionModel->create($data);
        
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
     * Show form to edit an inspection report.
     */
    public function edit($id) {
        $report = $this->inspectionModel->getById($id);
        
        if (!$report) {
            return [
                'success' => false,
                'page_title' => 'Error al Editar Reporte #' . $id,
                'action' => 'edit'
            ];
        }

        $inspectors = $this->inspectionModel->getInspectors();
        $stalls = $this->inspectionModel->getStalls();
        $awardees = $this->inspectionModel->getAwardees();

        return [
            'success' => true,
            'report' => $report,
            'page_title' => 'Editar Reporte #' . $id,
            'inspectors' => $inspectors,
            'stalls' => $stalls,
            'awardees' => $awardees,
            'action' => 'edit'
        ];
    }

    /**
     * Process the update of an inspection report.
     */
    public function update($id, $data) {
        $validation = $this->validateReportData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->inspectionModel->update($id, $data);
        
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
     * Delete an inspection report.
     */
    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de reporte inválido'];
        }
        
        $result = $this->inspectionModel->delete($id);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        // La redirección se hará en la vista que procesa el delete.
        return $result;
    }

    /**
     * Validate inspection report data.
     * @param array $data
     * @return array
     */
    private function validateReportData($data) {
        $errors = [];
        
        if (empty($data['main_inspector_id'])) {
            $errors[] = 'El inspector principal es obligatorio.';
        }
        
        if (empty($data['stall_id'])) {
            $errors[] = 'El puesto es obligatorio.';
        }
        
        if (empty($data['awardee_id'])) {
            $errors[] = 'El adjudicatario es obligatorio.';
        }
        
        // Puedes agregar más validaciones si lo necesitas
        
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Errores de validación', 'errors' => $errors];
        }
        
        return ['success' => true];
    }
}