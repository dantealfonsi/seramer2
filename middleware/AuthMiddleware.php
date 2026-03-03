<?php

require_once __DIR__ . '/../controllers/AuthController.php';

class AuthMiddleware {
    private static $authController;
    
    private static function getAuthController() {
        if (self::$authController === null) {
            self::$authController = new AuthController();
        }
        return self::$authController;
    }

    /**
     * Middleware básico de autenticación
     * Usar al inicio de cualquier página que requiera login
     * 
     * @param string $redirect_url URL de redirección si no está autenticado
     */
    public static function requireAuth($redirect_url = '../auth/login.php') {
        $auth = self::getAuthController();
        $auth->requireAuth($redirect_url);
    }

    /**
     * Middleware para requerir departamento específico
     * 
     * @param string $required_department Nombre del departamento requerido
     * @param string $redirect_url URL de redirección si no tiene acceso
     */
    public static function requireDepartment($required_department, $redirect_url = '../dashboard/dashboard.php') {
        $auth = self::getAuthController();
        $auth->requireDepartment($required_department, $redirect_url);
    }

    /**
     * Obtener usuario actual (ya autenticado)
     * 
     * @return array|null Datos del usuario o null si no está autenticado
     */
    public static function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        // Si ya tenemos el usuario en sesión, lo devolvemos
        // Pero siempre validamos el estado súper crítico de is_superadmin contra la BD en tiempo real
        if (isset($_SESSION['user']) && !empty($_SESSION['user']['departments'])) {
            require_once __DIR__ . '/../models/UserModel.php';
            $userModel = new UserModel();
            $dbUser = $userModel->getById($_SESSION['user_id']);
            if ($dbUser) {
                $_SESSION['is_superadmin'] = !empty($dbUser['is_superadmin']) ? 1 : 0;
            }
            return $_SESSION['user'];
        }

        // Si no está en sesión o está incompleto, forzamos recarga
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getById($_SESSION['user_id']);
        if ($user) {
            $_SESSION['user'] = $user; // Actualizar sesión
            $_SESSION['is_superadmin'] = !empty($user['is_superadmin']); // Sincronizar flag global
            return $user;
        }
        
        return null;
    }

    /**
     * Verificar si está autenticado sin redireccionar
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        $auth = self::getAuthController();
        return $auth->isAuthenticated();
    }

    /**
     * Middleware para páginas que solo deben ver usuarios NO autenticados
     * (como login, registro, etc.)
     * 
     * @param string $redirect_url URL de redirección si ya está autenticado
     */
    public static function requireGuest($redirect_url = '../dashboard/dashboard.php') {
        if (self::isAuthenticated()) {
            header("Location: $redirect_url");
            exit;
        }
    }

    /**
     * Middleware para verificar múltiples departamentos (acceso OR)
     * 
     * @param array $allowed_departments Array de departamentos permitidos
     * @param string $redirect_url URL de redirección si no tiene acceso
     */
    public static function requireAnyDepartment($allowed_departments, $redirect_url = '../dashboard/dashboard.php') {
        self::requireAuth();
        
        $auth = self::getAuthController();
        $current_user = $auth->getCurrentUser();
        
        if (empty($current_user['departments'])) {
            header("Location: $redirect_url");
            exit;
        }
        
        $user_dept_names = array_column($current_user['departments'], 'name');
        $has_access = !empty(array_intersect($allowed_departments, $user_dept_names));
        
        if (!$has_access) {
            header("Location: $redirect_url");
            exit;
        }
    }

    /**
     * Obtener departamentos del usuario actual
     * 
     * @return array Lista de departamentos
     */
    public static function getUserDepartments() {
        $user = self::getCurrentUser();
        return $user['departments'] ?? [];
    }

    /**
     * Verificar si el usuario actual tiene acceso a un departamento específico
     * 
     * @param string $department_name
     * @return bool
     */
    public static function hasAccessToDepartment($department_name) {
        $user = self::getCurrentUser();
        if (!$user) return false;
        
        if (!empty($user['is_superadmin'])) {
            return true;
        }

        $departments = $user['departments'] ?? [];
        $dept_names = array_column($departments, 'name');
        return in_array($department_name, $dept_names);
    }

    /**
     * Verificar si el usuario actual tiene permisos específicos en un departamento
     * 
     * @param string $department_name Nombre del departamento o area
     * @param string $permission 'can_read', 'can_write', 'can_modify', 'can_delete'
     * @return bool
     */
    public static function hasPermission($department_name, $permission) {
        $user = self::getCurrentUser();
        if (!$user) return false;

        // Si es superadmin global, tiene acceso a todo
        if (!empty($user['is_superadmin'])) {
            return true;
        }

        $departments = $user['departments'] ?? [];
        foreach ($departments as $dept) {
            if ($dept['name'] === $department_name) {
                // Verificar si tiene el permiso (1)
                if (isset($dept[$permission]) && $dept[$permission] == 1) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Middleware para requerir un permiso específico en un departamento
     * 
     * @param string $department_name
     * @param string $permission 'can_read', 'can_write', 'can_modify', 'can_delete'
     * @param string $redirect_url URL de redirección
     */
    public static function requirePermission($department_name, $permission, $redirect_url = '../dashboard/dashboard.php') {
        self::requireAuth();
        
        if (!self::hasPermission($department_name, $permission)) {
            header("Location: $redirect_url");
            exit;
        }
    }

    /**
     * Middleware para gestión de usuarios - permite acceso a RRHH y jefes de departamento
     * 
     * @param string $redirect_url URL de redirección si no tiene acceso
     */
    public static function requireUserManagementAccess($redirect_url = '../dashboard/dashboard.php') {
        // Requerir autenticación básica
        self::requireAuth();
        
        // Verificar si es superadmin (acceso global a RRHH / Usuarios)
        $user = self::getCurrentUser();
        if ($user && !empty($user['is_superadmin'])) {
            return;
        }

        // Verificar si es jefe de departamento (administrador)
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id && $userModel->isManager($user_id)) {
            return; // Es jefe de departamento, permitir acceso
        }
        
        // No tiene acceso ni como RRHH ni como jefe
        error_log("Access Denied for user_id $user_id in requireUserManagementAccess. Redirecting to $redirect_url");
        header("Location: $redirect_url");
        exit;
    }

    /**
     * Verificar si el usuario actual es jefe de departamento
     * 
     * @return array|false Información del departamento si es jefe, false si no
     */
    public static function isManager() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) return false;

        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        return $userModel->isManager($user_id);
    }
}

?>