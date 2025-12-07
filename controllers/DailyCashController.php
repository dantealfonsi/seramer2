<?php
require_once __DIR__ . '/../models/CashRegisterModel.php';
require_once __DIR__ . '/../models/DailyCashRegisterModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class DailyCashController {
    private $cashRegisterModel;
    private $dailyCashModel;
    
    public function __construct() {
        $this->cashRegisterModel = new CashRegisterModel();
        $this->dailyCashModel = new DailyCashRegisterModel();
    }
    
    public function index() {
        $cashRegisters = $this->cashRegisterModel->getAll();
        $userId = $_SESSION['user_id'] ?? 0;
        
        foreach ($cashRegisters as &$cashRegister) {
            $openCash = $this->dailyCashModel->getOpenCashByRegister($cashRegister['id']);
            $cashRegister['is_open'] = $openCash !== null;
            $cashRegister['open_cash'] = $openCash;
            $cashRegister['can_operate'] = ($cashRegister['user_id'] == $userId);
        }
        
        return [
            'page_title' => 'Apertura y Cierre de Caja',
            'cashRegisters' => $cashRegisters,
            'currentUserId' => $userId
        ];
    }
    
    public function openForm($cashRegisterId) {
        $cashRegister = $this->cashRegisterModel->getById($cashRegisterId);
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (!$cashRegister) {
            return ['success' => false, 'message' => 'Caja no encontrada', 'redirect' => 'index.php'];
        }
        
        if ($cashRegister['user_id'] != $userId) {
             return ['success' => false, 'message' => 'No tiene permiso para abrir esta caja', 'redirect' => 'index.php'];
        }
        
        $openCash = $this->dailyCashModel->getOpenCashByRegister($cashRegisterId);
        if ($openCash) {
             return ['success' => false, 'message' => 'Esta caja ya está abierta', 'redirect' => 'index.php'];
        }
        
        return [
            'success' => true,
            'page_title' => 'Abrir Caja: ' . $cashRegister['name'],
            'cashRegister' => $cashRegister
        ];
    }
    
    public function storeOpen($data) {
        $cashRegisterId = (int) ($data['cash_register_id'] ?? 0);
        $initialAmount = (float) ($data['initial_amount'] ?? 0);
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (empty($cashRegisterId) || $initialAmount < 0) {
            return ['success' => false, 'message' => 'Datos inválidos'];
        }
        
        $cashRegister = $this->cashRegisterModel->getById($cashRegisterId);
        if (!$cashRegister || $cashRegister['user_id'] != $userId) {
             return ['success' => false, 'message' => 'No tiene permiso para abrir esta caja'];
        }
        
        $id = $this->dailyCashModel->openCash($cashRegisterId, $userId, $initialAmount);
        
        if ($id) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Caja abierta exitosamente'];
            return ['success' => true, 'message' => 'Caja abierta exitosamente', 'redirect' => 'index.php'];
        } else {
            return ['success' => false, 'message' => 'Error al abrir la caja. Verifique que no esté ya abierta.'];
        }
    }
    
    public function closeForm($dailyCashId) {
        $dailyCash = $this->dailyCashModel->getById($dailyCashId);
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (!$dailyCash) {
            return ['success' => false, 'message' => 'Registro de caja no encontrado', 'redirect' => 'index.php'];
        }
        
        if ($dailyCash['status'] !== 'open') {
             return ['success' => false, 'message' => 'Esta caja ya está cerrada', 'redirect' => 'index.php'];
        }
        
        if ($dailyCash['user_id'] != $userId) {
             return ['success' => false, 'message' => 'No tiene permiso para cerrar esta caja', 'redirect' => 'index.php'];
        }
        
        $totalInstallments = $this->dailyCashModel->getTotalInstallments($dailyCashId);
        $calculatedFinal = (float) $dailyCash['initial_amount'] + $totalInstallments;
        $installments = $this->dailyCashModel->getInstallmentsByDailyCash($dailyCashId);
        
        return [
            'success' => true,
            'page_title' => 'Cerrar Caja',
            'dailyCash' => $dailyCash,
            'totalInstallments' => $totalInstallments,
            'calculatedFinal' => $calculatedFinal,
            'installments' => $installments
        ];
    }
    
    public function storeClose($data) {
        $dailyCashId = (int) ($data['daily_cash_id'] ?? 0);
        $finalAmount = (float) (str_replace(',', '.', $data['final_amount'] ?? '0'));
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (empty($dailyCashId) || $finalAmount < 0) {
             return ['success' => false, 'message' => 'Datos inválidos'];
        }
        
        $dailyCash = $this->dailyCashModel->getById($dailyCashId);
        if (!$dailyCash || $dailyCash['user_id'] != $userId) {
             return ['success' => false, 'message' => 'No tiene permiso para cerrar esta caja'];
        }
        
        $success = $this->dailyCashModel->closeCash($dailyCashId, $finalAmount);
        
        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Caja cerrada exitosamente'];
            return ['success' => true, 'message' => 'Caja cerrada exitosamente', 'redirect' => 'index.php'];
        } else {
            return ['success' => false, 'message' => 'Error al cerrar la caja.'];
        }
    }
}
