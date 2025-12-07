<?php
require_once __DIR__ . '/../models/CashRegisterModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class CashRegisterController {
    private $cashRegisterModel;
    private $userModel;
    
    public function __construct() {
        $this->cashRegisterModel = new CashRegisterModel();
        $this->userModel = new UserModel();
    }
    
    public function index() {
        return [
            'page_title' => 'Gestión de Cajas',
            'cashRegisters' => $this->cashRegisterModel->getAll()
        ];
    }
    
    public function create() {
        return [
            'page_title' => 'Crear Caja',
            'users' => $this->userModel->getAll()
        ];
    }
    
    public function store($data) {
        $name = $data['name'] ?? '';
        $userId = (int) ($data['user_id'] ?? 0);
        $status = $data['status'] ?? 'active';
        
        if (empty($name) || empty($userId)) {
            return ['success' => false, 'message' => 'Nombre y usuario requeridos'];
        }
        
        $id = $this->cashRegisterModel->create([
            'name' => $name,
            'user_id' => $userId,
            'status' => $status
        ]);
        
        if ($id) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Caja creada exitosamente'];
            return ['success' => true, 'redirect' => 'index.php'];
        } else {
            return ['success' => false, 'message' => 'Error al crear la caja'];
        }
    }
    
    public function edit($id) {
        $cashRegister = $this->cashRegisterModel->getById($id);
        if (!$cashRegister) {
            return ['success' => false, 'message' => 'Caja no encontrada', 'redirect' => 'index.php'];
        }
        
        return [
            'success' => true,
            'page_title' => 'Editar Caja',
            'cashRegister' => $cashRegister,
            'users' => $this->userModel->getAll()
        ];
    }
    
    public function update($id, $data) {
        $name = $data['name'] ?? '';
        $userId = (int) ($data['user_id'] ?? 0);
        $status = $data['status'] ?? 'active';
        
        $success = $this->cashRegisterModel->update($id, [
            'name' => $name,
            'user_id' => $userId,
            'status' => $status
        ]);
        
        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Caja actualizada exitosamente'];
            return ['success' => true, 'redirect' => 'index.php'];
        } else {
             return ['success' => false, 'message' => 'Error al actualizar la caja'];
        }
    }
    
    public function delete($id) {
        $validation = $this->cashRegisterModel->canDelete($id);
        if (!$validation['can_delete']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        if ($this->cashRegisterModel->delete($id)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Caja eliminada'];
            return ['success' => true, 'redirect' => 'index.php'];
        }
        return ['success' => false, 'message' => 'Error al eliminar'];
    }
}
