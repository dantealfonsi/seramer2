<?php

// Asume que la clase Database y UsersModel están cargadas
require_once __DIR__ . '/../models/UsersFiscModel.php';
require_once __DIR__ . '/../config/Database.php'; // Asumiendo que esta es tu ruta

class UsersFiscController {
    private $model;
        
    const PERMISSION_AREAS = [
        'INFRACTIONS' => 'Infracciones',
        'CONFIG_RATES' => 'Tasas/Config.',
        'USERS_AUDIT' => 'Usuarios/Auditoría'
    ];

    public function __construct() {
        $this->model = new UsersFiscModel();
        $this->model->createTablesUsersFisc(); // Asegura que la tabla exista
        $this->model->migrateFiscalizationUsers(); // Migra usuarios existentes si es necesario

    }
    
    public function indexRoles(): array {
        $roles = $this->model->getAllRolesWithPermissions();

        return [
            'roles' => $roles,
            'areas' => self::PERMISSION_AREAS,
            'page_title' => 'Gestión de Permisos (rwx)'
        ];
    }    

    public function updatePermissionsAjax(int $roleId, string $newPermissionsMask): void {
        header('Content-Type: application/json');

        if (empty($roleId) || empty($newPermissionsMask)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros de rol o máscara.']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }        

        $success = $this->model->updateRolePermissions($roleId, $newPermissionsMask);
        // Actualizar la máscara de permisos en la sesión si el rol modificado es el del usuario actual
        if (isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === $roleId) {
            $_SESSION['user_permissions_mask'] = $newPermissionsMask;
        }

        if ($success) {            
            echo json_encode(['success' => true, 'message' => 'Permisos actualizados correctamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al guardar los permisos en la base de datos.']);
        }
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