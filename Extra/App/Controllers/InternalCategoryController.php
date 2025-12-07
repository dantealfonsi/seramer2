<?php
/**
 * Controlador de Rubros Internos
 * 
 * Gestiona los rubros/categorías de negocios internos del mercado
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\InternalBusinessCategoryModel;

class InternalCategoryController extends Controller {
    private InternalBusinessCategoryModel $model;
    
    public function __construct() {
        $this->requireAuth();
        $this->model = new InternalBusinessCategoryModel();
    }
    
    /**
     * Lista todas las categorías internas
     */
    public function index(): void {
        $categories = $this->model->getAll();
        
        $data = [
            'title' => 'Rubros Internos',
            'categories' => $categories
        ];
        
        $this->view('Catalog/InternalCategory/Index', $data);
    }
    
    /**
     * Muestra el formulario para crear una categoría
     */
    public function create(): void {
        $data = [
            'title' => 'Crear Rubro Interno'
        ];
        
        $this->view('Catalog/InternalCategory/Create', $data);
    }
    
    /**
     * Procesa la creación de una categoría
     */
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('internalcategory/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'name' => $this->sanitize($this->post('name')),
            'payment_count' => $this->post('payment_count')
        ];
        
        if (empty($data['name']) || empty($data['payment_count'])) {
            Session::flash('error', 'Nombre y número de cobros son requeridos');
            $this->redirect('internalcategory/create');
        }
        
        $id = $this->model->create($data);
        
        if ($id) {
            Session::flash('success', 'Rubro interno creado exitosamente');
            $this->redirect('internalcategory/index');
        } else {
            Session::flash('error', 'Error al crear el rubro interno');
            $this->redirect('internalcategory/create');
        }
    }
    
    /**
     * Muestra el formulario para editar una categoría
     */
    public function edit(int $id): void {
        $category = $this->model->getById($id);
        
        if (!$category) {
            Session::flash('error', 'Rubro interno no encontrado');
            $this->redirect('internalcategory/index');
        }
        
        $data = [
            'title' => 'Editar Rubro Interno',
            'category' => $category
        ];
        
        $this->view('Catalog/InternalCategory/Edit', $data);
    }
    
    /**
     * Procesa la actualización de una categoría
     */
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('internalcategory/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'name' => $this->sanitize($this->post('name')),
            'payment_count' => $this->post('payment_count')
        ];
        
        if (empty($data['name']) || empty($data['payment_count'])) {
            Session::flash('error', 'Nombre y número de cobros son requeridos');
            $this->redirect('internalcategory/edit/' . $id);
        }
        
        $success = $this->model->update($id, $data);
        
        if ($success) {
            Session::flash('success', 'Rubro interno actualizado exitosamente');
            $this->redirect('internalcategory/index');
        } else {
            Session::flash('error', 'Error al actualizar el rubro interno');
            $this->redirect('internalcategory/edit/' . $id);
        }
    }
    
    /**
     * Elimina una categoría
     */
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        // Verificar si se puede eliminar
        $validation = $this->model->canDeleteCategory($id);
        
        if (!$validation['can_delete']) {
            $this->json([
                'success' => false, 
                'message' => $validation['message'],
                'relations' => $validation['relations']
            ], 400);
            return;
        }
        
        $success = $this->model->deleteCategory($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Rubro interno eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el rubro interno'], 400);
        }
    }
    
    /**
     * Elimina múltiples categorías (bulk delete)
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
        foreach ($ids as $id) {
            if ($this->model->deleteCategory((int)$id)) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->json(['success' => true, 'message' => "Se eliminaron {$deleted} rubro(s) exitosamente", 'deleted' => $deleted]);
        } else {
            $this->json(['success' => false, 'message' => 'No se pudieron eliminar los rubros seleccionados'], 400);
        }
    }
}

