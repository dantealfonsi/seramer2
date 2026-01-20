<?php
/**
 * Controlador de Métodos de Pago
 * 
 * Gestiona los métodos de pago aceptados en el sistema
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\PaymentMethodModel;

class PaymentMethodController extends Controller {
    private PaymentMethodModel $model;
    
    public function __construct() {
        $this->requireAuth();
        $this->model = new PaymentMethodModel();
    }
    
    /**
     * Lista todos los métodos de pago
     */
    public function index(): void {
        $paymentMethods = $this->model->getAll();
        
        $data = [
            'title' => 'Métodos de Pago',
            'paymentMethods' => $paymentMethods
        ];
        
        $this->view('Catalog/PaymentMethod/Index', $data);
    }
    
    /**
     * Muestra el formulario para crear un método de pago
     */
    public function create(): void {
        $data = [
            'title' => 'Crear Método de Pago'
        ];
        
        $this->view('Catalog/PaymentMethod/Create', $data);
    }
    
    /**
     * Procesa la creación de un método de pago
     */
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('paymentmethod/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'name' => $this->sanitize($this->post('name')),
            'is_active' => $this->post('is_active', 1)
        ];
        
        // Validaciones
        if (empty($data['name'])) {
            Session::flash('error', 'El nombre del método de pago es requerido');
            $this->redirect('paymentmethod/create');
        }
        
        // Verificar si ya existe
        if ($this->model->existsByName($data['name'])) {
            Session::flash('error', 'Ya existe un método de pago con ese nombre');
            $this->redirect('paymentmethod/create');
        }
        
        $id = $this->model->create($data);
        
        if ($id) {
            Session::flash('success', 'Método de pago creado exitosamente');
            $this->redirect('paymentmethod/index');
        } else {
            Session::flash('error', 'Error al crear el método de pago');
            $this->redirect('paymentmethod/create');
        }
    }
    
    /**
     * Muestra el formulario para editar un método de pago
     */
    public function edit(int $id): void {
        $paymentMethod = $this->model->getById($id);
        
        if (!$paymentMethod) {
            Session::flash('error', 'Método de pago no encontrado');
            $this->redirect('paymentmethod/index');
        }
        
        $data = [
            'title' => 'Editar Método de Pago',
            'paymentMethod' => $paymentMethod
        ];
        
        $this->view('Catalog/PaymentMethod/Edit', $data);
    }
    
    /**
     * Procesa la actualización de un método de pago
     */
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('paymentmethod/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'name' => $this->sanitize($this->post('name')),
            'is_active' => $this->post('is_active', 1)
        ];
        
        // Validaciones
        if (empty($data['name'])) {
            Session::flash('error', 'El nombre del método de pago es requerido');
            $this->redirect('paymentmethod/edit/' . $id);
        }
        
        // Verificar si ya existe (excluyendo el actual)
        if ($this->model->existsByName($data['name'], $id)) {
            Session::flash('error', 'Ya existe un método de pago con ese nombre');
            $this->redirect('paymentmethod/edit/' . $id);
        }
        
        $success = $this->model->update($id, $data);
        
        if ($success) {
            Session::flash('success', 'Método de pago actualizado exitosamente');
            $this->redirect('paymentmethod/index');
        } else {
            Session::flash('error', 'Error al actualizar el método de pago');
            $this->redirect('paymentmethod/edit/' . $id);
        }
    }
    
    /**
     * Elimina un método de pago
     */
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        // Verificar si se puede eliminar
        $validation = $this->model->canDeleteMethod($id);
        
        if (!$validation['can_delete']) {
            $this->json([
                'success' => false, 
                'message' => $validation['message'],
                'relations' => $validation['relations']
            ], 400);
            return;
        }
        
        $success = $this->model->deleteMethod($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Método de pago eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el método de pago'], 400);
        }
    }
    
    /**
     * Elimina múltiples métodos de pago (bulk delete)
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
            $validation = $this->model->canDeleteMethod((int)$id);
            
            if (!$validation['can_delete']) {
                $method = $this->model->getById((int)$id);
                $errors[] = $method['name'] ?? "ID {$id}";
                continue;
            }
            
            if ($this->model->deleteMethod((int)$id)) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $message = "Se eliminaron {$deleted} método(s) exitosamente";
            if (!empty($errors)) {
                $message .= ". No se pudieron eliminar: " . implode(', ', $errors);
            }
            $this->json(['success' => true, 'message' => $message, 'deleted' => $deleted]);
        } else {
            $this->json(['success' => false, 'message' => 'No se pudieron eliminar los métodos seleccionados'], 400);
        }
    }
    
    /**
     * Cambia el estado de un método de pago (activo/inactivo)
     */
    public function toggleActive(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        $paymentMethod = $this->model->getById($id);
        
        if (!$paymentMethod) {
            $this->json(['success' => false, 'message' => 'Método de pago no encontrado'], 404);
        }
        
        $newStatus = !$paymentMethod['is_active'];
        $success = $this->model->toggleActive($id, $newStatus);
        
        if ($success) {
            $statusText = $newStatus ? 'activado' : 'desactivado';
            $this->json(['success' => true, 'message' => "Método de pago {$statusText} exitosamente", 'is_active' => $newStatus]);
        } else {
            $this->json(['success' => false, 'message' => 'Error al cambiar el estado del método de pago'], 400);
        }
    }
}

