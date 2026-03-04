<?php

require_once __DIR__ . '/../models/ComplaintTrackingModel.php';
require_once __DIR__ . '/../models/ComplaintsModel.php';

class ComplaintTrackingController {
    private $model;
    private $complaintsModel;

    public function __construct() {
        $this->model = new ComplaintTrackingModel();
        $this->complaintsModel = new ComplaintsModel();
    }

    /**
     * Muestra una lista de todos los registros de seguimiento para una queja específica.
     *
     * @param int $complaint_id El ID de la queja a la que pertenecen los registros de seguimiento.
     * @return array
     */
    public function index($complaint_id) {
        $tracking_records = $this->model->getAllByComplaintId($complaint_id);
        $complaint = $this->complaintsModel->getById($complaint_id);

        if (!$complaint) {
            return ['success' => false, 'message' => 'Queja no encontrada.'];
        }

        return [
            'success' => true,
            'tracking_records' => $tracking_records,
            'complaint' => $complaint,
            'page_title' => 'Seguimiento de Queja #' . htmlspecialchars($complaint_id)
        ];
    }
    
    /**
     * Almacena un nuevo registro de seguimiento en la base de datos.
     *
     * @param array $data Los datos del formulario.
     * @return array
     */
    public function store($data) {
        $validation = $this->validate($data);
        if (!$validation['success']) {
            return $validation;
        }

        $result = $this->model->create($data);
        if ($result['success']) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Registro de seguimiento creado exitosamente.'];
            return ['success' => true, 'redirect' => '../complaints/view.php?id=' . $data['complaint_id']];
        } else {
            return ['success' => false, 'message' => 'Error al crear el registro.'];
        }
    }

    /**
     * Prepara los datos para el formulario de edición.
     *
     * @param int $id El ID del registro de seguimiento a editar.
     * @return array
     */
    public function edit($id) {
        $tracking_record = $this->model->getById($id);
        
        if (!$tracking_record) {
            return ['success' => false, 'message' => 'Registro no encontrado.'];
        }

        return [
            'success' => true,
            'tracking_record' => $tracking_record,
            'page_title' => 'Editar Registro de Seguimiento'
        ];
    }
    
    /**
     * Actualiza un registro de seguimiento existente en la base de datos.
     *
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los datos del formulario.
     * @return array
     */
    public function update($id, $data) {
        $validation = $this->validate($data);
        if (!$validation['success']) {
            return $validation;
        }

        $result = $this->model->update($id, $data);
        if ($result['success']) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Registro de seguimiento actualizado exitosamente.'];
            return ['success' => true, 'redirect' => '../complaints/view.php?id=' . $data['complaint_id']];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar el registro.'];
        }
    }

    /**
     * Elimina un registro de seguimiento.
     *
     * @param int $id El ID del registro a eliminar.
     * @return array
     */
    public function delete($id) {
        $result = $this->model->delete($id);
        if ($result['success']) {
            return ['success' => true, 'message' => 'Registro de seguimiento eliminado exitosamente.'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar el registro.'];
        }
    }

    /**
     * Valida los datos del formulario.
     *
     * @param array $data Los datos a validar.
     * @return array
     */
    private function validate($data) {
        $errors = [];
        if (empty($data['complaint_id'])) {
            $errors[] = 'El ID de la queja es obligatorio.';
        }
        if (empty($data['admin_user_id'])) {
            $errors[] = 'El usuario administrador es obligatorio.';
        }
        if (empty($data['action_type'])) {
            $errors[] = 'El tipo de acción es obligatorio.';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        return ['success' => true];
    }
}