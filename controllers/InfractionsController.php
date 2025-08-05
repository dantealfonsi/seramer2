<?php

require_once __DIR__ . '/../models/InfractionsModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/app.php';

// Importar los modelos de tablas relacionadas para los selects en los formularios
require_once __DIR__ . '/../models/AdjudicatoriesModel.php';
require_once __DIR__ . '/../models/MarketStallsModel.php';
require_once __DIR__ . '/../models/InfractionTypesModel.php';

// Nuevo archivo para la clase de carga de archivos.
require_once __DIR__ . '/../public/utils/FileUpload.php';

class InfractionsController {
    private $infractionsModel;
    public $marketStallsModel;
    
    public function __construct() {
        $this->infractionsModel = new InfractionsModel();
        $this->marketStallsModel = new MarketStallsModel();
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
        
        $infractions = $this->infractionsModel->getAll($page, $limit, $search);
        $total = $this->infractionsModel->countAll($search);
        $totalPages = ceil($total / $limit);
        
        $result = [
            'infractions' => $infractions,
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
            'page_title' => 'Detalles de Infracción #' . $infraction['id_infraction']
        ];
    }

    /**
     * Muestra el formulario para crear una nueva infracción.
     * @return array
     */
    public function create() {
        //AuthMiddleware::requireFiscalizationAccess();
        
        // Cargar datos necesarios para los selects en el formulario
        $adjudicatoriesModel = new AdjudicatoriesModel();
        $infractionTypesModel = new InfractionTypesModel();
        $stalls = $this->marketStallsModel->getAll();

        return [
            'page_title' => 'Registrar Nueva Infracción',
            'action' => 'create',
            'stalls' => $stalls,
            'adjudicatories' => $adjudicatoriesModel->getAll(),
            'infraction_types' => $infractionTypesModel->getAll()
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
            return $result;
        }
    }

    /**
     * Muestra el formulario para editar una infracción.
     * @param int $id
     * @return array
     */
    public function edit($id) {
        //AuthMiddleware::requireFiscalizationAccess();
        $stalls = $this->marketStallsModel->getAll();
        
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
        $adjudicatoriesModel = new AdjudicatoriesModel();
        $infractionTypesModel = new InfractionTypesModel();

        return [
            'success' => true,
            'infraction' => $infraction,
            'stalls' => $stalls,
            'page_title' => 'Editar Infracción #' . $infraction['id_infraction'],
            'action' => 'edit',
            'adjudicatories' => $adjudicatoriesModel->getAll(),
            'infraction_types' => $infractionTypesModel->getAll()
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
        if (empty($data['id_adjudicatory']) || !is_numeric($data['id_adjudicatory'])) {
            $errors[] = 'El adjudicatario es obligatorio.';
        }
        
        // Validación de id_stall (puede ser NULL)
        if (!empty($data['id_stall']) && !is_numeric($data['id_stall'])) {
            $errors[] = 'El puesto de mercado debe ser un número válido.';
        }

        // Validación de id_infraction_type
        if (empty($data['id_infraction_type']) || !is_numeric($data['id_infraction_type'])) {
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
}