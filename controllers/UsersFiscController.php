<?php

// Asume que la clase Database y UsersModel están cargadas
require_once __DIR__ . '/../models/UsersFiscModel.php';
require_once __DIR__ . '/../config/Database.php'; // Asumiendo que esta es tu ruta

class UsersFiscController {
    private $model;

    public function __construct() {
        $this->model = new UsersFiscModel();
        $this->model->createTablesUsersFisc(); // Asegura que la tabla exista
        $this->model->migrateFiscalizationUsers(); // Migra usuarios existentes si es necesario
    }

    /**
     * Prepara los datos de los usuarios de Fiscalización para la vista.
     *
     * @return array Datos listos para ser mostrados en la vista.
     */
    public function indexFiscalizationUsers(): array {
        $users = $this->model->getUsersByFiscalizationDepartment();
        $roles = $this->model->getAllFiscalizationRoles(); // Obtener los roles

        // Puedes añadir aquí lógica de paginación o formateo si es necesario

        return [
            'users' => $users,
            'roles' => $roles,
            'page_title' => 'Usuarios del Departamento de Fiscalización'
        ];
    }

    public function updateRoleAjax(int $userId, int $roleId): void {
        if (empty($userId) || empty($roleId)) {
            header('Content-Type: application/json');
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros de usuario o rol.']);
            return;
        }

        $success = $this->model->updateUserFiscalizationRole($userId, $roleId);

        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Rol actualizado correctamente.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Error al guardar el rol en la base de datos.']);
        }    
    }
}