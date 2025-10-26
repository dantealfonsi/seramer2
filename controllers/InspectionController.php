<?php

require_once __DIR__ . '/../models/InspectionModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/app.php';

class InspectionController {
    private $inspectionModel;
    
    public function __construct() {
        // AuthMiddleware::requireLogin(); // Ejemplo de uso de middleware si es necesario
        $this->inspectionModel = new InspectionModel();
    }

    /**
     * Display a list of inspection reports with filters.
     * * @param array $params Contains filters, search, page, and limit.
     */
    public function index($params = []) {
        // --- Parámetros de Paginación/Búsqueda/Filtros ---
        // Para DataTables del lado del cliente, solo necesitamos aplicar los filtros avanzados (no paginación)
        // El 'search' principal se pasa en 'filters' y lo usaremos para filtrar el resultado del servidor.
        $filters = $params['filters'] ?? [];
        $search = $params['search'] ?? '';
        
        // El listado para DataTables no debe tener límite ni paginación en el controlador,
        // ya que DataTables maneja eso en el cliente.
        $reports = $this->inspectionModel->getFilteredReports($filters); 
        
        // Simular las variables de paginación para evitar errores en la vista, aunque DataTables lo maneje
        $total = count($reports);
        $page = 1;
        $limit = $total > 0 ? $total : 1;
        $totalPages = 1;

        // Se retorna la lista completa filtrada por GET, y DataTables la procesará
        return [
            'inspections' => $reports, // Cambié 'reports' a 'inspections' para la vista
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Reportes de Inspección',
            'has_filters' => !empty($filters), // Indica si se aplicó algún filtro
        ];
    }

    // --- MÉTODOS AUXILIARES PARA LOS SELECTS DE FILTRADO ---

    /**
     * Get a list of all inspection types for filters.
     */
    public function getInspectionTypesList() {
        // Implementar la llamada al modelo. Ejemplo:
        // return $this->inspectionModel->getInspectionTypes(); 
        // Retorno de ejemplo si el modelo no existe:
        return [
            ['inspection_type_id' => 1, 'name' => 'Inicial'],
            ['inspection_type_id' => 2, 'name' => 'Rutina'],
            ['inspection_type_id' => 3, 'name' => 'Queja']
        ];
    }

    /**
     * Get a list of all stalls for filters (if needed).
     */
    public function getStallsList() {
        // return $this->inspectionModel->getStalls();
        return []; 
    }

    // --- MÉTODOS EXISTENTES (Mantienen su lógica original) ---

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