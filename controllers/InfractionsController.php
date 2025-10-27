<?php

require_once __DIR__ . '/../models/InfractionsModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/app.php';

// Importar los modelos de tablas relacionadas para los selects en los formularios
require_once __DIR__ . '/../models/InfractionTypesModel.php';
require_once __DIR__ . '/../controllers/SanctionTypesController.php';
require_once __DIR__ . '/../controllers/SanctionsController.php';

// Nuevo archivo para la clase de carga de archivos.
require_once __DIR__ . '/../public/utils/FileUpload.php';

class InfractionsController {
    private $infractionsModel;
    //public $marketStallsModel;
    
    public function __construct() {
        $this->infractionsModel = new InfractionsModel();
        $this->infractionsModel->createEconomicIndicatorsTable();
    }

    public function getStallsList() {
        return $this->infractionsModel->getStallsList();
    }

    public function getInfractionTypesList()
    {
        return $this->infractionsModel->getInfractionTypesList(); // Ajusta el método según tu modelo
    }    

    public function getLatestEconomicIndicators()
    {
        return $this->infractionsModel->getLatestEconomicIndicators();
    }

    public function saveOrUpdateEconomicIndicators($ut_value, $euro_bcv_rate)
    {
        return $this->infractionsModel->saveOrUpdateEconomicIndicators($ut_value, $euro_bcv_rate);
    }
    /**
     * Muestra la lista de infracciones con filtros y paginación.
     * @param array $params
     * @return array
     */
    public function index($params = []) {
        // Verificar acceso - Se asume que solo personal de fiscalización tiene acceso
        // Debes implementar el método adecuado en tu AuthMiddleware
        //AuthMiddleware::requireFiscalizationAccess();
                
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 10;
        $search = isset($params['search']) ? trim($params['search']) : '';

        $filters = $params['filters'] ?? [];
        
        $infractions = $this->infractionsModel->getAll($page, $limit, $filters);        
        $awardees = $this->infractionsModel->getAwardeesList();        
        $total = $this->infractionsModel->countAll($filters);        
        $totalPages = ceil($total / $limit);
        
        $result = [
            'infractions' => $infractions,
            'awardees' => $awardees,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Infracciones',
            'has_search' => !empty($search)
        ];
        
        return $result;
    }

