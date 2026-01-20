<?php
/**
 * Controlador de Errores
 * 
 * Maneja las páginas de error del sistema
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;

class ErrorController extends Controller {
    
    /**
     * Página de error 404
     */
    public function notFound(): void {
        http_response_code(404);
        
        $data = [
            'title' => '404 - Página No Encontrada',
            'code' => '404',
            'message' => 'La página que buscas no existe o fue movida.',
            'suggestion' => 'Verifica la URL o vuelve al inicio.'
        ];
        
        $this->view('Error/NotFound', $data, null);
    }
    
    /**
     * Página de error 403
     */
    public function forbidden(): void {
        http_response_code(403);
        
        $data = [
            'title' => '403 - Acceso Denegado',
            'code' => '403',
            'message' => 'No tienes permisos para acceder a este recurso.',
            'suggestion' => 'Contacta al administrador si crees que esto es un error.'
        ];
        
        $this->view('Error/Forbidden', $data, null);
    }
    
    /**
     * Página de error genérico
     */
    public function generic(): void {
        http_response_code(500);
        
        $data = [
            'title' => 'Error del Servidor',
            'code' => '500',
            'message' => 'Ha ocurrido un error inesperado.',
            'suggestion' => 'Intenta de nuevo en unos momentos.'
        ];
        
        $this->view('Error/Generic', $data, null);
    }
}

