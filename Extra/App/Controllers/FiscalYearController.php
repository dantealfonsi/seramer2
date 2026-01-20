<?php
/**
 * Controlador de Años Fiscales
 * 
 * Gestiona los años fiscales y tasas de euro
 * Implementa RF01-RF06
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\FiscalYearModel;
use App\Models\EuroRateModel;

class FiscalYearController extends Controller {
    private FiscalYearModel $fiscalYearModel;
    private EuroRateModel $euroRateModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->fiscalYearModel = new FiscalYearModel();
        $this->euroRateModel = new EuroRateModel();
    }
    
    /**
     * Lista todos los años fiscales (RF01)
     */
    public function index(): void {
        $fiscalYears = $this->fiscalYearModel->getAll();
        
        $data = [
            'title' => 'Años Fiscales',
            'fiscalYears' => $fiscalYears
        ];
        
        $this->view('FiscalYear/Index', $data);
    }
    
    /**
     * Muestra el formulario para crear un año fiscal (RF01)
     */
    public function create(): void {
        $data = [
            'title' => 'Crear Año Fiscal'
        ];
        
        $this->view('FiscalYear/Create', $data);
    }
    
    /**
     * Procesa la creación de un año fiscal (RF01 + RF02)
     */
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('fiscalyear/index');
        }
        
        $this->requireCsrfToken();
        
        $year = (int) $this->post('year');
        $startDate = $this->post('start_date');
        $endDate = $this->post('end_date');
        
        // Validar
        if (empty($year) || empty($startDate) || empty($endDate)) {
            Session::flash('error', 'Todos los campos son requeridos');
            $this->redirect('fiscalyear/create');
        }
        
        // Verificar que el año no exista
        if ($this->fiscalYearModel->yearExists($year)) {
            Session::flash('error', 'El año fiscal ya existe');
            $this->redirect('fiscalyear/create');
        }
        
        // Crear el año fiscal
        $id = $this->fiscalYearModel->create([
            'year' => $year,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        if ($id) {
            Session::flash('success', 'Año fiscal creado exitosamente. Se generaron los pagos para todos los contratos.');
            $this->redirect('fiscalyear/index');
        } else {
            Session::flash('error', 'Error al crear el año fiscal');
            $this->redirect('fiscalyear/create');
        }
    }
    
    /**
     * Lista las tasas de euro (RF04)
     */
    public function rates(): void {
        $rates = $this->euroRateModel->getAll();
        
        $data = [
            'title' => 'Tasas de Euro',
            'rates' => $rates
        ];
        
        $this->view('FiscalYear/Rates', $data);
    }
    
    /**
     * Muestra el formulario para crear/actualizar una tasa (RF04)
     */
    public function createRate(): void {
        $data = [
            'title' => 'Establecer Tasa de Euro',
            'months' => [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ]
        ];
        
        $this->view('FiscalYear/CreateRate', $data);
    }
    
    /**
     * Procesa la creación/actualización de una tasa (RF04 + RF05)
     */
    public function storeRate(): void {
        if (!$this->isPost()) {
            $this->redirect('fiscalyear/rates');
        }
        
        $this->requireCsrfToken();
        
        $monthNumber = (int) $this->post('month');
        $year = (int) $this->post('year');
        $bsValue = (float) $this->post('bs_value');
        
        // Validar
        if (empty($monthNumber) || empty($year) || empty($bsValue)) {
            Session::flash('error', 'Todos los campos son requeridos');
            $this->redirect('fiscalyear/createrate');
        }
        
        if ($monthNumber < 1 || $monthNumber > 12) {
            Session::flash('error', 'Mes inválido');
            $this->redirect('fiscalyear/createrate');
        }
        
        // Convertir número de mes a nombre en minúsculas
        $monthNames = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $monthName = $monthNames[$monthNumber];
        
        // Crear/actualizar la tasa (automáticamente actualiza las facturas - RF05)
        $id = $this->euroRateModel->createOrUpdate([
            'month' => $monthName,
            'month_number' => $monthNumber,
            'year' => $year,
            'bs_value' => $bsValue
        ]);
        
        if ($id) {
            Session::flash('success', 'Tasa de euro establecida exitosamente. Se actualizaron todas las facturas del mes.');
            $this->redirect('fiscalyear/rates');
        } else {
            Session::flash('error', 'Error al establecer la tasa');
            $this->redirect('fiscalyear/createrate');
        }
    }
    
    /**
     * Muestra el formulario para editar una tasa (RF04)
     */
    public function editRate(int $id): void {
        $rate = $this->euroRateModel->getById($id);
        
        if (!$rate) {
            Session::flash('error', 'Tasa no encontrada');
            $this->redirect('fiscalyear/rates');
        }
        
        $data = [
            'title' => 'Editar Tasa de Euro',
            'rate' => $rate,
            'months' => [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ]
        ];
        
        $this->view('FiscalYear/EditRate', $data);
    }
    
    /**
     * Procesa la actualización de una tasa (RF04 + RF05)
     */
    public function updateRate(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('fiscalyear/rates');
        }
        
        $this->requireCsrfToken();
        
        $rate = $this->euroRateModel->getById($id);
        
        if (!$rate) {
            Session::flash('error', 'Tasa no encontrada');
            $this->redirect('fiscalyear/rates');
        }
        
        $bsValue = (float) $this->post('bs_value');
        
        // Validar
        if (empty($bsValue)) {
            Session::flash('error', 'El valor en Bs. es requerido');
            $this->redirect('fiscalyear/editrate/' . $id);
        }
        
        // Actualizar la tasa usando el método createOrUpdate
        // (detectará que ya existe y actualizará)
        $monthNumber = (int) $this->post('month');
        $year = (int) $this->post('year');
        
        // Convertir número de mes a nombre en minúsculas
        $monthNames = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $monthName = $monthNames[$monthNumber];
        
        $success = $this->euroRateModel->createOrUpdate([
            'month' => $monthName,
            'month_number' => $monthNumber,
            'year' => $year,
            'bs_value' => $bsValue
        ]);
        
        if ($success) {
            Session::flash('success', 'Tasa actualizada exitosamente. Se actualizaron todas las facturas del mes.');
            $this->redirect('fiscalyear/rates');
        } else {
            Session::flash('error', 'Error al actualizar la tasa');
            $this->redirect('fiscalyear/editrate/' . $id);
        }
    }
    
    /**
     * Elimina una tasa de euro (AJAX)
     */
    public function deleteRate(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        // Verificar si la tasa puede ser eliminada
        $validation = $this->euroRateModel->canDeleteRate($id);
        
        if (!$validation['can_delete']) {
            $this->json(['success' => false, 'message' => $validation['message']], 400);
        }
        
        // Eliminar la tasa
        $success = $this->euroRateModel->deleteRate($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Tasa eliminada exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar la tasa'], 400);
        }
    }
    
    /**
     * Elimina múltiples tasas (AJAX)
     */
    public function bulkDeleteRate(): void {
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
            // Verificar si la tasa puede ser eliminada
            $validation = $this->euroRateModel->canDeleteRate((int)$id);
            
            if (!$validation['can_delete']) {
                $errors[] = "Tasa ID {$id}: " . $validation['message'];
                continue;
            }
            
            if ($this->euroRateModel->deleteRate((int)$id)) {
                $deleted++;
            } else {
                $errors[] = "Error al eliminar tasa ID {$id}";
            }
        }
        
        if ($deleted > 0) {
            $message = "Se eliminaron {$deleted} tasa(s) exitosamente";
            if (!empty($errors)) {
                $message .= ". " . implode(', ', $errors);
            }
            $this->json(['success' => true, 'message' => $message, 'deleted' => $deleted]);
        } else {
            $message = 'No se pudieron eliminar las tasas seleccionadas';
            if (!empty($errors)) {
                $message .= ": " . implode(', ', $errors);
            }
            $this->json(['success' => false, 'message' => $message], 400);
        }
    }
    
    /**
     * Elimina un año fiscal
     */
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        // Verificar si se puede eliminar
        $validation = $this->fiscalYearModel->canDeleteFiscalYear($id);
        
        if (!$validation['can_delete']) {
            $this->json([
                'success' => false, 
                'message' => $validation['message'],
                'relations' => $validation['relations']
            ], 400);
            return;
        }
        
        $success = $this->fiscalYearModel->deleteFiscalYear($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Año fiscal eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el año fiscal'], 400);
        }
    }
    
    /**
     * Elimina múltiples años fiscales (bulk delete)
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
            // Verificar si se puede eliminar
            $validation = $this->fiscalYearModel->canDeleteFiscalYear((int)$id);
            
            if (!$validation['can_delete']) {
                $year = $this->fiscalYearModel->getById((int)$id);
                $errors[] = "Año fiscal {$year['year']}: {$validation['message']}";
                continue;
            }
            
            if ($this->fiscalYearModel->deleteFiscalYear((int)$id)) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $message = "Se eliminaron {$deleted} año(s) fiscal(es)";
            if (!empty($errors)) {
                $message .= ". Algunos registros no se pudieron eliminar: " . implode('; ', $errors);
            }
            $this->json(['success' => true, 'message' => $message]);
        } else {
            $message = 'No se pudo eliminar ningún registro';
            if (!empty($errors)) {
                $message .= ': ' . implode('; ', $errors);
            }
            $this->json(['success' => false, 'message' => $message], 400);
        }
    }
}

