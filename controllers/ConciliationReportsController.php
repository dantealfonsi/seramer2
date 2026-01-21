<?php

require_once __DIR__ . '/../models/ConciliationReportsModel.php';
require_once __DIR__ . '/../models/CitationsModel.php';
require_once __DIR__ . '/../models/InfractionsModel.php';
require_once __DIR__ . '/../models/SanctionsModel.php';
require_once __DIR__ . '/../models/CitationsModel.php';
require_once __DIR__ . '/../models/InfractionsModel.php';
require_once __DIR__ . '/../models/SanctionsModel.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../config/app.php';

class ConciliationReportsController {
    private $reportsModel;
    private $citationsModel;
    private $infractionsModel;
    private $sanctionsModel;
    private $notificationController;
    
    public function __construct() {
        $this->reportsModel = new ConciliationReportsModel();
        $this->citationsModel = new CitationsModel();
        $this->infractionsModel = new InfractionsModel();
        $this->sanctionsModel = new SanctionsModel();
        $this->notificationController = new NotificationController();
    }

    /**
     * Muestra una lista de informes de conciliación con filtros y paginación.
     */
public function index($params = []) {
    $page = isset($params['page']) ? (int)$params['page'] : 1;
    $limit = 10;
    
    // --- PARÁMETROS DE FILTRADO AVANZADO (CORREGIDO) ---
    // La vista ya procesó los filtros y los pasó en $params['filters'].
    // Extraemos solo los filtros activos para pasarlos al Modelo.
    $activeFilters = $params['filters'] ?? [];
    
    // Opcional: Re-filtrar por seguridad, aunque la vista ya lo hizo.
    $activeFilters = array_filter($activeFilters, function($value) {
        return $value !== null && $value !== '';
    });
    
    // Se pasa la paginación y los filtros
    $reports = $this->reportsModel->getAll($page, $limit, $activeFilters);
    $total = (int)$this->reportsModel->countAll($activeFilters);
    $totalPages = (int)ceil($total / $limit);
    
    // Obtenemos la lista de citaciones para el select del formulario (si es necesario)
    $citationsList = $this->reportsModel->getCitationsList();

    return [
        'reports' => $reports,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $total,
        // Devolvemos todos los filtros activos para rellenar el formulario (como ya vienen de la vista)
        'filters' => $activeFilters, 
        'page_title' => 'Gestión de Informes de Conciliación',
        'citations_list' => $citationsList, // Para el select de citaciones
        'has_filters' => !empty($activeFilters)
    ];
}
    /**
     * Muestra un informe de conciliación específico.
     */
    public function view($id) {
        if (!$id || !is_numeric($id)) {
            // Manejar error de ID inválido
            return ['success' => false, 'message' => 'ID de reporte inválido.'];
        }
        
        $report = $this->reportsModel->getById($id);
        
        if (!$report) {
            // Manejar error de "no encontrado"
            return ['success' => false, 'message' => 'Reporte no encontrado.'];
        }
        
        return [
            'success' => true,
            'report' => $report,
            'page_title' => 'Detalle de Reporte #' . $report['report_id']
        ];
    }

    /**
     * Muestra el formulario para crear un nuevo informe.
     */
    public function create() {
        $citations = $this->reportsModel->getCitationsList();
        
        return [
            'page_title' => 'Registrar Nuevo Informe de Conciliación',
            'citations' => $citations,
            'action' => 'create'
        ];
    }

    /**
     * Procesa la creación de un nuevo informe.
     */
    public function store($data) {
        $validation = $this->validateConciliationReportData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->reportsModel->create($data);
        $this->updateCitationStatus($data);
        
        // Handle agreement reached logic
        if ($result['success'] && $data['result'] === 'Agreement Reached') {
            $this->handleAgreementReached($data['citation_id']);
        }

        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        if ($result['success']) {
            return ['success' => true, 'redirect' => '../citations/index.php'];
        }
        return $result;
    }

    /**
     * Muestra el formulario para editar un informe.
     */
    public function edit($id) {
        $report = $this->reportsModel->getById($id);
        $citations = $this->reportsModel->getCitationsList();
        
        if (!$report) {
            return [
                'success' => false,
                'page_title' => 'Error al Editar Reporte #' . $id,
                'action' => 'edit'
            ];
        }

        return [
            'success' => true,
            'report' => $report,
            'page_title' => 'Editar Reporte #' . $id,
            'action' => 'edit',
            'citations' => $citations
        ];
    }

