<?php
/**
 * Controlador de Cajas
 * 
 * Gestiona el CRUD de cajas asignadas a usuarios
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\CashRegisterModel;
use App\Models\UserModel;

class CashRegisterController extends Controller {
    private CashRegisterModel $cashRegisterModel;
    private UserModel $userModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->cashRegisterModel = new CashRegisterModel();
        $this->userModel = new UserModel();
    }
    
    /**
     * Lista todas las cajas
     */
    public function index(): void {
        $cashRegisters = $this->cashRegisterModel->getAll();
        
        $data = [
            'title' => 'Gestión de Cajas',
            'cashRegisters' => $cashRegisters
        ];
        
        $this->view('CashRegister/Index', $data);
    }
    
    /**
     * Muestra el formulario para crear una caja
     */
    public function create(): void {
        $users = $this->userModel->getAll();
        
        $data = [
            'title' => 'Crear Caja',
            'users' => $users
        ];
        
        $this->view('CashRegister/Create', $data);
    }
    
    /**
     * Procesa la creación de una caja
     */
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('cashregister/index');
        }
        
        $this->requireCsrfToken();
        
        $name = $this->post('name');
        $user_id = (int) $this->post('user_id');
        $status = $this->post('status', 'active');
        
        // Validar
        if (empty($name) || empty($user_id)) {
            Session::flash('error', 'Nombre y usuario son requeridos');
            $this->redirect('cashregister/create');
        }
        
        $id = $this->cashRegisterModel->create([
            'name' => $name,
            'user_id' => $user_id,
            'status' => $status
        ]);
        
        if ($id) {
            Session::flash('success', 'Caja creada exitosamente');
            $this->redirect('cashregister/index');
        } else {
            Session::flash('error', 'Error al crear la caja');
            $this->redirect('cashregister/create');
        }
    }
    
    /**
     * Muestra el formulario para editar una caja
     */
    public function edit(int $id): void {
        $cashRegister = $this->cashRegisterModel->getById($id);
        
        if (!$cashRegister) {
            Session::flash('error', 'Caja no encontrada');
            $this->redirect('cashregister/index');
        }
        
        $users = $this->userModel->getAll();
        
        $data = [
            'title' => 'Editar Caja',
            'cashRegister' => $cashRegister,
            'users' => $users
        ];
        
        $this->view('CashRegister/Edit', $data);
    }
    
    /**
     * Procesa la actualización de una caja
     */
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('cashregister/index');
        }
        
        $this->requireCsrfToken();
        
        $cashRegister = $this->cashRegisterModel->getById($id);
        
        if (!$cashRegister) {
            Session::flash('error', 'Caja no encontrada');
            $this->redirect('cashregister/index');
        }
        
        $name = $this->post('name');
        $user_id = (int) $this->post('user_id');
        $status = $this->post('status', 'active');
        
        // Validar
        if (empty($name) || empty($user_id)) {
            Session::flash('error', 'Nombre y usuario son requeridos');
            $this->redirect('cashregister/edit/' . $id);
        }
        
        $success = $this->cashRegisterModel->update($id, [
            'name' => $name,
            'user_id' => $user_id,
            'status' => $status
        ]);
        
        if ($success) {
            Session::flash('success', 'Caja actualizada exitosamente');
            $this->redirect('cashregister/index');
        } else {
            Session::flash('error', 'Error al actualizar la caja');
            $this->redirect('cashregister/edit/' . $id);
        }
    }
    
    /**
     * Elimina una caja (AJAX)
     */
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        $validation = $this->cashRegisterModel->canDelete($id);
        
        if (!$validation['can_delete']) {
            $this->json(['success' => false, 'message' => $validation['message']], 400);
        }
        
        $success = $this->cashRegisterModel->delete($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Caja eliminada exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar la caja'], 500);
        }
    }
}

