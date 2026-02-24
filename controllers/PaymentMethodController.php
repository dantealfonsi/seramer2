<?php
require_once __DIR__ . '/../models/PaymentMethodModel.php';

class PaymentMethodController {
    private $model;

    public function __construct() {
        $this->model = new PaymentMethodModel();
    }

    public function index() {
        $filters = [
            'name' => $_GET['name'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        $methods = $this->model->getAll($filters);
        return [
            'page_title' => 'Métodos de Pago',
            'methods' => $methods,
            'filters' => $filters
        ];
    }

    public function create() {
        return ['page_title' => 'Crear Método de Pago'];
    }

    public function store($data) {
        if (empty($data['name'])) {
             return ['success' => false, 'message' => 'El nombre es requerido'];
        }

        $id = $this->model->create($data);
        if ($id) {
            return ['success' => true, 'message' => 'Método de pago creado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al crear el método de pago'];
    }

    public function edit($id) {
        $method = $this->model->getById($id);
        if (!$method) return null;
        
        return [
            'page_title' => 'Editar Método de Pago',
            'method' => $method
        ];
    }

    public function update($id, $data) {
        if (empty($data['name'])) {
             return ['success' => false, 'message' => 'El nombre es requerido'];
        }

        if ($this->model->update($id, $data)) {
            return ['success' => true, 'message' => 'Método de pago actualizado'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el método de pago'];
    }

    public function delete($id) {
        $validation = $this->model->canDeleteMethod($id);
        if (!$validation['can_delete']) {
             return ['success' => false, 'message' => $validation['message']];
        }

        if ($this->model->deleteMethod($id)) {
            return ['success' => true, 'message' => 'Método de pago eliminado'];
        }
        return ['success' => false, 'message' => 'Error al eliminar'];
    }

    public function toggleStatus($id, $status) {
        if ($this->model->toggleActive($id, $status)) {
            return ['success' => true, 'message' => 'Estado actualizado'];
        }
        return ['success' => false, 'message' => 'Error al actualizar estado'];
    }
}
