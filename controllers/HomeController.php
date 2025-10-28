<?php
require_once __DIR__ . '/../models/UsersFiscModel.php';
class HomeController {
    private $model;
    
    public function __construct() {
        $this->model = new UsersFiscModel();
    }

    public function index() {
        // Asegúrate de que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $mask = $this->model->getUserPermissionsMask($_SESSION['user_id'] ?? null);

        if ($mask) {         
            $_SESSION['user_permissions_mask'] = $mask;
        }
        // Lógica para la página de inicio
        // Por ejemplo, cargar una vista de "bienvenida".
        require_once 'views/auth/login.php';
    }
}

?>