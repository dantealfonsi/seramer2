<?php
/**
 * Controlador de Caja Diaria
 * 
 * Gestiona la apertura y cierre de caja diaria
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\CashRegisterModel;
use App\Models\DailyCashRegisterModel;
use App\Models\ContractPaymentInstallmentModel;

class DailyCashController extends Controller {
    private CashRegisterModel $cashRegisterModel;
    private DailyCashRegisterModel $dailyCashModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->cashRegisterModel = new CashRegisterModel();
        $this->dailyCashModel = new DailyCashRegisterModel();
    }
    
    /**
     * Lista todas las cajas y su estado
     */
    public function index(): void {
        $cashRegisters = $this->cashRegisterModel->getAll();
        $userId = Session::get('user_id');
        
        // Obtener estado de cada caja
        foreach ($cashRegisters as &$cashRegister) {
            $openCash = $this->dailyCashModel->getOpenCashByRegister($cashRegister['id']);
            $cashRegister['is_open'] = $openCash !== null;
            $cashRegister['open_cash'] = $openCash;
            $cashRegister['can_operate'] = ($cashRegister['user_id'] == $userId);
        }
        
        $data = [
            'title' => 'Apertura y Cierre de Caja',
            'cashRegisters' => $cashRegisters,
            'currentUserId' => $userId
        ];
        
        $this->view('DailyCash/Index', $data);
    }
    
    /**
     * Muestra el formulario para abrir una caja
     */
    public function open(int $cashRegisterId): void {
        $cashRegister = $this->cashRegisterModel->getById($cashRegisterId);
        
        if (!$cashRegister) {
            Session::flash('error', 'Caja no encontrada');
            $this->redirect('dailycash/index');
        }
        
        $userId = Session::get('user_id');
        
        // Verificar que el usuario sea el asignado
        if ($cashRegister['user_id'] != $userId) {
            Session::flash('error', 'No tiene permiso para abrir esta caja');
            $this->redirect('dailycash/index');
        }
        
        // Verificar que no esté ya abierta
        $openCash = $this->dailyCashModel->getOpenCashByRegister($cashRegisterId);
        if ($openCash) {
            Session::flash('error', 'Esta caja ya está abierta');
            $this->redirect('dailycash/index');
        }
        
        $data = [
            'title' => 'Abrir Caja',
            'cashRegister' => $cashRegister
        ];
        
        $this->view('DailyCash/Open', $data);
    }
    
    /**
     * Procesa la apertura de una caja
     */
    public function storeOpen(): void {
        if (!$this->isPost()) {
            $this->redirect('dailycash/index');
        }
        
        $this->requireCsrfToken();
        
        $cashRegisterId = (int) $this->post('cash_register_id');
        $initialAmount = (float) $this->post('initial_amount');
        $userId = Session::get('user_id');
        
        // Validar
        if (empty($cashRegisterId) || $initialAmount < 0) {
            Session::flash('error', 'Datos inválidos');
            $this->redirect('dailycash/open/' . $cashRegisterId);
        }
        
        // Verificar que el usuario sea el asignado
        $cashRegister = $this->cashRegisterModel->getById($cashRegisterId);
        if (!$cashRegister || $cashRegister['user_id'] != $userId) {
            Session::flash('error', 'No tiene permiso para abrir esta caja');
            $this->redirect('dailycash/index');
        }
        
        $id = $this->dailyCashModel->openCash($cashRegisterId, $userId, $initialAmount);
        
        if ($id) {
            Session::flash('success', 'Caja abierta exitosamente');
            $this->redirect('dailycash/index');
        } else {
            Session::flash('error', 'Error al abrir la caja. Verifique que no esté ya abierta.');
            $this->redirect('dailycash/open/' . $cashRegisterId);
        }
    }
    
    /**
     * Muestra el formulario para cerrar una caja
     */
    public function close(int $dailyCashId): void {
        $dailyCash = $this->dailyCashModel->getById($dailyCashId);
        
        if (!$dailyCash) {
            Session::flash('error', 'Registro de caja no encontrado');
            $this->redirect('dailycash/index');
        }
        
        if ($dailyCash['status'] !== 'open') {
            Session::flash('error', 'Esta caja ya está cerrada');
            $this->redirect('dailycash/index');
        }
        
        $userId = Session::get('user_id');
        
        // Verificar que el usuario sea el asignado
        if ($dailyCash['user_id'] != $userId) {
            Session::flash('error', 'No tiene permiso para cerrar esta caja');
            $this->redirect('dailycash/index');
        }
        
        // Calcular total de abonos del día
        $totalInstallments = $this->dailyCashModel->getTotalInstallments($dailyCashId);
        $calculatedFinal = (float) $dailyCash['initial_amount'] + $totalInstallments;
        
        // Obtener todos los movimientos (abonos) realizados en esta caja
        $installments = $this->dailyCashModel->getInstallmentsByDailyCash($dailyCashId);
        
        $data = [
            'title' => 'Cerrar Caja',
            'dailyCash' => $dailyCash,
            'totalInstallments' => $totalInstallments,
            'calculatedFinal' => $calculatedFinal,
            'installments' => $installments
        ];
        
        $this->view('DailyCash/Close', $data);
    }
    
    /**
     * Procesa el cierre de una caja
     */
    public function storeClose(): void {
        if (!$this->isPost()) {
            $this->redirect('dailycash/index');
        }
        
        $this->requireCsrfToken();
        
        $dailyCashId = (int) $this->post('daily_cash_id');
        $finalAmountRaw = $this->post('final_amount');
        $userId = Session::get('user_id');
        
        // Validar que el ID no esté vacío
        if (empty($dailyCashId)) {
            Session::flash('error', 'ID de caja no válido');
            $this->redirect('dailycash/index');
        }
        
        // Convertir el monto, manejando tanto punto como coma como separador decimal
        $finalAmount = 0;
        if (!empty($finalAmountRaw)) {
            // Reemplazar coma por punto para convertir a float
            $finalAmountRaw = str_replace(',', '.', $finalAmountRaw);
            $finalAmount = (float) $finalAmountRaw;
        }
        
        // Validar monto
        if ($finalAmount < 0) {
            Session::flash('error', 'El monto final no puede ser negativo');
            $this->redirect('dailycash/close/' . $dailyCashId);
        }
        
        // Verificar que el usuario sea el que abrió la caja
        $dailyCash = $this->dailyCashModel->getById($dailyCashId);
        if (!$dailyCash) {
            Session::flash('error', 'Registro de caja no encontrado');
            $this->redirect('dailycash/index');
        }
        
        if ($dailyCash['user_id'] != $userId) {
            Session::flash('error', 'No tiene permiso para cerrar esta caja');
            $this->redirect('dailycash/index');
        }
        
        if ($dailyCash['status'] !== 'open') {
            Session::flash('error', 'Esta caja ya está cerrada');
            $this->redirect('dailycash/index');
        }
        
        // Verificar nuevamente el estado antes de cerrar (para mostrar un mensaje más específico)
        $currentCash = $this->dailyCashModel->getById($dailyCashId);
        if (!$currentCash) {
            Session::flash('error', 'El registro de caja no existe o fue eliminado');
            $this->redirect('dailycash/index');
        }
        
        if ($currentCash['status'] !== 'open') {
            Session::flash('error', 'Esta caja ya está cerrada. Estado actual: ' . $currentCash['status']);
            $this->redirect('dailycash/index');
        }
        
        $success = $this->dailyCashModel->closeCash($dailyCashId, $finalAmount);
        
        if ($success) {
            Session::flash('success', 'Caja cerrada exitosamente');
            $this->redirect('dailycash/index');
        } else {
            // Verificar el estado después del intento fallido
            $afterCash = $this->dailyCashModel->getById($dailyCashId);
            $statusInfo = $afterCash ? "Estado actual: {$afterCash['status']}" : "Registro no encontrado";
            Session::flash('error', "Error al cerrar la caja. {$statusInfo}. Revise los logs del servidor para más detalles.");
            $this->redirect('dailycash/close/' . $dailyCashId);
        }
    }
}

