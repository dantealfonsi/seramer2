<?php
require_once __DIR__ . '/../models/EuroRateModel.php';

class EuroRateController {
    private $model;

    public function __construct() {
        $this->model = new EuroRateModel();
    }

    public function index() {
        $rates = $this->model->getAll();
        return [
            'page_title' => 'Tasas de Cambio',
            'rates' => $rates
        ];
    }

    public function create() {
        // Prepare data for form (e.g. months)
        return ['page_title' => 'Registrar Tasa'];
    }

    public function store($data) {
        if (empty($data['month']) || empty($data['year']) || empty($data['bs_value'])) {
             return ['success' => false, 'message' => 'Mes, año y valor son requeridos'];
        }

        // Check duplicate
        if ($this->model->getByMonthYear($data['month'], $data['year'])) {
             return ['success' => false, 'message' => 'Ya existe una tasa para este mes y año'];
        }

        $id = $this->model->create($data);
        if ($id) {
            return ['success' => true, 'message' => 'Tasa creada exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al crear la tasa'];
    }

    public function edit($id) {
        $rate = $this->model->getById($id);
        if (!$rate) return null;
        
        return [
            'page_title' => 'Editar Tasa',
            'rate' => $rate
        ];
    }

    public function update($id, $data) {
        if (empty($data['bs_value'])) {
             return ['success' => false, 'message' => 'Valor es requerido'];
        }

        if ($this->model->update($id, $data)) {
            return ['success' => true, 'message' => 'Tasa actualizada'];
        }
        return ['success' => false, 'message' => 'Error al actualizar la tasa'];
    }

    public function delete($id) {
         $validation = $this->model->canDeleteRate($id);
        if (!$validation['can_delete']) {
             return ['success' => false, 'message' => $validation['message']];
        }

        if ($this->model->deleteRate($id)) {
            return ['success' => true, 'message' => 'Tasa eliminada'];
        }
        return ['success' => false, 'message' => 'Error al eliminar'];
    }
}
