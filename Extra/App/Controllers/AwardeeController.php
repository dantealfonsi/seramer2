<?php
/**
 * Controlador de Adjudicatarios
 * 
 * Gestiona los adjudicatarios del mercado
 * Implementa RF07
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\AwardeeModel;

class AwardeeController extends Controller {
    private AwardeeModel $awardeeModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->awardeeModel = new AwardeeModel();
    }
    
    /**
     * Lista todos los adjudicatarios
     */
    public function index(): void {
        $awardees = $this->awardeeModel->getAll();
        
        $data = [
            'title' => 'Adjudicatarios',
            'awardees' => $awardees
        ];
        
        $this->view('Awardee/Index', $data);
    }
    
    /**
     * Muestra el formulario para crear un adjudicatario (RF07)
     */
    public function create(): void {
        $data = [
            'title' => 'Crear Adjudicatario'
        ];
        
        $this->view('Awardee/Create', $data);
    }
    
    /**
     * Procesa la creación de un adjudicatario (RF07)
     */
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('awardee/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'first_name' => $this->sanitize($this->post('first_name')),
            'middle_name' => $this->sanitize($this->post('middle_name')),
            'last_name' => $this->sanitize($this->post('last_name')),
            'second_last_name' => $this->sanitize($this->post('second_last_name')),
            'id_number' => $this->sanitize($this->post('id_number')),
            'phone' => $this->sanitize($this->post('phone')),
            'email' => $this->sanitize($this->post('email')),
            'address' => $this->sanitize($this->post('address'))
        ];
        
        // Validar campos requeridos
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['id_number'])) {
            Session::flash('error', 'Nombre, apellido y cédula son requeridos');
            $this->redirect('awardee/create');
        }
        
        // Crear el adjudicatario
        $id = $this->awardeeModel->create($data);
        
        if ($id) {
            Session::flash('success', 'Adjudicatario creado exitosamente');
            $this->redirect('awardee/index');
        } else {
            Session::flash('error', 'Error al crear el adjudicatario. La cédula puede estar duplicada.');
            $this->redirect('awardee/create');
        }
    }
    
    /**
     * Muestra el formulario para editar un adjudicatario
     */
    public function edit(int $id): void {
        $awardee = $this->awardeeModel->getById($id);
        
        if (!$awardee) {
            Session::flash('error', 'Adjudicatario no encontrado');
            $this->redirect('awardee/index');
        }
        
        $data = [
            'title' => 'Editar Adjudicatario',
            'awardee' => $awardee
        ];
        
        $this->view('Awardee/Edit', $data);
    }
    
    /**
     * Procesa la actualización de un adjudicatario
     */
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('awardee/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'first_name' => $this->sanitize($this->post('first_name')),
            'middle_name' => $this->sanitize($this->post('middle_name')),
            'last_name' => $this->sanitize($this->post('last_name')),
            'second_last_name' => $this->sanitize($this->post('second_last_name')),
            'id_number' => $this->sanitize($this->post('id_number')),
            'phone' => $this->sanitize($this->post('phone')),
            'email' => $this->sanitize($this->post('email')),
            'address' => $this->sanitize($this->post('address'))
        ];
        
        // Validar campos requeridos
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['id_number'])) {
            Session::flash('error', 'Nombre, apellido y cédula son requeridos');
            $this->redirect('awardee/edit/' . $id);
        }
        
        // Actualizar el adjudicatario
        $success = $this->awardeeModel->update($id, $data);
        
        if ($success) {
            Session::flash('success', 'Adjudicatario actualizado exitosamente');
            $this->redirect('awardee/index');
        } else {
            Session::flash('error', 'Error al actualizar el adjudicatario');
            $this->redirect('awardee/edit/' . $id);
        }
    }
    
    /**
     * Elimina un adjudicatario
     */
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        // Verificar si se puede eliminar
        $validation = $this->awardeeModel->canDeleteAwardee($id);
        
        if (!$validation['can_delete']) {
            $this->json([
                'success' => false, 
                'message' => $validation['message'],
                'relations' => $validation['relations']
            ], 400);
            return;
        }
        
        $success = $this->awardeeModel->deleteAwardee($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Adjudicatario eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el adjudicatario'], 400);
        }
    }
    
    /**
     * Elimina múltiples adjudicatarios (bulk delete)
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
        $errors = 0;
        
        foreach ($ids as $id) {
            if ($this->awardeeModel->deleteAwardee((int)$id)) {
                $deleted++;
            } else {
                $errors++;
            }
        }
        
        if ($deleted > 0) {
            $message = "Se eliminaron {$deleted} adjudicatario(s) exitosamente";
            if ($errors > 0) {
                $message .= ". {$errors} no pudieron eliminarse (tienen contratos asociados)";
            }
            $this->json(['success' => true, 'message' => $message, 'deleted' => $deleted, 'errors' => $errors]);
        } else {
            $this->json(['success' => false, 'message' => 'No se pudieron eliminar los adjudicatarios seleccionados'], 400);
        }
    }
    
    /**
     * Creación rápida de adjudicatario (para modales/AJAX)
     * Retorna JSON con los datos del adjudicatario creado
     */
    public function quickStore(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $data = [
            'first_name' => $this->sanitize($this->post('first_name')),
            'middle_name' => $this->sanitize($this->post('second_name')),  // Nombre del campo en el modal
            'last_name' => $this->sanitize($this->post('first_surname')),  // Nombre del campo en el modal
            'second_last_name' => $this->sanitize($this->post('second_surname')),  // Nombre del campo en el modal
            'id_number' => $this->sanitize($this->post('id_number')),
            'phone' => $this->sanitize($this->post('phone')),
            'email' => $this->sanitize($this->post('email')),
            'address' => $this->sanitize($this->post('address'))
        ];
        
        // Validar campos requeridos
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['id_number'])) {
            $this->json(['success' => false, 'message' => 'Nombre, apellido y cédula son requeridos']);
        }
        
        // Verificar si la cédula ya existe
        $existing = $this->awardeeModel->getByIdNumber($data['id_number']);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Ya existe un adjudicatario con esta cédula']);
        }
        
        // Crear el adjudicatario
        $id = $this->awardeeModel->create($data);
        
        if ($id) {
            // Obtener el adjudicatario recién creado
            $awardee = $this->awardeeModel->getById($id);
            
            // Preparar respuesta con los datos del adjudicatario
            $fullName = AwardeeModel::getFullName($awardee);
            
            $this->json([
                'success' => true,
                'message' => 'Adjudicatario creado exitosamente',
                'awardee' => [
                    'id' => $id,
                    'full_name' => $fullName,
                    'id_number' => $awardee['id_number']
                ]
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Error al crear el adjudicatario'], 500);
        }
    }
    
    /**
     * Muestra los contratos de un adjudicatario
     */
    public function showContracts(int $id): void {
        $awardee = $this->awardeeModel->getById($id);
        
        if (!$awardee) {
            Session::flash('error', 'Adjudicatario no encontrado');
            $this->redirect('awardee/index');
        }
        
        // Obtener los contratos del adjudicatario
        $contractModel = new \App\Models\ContractModel();
        $contracts = $contractModel->getByAwardee($id);
        
        // Obtener información adicional de cada contrato
        foreach ($contracts as &$contract) {
            $contract['categories'] = $contractModel->getCategories($contract['id']);
            $contract['locations'] = $contractModel->getLocations($contract['id']);
            
            // Obtener los pagos del contrato
            $paymentModel = new \App\Models\ContractPaymentModel();
            $payments = $paymentModel->getByContract($contract['id']);
            $contract['payments'] = $payments;
        }
        
        $data = [
            'title' => 'Contratos de Adjudicatario',
            'awardee' => $awardee,
            'contracts' => $contracts
        ];
        
        $this->view('Awardee/ShowContracts', $data);
    }
}

