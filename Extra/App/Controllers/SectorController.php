<?php
/**
 * Controlador de Sectores
 * 
 * Gestiona los sectores del mercado (relacionados con zonas)
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\SectorModel;
use App\Models\ZoneModel;

class SectorController extends Controller {
    private SectorModel $model;
    private ZoneModel $zoneModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->model = new SectorModel();
        $this->zoneModel = new ZoneModel();
    }
    
    public function index(): void {
        $sectors = $this->model->getAll();
        
        $data = [
            'title' => 'Sectores',
            'sectors' => $sectors
        ];
        
        $this->view('Catalog/Sector/Index', $data);
    }
    
    public function create(): void {
        $zones = $this->zoneModel->getAll();
        
        $data = [
            'title' => 'Crear Sector',
            'zones' => $zones
        ];
        
        $this->view('Catalog/Sector/Create', $data);
    }
    
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('sector/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'zone_id' => $this->post('zone_id'),
            'name' => $this->sanitize($this->post('name')),
            'description' => $this->sanitize($this->post('description'))
        ];
        
        if (empty($data['name']) || empty($data['zone_id'])) {
            Session::flash('error', 'Nombre y zona son requeridos');
            $this->redirect('sector/create');
        }
        
        $id = $this->model->create($data);
        
        if ($id) {
            Session::flash('success', 'Sector creado exitosamente');
            $this->redirect('sector/index');
        } else {
            Session::flash('error', 'Error al crear el sector');
            $this->redirect('sector/create');
        }
    }
    
    public function edit(int $id): void {
        $sector = $this->model->getById($id);
        
        if (!$sector) {
            Session::flash('error', 'Sector no encontrado');
            $this->redirect('sector/index');
        }
        
        $zones = $this->zoneModel->getAll();
        
        $data = [
            'title' => 'Editar Sector',
            'sector' => $sector,
            'zones' => $zones
        ];
        
        $this->view('Catalog/Sector/Edit', $data);
    }
    
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('sector/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'zone_id' => $this->post('zone_id'),
            'name' => $this->sanitize($this->post('name')),
            'description' => $this->sanitize($this->post('description'))
        ];
        
        if (empty($data['name']) || empty($data['zone_id'])) {
            Session::flash('error', 'Nombre y zona son requeridos');
            $this->redirect('sector/edit/' . $id);
        }
        
        $success = $this->model->update($id, $data);
        
        if ($success) {
            Session::flash('success', 'Sector actualizado exitosamente');
            $this->redirect('sector/index');
        } else {
            Session::flash('error', 'Error al actualizar el sector');
            $this->redirect('sector/edit/' . $id);
        }
    }
    
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        $success = $this->model->deleteSector($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Sector eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'No se puede eliminar el sector'], 400);
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
            if ($this->model->deleteSector((int)$id)) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->json(['success' => true, 'message' => "Se eliminaron {$deleted} sector(es) exitosamente", 'deleted' => $deleted]);
        } else {
            $this->json(['success' => false, 'message' => 'No se pudieron eliminar los sectores seleccionados'], 400);
        }
    }
    
    /**
     * Obtiene sectores por zona (AJAX)
     */
    public function getByZone(int $zoneId): void {
        $sectors = $this->model->getByZone($zoneId);
        $this->json([
            'success' => true,
            'sectors' => $sectors
        ]);
    }
}

