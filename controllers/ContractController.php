<?php
require_once __DIR__ . '/../models/ContractModel.php';
require_once __DIR__ . '/../models/AwardeeModel.php';
require_once __DIR__ . '/../models/FiscalYearModel.php';
require_once __DIR__ . '/../models/InternalBusinessCategoryModel.php';
require_once __DIR__ . '/../models/ExternalBusinessCategoryModel.php';
require_once __DIR__ . '/../models/MarketStallModel.php';
require_once __DIR__ . '/../models/ZoneModel.php';
require_once __DIR__ . '/../models/ContractPaymentModel.php';

class ContractController {
    private $contractModel;
    private $awardeeModel;
    private $fiscalYearModel;
    
    public function __construct() {
        $this->contractModel = new ContractModel();
        $this->awardeeModel = new AwardeeModel();
        $this->fiscalYearModel = new FiscalYearModel();
    }
    
    public function index($params = []) {
        $contracts = $this->contractModel->getAll();
        $metrics = $this->contractModel->getMetrics();
        
        return [
            'page_title' => 'Contratos',
            'contracts' => $contracts,
            'metrics' => $metrics
        ];
    }
    
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
                $categories[] = [
                    'type' => 'external',
                    'id' => (int) $categoryId
                ];
            }
        }
        
        foreach ($internalCategories as $categoryId) {
            if (!empty($categoryId)) {
                $categories[] = [
                    'type' => 'internal',
                    'id' => (int) $categoryId
                ];
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
        } else {
             return [
                'success' => false,
                'message' => 'Error al crear el contrato'
            ];
        }
    }
    
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
        $internalCategories = $internalCategoryModel->getAll();
        $externalCategories = $externalCategoryModel->getAll();
        
        return [
            'success' => true,
            'page_title' => 'Detalle de Contrato',
            'contract' => $contract,
            'categories' => $categories,
            'locations' => $locations,
            'payments' => $payments,
            'zones' => $zones,
            'internalCategories' => $internalCategories,
            'externalCategories' => $externalCategories
        ];
    }
    
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
    
    public function update(int $id, $data) {
        $contract = $this->contractModel->getById($id);
        
        if (!$contract) {
            return ['success' => false, 'message' => 'Contrato no encontrado'];
        }
        
        $awardeeId = (int) ($data['awardee_id'] ?? 0);
        $fiscalYearId = (int) ($data['fiscal_year_id'] ?? 0);
        $startDate = $data['start_date'] ?? '';
        $endDate = $data['end_date'] ?? '';
        $type = $data['type'] ?? '';
        $contractMode = $data['contract_mode'] ?? '';
        
        if (empty($awardeeId) || empty($fiscalYearId) || empty($startDate) || empty($endDate)) {
            return ['success' => false, 'message' => 'Todos los campos son requeridos'];
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
        } else {
             return ['success' => false, 'message' => 'Error al actualizar el contrato'];
        }
    }
    
    public function delete(int $id) {
        $validation = $this->contractModel->canDeleteContract($id);
        
        if (!$validation['can_delete']) {
             return ['success' => false, 'message' => $validation['message']];
        }
        
        $success = $this->contractModel->deleteContract($id);
        
        if ($success) {
             return ['success' => true, 'message' => 'Contrato eliminado exitosamente'];
        } else {
             return ['success' => false, 'message' => 'Error al eliminar el contrato'];
        }
    }

    public function addLocation($data) {
        $contractId = (int) ($data['contract_id'] ?? 0);
        $createNew = $data['create_new'] ?? false;
        
        if (!$contractId) {
            return ['success' => false, 'message' => 'ID de contrato requerido'];
        }
        
        $contract = $this->contractModel->getById($contractId);
        if (!$contract) {
            return ['success' => false, 'message' => 'Contrato no encontrado'];
        }
        
        $stallId = 0;
        
        if ($createNew) {
            $sectorId = (int) ($data['sector_id'] ?? 0);
            $stallNumber = $data['stall_number'] ?? '';
            $description = $data['description'] ?? '';
            
            if (!$sectorId || !$stallNumber) {
                return ['success' => false, 'message' => 'Sector y número de local son requeridos'];
            }
            
            $marketStallModel = new MarketStallModel();
            $existing = $marketStallModel->getByStallNumber($sectorId, $stallNumber);
            
            if ($existing) {
                return ['success' => false, 'message' => 'Ya existe un local con ese número en este sector'];
            }
            
            $stallId = $marketStallModel->create([
                'sector_id' => $sectorId,
                'stall_number' => $stallNumber,
                'location_description' => $description
            ]);
            
            if (!$stallId) {
                return ['success' => false, 'message' => 'Error al crear el local'];
            }
        } else {
            $stallId = (int) ($data['stall_id'] ?? 0);
            if (!$stallId) {
                return ['success' => false, 'message' => 'ID de local requerido'];
            }
        }
        
        // Check if stall is already assigned to this contract
        $existingLocations = $this->contractModel->getLocations($contractId);
        foreach ($existingLocations as $location) {
            if ($location['stall_id'] == $stallId) {
                return ['success' => false, 'message' => 'Este local ya está asignado a este contrato'];
            }
        }
        
        // VALIDACIÓN IMPORTANTE: Verificar que el adjudicatario no tenga este local en otro contrato activo
        $awardeeId = $contract['awardee_id'];
        $validation = $this->contractModel->canAssignLocationToAwardee($awardeeId, $stallId, $contractId);
        
        if (!$validation['can_assign']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        $success = $this->contractModel->addLocation($contractId, $stallId);
        
        if ($success) {
            $message = $createNew ? 'Local creado y agregado exitosamente' : 'Local agregado exitosamente';
            return ['success' => true, 'message' => $message];
        } else {
            return ['success' => false, 'message' => 'Error al agregar el local'];
        }
    }
    
    public function removeLocation($data) {
        $contractId = (int) ($data['contract_id'] ?? 0);
        $stallId = (int) ($data['stall_id'] ?? 0);
        
        if (!$contractId || !$stallId) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }
        
        $success = $this->contractModel->removeLocation($contractId, $stallId);
        
        if ($success) {
            return ['success' => true, 'message' => 'Local eliminado del contrato'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar el local'];
        }
    }
    
    public function addCategory($data) {
        $contractId = (int) ($data['contract_id'] ?? 0);
        $categoryType = $data['category_type'] ?? '';
        $categoryId = (int) ($data['category_id'] ?? 0);
        
        if (!$contractId || !$categoryType || !$categoryId) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }
        
        $contract = $this->contractModel->getById($contractId);
        if (!$contract) {
             return ['success' => false, 'message' => 'Contrato no encontrado'];
        }
        
        $existingCategories = $this->contractModel->getCategories($contractId);
        foreach ($existingCategories as $category) {
            if ($categoryType === 'internal' && isset($category['internal_category_id']) && $category['internal_category_id'] == $categoryId) {
                 return ['success' => false, 'message' => 'Esta categoría ya está asignada al contrato'];
            }
            if ($categoryType === 'external' && isset($category['external_category_id']) && $category['external_category_id'] == $categoryId) {
                 return ['success' => false, 'message' => 'Esta categoría ya está asignada al contrato'];
            }
        }
        
        $success = $this->contractModel->addCategory($contractId, $categoryType, $categoryId);
        
        if ($success) {
             return ['success' => true, 'message' => 'Categoría agregada exitosamente'];
        } else {
             return ['success' => false, 'message' => 'Error al agregar la categoría'];
        }
    }
    
    public function removeCategory($data) {
        $contractId = (int) ($data['contract_id'] ?? 0);
        $categoryType = $data['category_type'] ?? '';
        $categoryId = (int) ($data['category_id'] ?? 0);
        
        if (!$contractId || !$categoryType || !$categoryId) {
             return ['success' => false, 'message' => 'Datos incompletos'];
        }
        
        $success = $this->contractModel->removeCategory($contractId, $categoryType, $categoryId);
        
        if ($success) {
             return ['success' => true, 'message' => 'Categoría eliminada del contrato'];
        } else {
             return ['success' => false, 'message' => 'Error al eliminar la categoría'];
        }
    }
    
}