    /**
     * Procesa la actualización de un informe.
     */
    public function update($id, $data) {
        $validation = $this->validateConciliationReportData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->reportsModel->update($id, $data);
        $this->updateCitationStatus($data);
        
        // Handle agreement reached logic
        if ($result['success'] && $data['result'] === 'Agreement Reached') {
            $this->handleAgreementReached($data['citation_id']);
        }
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        if ($result['success']) {
            return ['success' => true, 'redirect' => '../citations/index.php'];
        }
        return $result;
    }

    /**
     * Elimina un informe.
     */
    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de reporte inválido'];
        }
        
        $result = $this->reportsModel->delete($id);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        return $result;
    }

    // Método privado para manejar la lógica de actualización del estado de la citación
    private function updateCitationStatus($data) {
        $citation_id = $data['citation_id'];
        $new_status = null;
        $new_datetime = null;
        
        if ($data['result'] === 'Agreement Reached') {
            $new_status = 'Resuelta';
        } elseif ($data['result'] === 'Case Postponed') {
            $new_status = 'Reprogramada';
            $new_datetime = $data['reprogramming_datetime'];
        }
        
        if ($new_status) {
            $citationUpdateResult = $this->citationsModel->updateStatusAndDate($citation_id, $new_status, $new_datetime);
            if (!$citationUpdateResult['success']) {
                 $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Informe de conciliación actualizado, pero la citación no pudo ser actualizada.'];
            }
        }
    }

    /**
     * Valida los datos del informe.
     * @param array $data
     * @return array
     */
    private function validateConciliationReportData($data) {
        $errors = [];
        
        if (empty($data['citation_id'])) {
            $errors[] = 'Debe seleccionar una citación.';
        }
        
        // awardee_attendance es TINYINT(1), lo validamos como un booleano (0 o 1)
        if (!isset($data['awardee_attendance']) || !in_array($data['awardee_attendance'], [0, 1])) {
            $errors[] = 'El campo de asistencia es obligatorio.';
        }

        if (empty(trim($data['result']))) {
            $errors[] = 'El resultado de la conciliación es obligatorio.';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Errores de validación', 'errors' => $errors];
        }
        
        return ['success' => true];
    }

   // Muestra los detalles de un solo informe de conciliación
    public function show($id) {
        $report = $this->reportsModel->getById($id);
        
        if (!$report) {
            return ['success' => false, 'message' => 'Informe no encontrado.'];
        }

        return ['success' => true, 'report' => $report];
    }
    
    /**
     * Obtiene un informe de conciliación por ID de citación.
     * @param int $citationId
     * @return array|false
     */
    public function getByCitationId($citationId) {
        return $this->reportsModel->getByCitationId($citationId);
    }
    
    /**
     * Handles the business logic when an agreement is reached.
     * Cancels the related infraction and sanction, and notifies Cobranzas users.
     * @param int $citationId
     */
    private function handleAgreementReached($citationId) {
        try {
            // Get citation to find the infraction_id
            $citation = $this->citationsModel->getById($citationId);
            if (!$citation || empty($citation['infraction_id'])) {
                error_log("No se pudo encontrar la infracción para la citación ID: $citationId");
                return;
            }
            
            $infractionId = $citation['infraction_id'];
            
            // Actualizar estado de la infracción a "Resolved"
            $this->infractionsModel->updateStatus($infractionId, 'Resolved');
            
            // Perdonar la sanción asociada (si existe)
            $sanction = $this->sanctionsModel->getByInfractionId($infractionId);
            if ($sanction) {
                $this->sanctionsModel->pardonSanction($sanction['sanction_id']);
            }
            
            // Send notification to Cobranzas users
            $message = "Se ha alcanzado un acuerdo en la citación #{$citationId}. La infracción #{$infractionId} ha sido resuelta y la sanción perdonada.";
            $this->notificationController->sendNotificationToRole('Cobranzas', $message, 'info');
            
        } catch (Exception $e) {
            error_log("Error al procesar acuerdo alcanzado: " . $e->getMessage());
        }
    }    
}
