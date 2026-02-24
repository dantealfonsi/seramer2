<?php
require_once __DIR__ . '/../models/FiscalYearModel.php';

class FiscalYearController {
    private $model;

    public function __construct() {
        $this->model = new FiscalYearModel();
    }

    public function index() {
        $fiscalYears = $this->model->getAll();
        return [
            'page_title' => 'Gestión de Años Fiscales',
            'fiscalYears' => $fiscalYears
        ];
    }

    public function create() {
        return ['page_title' => 'Nuevo Año Fiscal'];
    }

    public function store($data) {
        if (empty($data['year']) || empty($data['start_date']) || empty($data['end_date'])) {
            return ['success' => false, 'message' => 'Todos los campos son requeridos'];
        }

        if ($this->model->yearExists($data['year'])) {
            return ['success' => false, 'message' => 'El año fiscal ya existe'];
        }

        $id = $this->model->create($data);
        if ($id) {
            return ['success' => true, 'message' => 'Año fiscal creado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al crear el año fiscal'];
    }

    public function edit($id) {
        $fiscalYear = $this->model->getById($id);
        if (!$fiscalYear) return null;
        
        return [
            'page_title' => 'Editar Año Fiscal',
            'fiscalYear' => $fiscalYear
        ];
    }

    public function update($id, $data) {
        if (empty($data['year']) || empty($data['start_date']) || empty($data['end_date'])) {
            return ['success' => false, 'message' => 'Todos los campos son requeridos'];
        }

        if ($this->model->update($id, $data)) {
            return ['success' => true, 'message' => 'Año fiscal actualizado'];
        }
        return ['success' => false, 'message' => 'Error al actualizar'];
    }

    public function delete($id) {
        $validation = $this->model->canDeleteFiscalYear($id);
        if (!$validation['can_delete']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        if ($this->model->deleteFiscalYear($id)) {
            return ['success' => true, 'message' => 'Año fiscal eliminado'];
        }
        return ['success' => false, 'message' => 'Error al eliminar'];
    }
}
