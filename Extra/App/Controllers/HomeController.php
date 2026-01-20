<?php
/**
 * Controlador Home
 * 
 * Controlador principal de la aplicación
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;

class HomeController extends Controller {
    
    public function __construct() {
        $this->requireAuth();
    }
    
    /**
     * Página principal
     */
    public function index(): void {
        $data = [
            'title' => 'Dashboard',
            'page' => 'Dashboard Principal'
        ];
        
        $this->view('Home/Index', $data);
    }
}

