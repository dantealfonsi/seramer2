<?php
/**
 * Controlador de Zonas
 * 
 * Gestiona las zonas del mercado
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\ZoneModel;

class ZoneController extends Controller {
    private ZoneModel $model;
    
    public function __construct() {
        $this->requireAuth();
        $this->model = new ZoneModel();
    }
    
    public function index(): void {
        $zones = $this->model->getAll();
        
        $data = [
            'title' => 'Zonas',
            'zones' => $zones
        ];
        
        $this->view('Catalog/Zone/Index', $data);
    }
    
    public function create(): void {
        $data = ['title' => 'Crear Zona'];
        $this->view('Catalog/Zone/Create', $data);
    }
    
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('zone/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'name' => $this->sanitize($this->post('name')),
            'description' => $this->sanitize($this->post('description'))
        ];
        
        if (empty($data['name'])) {
            Session::flash('error', 'El nombre es requerido');
            $this->redirect('zone/create');
        }
        
        $id = $this->model->create($data);
        
        if ($id) {
            Session::flash('success', 'Zona creada exitosamente');
            $this->redirect('zone/index');
        } else {
            Session::flash('error', 'Error al crear la zona');
            $this->redirect('zone/create');
        }
    }
    
    public function edit(int $id): void {
        $zone = $this->model->getById($id);
        
        if (!$zone) {
            Session::flash('error', 'Zona no encontrada');
            $this->redirect('zone/index');
        }
        
        $data = [
            'title' => 'Editar Zona',
            'zone' => $zone
        ];
        
        $this->view('Catalog/Zone/Edit', $data);
    }
    
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('zone/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'name' => $this->sanitize($this->post('name')),
            'description' => $this->sanitize($this->post('description'))
        ];
        
        if (empty($data['name'])) {
            Session::flash('error', 'El nombre es requerido');
            $this->redirect('zone/edit/' . $id);
        }
        
        $success = $this->model->update($id, $data);
        
        if ($success) {
            Session::flash('success', 'Zona actualizada exitosamente');
            $this->redirect('zone/index');
        } else {
            Session::flash('error', 'Error al actualizar la zona');
            $this->redirect('zone/edit/' . $id);
        }
    }
    
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        $success = $this->model->deleteZone($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Zona eliminada exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'No se puede eliminar la zona'], 400);
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
            if ($this->model->deleteZone((int)$id)) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->json(['success' => true, 'message' => "Se eliminaron {$deleted} zona(s) exitosamente", 'deleted' => $deleted]);
        } else {
            $this->json(['success' => false, 'message' => 'No se pudieron eliminar las zonas seleccionadas'], 400);
        }
    }
}