    /**
     * Muestra los detalles de una infracción específica.
     * @param int $id
     * @return array
     */
    public function view($id) {
        //AuthMiddleware::requireFiscalizationAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de infracción inválido'
            ];
        }
        
        $infraction = $this->infractionsModel->getInfractionDetails($id);
        
        if (!$infraction) {
            return [
                'success' => false,
                'message' => 'Infracción no encontrada o ha sido eliminada'
            ];
        }
        
        return [
            'success' => true,
            'infraction' => $infraction,
            'page_title' => 'Detalles de Infracción #' . $infraction['infraction_id']
        ];
    }

    /**
     * Muestra el formulario para crear una nueva infracción.
     * @return array
     */
    public function create() {
        //AuthMiddleware::requireFiscalizationAccess();
        
        // Cargar datos necesarios para los selects en el formulario
        //$adjudicatoriesModel = new AdjudicatoriesModel();
        $infractionTypesModel = new InfractionTypesModel();
        $stalls = $this->infractionsModel->getStallsList();
        $sanctionTypesController = new SanctionTypesController();

        return [
            'page_title' => 'Registrar Nueva Infracción',
            'action' => 'create',
            'stalls' => $stalls,
            'awardees' => $this->infractionsModel->getAwardeesList(),
            'infraction_types' => $infractionTypesModel->getAll(null, null, null),
            'sanction_types' => $sanctionTypesController->index()['sanction_types']
        ];
    }

    /**
     * Procesa la creación de una nueva infracción.
     * @param array $data
     * @return array
     */
    public function store($data) {
        //AuthMiddleware::requireFiscalizationAccess();
        
        // Validar los datos del formulario, incluyendo la carga del archivo
        $validation = $this->validateInfractionData($data, $_FILES['proof'] ?? null);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->infractionsModel->create($data);
        //preparamos los datos nevesarios para crear la sancion
        if($result['success']){
            $sanctionsController = new SanctionsController();
            $sanctionData = [
                'infraction_id'         => $result['id'],
                'sanction_type_id'      => $data['sanction_type_id'] ?? null,
                'fine_amount'           => $data['fine_amount'] ?? 0,
                'fine_currency'         => $data['fine_currency'] ?? 'USD',
                'effect_start_date'     => $data['infraction_datetime'] ?? date('Y-m-d'),
                'effect_end_date'       => $data['effect_end_date'] ?? null,
                'sanction_status'       => 'Imposed',
                'sanction_observations' => $data['inspector_observations'] ?? '',
                'is_repeat_offense'     => $data['is_repeat_offense'] ?? 0,
                'imposed_by_user_id'    => 1 // Asumiendo un ID de usuario por defecto
            ];
            $sanctionResult = $sanctionsController->create($sanctionData);
            if(!$sanctionResult['success']){
                // Si la creación de la sanción falla, eliminamos la infracción creada para mantener la integridad.
                $this->infractionsModel->logicalDelete($result['id']);
                return [
                    'success' => false,
                    'message' => 'Error al crear la sanción asociada: ' . $sanctionResult['message']
                ];
            }

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            
            return [
                'message' => $result['message'],
                'success' => true,                
                'redirect' =>  '../reports/index.php?report=print_infraction.rep&action=view&id=' . $result['id']
            ];   

        } else {
            return $result;
        }
    }

    /**
     * Muestra el formulario para editar una infracción.
     * @param int $id
     * @return array
     */
    public function edit($id) {        
        $stalls = $this->infractionsModel->getStallsList();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de infracción inválido'
            ];
        }
        
        $infraction = $this->infractionsModel->getById($id);
        
        if (!$infraction) {
            return [
                'success' => false,
                'message' => 'Infracción no encontrada'
            ];
        }
        
        // Cargar datos para los selects
        //$adjudicatoriesModel = new AdjudicatoriesModel();
        $infractionTypesModel = new InfractionTypesModel();

        return [
            'success' => true,
            'infraction' => $infraction,
            'stalls' => $stalls,
            'page_title' => 'Editar Infracción #' . $infraction['infraction_id'],
            'action' => 'edit',
            'awardees' => $this->infractionsModel->getAwardeesList(),
            'infraction_types' => $infractionTypesModel->getAll(null, null, null)
        ];
    }

    /**
     * Procesa la actualización de una infracción.
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        //AuthMiddleware::requireFiscalizationAccess();
        
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de infracción inválido'];
        }

        // Obtener la infracción actual para saber si hay un archivo existente
        $existing_infraction = $this->infractionsModel->getById($id);
        if (!$existing_infraction) {
            return ['success' => false, 'message' => 'Infracción no encontrada.'];
        }
        
        $validation = $this->validateInfractionData($data, true);
        if (!$validation['success']) {
            return $validation;
        }
        
        $result = $this->infractionsModel->update($id, $data);
        
        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            
            return [
                'success' => true,
                'redirect' =>  'index.php'
            ];
        } else {
            // Si la actualización falla, no eliminamos el archivo ya que podría ser el original.
            return $result;
        }
    }

    /**
     * Elimina lógicamente una infracción.
     * @param int $id
     * @return array
     */
    public function delete($id) {
        //AuthMiddleware::requireFiscalizationAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de infracción inválido'
            ];
        }
        
        $result = $this->infractionsModel->logicalDelete($id);
        
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        return $result;
    }

    /**
     * Valida los datos de la infracción.
     * @param array $data
     * @param array|null $file
     * @param bool $is_edit
     * @return array
     */
    private function validateInfractionData($data, $file = null, $is_edit = false) {
        $errors = [];
        
        // Validación de id_adjudicatory
        if (empty($data['awardee_id']) || !is_numeric($data['awardee_id'])) {
            $errors[] = 'El adjudicatario es obligatorio.';
        }
        
        // Validación de id_stall (puede ser NULL)
        if (!empty($data['stall_id']) && !is_numeric($data['stall_id'])) {
            $errors[] = 'El puesto de mercado debe ser un número válido.';
        }

        // Validación de infraction_id_type
        if (empty($data['infraction_type_id']) || !is_numeric($data['infraction_type_id'])) {
            $errors[] = 'El tipo de infracción es obligatorio.';
        }

        // Validación de infraction_description
        if (empty($data['infraction_description'])) {
            $errors[] = 'La descripción de la infracción es obligatoria.';
        } else if (strlen(trim($data['infraction_description'])) < 10) {
            $errors[] = 'La descripción de la infracción debe tener al menos 10 caracteres.';
        }
        
        // Validación de infraction_status
        $validStatuses = ['Reported', 'In Process', 'Resolved', 'Cancelled'];
        if (empty($data['infraction_status']) || !in_array($data['infraction_status'], $validStatuses)) {
            $errors[] = 'El estado de la infracción no es válido.';
        }

        // Validación del campo de prueba
        // Si no es edición y no se ha subido un archivo, se considera un error.
        if (!$is_edit && (empty($file) || $file['error'] !== UPLOAD_ERR_OK)) {
            // Este es un ejemplo de cómo podrías hacer la prueba obligatoria.
            // Si quieres que sea opcional, puedes comentar esta línea.
            // $errors[] = 'La prueba (imagen/video) de la infracción es obligatoria.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errors
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Maneja solicitudes AJAX.
     * @param string $action
     * @param array $params
     * @return array
     */
    public function handleAjax($action, $params = []) {
        switch ($action) {
            case 'delete':
                if (isset($params['id'])) {
                    return $this->delete($params['id']);
                }
                return ['success' => false, 'message' => 'ID no proporcionado'];
                
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }

    public function contarSancionesPorSeveridad(int $awardeeId): array 
    {
        return $this->infractionsModel->contarSancionesPorSeveridad($awardeeId);
    }

    public function contarInfraccionesPorTipoAnual(int $awardeeId): array 
    {
        return $this->infractionsModel->contarInfraccionesPorTipoAnual($awardeeId);
    }

    public function contarTipoInfraccionEspecificoAnual(int $awardeeId, int $infractionTypeId): int
    {
        return $this->infractionsModel->contarTipoInfraccionEspecificoAnual($awardeeId,$infractionTypeId);
    }

}