<?php

require_once __DIR__ . '/../models/SanctionTypesModel.php';
require_once __DIR__ . '/../config/app.php';

class SanctionTypesController {
    private $sanctionTypesModel;

    public function __construct() {
        $this->sanctionTypesModel = new SanctionTypesModel();
    }

    /**
     * Muestra una lista de tipos de sanción con filtros y paginación.
     */
    public function index($params = []) {
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 10;
        $search = isset($params['search']) ? trim($params['search']) : '';
        
        $sanctionTypes = $this->sanctionTypesModel->getAll($page, $limit, $search);
        $total = (int)$this->sanctionTypesModel->countAll($search);
        $totalPages = (int)ceil($total / $limit);

        return [
            'sanction_types' => $sanctionTypes,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Tipos de Sanción',
            'has_search' => !empty($search)
        ];
    }

    /**
     * Muestra un tipo de sanción específico.
     */
    public function view($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de tipo de sanción inválido.'];
        }

        $sanctionType = $this->sanctionTypesModel->getById($id);

        if (!$sanctionType) {
            return ['success' => false, 'message' => 'Tipo de sanción no encontrado.'];
        }

        return [
            'success' => true,
            'sanction_type' => $sanctionType,
            'page_title' => 'Detalle de Tipo de Sanción #' . $sanctionType['sanction_type_id']
        ];
    }

    /**
     * Procesa la creación de un nuevo tipo de sanción.
     */
    public function store($data) {
        $validation = $this->validateSanctionTypeData($data);
        if (!$validation['success']) {
            return $validation;
        }

        $result = $this->sanctionTypesModel->create($data);

        if ($result['success']) {
            return ['success' => true, 'redirect' => 'index.php', 'message' => $result['message']];
        }
        return $result;
    }

    /**
     * Muestra el formulario para editar un tipo de sanción.
     */
    public function edit($id) {
        $sanctionType = $this->sanctionTypesModel->getById($id);

        if (!$sanctionType) {
            return [
                'success' => false,
                'page_title' => 'Error al Editar Tipo de Sanción #' . $id,
                'action' => 'edit',
                'message' => 'Tipo de sanción no encontrado.'
            ];
        }

        return [
            'success' => true,
            'sanction_type' => $sanctionType,
            'page_title' => 'Editar Tipo de Sanción #' . $id,
            'action' => 'edit'
        ];
    }

    /**
     * Procesa la actualización de un tipo de sanción.
     */
    public function update($data) { 
        print_r($data);             
        $validation = $this->validateSanctionTypeData($data);
  
        if (!$validation['success']) {
            return $validation;
        }

        $result = $this->sanctionTypesModel->update($data['sanction_type_id'], $data);

        if ($result['success']) {
            return ['success' => true, 'redirect' => 'index.php', 'message' => $result['message']];
        }
        return $result;
    }

    /**
     * Elimina un tipo de sanción.
     */
    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'message' => 'ID de tipo de sanción inválido.'];
        }

        $result = $this->sanctionTypesModel->delete($id);

        return $result;
    }

    /**
     * Valida los datos de un tipo de sanción.
     * @param array $data
     * @return array
     */
    private function validateSanctionTypeData($data) {
        $errors = [];

        if (empty(trim($data['sanction_type_name']))) {
            $errors[] = 'El nombre del tipo de sanción es obligatorio.';
        }
             
        // El campo 'description' puede ser nulo, por lo que no es necesario validarlo como obligatorio.

        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Errores de validación', 'errors' => $errors];
        }

        return ['success' => true];
    }

    /**
     * Obtiene los datos de un tipo de sanción por su ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        return $this->sanctionTypesModel->getById($id);
    }
}