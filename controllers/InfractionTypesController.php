<?php

require_once __DIR__ . '/../models/InfractionTypesModel.php';
require_once __DIR__ . '/../config/app.php';

class InfractionTypesController {
    private $infractionTypesModel;

    public function __construct() {
        $this->infractionTypesModel = new InfractionTypesModel();
    }

    /**
     * Muestra una lista de tipos de infracción con filtros y paginación.
     */
    public function index($params = []) {
        // DataTables manejará la paginación y búsqueda en el cliente.
        // Obtenemos todos los registros pasando limit = 0 o null.
        $search = isset($params['search']) ? trim($params['search']) : '';
        
        $infractionTypes = $this->infractionTypesModel->getAll(1, 0, $search);
        
        return [
            'infraction_types' => $infractionTypes,
            'search' => $search,
            'page_title' => 'Gestión de Tipos de Infracción',
            'has_search' => !empty($search) // Mantenemos para compatibilidad con la vista, aunque DataTables tiene su propio search
        ];
    }

    /**
     * Muestra un tipo de infracción específico.
     */
    public function view($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de tipo de infracción inválido.'];
        }

        $infractionType = $this->infractionTypesModel->getById($id);

        if (!$infractionType) {
            return ['success' => false, 'message' => 'Tipo de infracción no encontrado.'];
        }

        return [
            'success' => true,
            'infraction_type' => $infractionType,
            'page_title' => 'Detalle de Tipo de Infracción #' . $infractionType['infraction_type_id']
        ];
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de infracción.
     */
    public function create() {
        return [
            'page_title' => 'Registrar Nuevo Tipo de Infracción',
            'action' => 'create'
        ];
    }

    /**
     * Procesa la creación de un nuevo tipo de infracción.
     */
    public function store($data) {
        $validation = $this->validateInfractionTypeData($data);
        if (!$validation['success']) {
            return $validation;
        }

        $result = $this->infractionTypesModel->create($data);

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
     * Muestra el formulario para editar un tipo de infracción.
     */
    public function edit($id) {
        $infractionType = $this->infractionTypesModel->getById($id);

        if (!$infractionType) {
            return [
                'success' => false,
                'page_title' => 'Error al Editar Tipo de Infracción #' . $id,
                'action' => 'edit',
                'message' => 'Tipo de infracción no encontrado.'
            ];
        }

        return [
            'success' => true,
            'infraction_type' => $infractionType,
            'page_title' => 'Editar Tipo de Infracción #' . $id,
            'action' => 'edit'
        ];
    }

    /**
     * Procesa la actualización de un tipo de infracción.
     */
    public function update($id, $data) {
        $validation = $this->validateInfractionTypeData($data);
        if (!$validation['success']) {
            return $validation;
        }

        $result = $this->infractionTypesModel->update($id, $data);

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
     * Elimina un tipo de infracción.
     */
    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de tipo de infracción inválido.'];
        }

        $result = $this->infractionTypesModel->delete($id);

        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];

        return $result;
    }

    /**
     * Valida los datos de un tipo de infracción.
     * @param array $data
     * @return array
     */
    private function validateInfractionTypeData($data) {
        $errors = [];

        if (empty(trim($data['infraction_type_name']))) {
            $errors[] = 'El nombre del tipo de infracción es obligatorio.';
        }
        
        // El campo 'description' puede ser nulo, por lo que no es necesario validarlo como obligatorio.

        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Errores de validación', 'errors' => $errors];
        }

        return ['success' => true];
    }

    /**
     * Obtiene los datos de un tipo de infracción por su ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        return $this->infractionTypesModel->getById($id);
    }    
}