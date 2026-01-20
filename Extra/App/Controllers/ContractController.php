<?php
/**
 * Controlador de Contratos
 * 
 * Gestiona los contratos de adjudicación
 * Implementa RF07-RF12
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\ContractModel;
use App\Models\AwardeeModel;
use App\Models\FiscalYearModel;
use App\Models\InternalBusinessCategoryModel;
use App\Models\ExternalBusinessCategoryModel;
use App\Models\MarketStallModel;

class ContractController extends Controller {
    private ContractModel $contractModel;
    private AwardeeModel $awardeeModel;
    private FiscalYearModel $fiscalYearModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->contractModel = new ContractModel();
        $this->awardeeModel = new AwardeeModel();
        $this->fiscalYearModel = new FiscalYearModel();
    }
    
    /**
     * Lista todos los contratos
     */
    public function index(): void {
        $contracts = $this->contractModel->getAll();
        $metrics = $this->contractModel->getMetrics();
        
        $data = [
            'title' => 'Contratos',
            'contracts' => $contracts,
            'metrics' => $metrics
        ];
        
        $this->view('Contract/Index', $data);
    }
    
    /**
     * Muestra el formulario para crear un contrato (RF08)
     */
    public function create(): void {
        $internalCategoryModel = new InternalBusinessCategoryModel();
        $externalCategoryModel = new ExternalBusinessCategoryModel();
        $zoneModel = new \App\Models\ZoneModel();
        
        $data = [
            'title' => 'Crear Contrato',
            'awardees' => $this->awardeeModel->getAll(),
            'fiscalYears' => $this->fiscalYearModel->getAll(),
            'internalCategories' => $internalCategoryModel->getAll(),
            'externalCategories' => $externalCategoryModel->getAll(),
            'zones' => $zoneModel->getAll()
        ];
        
        $this->view('Contract/Create', $data);
    }
    
    /**
     * Procesa la creación de un contrato (RF08 + RF10)
     */
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('contract/index');
        }
        
        $this->requireCsrfToken();
        
        $awardeeId = (int) $this->post('awardee_id');
        $fiscalYearId = (int) $this->post('fiscal_year_id');
        $startDate = $this->post('start_date');
        $endDate = $this->post('end_date');
        $type = $this->post('type');
        $contractMode = $this->post('contract_mode');
        
        // Categorías y locales (RF10)
        $externalCategories = $this->post('external_categories', []);
        $internalCategories = $this->post('internal_categories', []);
        $locationIds = $this->post('location_ids', []);
        
        // Validar campos básicos
        if (empty($awardeeId) || empty($fiscalYearId) || empty($startDate) || empty($endDate)) {
            Session::flash('error', 'Todos los campos son requeridos');
            $this->redirect('contract/create');
        }
        
        // Preparar categorías (combinar externas e internas)
        $categories = [];
        
        // Agregar categorías externas
        foreach ($externalCategories as $categoryId) {
            if (!empty($categoryId)) {
                $categories[] = [
                    'type' => 'external',
                    'id' => (int) $categoryId
                ];
            }
        }
        
        // Agregar categorías internas
        foreach ($internalCategories as $categoryId) {
            if (!empty($categoryId)) {
                $categories[] = [
                    'type' => 'internal',
                    'id' => (int) $categoryId
                ];
            }
        }
        
        // Crear el contrato
        $id = $this->contractModel->create([
            'awardee_id' => $awardeeId,
            'fiscal_year_id' => $fiscalYearId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => $type,
            'contract_mode' => $contractMode
        ], $categories, $locationIds);
        
        if ($id) {
            Session::flash('success', 'Contrato creado exitosamente');
            $this->redirect('contract/detail/' . $id);
        } else {
            Session::flash('error', 'Error al crear el contrato');
            $this->redirect('contract/create');
        }
    }
    
    /**
     * Muestra el detalle de un contrato
     */
    public function detail(int $id): void {
        $contract = $this->contractModel->getById($id);
        
        if (!$contract) {
            Session::flash('error', 'Contrato no encontrado');
            $this->redirect('contract/index');
        }
        
        $categories = $this->contractModel->getCategories($id);
        $locations = $this->contractModel->getLocations($id);
        
        // Obtener los pagos del contrato
        $paymentModel = new \App\Models\ContractPaymentModel();
        $payments = $paymentModel->getByContract($id);
        
        // Obtener zonas para el modal de agregar locales
        $zoneModel = new \App\Models\ZoneModel();
        $zones = $zoneModel->getAll();
        
        // Obtener categorías internas y externas para el modal
        $internalCategoryModel = new \App\Models\InternalBusinessCategoryModel();
        $externalCategoryModel = new \App\Models\ExternalBusinessCategoryModel();
        $internalCategories = $internalCategoryModel->getAll();
        $externalCategories = $externalCategoryModel->getAll();
        
        $data = [
            'title' => 'Detalle de Contrato',
            'contract' => $contract,
            'categories' => $categories,
            'locations' => $locations,
            'payments' => $payments,
            'zones' => $zones,
            'internalCategories' => $internalCategories,
            'externalCategories' => $externalCategories
        ];
        
        $this->view('Contract/Detail', $data);
    }
    
    /**
     * Muestra el formulario para editar un contrato
     */
    public function edit(int $id): void {
        $contract = $this->contractModel->getById($id);
        
        if (!$contract) {
            Session::flash('error', 'Contrato no encontrado');
            $this->redirect('contract/index');
        }
        
        $data = [
            'title' => 'Editar Contrato',
            'contract' => $contract,
            'awardees' => $this->awardeeModel->getAll(),
            'fiscalYears' => $this->fiscalYearModel->getAll()
        ];
        
        $this->view('Contract/Edit', $data);
    }
    
    /**
     * Procesa la actualización de un contrato
     */
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('contract/index');
        }
        
        $this->requireCsrfToken();
        
        $contract = $this->contractModel->getById($id);
        
        if (!$contract) {
            Session::flash('error', 'Contrato no encontrado');
            $this->redirect('contract/index');
        }
        
        $awardeeId = (int) $this->post('awardee_id');
        $fiscalYearId = (int) $this->post('fiscal_year_id');
        $startDate = $this->post('start_date');
        $endDate = $this->post('end_date');
        $type = $this->post('type');
        $contractMode = $this->post('contract_mode');
        
        // Validar
        if (empty($awardeeId) || empty($fiscalYearId) || empty($startDate) || empty($endDate)) {
            Session::flash('error', 'Todos los campos son requeridos');
            $this->redirect('contract/edit/' . $id);
        }
        
        // Actualizar el contrato
        $success = $this->contractModel->update($id, [
            'awardee_id' => $awardeeId,
            'fiscal_year_id' => $fiscalYearId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => $type,
            'contract_mode' => $contractMode
        ]);
        
        if ($success) {
            Session::flash('success', 'Contrato actualizado exitosamente');
            $this->redirect('contract/detail/' . $id);
        } else {
            Session::flash('error', 'Error al actualizar el contrato');
            $this->redirect('contract/edit/' . $id);
        }
    }
    
    /**
     * Elimina un contrato (AJAX)
     */
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        // Verificar si el contrato puede ser eliminado
        $validation = $this->contractModel->canDeleteContract($id);
        
        if (!$validation['can_delete']) {
            $this->json(['success' => false, 'message' => $validation['message']], 400);
        }
        
        // Eliminar el contrato
        $success = $this->contractModel->deleteContract($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Contrato eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el contrato'], 400);
        }
    }
    
    /**
     * Elimina múltiples contratos (AJAX)
     */
    public function bulkDelete(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        $ids = $this->post('ids', []);
        
        if (empty($ids) || !is_array($ids)) {
            $this->json(['success' => false, 'message' => 'No se seleccionaron registros']);
        }
        
        $deleted = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            // Verificar si el contrato puede ser eliminado
            $validation = $this->contractModel->canDeleteContract((int)$id);
            
            if (!$validation['can_delete']) {
                $errors[] = "Contrato ID {$id}: " . $validation['message'];
                continue;
            }
            
            if ($this->contractModel->deleteContract((int)$id)) {
                $deleted++;
            } else {
                $errors[] = "Error al eliminar contrato ID {$id}";
            }
        }
        
        if ($deleted > 0) {
            $message = "Se eliminaron {$deleted} contrato(s) exitosamente";
            if (!empty($errors)) {
                $message .= ". " . implode(', ', $errors);
            }
            $this->json(['success' => true, 'message' => $message, 'deleted' => $deleted]);
        } else {
            $message = 'No se pudieron eliminar los contratos seleccionados';
            if (!empty($errors)) {
                $message .= ": " . implode(', ', $errors);
            }
            $this->json(['success' => false, 'message' => $message], 400);
        }
    }
    
    /**
     * Actualiza el estado de múltiples contratos (AJAX)
     */
    public function bulkUpdateStatus(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? '';
        
        if (empty($ids) || !is_array($ids)) {
            $this->json(['success' => false, 'message' => 'No se seleccionaron registros']);
        }
        
        if (!in_array($status, ['active', 'renewed', 'canceled'])) {
            $this->json(['success' => false, 'message' => 'Estado inválido']);
        }
        
        $updated = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            if ($this->contractModel->updateStatus((int)$id, $status)) {
                $updated++;
            } else {
                $errors[] = "Error al actualizar contrato ID {$id}";
            }
        }
        
        $message = $updated > 0 
            ? "Se actualizaron {$updated} contrato(s)" . (!empty($errors) ? ", pero hubo errores: " . implode(', ', $errors) : '')
            : "No se pudieron actualizar los contratos: " . implode(', ', $errors);
        
        $this->json([
            'success' => $updated > 0,
            'message' => $message,
            'updated' => $updated,
            'errors' => $errors
        ]);
    }
    
    /**
     * Actualiza el estado de pago de múltiples contratos (AJAX)
     */
    public function bulkUpdatePaymentStatus(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $ids = $data['ids'] ?? [];
        $statusPayment = $data['status_payment'] ?? '';
        
        if (empty($ids) || !is_array($ids)) {
            $this->json(['success' => false, 'message' => 'No se seleccionaron registros']);
        }
        
        if (!in_array($statusPayment, ['up to date', 'delinquent', 'unable to pay'])) {
            $this->json(['success' => false, 'message' => 'Estado de pago inválido']);
        }
        
        $updated = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            if ($this->contractModel->updatePaymentStatus((int)$id, $statusPayment)) {
                $updated++;
            } else {
                $errors[] = "Error al actualizar contrato ID {$id}";
            }
        }
        
        $message = $updated > 0 
            ? "Se actualizaron {$updated} contrato(s)" . (!empty($errors) ? ", pero hubo errores: " . implode(', ', $errors) : '')
            : "No se pudieron actualizar los contratos: " . implode(', ', $errors);
        
        $this->json([
            'success' => $updated > 0,
            'message' => $message,
            'updated' => $updated,
            'errors' => $errors
        ]);
    }
    
    /**
     * Agrega un local al contrato (AJAX)
     * También puede crear un nuevo local si se especifica
     */
    public function addLocation(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $contractId = (int) ($data['contract_id'] ?? 0);
        $createNew = $data['create_new'] ?? false;
        
        if (!$contractId) {
            $this->json(['success' => false, 'message' => 'ID de contrato requerido']);
        }
        
        // Verificar que el contrato existe
        $contract = $this->contractModel->getById($contractId);
        if (!$contract) {
            $this->json(['success' => false, 'message' => 'Contrato no encontrado']);
        }
        
        $stallId = 0;
        
        // Si se va a crear un nuevo local
        if ($createNew) {
            $zoneId = (int) ($data['zone_id'] ?? 0);
            $sectorId = (int) ($data['sector_id'] ?? 0);
            $stallNumber = $data['stall_number'] ?? '';
            $description = $data['description'] ?? '';
            
            if (!$sectorId || !$stallNumber) {
                $this->json(['success' => false, 'message' => 'Sector y número de local son requeridos']);
            }
            
            // Verificar si el número de local ya existe en ese sector
            $marketStallModel = new \App\Models\MarketStallModel();
            $existing = $marketStallModel->getByStallNumber($sectorId, $stallNumber);
            
            if ($existing) {
                $this->json(['success' => false, 'message' => 'Ya existe un local con ese número en este sector']);
            }
            
            // Crear el nuevo local
            $stallId = $marketStallModel->create([
                'sector_id' => $sectorId,
                'stall_number' => $stallNumber,
                'location_description' => $description
            ]);
            
            if (!$stallId) {
                $this->json(['success' => false, 'message' => 'Error al crear el local'], 500);
            }
        } else {
            $stallId = (int) ($data['stall_id'] ?? 0);
            
            if (!$stallId) {
                $this->json(['success' => false, 'message' => 'ID de local requerido']);
            }
        }
        
        // Verificar que el local no esté ya asignado a este contrato
        $existingLocations = $this->contractModel->getLocations($contractId);
        foreach ($existingLocations as $location) {
            if ($location['stall_id'] == $stallId) {
                $this->json(['success' => false, 'message' => 'Este local ya está asignado a este contrato']);
            }
        }
        
        // VALIDACIÓN IMPORTANTE: Verificar que el adjudicatario no tenga este local en otro contrato activo
        $awardeeId = $contract['awardee_id'];
        $validation = $this->contractModel->canAssignLocationToAwardee($awardeeId, $stallId, $contractId);
        
        if (!$validation['can_assign']) {
            $this->json(['success' => false, 'message' => $validation['message']], 400);
        }
        
        // Agregar el local al contrato
        $success = $this->contractModel->addLocation($contractId, $stallId);
        
        if ($success) {
            $message = $createNew ? 'Local creado y agregado exitosamente' : 'Local agregado exitosamente';
            $this->json(['success' => true, 'message' => $message]);
        } else {
            $this->json(['success' => false, 'message' => 'Error al agregar el local'], 500);
        }
    }
    
    /**
     * Elimina un local del contrato (AJAX)
     */
    public function removeLocation(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $contractId = (int) ($data['contract_id'] ?? 0);
        $stallId = (int) ($data['stall_id'] ?? 0);
        
        if (!$contractId || !$stallId) {
            $this->json(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        // Eliminar el local del contrato
        $success = $this->contractModel->removeLocation($contractId, $stallId);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Local eliminado del contrato']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el local'], 500);
        }
    }
    
    /**
     * Agrega una categoría al contrato (AJAX)
     */
    public function addCategory(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $contractId = (int) ($data['contract_id'] ?? 0);
        $categoryType = $data['category_type'] ?? ''; // 'internal' o 'external'
        $categoryId = (int) ($data['category_id'] ?? 0);
        
        if (!$contractId || !$categoryType || !$categoryId) {
            $this->json(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        if (!in_array($categoryType, ['internal', 'external'])) {
            $this->json(['success' => false, 'message' => 'Tipo de categoría inválido']);
        }
        
        // Verificar que el contrato existe
        $contract = $this->contractModel->getById($contractId);
        if (!$contract) {
            $this->json(['success' => false, 'message' => 'Contrato no encontrado']);
        }
        
        // Verificar que la categoría no esté ya asignada a este contrato
        $existingCategories = $this->contractModel->getCategories($contractId);
        foreach ($existingCategories as $category) {
            if ($categoryType === 'internal' && 
                isset($category['internal_category_id']) && 
                $category['internal_category_id'] == $categoryId) {
                $this->json(['success' => false, 'message' => 'Esta categoría ya está asignada al contrato']);
            }
            if ($categoryType === 'external' && 
                isset($category['external_category_id']) && 
                $category['external_category_id'] == $categoryId) {
                $this->json(['success' => false, 'message' => 'Esta categoría ya está asignada al contrato']);
            }
        }
        
        // Agregar la categoría
        $success = $this->contractModel->addCategory($contractId, $categoryType, $categoryId);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Categoría agregada exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al agregar la categoría'], 500);
        }
    }
    
    /**
     * Elimina una categoría del contrato (AJAX)
     */
    public function removeCategory(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $contractId = (int) ($data['contract_id'] ?? 0);
        $categoryType = $data['category_type'] ?? ''; // 'internal' o 'external'
        $categoryId = (int) ($data['category_id'] ?? 0);
        
        if (!$contractId || !$categoryType || !$categoryId) {
            $this->json(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        if (!in_array($categoryType, ['internal', 'external'])) {
            $this->json(['success' => false, 'message' => 'Tipo de categoría inválido']);
        }
        
        $success = $this->contractModel->removeCategory($contractId, $categoryType, $categoryId);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Categoría eliminada del contrato']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar la categoría'], 500);
        }
    }
    
    /**
     * Elimina un registro de pago (AJAX)
     */
    public function deletePayment(int $paymentId): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $paymentModel = new \App\Models\ContractPaymentModel();
        
        // Verificar si el pago tiene abonos
        $validation = $paymentModel->canDeletePayment($paymentId);
        
        if (!$validation['can_delete']) {
            $this->json(['success' => false, 'message' => $validation['message']], 400);
        }
        
        // Eliminar el pago
        $success = $paymentModel->deletePayment($paymentId);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Pago eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el pago'], 500);
        }
    }
    
    /**
     * Búsqueda de adjudicatario por cédula (RF11)
     */
    public function searchAwardee(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $idNumber = $this->post('id_number');
        
        if (empty($idNumber)) {
            $this->json(['success' => false, 'message' => 'Cédula requerida']);
        }
        
        $awardee = $this->awardeeModel->getByIdNumber($idNumber);
        
        if (!$awardee) {
            $this->json(['success' => false, 'message' => 'Adjudicatario no encontrado']);
        }
        
        // Obtener los contratos del adjudicatario
        $contracts = $this->contractModel->getByAwardee($awardee['id']);
        
        $this->json([
            'success' => true,
            'awardee' => $awardee,
            'contracts' => $contracts
        ]);
    }
    
    /**
     * Actualiza el estado de múltiples registros de pago individuales (bulk)
     * Nota: Diferente de bulkUpdatePaymentStatus que actualiza el estado de pago del contrato
     */
    public function bulkupdateindividualpaymentstatus(): void {
        // Evitar que se muestre cualquier salida antes del JSON
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        
        try {
            if (!$this->isPost()) {
                $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->json(['success' => false, 'message' => 'JSON inválido: ' . json_last_error_msg()]);
            }
            
            $ids = $data['ids'] ?? [];
            $status = $data['status'] ?? '';
            $csrfToken = $data['csrf_token'] ?? '';
            
            // Verificar CSRF token desde JSON body
            if (!$this->verifyCsrfToken($csrfToken)) {
                $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            }
            
            // Validar datos
            if (empty($ids) || !is_array($ids)) {
                $this->json(['success' => false, 'message' => 'IDs de pagos requeridos']);
            }
            
            if (empty($status)) {
                $this->json(['success' => false, 'message' => 'Estado requerido']);
            }
            
            // Validar estado
            $validStatuses = ['pending', 'paid', 'cancelled', 'refunded'];
            if (!in_array($status, $validStatuses)) {
                $this->json(['success' => false, 'message' => 'Estado inválido']);
            }
            
            $paymentModel = new \App\Models\ContractPaymentModel();
            $updated = 0;
            $errors = 0;
            $errorDetails = [];
            
            foreach ($ids as $id) {
                try {
                    if ($paymentModel->updatePaymentStatus((int)$id, $status)) {
                        $updated++;
                        error_log("Pago {$id} actualizado exitosamente a {$status}");
                    } else {
                        $errors++;
                        $errorDetails[] = "Pago {$id} no pudo actualizarse";
                        error_log("Pago {$id} no pudo actualizarse");
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $errorDetails[] = "Pago {$id}: " . $e->getMessage();
                    error_log("Error al actualizar pago {$id}: " . $e->getMessage());
                }
            }
            
            if ($updated > 0) {
                $message = "Se actualizaron $updated pago(s) exitosamente";
                if ($errors > 0) {
                    $message .= " ($errors no se pudieron actualizar)";
                }
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $this->json(['success' => false, 'message' => 'No se pudo actualizar ningún pago'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en bulkupdateindividualpaymentstatus: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Elimina múltiples pagos (bulk)
     */
    public function bulkdeletepayments(): void {
        // Evitar que se muestre cualquier salida antes del JSON
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        
        try {
            if (!$this->isPost()) {
                $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->json(['success' => false, 'message' => 'JSON inválido: ' . json_last_error_msg()]);
            }
            
            $ids = $data['ids'] ?? [];
            $csrfToken = $data['csrf_token'] ?? '';
            
            // Verificar CSRF token desde JSON body
            if (!$this->verifyCsrfToken($csrfToken)) {
                $this->json(['success' => false, 'message' => 'Token CSRF inválido'], 403);
            }
            
            // Validar datos
            if (empty($ids) || !is_array($ids)) {
                $this->json(['success' => false, 'message' => 'IDs de pagos requeridos']);
            }
            
            $paymentModel = new \App\Models\ContractPaymentModel();
            $deleted = 0;
            $errors = 0;
            $hasAbonos = [];
            
            foreach ($ids as $id) {
                // Verificar si el pago tiene abonos
                $validation = $paymentModel->canDeletePayment((int)$id);
                
                if (!$validation['can_delete']) {
                    $hasAbonos[] = $id;
                    $errors++;
                    continue;
                }
                
                if ($paymentModel->deletePayment((int)$id)) {
                    $deleted++;
                } else {
                    $errors++;
                }
            }
            
            if ($deleted > 0) {
                $message = "Se eliminaron $deleted pago(s) exitosamente";
                if (!empty($hasAbonos)) {
                    $message .= " (" . count($hasAbonos) . " no se pudieron eliminar porque tienen abonos registrados)";
                } elseif ($errors > 0) {
                    $message .= " ($errors no se pudieron eliminar)";
                }
                $this->json(['success' => true, 'message' => $message]);
            } else {
                if (!empty($hasAbonos)) {
                    $this->json(['success' => false, 'message' => 'No se pudieron eliminar los pagos porque tienen abonos registrados'], 400);
                } else {
                    $this->json(['success' => false, 'message' => 'No se pudo eliminar ningún pago'], 500);
                }
            }
        } catch (\Exception $e) {
            error_log("Error en bulkdeletepayments: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
}

