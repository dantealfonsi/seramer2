<?php
/**
 * Controlador de Usuarios
 * 
 * Gestiona los usuarios del sistema
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\UserModel;

class UserController extends Controller {
    private UserModel $userModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->userModel = new UserModel();
    }
    
    /**
     * Lista todos los usuarios
     */
    public function index(): void {
        $users = $this->userModel->getAll();
        
        $data = [
            'title' => 'Usuarios',
            'users' => $users
        ];
        
        $this->view('User/Index', $data);
    }
    
    /**
     * Muestra el formulario para crear un usuario
     */
    public function create(): void {
        $data = [
            'title' => 'Crear Usuario'
        ];
        
        $this->view('User/Create', $data);
    }
    
    /**
     * Procesa la creación de un usuario
     */
    public function store(): void {
        if (!$this->isPost()) {
            $this->redirect('user/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'username' => $this->sanitize($this->post('username')),
            'email' => $this->sanitize($this->post('email')),
            'password' => $this->post('password'),
            'status' => $this->sanitize($this->post('status')) ?? 'active'
        ];
        
        // Validar campos requeridos
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            Session::flash('error', 'Todos los campos son requeridos');
            $this->redirect('user/create');
        }
        
        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Email inválido');
            $this->redirect('user/create');
        }
        
        // Crear el usuario
        $id = $this->userModel->create($data);
        
        if ($id) {
            Session::flash('success', 'Usuario creado exitosamente');
            $this->redirect('user/index');
        } else {
            Session::flash('error', 'Error al crear el usuario. El username o email ya existe.');
            $this->redirect('user/create');
        }
    }
    
    /**
     * Muestra el formulario para editar un usuario
     */
    public function edit(int $id): void {
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            Session::flash('error', 'Usuario no encontrado');
            $this->redirect('user/index');
        }
        
        $data = [
            'title' => 'Editar Usuario',
            'user' => $user
        ];
        
        $this->view('User/Edit', $data);
    }
    
    /**
     * Procesa la actualización de un usuario
     */
    public function update(int $id): void {
        if (!$this->isPost()) {
            $this->redirect('user/index');
        }
        
        $this->requireCsrfToken();
        
        $data = [
            'username' => $this->sanitize($this->post('username')),
            'email' => $this->sanitize($this->post('email')),
            'status' => $this->sanitize($this->post('status')) ?? 'active'
        ];
        
        // Validar campos requeridos
        if (empty($data['username']) || empty($data['email'])) {
            Session::flash('error', 'Username y email son requeridos');
            $this->redirect('user/edit/' . $id);
        }
        
        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Email inválido');
            $this->redirect('user/edit/' . $id);
        }
        
        // Actualizar el usuario
        $success = $this->userModel->update($id, $data);
        
        if ($success) {
            Session::flash('success', 'Usuario actualizado exitosamente');
            $this->redirect('user/index');
        } else {
            Session::flash('error', 'Error al actualizar el usuario. El username o email ya existe.');
            $this->redirect('user/edit/' . $id);
        }
    }
    
    /**
     * Elimina un usuario
     */
    public function delete(int $id): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $this->requireCsrfToken();
        
        $success = $this->userModel->deleteUser($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al eliminar el usuario. No se puede eliminar el usuario actual.'], 400);
        }
    }
    
    /**
     * Elimina múltiples usuarios
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
        $errors = 0;
        
        foreach ($ids as $id) {
            if ($this->userModel->deleteUser((int)$id)) {
                $deleted++;
            } else {
                $errors++;
            }
        }
        
        if ($deleted > 0) {
            $message = "Se eliminaron {$deleted} usuario(s) exitosamente";
            if ($errors > 0) {
                $message .= ". {$errors} no pudieron eliminarse";
            }
            $this->json(['success' => true, 'message' => $message, 'deleted' => $deleted, 'errors' => $errors]);
        } else {
            $this->json(['success' => false, 'message' => 'No se pudieron eliminar los usuarios seleccionados'], 400);
        }
    }
}

