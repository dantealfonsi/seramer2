<?php
/**
 * Controlador de Locales del Mercado
 * 
 * Gestiona los locales del mercado (relacionados con sectores)
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\MarketStallModel;
use App\Models\SectorModel;
use App\Models\ZoneModel;

class MarketStallController extends Controller {
    private MarketStallModel $model;
    private SectorModel $sectorModel;
    private ZoneModel $zoneModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->model = new MarketStallModel();
        $this->sectorModel = new SectorModel();
        $this->zoneModel = new ZoneModel();
    }
    
    public function index(): void {
        $stalls = $this->model->getAll();
        
        $data = [
            'title' => 'Locales',
            'stalls' => $stalls
        ];
        
        $this->view('Catalog/MarketStall/Index', $data);
    }
    
    public function create(): void {
        $zones = $this->zoneModel->getAll();
        $sectors = $this->sectorModel->getAll();
        
        $data = [
            'title' => 'Crear Local',
            'zones' => $zones,
            'sectors' => $sectors
        ];
        
        $this->view('Catalog/MarketStall/Create', $data);
    }
    
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('marketstall/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'sector_id' => $this->post('sector_id'),
            'stall_number' => $this->sanitize($this->post('stall_number')),
            'location_description' => $this->sanitize($this->post('location_description'))
        ];
        
        if (empty($data['stall_number']) || empty($data['sector_id'])) {
            Session::flash('error', 'Número de local y sector son requeridos');
            $this->redirect('marketstall/create');
        }
        
        $id = $this->model->create($data);
        
        if ($id) {
            Session::flash('success', 'Local creado exitosamente');
            $this->redirect('marketstall/index');
        } else {
            Session::flash('error', 'Error al crear el local');
            $this->redirect('marketstall/create');
        }
    }
    
    public function edit(int $id): void {
        $stall = $this->model->getById($id);
        
        if (!$stall) {
            Session::flash('error', 'Local no encontrado');
            $this->redirect('marketstall/index');
        }
        
        $zones = $this->zoneModel->getAll();
        $sectors = $this->sectorModel->getAll();
        
        $data = [
            'title' => 'Editar Local',
            'stall' => $stall,
            'zones' => $zones,
            'sectors' => $sectors
        ];
        
        $this->view('Catalog/MarketStall/Edit', $data);
    }
    
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('marketstall/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'sector_id' => $this->post('sector_id'),
            'stall_number' => $this->sanitize($this->post('stall_number')),
            'location_description' => $this->sanitize($this->post('location_description'))
        ];
        
        if (empty($data['stall_number']) || empty($data['sector_id'])) {
            Session::flash('error', 'Número de local y sector son requeridos');
            $this->redirect('marketstall/edit/' . $id);
        }
        
        $success = $this->model->update($id, $data);
        
        if ($success) {
            Session::flash('success', 'Local actualizado exitosamente');
            $this->redirect('marketstall/index');
        } else {
            Session::flash('error', 'Error al actualizar el local');
            $this->redirect('marketstall/edit/' . $id);
        }
    }
    
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        $success = $this->model->deleteStall($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Local eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'No se puede eliminar el local'], 400);
        }
    }
    
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
        foreach ($ids as $id) {
            if ($this->model->deleteStall((int)$id)) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->json(['success' => true, 'message' => "Se eliminaron {$deleted} local(es) exitosamente", 'deleted' => $deleted]);
        } else {
            $this->json(['success' => false, 'message' => 'No se pudieron eliminar los locales seleccionados'], 400);
        }
    }
    
    /**
     * Obtiene locales disponibles por sector (AJAX)
     */
    public function getBySector(int $sectorId): void {
        $stalls = $this->model->getBySector($sectorId);
        $this->json([
            'success' => true,
            'stalls' => $stalls
        ]);
    }
    
    /**
     * Creación rápida de local (para modales/AJAX)
     */
    public function quickStore(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $data = [
            'sector_id' => (int) $this->post('sector_id'),
            'stall_number' => $this->sanitize($this->post('stall_number')),
            'description' => $this->sanitize($this->post('description'))
        ];
        
        // Validar campos requeridos
        if (empty($data['sector_id']) || empty($data['stall_number'])) {
            $this->json(['success' => false, 'message' => 'Sector y número de local son requeridos']);
        }
        
        // Verificar si el número de local ya existe en ese sector
        $existing = $this->model->getByStallNumber($data['sector_id'], $data['stall_number']);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Ya existe un local con ese número en este sector']);
        }
        
        // Crear el local
        $id = $this->model->create($data);
        
        if ($id) {
            // Obtener el local recién creado con información completa
            $stall = $this->model->getById($id);
            
            $this->json([
                'success' => true,
                'message' => 'Local creado exitosamente',
                'stall' => $stall
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Error al crear el local'], 500);
        }
    }
    
    /**
     * Obtiene sectores de una zona (AJAX)
     */
    public function getSectorsByZone(): void {
        if (!$this->isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición no válida'], 400);
        }
        
        $zoneId = $this->get('zone_id');
        
        if (!$zoneId) {
            $this->json(['success' => false, 'message' => 'Zona no especificada'], 400);
        }
        
        $sectors = $this->sectorModel->getByZone((int)$zoneId);
        $this->json(['success' => true, 'sectors' => $sectors]);
    }
}

