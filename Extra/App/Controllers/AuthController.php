<?php
/**
 * Controlador de Autenticación
 * 
 * Maneja el inicio de sesión, cierre de sesión y autenticación de usuarios
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Security;
use Core\Audit;
use App\Models\UserModel;

class AuthController extends Controller {
    private UserModel $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }
    
    /**
     * Muestra el formulario de login
     */
    public function login(): void {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('home/index');
        }
        
        $this->view('Auth/Login', [], null);
    }
    
    /**
     * Procesa el login
     */
    public function processLogin(): void {
        if (!$this->isPost()) {
            $this->redirect('auth/login');
        }
        
        // Verificar CSRF
        $this->requireCsrfToken();
        
        $username = $this->post('username');
        $password = $this->post('password');
        
        // Validar campos
        if (empty($username) || empty($password)) {
            Session::flash('error', 'Usuario y contraseña son requeridos');
            $this->redirect('auth/login');
        }
        
        // Autenticar
        $user = $this->userModel->authenticate($username, $password);
        
        if (!$user) {
            Session::flash('error', 'Usuario o contraseña incorrectos');
            $this->redirect('auth/login');
        }
        
        // Verificar que el usuario esté activo
        if ($user['status'] !== 'active') {
            Session::flash('error', 'Usuario inactivo');
            $this->redirect('auth/login');
        }
        
        // Establecer sesión
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('user_email', $user['email']);
        
        // Redirigir
        $redirect = Session::get('redirect_after_login', 'home/index');
        Session::delete('redirect_after_login');
        $this->redirect($redirect);
    }
    
    /**
     * Cierra la sesión
     */
    public function logout(): void {
        $userId = Session::get('user_id');
        
        if ($userId) {
            Audit::logLogout($userId);
        }
        
        Session::destroy();
        $this->redirect('auth/login');
    }
}

