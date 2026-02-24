<?php
require_once __DIR__ . '/../models/ContractModel.php';
require_once __DIR__ . '/../models/AwardeeModel.php';
require_once __DIR__ . '/../models/FiscalYearModel.php';
require_once __DIR__ . '/../models/InternalBusinessCategoryModel.php';
require_once __DIR__ . '/../models/ExternalBusinessCategoryModel.php';
require_once __DIR__ . '/../models/MarketStallModel.php';
require_once __DIR__ . '/../models/ZoneModel.php';
require_once __DIR__ . '/../models/ContractPaymentModel.php';
require_once __DIR__ . '/../models/ContractPaymentInstallmentModel.php';

class ContractController {
    private $contractModel;
    private $awardeeModel;
    private $fiscalYearModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->contractModel = new ContractModel();
        $this->awardeeModel = new AwardeeModel();
        $this->fiscalYearModel = new FiscalYearModel();
    }
    
    /**
     * Lista todos los contratos
     */
    public function index() {
        $filters = [
            'awardee' => $_GET['awardee'] ?? '',
            'fiscal_year_id' => $_GET['fiscal_year_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type' => $_GET['type'] ?? ''
        ];

        $contracts = $this->contractModel->getAll($filters);
        $metrics = $this->contractModel->getMetrics();
        $fiscalYears = $this->fiscalYearModel->getAll();
        
        return [
            'page_title' => 'Contratos',
            'contracts' => $contracts,
            'metrics' => $metrics,
            'filters' => $filters,
            'fiscalYears' => $fiscalYears
        ];
    }
    
    /**
     * Prepara datos para crear un contrato
     */
    public function create() {
        $internalCategoryModel = new InternalBusinessCategoryModel();
        $externalCategoryModel = new ExternalBusinessCategoryModel();
        $zoneModel = new ZoneModel();
        
        return [
            'page_title' => 'Crear Contrato',
            'awardees' => $this->awardeeModel->getAll(),
            'fiscalYears' => $this->fiscalYearModel->getAll(),
            'internalCategories' => $internalCategoryModel->getAll(),
            'externalCategories' => $externalCategoryModel->getAll(),
            'zones' => $zoneModel->getAll()
        ];
    }
    
    /**
     * Procesa la creación de un contrato
     */
    public function store($data) {
        $awardeeId = (int) ($data['awardee_id'] ?? 0);
        $fiscalYearId = (int) ($data['fiscal_year_id'] ?? 0);
        $startDate = $data['start_date'] ?? '';
        $endDate = $data['end_date'] ?? '';
        $type = $data['type'] ?? '';
        $contractMode = $data['contract_mode'] ?? '';
        
        $externalCategories = $data['external_categories'] ?? [];
        $internalCategories = $data['internal_categories'] ?? [];
        $locationIds = $data['location_ids'] ?? [];
        
        if (empty($awardeeId) || empty($fiscalYearId) || empty($startDate) || empty($endDate)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son requeridos'
            ];
        }
        
        $categories = [];
        foreach ($externalCategories as $categoryId) {
            if (!empty($categoryId)) {
                $categories[] = ['type' => 'external', 'id' => (int) $categoryId];
            }
        }
        foreach ($internalCategories as $categoryId) {
            if (!empty($categoryId)) {
                $categories[] = ['type' => 'internal', 'id' => (int) $categoryId];
            }
        }
        
        $id = $this->contractModel->create([
            'awardee_id' => $awardeeId,
            'fiscal_year_id' => $fiscalYearId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => $type,
            'contract_mode' => $contractMode
        ], $categories, $locationIds);
        
        if ($id) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Contrato creado exitosamente'
            ];
            return [
                'success' => true,
                'message' => 'Contrato creado exitosamente',
                'redirect' => 'detail.php?id=' . $id,
                'id' => $id
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al crear el contrato'
        ];
    }
    
    /**
     * Muestra el detalle de un contrato
     */
    public function detail(int $id) {
        $contract = $this->contractModel->getById($id);
        
        if (!$contract) {
            return [
                'success' => false,
                'message' => 'Contrato no encontrado',
                'redirect' => 'index.php'
            ];
        }
        
        $categories = $this->contractModel->getCategories($id);
        $locations = $this->contractModel->getLocations($id);
        
        $paymentModel = new ContractPaymentModel();
        $payments = $paymentModel->getByContract($id);
        
        $zoneModel = new ZoneModel();
        $zones = $zoneModel->getAll();
        
        $internalCategoryModel = new InternalBusinessCategoryModel();
        $externalCategoryModel = new ExternalBusinessCategoryModel();
        
        return [
            'success' => true,
            'page_title' => 'Detalle de Contrato',
            'contract' => $contract,
            'categories' => $categories,
            'locations' => $locations,
            'payments' => $payments,
            'zones' => $zones,
            'internalCategories' => $internalCategoryModel->getAll(),
            'externalCategories' => $externalCategoryModel->getAll()
        ];
    }
    
    /**
     * Prepara datos para editar
     */
    public function edit(int $id) {
        $contract = $this->contractModel->getById($id);
        
        if (!$contract) {
            return [
                'success' => false,
                'message' => 'Contrato no encontrado',
                'redirect' => 'index.php'
            ];
        }
        
        return [
            'success' => true,
            'page_title' => 'Editar Contrato',
            'contract' => $contract,
            'awardees' => $this->awardeeModel->getAll(),
            'fiscalYears' => $this->fiscalYearModel->getAll()
        ];
    }
    
    /**
     * Procesa la actualización
     */
    public function update(int $id, $data) {
        $awardeeId = (int) ($data['awardee_id'] ?? 0);
        $fiscalYearId = (int) ($data['fiscal_year_id'] ?? 0);
        $startDate = $data['start_date'] ?? '';
        $endDate = $data['end_date'] ?? '';
        $type = $data['type'] ?? '';
        $contractMode = $data['contract_mode'] ?? '';
        
        if (empty($awardeeId) || empty($fiscalYearId) || empty($startDate) || empty($endDate)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son requeridos'
            ];
        }
        
        $success = $this->contractModel->update($id, [
            'awardee_id' => $awardeeId,
            'fiscal_year_id' => $fiscalYearId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => $type,
            'contract_mode' => $contractMode
        ]);
        
        if ($success) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Contrato actualizado exitosamente'
            ];
            return [
                'success' => true,
                'message' => 'Contrato actualizado exitosamente',
                'redirect' => 'detail.php?id=' . $id
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al actualizar el contrato'
        ];
    }
    
    /**
     * Elimina un contrato (AJAX)
     */
    public function delete(int $id) {
        $validation = $this->contractModel->canDeleteContract($id);
        if (!$validation['can_delete']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        $success = $this->contractModel->deleteContract($id);
        return [
            'success' => $success,
            'message' => $success ? 'Contrato eliminado' : 'Error al eliminar'
        ];
    }
    
    /**
     * Bulk actions
     */
    public function bulkDelete($ids) {
        if (empty($ids)) return ['success' => false, 'message' => 'No IDs'];
        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->contractModel->deleteContract((int)$id)) {
                $deleted++;
            }
        }
        return ['success' => true, 'message' => "Eliminados $deleted registros"];
    }
    
    public function bulkUpdateStatus($data) {
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? '';
        if (empty($ids) || empty($status)) return ['success' => false, 'message' => 'Datos incompletos'];
        
        $updated = 0;
        foreach ($ids as $id) {
            if ($this->contractModel->updateStatus((int)$id, $status)) {
                $updated++;
            }
        }
        return ['success' => true, 'updated' => $updated];
    }
    
    public function bulkUpdatePaymentStatus($data) {
        $ids = $data['ids'] ?? [];
        $status = $data['status_payment'] ?? '';
        if (empty($ids) || empty($status)) return ['success' => false, 'message' => 'Datos incompletos'];
        
        $updated = 0;
        foreach ($ids as $id) {
            if ($this->contractModel->updatePaymentStatus((int)$id, $status)) {
                $updated++;
            }
        }
        return ['success' => true, 'updated' => $updated];
    }
    
    public function addLocation($data) {
        $contractId = (int) ($data['contract_id'] ?? 0);
        $createNew = $data['create_new'] ?? false;
        
        if ($createNew) {
            $marketStallModel = new MarketStallModel();
            $stallId = $marketStallModel->create([
                'sector_id' => $data['sector_id'],
                'stall_number' => $data['stall_number'],
                'location_description' => $data['description'] ?? ''
            ]);
        } else {
            $stallId = (int) ($data['stall_id'] ?? 0);
        }
        
        if (!$stallId) return ['success' => false, 'message' => 'Error al obtener local'];
        
        $success = $this->contractModel->addLocation($contractId, $stallId);
        return ['success' => $success, 'message' => $success ? 'Local agregado' : 'Error'];
    }
    
    public function removeLocation($data) {
        $contractId = (int) ($data['contract_id'] ?? 0);
        $stallId = (int) ($data['stall_id'] ?? 0);
        $success = $this->contractModel->removeLocation($contractId, $stallId);
        return ['success' => $success];
    }
    
    public function addCategory($data) {
        $contractId = (int) ($data['contract_id'] ?? 0);
        $type = $data['category_type'] ?? '';
        $categoryId = (int) ($data['category_id'] ?? 0);
        $success = $this->contractModel->addCategory($contractId, $type, $categoryId);
        return ['success' => $success];
    }
    
    public function removeCategory($data) {
        $contractId = (int) ($data['contract_id'] ?? 0);
        $type = $data['category_type'] ?? '';
        $categoryId = (int) ($data['category_id'] ?? 0);
        $success = $this->contractModel->removeCategory($contractId, $type, $categoryId);
        return ['success' => $success];
    }
    
    public function deletePayment(int $id) {
        $paymentModel = new ContractPaymentModel();
        $success = $paymentModel->deletePayment($id);
        return ['success' => $success];
    }
    
    public function searchAwardee($data) {
        $idNumber = $data['id_number'] ?? '';
        $awardee = $this->awardeeModel->getByIdNumber($idNumber);
        if (!$awardee) return ['success' => false];
        $contracts = $this->contractModel->getByAwardee($awardee['id']);
        return ['success' => true, 'awardee' => $awardee, 'contracts' => $contracts];
    }
    
    public function bulkUpdateIndividualPaymentStatus($data) {
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? '';
        $model = new ContractPaymentModel();
        $updated = 0;
        foreach ($ids as $id) {
            if ($model->updatePaymentStatus((int)$id, $status)) $updated++;
        }
        return ['success' => $updated > 0, 'updated' => $updated];
    }
    
    public function bulkDeletePayments($data) {
        $ids = $data['ids'] ?? [];
        $model = new ContractPaymentModel();
        $deleted = 0;
        foreach ($ids as $id) {
            if ($model->deletePayment((int)$id)) $deleted++;
        }
        return ['success' => $deleted > 0, 'deleted' => $deleted];
    }
}
