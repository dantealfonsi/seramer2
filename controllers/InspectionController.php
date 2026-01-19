<?php

require_once __DIR__ . '/../models/InspectionModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/app.php';

class InspectionController {
    private $inspectionModel;
    
    public function __construct() {
        // AuthMiddleware::requireLogin(); // Ejemplo de uso de middleware si es necesario
        $this->inspectionModel = new InspectionModel();
        // Nota: Asume que la sesión ya ha sido iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // =======================================================================
    // MÉTODO AUXILIAR (SIMULACIÓN DE AUTENTICACIÓN)
    // =======================================================================

    /**
     * Obtiene el ID del usuario actualmente logueado para fines de auditoría.
     * @return int|null
     */
    private function getCurrentUserId() {
        // Esto es una SIMULACIÓN. Deberías obtener el ID real de tu sistema de autenticación (ej. $_SESSION['user_id'])
        return $_SESSION['user_id'] ?? 1; // Devuelve 1 por defecto o null
    }

    // =======================================================================
    // MÉTODO INDEX (SIN CAMBIOS RELEVANTES EN LA LÓGICA DE LISTADO)
    // =======================================================================

    public function index($params = []) {
        $filters = $params['filters'] ?? [];
        $search = $params['search'] ?? '';
        
        $reports = $this->inspectionModel->getFilteredReports($filters); 
        $inspectors = $this->inspectionModel->getInspectors();
        $stalls = $this->inspectionModel->getStalls();
        
        $total = count($reports);
        $page = 1;
        $limit = $total > 0 ? $total : 1;
        $totalPages = 1;

        return [
            'inspections' => $reports, 
            'inspectors' => $inspectors,
            'stalls' => $stalls,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Reportes de Inspección',
            'has_filters' => !empty($filters),
        ];
    }

    // --- MÉTODOS AUXILIARES PARA LOS SELECTS DE FILTRADO (Sin cambios) ---

    public function getInspectionTypesList() {
        // ... (lógica del listado de tipos) ...
        return [
            ['inspection_type_id' => 1, 'name' => 'Inicial'],
            ['inspection_type_id' => 2, 'name' => 'Rutina'],
            ['inspection_type_id' => 3, 'name' => 'Queja']
        ];
    }

    public function getStallsList() {
        // return $this->inspectionModel->getStalls();
        return []; 
    }

    // =======================================================================
    // MÉTODO VIEW (ACTUALIZADO PARA INCLUIR LA LÍNEA DE TIEMPO)
    // =======================================================================

    /**
     * Display a specific inspection report and its timeline.
     */
    public function view($id) {
        if (!$id || !is_numeric($id)) {
             // Manejar error de ID inválido
             return ['success' => false, 'message' => 'ID de reporte inválido.'];
        }
        
        $report = $this->inspectionModel->getById($id);
        
        if (!$report) {
             // Manejar error de "no encontrado"
             return ['success' => false, 'message' => 'Reporte no encontrado.'];
        }
        
        // **NUEVA LÓGICA: Obtener la línea de tiempo**
        $timeline = $this->inspectionModel->getReportTimeline($id);
        
        return [
            'success' => true,
            'report' => $report,
            'timeline' => $timeline, // <-- Agregado para la vista
            'page_title' => 'Detalle de Reporte #' . $report['report_id']
        ];
    }

    // --- MÉTODOS DE CREACIÓN (Mantienen su lógica original) ---

    public function create() {
        $inspectors = $this->inspectionModel->getInspectors();
        $stalls = $this->inspectionModel->getStalls();
        $awardees = $this->inspectionModel->getAwardees();
        $users = $this->inspectionModel->getUsers();
        $stallAwardeeMapping = $this->inspectionModel->getStallAwardeeMapping();
        
        return [
            'page_title' => 'Registrar Nuevo Reporte de Inspección',
            'inspectors' => $inspectors,
            'stalls' => $stalls,
            'users' => $users,
            'awardees' => $awardees,
            'stallAwardeeMapping' => $stallAwardeeMapping,
            'action' => 'create'
        ];
    }

    public function store($data) {
        $validation = $this->validateReportData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->inspectionModel->create($data);
        
        if ($result['success']) {
            // Se asume que el 'create' del modelo ya inserta el estado inicial, 
            // por lo que NO se necesita un log inicial aquí, a menos que se quiera 
            // diferenciar el estado de "Creado" del estado de "Programado".
            
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            return ['success' => true, 'redirect' => 'index.php', 'message' => $result['message']];
        }
        
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => $result['message']];
        return $result;
    }

    public function edit($id) {
        $report = $this->inspectionModel->getById($id);
        // ... (Lógica de listas para edición) ...
        $inspectors = $this->inspectionModel->getInspectors();
        $stalls = $this->inspectionModel->getStalls();
        $awardees = $this->inspectionModel->getAwardees();
        $stallAwardeeMapping = $this->inspectionModel->getStallAwardeeMapping();

        return [
            'success' => true,
            'report' => $report,
            'page_title' => 'Editar Reporte #' . $id,
            'inspectors' => $inspectors,
            'stalls' => $stalls,
            'awardees' => $awardees,
            'stallAwardeeMapping' => $stallAwardeeMapping,
            'action' => 'edit'
        ];
    }

    // =======================================================================
    // MÉTODO UPDATE (ACTUALIZADO PARA LOGUEAR EL CAMBIO DE ESTADO)
    // =======================================================================

    /**
     * Process the update of an inspection report and log status changes.
     */
    public function update($id, $data) {
        // 1. Validar datos
        $validation = $this->validateReportData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        // 2. Obtener el reporte actual para chequear el estado viejo
        $currentReport = $this->inspectionModel->getById($id);
        if (!$currentReport) {
             return ['success' => false, 'message' => 'Reporte no encontrado para actualizar.'];
        }

        $oldStatus = $currentReport['inspection_status']; // Estado en scheduled_inspections
        $newStatus = $data['inspection_status']; // Nuevo estado enviado en $data
        
        // 3. Procesar la actualización en la BD
        $result = $this->inspectionModel->update($id, $data);
        
        // 4. Lógica de Log de la Línea de Tiempo
        if ($result['success'] && $oldStatus !== $newStatus) {
            $userId = $this->getCurrentUserId(); // Obtener el ID del usuario logueado
            $description = $data['update_description'] ?? "Cambio de estado automático a '{$newStatus}'";
            
            $logResult = $this->inspectionModel->logStatusUpdate(
                $id, 
                $oldStatus, 
                $newStatus, 
                $description, 
                $userId
            );

            // Aunque el log falle, la actualización principal tuvo éxito.
            if (!$logResult['success']) {
                 // Podrías registrar un error interno en un log de sistema
            }
        }
        
        // 5. Devolver resultado y mensaje Flash
        $statusTranslations = [
            'Pending' => 'Pendiente',
            'In Progress' => 'En Curso',
            'Completed' => 'Completado',
            'Cancelled' => 'Cancelado'
        ];
        $translatedStatus = $statusTranslations[$newStatus] ?? $newStatus;
        
        $msg = $result['success'] 
            ? "Reporte de inspección actualizado exitosamente. Estado: {$translatedStatus}" 
            : $result['message'];

        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $msg
        ];
        
        if ($result['success']) {
            return ['success' => true, 'redirect' => 'index.php'];
        }
        return $result;
    }

    // --- MÉTODOS RESTANTES (Sin cambios) ---

    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de reporte inválido'];
        }
        
        $result = $this->inspectionModel->delete($id);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        return $result;
    }

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
        
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Errores de validación', 'errors' => $errors];
        }
        
        return ['success' => true];
    }
}