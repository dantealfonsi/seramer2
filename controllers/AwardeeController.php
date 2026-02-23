<?php
require_once __DIR__ . '/../models/AwardeeModel.php';
require_once __DIR__ . '/../models/ContractModel.php';
require_once __DIR__ . '/../models/ContractPaymentModel.php';

class AwardeeController {
    private $awardeeModel;
    private $contractModel;
    private $paymentModel;

    public function __construct() {
        $this->awardeeModel = new AwardeeModel();
        $this->contractModel = new ContractModel();
        $this->paymentModel = new ContractPaymentModel();
    }

    public function index($params = []) {
        $filters = $params['filters'] ?? [];
        $awardees = $this->awardeeModel->getAll($filters);
        return [
            'page_title' => 'Gestión de Adjudicatarios',
            'awardees' => $awardees,
            'search' => $params['search'] ?? ''
        ];
    }

    public function create() {
        return ['page_title' => 'Registrar Nuevo Adjudicatario'];
    }

    public function store($data) {
        // Validation
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['id_number'])) {
            return ['success' => false, 'message' => 'Nombre, apellido y cédula son requeridos'];
        }

        // Check duplicate
        if ($this->awardeeModel->getByIdNumber($data['id_number'])) {
             return ['success' => false, 'message' => 'Ya existe un adjudicatario con esta cédula'];
        }

        $id = $this->awardeeModel->create($data);
        if ($id) {
            return ['success' => true, 'message' => 'Adjudicatario registrado exitosamente', 'id' => $id];
        }
        return ['success' => false, 'message' => 'Error al registrar el adjudicatario'];
    }

    public function edit($id) {
        $awardee = $this->awardeeModel->getById($id);
        if (!$awardee) {
            return null;
        }
        return [
            'page_title' => 'Editar Adjudicatario',
            'awardee' => $awardee
        ];
    }

    public function update($id, $data) {
         if (empty($data['first_name']) || empty($data['last_name']) || empty($data['id_number'])) {
            return ['success' => false, 'message' => 'Nombre, apellido y cédula son requeridos'];
        }

        $success = $this->awardeeModel->update($id, $data);
        if ($success) {
            return ['success' => true, 'message' => 'Adjudicatario actualizado correctamente'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el adjudicatario'];
    }

    public function delete($id) {
        $validation = $this->awardeeModel->canDeleteAwardee($id);
        if (!$validation['can_delete']) {
             return ['success' => false, 'message' => $validation['message']];
        }

        if ($this->awardeeModel->deleteAwardee($id)) { // Using deleteAwardee from ported model
             return ['success' => true, 'message' => 'Adjudicatario eliminado'];
        }
        return ['success' => false, 'message' => 'Error al eliminar'];
    }
    
    public function showContracts($id) {
        $awardee = $this->awardeeModel->getById($id);
        if (!$awardee) return null;

        $contracts = $this->contractModel->getByAwardee($id);
        
        // Enrich data
        foreach ($contracts as &$contract) {
             $contract['categories'] = $this->contractModel->getCategories($contract['id']);
             $contract['locations'] = $this->contractModel->getLocations($contract['id']);
             $contract['payments'] = $this->paymentModel->getByContract($contract['id']);
        }

        return [
            'page_title' => 'Contratos de: ' . $awardee['first_name'] . ' ' . $awardee['last_name'],
            'awardee' => $awardee,
            'contracts' => $contracts
        ];
    }
}
