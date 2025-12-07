<?php
require_once __DIR__ . '/../models/ExternalBusinessCategoryModel.php';

class ExternalCategoryController {
    private $model;

    public function __construct() {
        $this->model = new ExternalBusinessCategoryModel();
    }

    public function index() {
        $categories = $this->model->getAll();
        return [
            'page_title' => 'Rubros Externos',
            'categories' => $categories
        ];
    }

    public function create() {
        return ['page_title' => 'Crear Rubro Externo'];
    }

    public function store($data) {
        if (empty($data['name']) || empty($data['payment_count'])) {
             return ['success' => false, 'message' => 'Nombre y cantidad de pagos son requeridos'];
        }

        $id = $this->model->create($data);
        if ($id) {
            return ['success' => true, 'message' => 'Rubro creado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al crear el rubro'];
    }

    public function edit($id) {
        $category = $this->model->getById($id);
        if (!$category) return null;
        
        return [
            'page_title' => 'Editar Rubro Externo',
            'category' => $category
        ];
    }

    public function update($id, $data) {
        if (empty($data['name']) || empty($data['payment_count'])) {
             return ['success' => false, 'message' => 'Nombre y cantidad de pagos son requeridos'];
        }

        if ($this->model->update($id, $data)) {
            return ['success' => true, 'message' => 'Rubro actualizado'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el rubro'];
    }

    public function delete($id) {
        $validation = $this->model->canDeleteCategory($id);
        if (!$validation['can_delete']) {
             return ['success' => false, 'message' => $validation['message']];
        }

        if ($this->model->deleteCategory($id)) {
            return ['success' => true, 'message' => 'Rubro eliminado'];
        }
        return ['success' => false, 'message' => 'Error al eliminar'];
    }
}
