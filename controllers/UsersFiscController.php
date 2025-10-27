<?php

// Asume que la clase Database y UsersModel están cargadas
require_once __DIR__ . '/../models/UsersFiscModel.php';
require_once __DIR__ . '/../config/Database.php'; // Asumiendo que esta es tu ruta

class UsersFiscController {
    private $model;

    public function __construct() {
        $this->model = new UsersFiscModel();
    }

    /**
     * Prepara los datos de los usuarios de Fiscalización para la vista.
     *
     * @return array Datos listos para ser mostrados en la vista.
     */
    public function indexFiscalizationUsers(): array {
        $users = $this->model->getUsersByFiscalizationDepartment();

        // Puedes añadir aquí lógica de paginación o formateo si es necesario

        return [
            'users' => $users,
            'page_title' => 'Usuarios del Departamento de Fiscalización'
        ];
    }
}