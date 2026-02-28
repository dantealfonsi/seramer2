<?php

require_once __DIR__ . '/../models/RoleModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class RolesController {
    private $roleModel;
    private $userModel;

    public function __construct() {
        $this->roleModel = new RoleModel();
        $this->userModel = new UserModel();
    }

    /**
     * List rules. Superadmin sees all, Dept Admin sees theirs.
     */
    public function index($params = []) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user_id = $_SESSION['user_id'];
        
        $is_superadmin = $this->userModel->isSuperadmin($user_id);
        $managerInfo = $this->userModel->isManager($user_id);

        if (!$is_superadmin && !$managerInfo) {
            return [
                'success' => false,
                'message' => 'No tiene permisos para acceder a esta sección',
                'redirect' => '../dashboard/dashboard.php'
            ];
        }

        // Backend Filters
        $dept_filter = $_GET['department'] ?? '';
        
        $roles = [];
        $all_departments = [];

        if ($is_superadmin) {
            $roles = $this->roleModel->getAll($dept_filter);
            $all_departments = $this->roleModel->getDepartments();
        } else {
            // Manager only sees their department's roles
            $roles = $this->roleModel->getAll($managerInfo['id']);
            $all_departments = [['id' => $managerInfo['id'], 'name' => $managerInfo['name']]];
        }

        return [
            'success' => true,
            'roles' => $roles,
            'all_departments' => $all_departments,
            'dept_filter' => $dept_filter,
            'is_superadmin' => $is_superadmin,
            'manager_info' => $managerInfo,
            'page_title' => 'Gestión de Roles'
        ];
    }

    /**
     * Create a new role
     */
    public function create($params) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user_id = $_SESSION['user_id'];
        
        $is_superadmin = $this->userModel->isSuperadmin($user_id);
        
        // Solo superadmin puede crear
        if (!$is_superadmin) {
            return ['success' => false, 'message' => 'Solo el Superadmin puede crear roles'];
        }

        if (isset($params['_method']) && $params['_method'] === 'POST') {
            
            // Extract menu_permissions array (which modules are checked)
            $menu_permissions = isset($params['menu_permissions']) ? $params['menu_permissions'] : [];
            $menu_json = !empty($menu_permissions) ? json_encode($menu_permissions) : null;

            $data = [
                'department_id' => $params['department_id'] ?? null,
                'name' => trim($params['name'] ?? ''),
                'description' => trim($params['description'] ?? ''),
                'can_read' => isset($params['can_read']) ? 1 : 0,
                'can_write' => isset($params['can_write']) ? 1 : 0,
                'can_modify' => isset($params['can_modify']) ? 1 : 0,
                'can_delete' => isset($params['can_delete']) ? 1 : 0,
                'menu_json' => $menu_json
            ];

            if (empty($data['name']) || empty($data['department_id'])) {
                return ['success' => false, 'message' => 'Nombre y departamento son requeridos'];
            }

            if ($this->roleModel->create($data)) {
                return ['success' => true, 'message' => 'Rol creado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al crear el rol'];
            }
        }
        return ['success' => false, 'message' => 'Method not allowed'];
    }

    /**
     * Update role
     */
    public function update($id, $params) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user_id = $_SESSION['user_id'];
        
        $is_superadmin = $this->userModel->isSuperadmin($user_id);
        $managerInfo = $this->userModel->isManager($user_id);

        if (!$is_superadmin && !$managerInfo) {
            return ['success' => false, 'message' => 'Sin permisos'];
        }

        $role = $this->roleModel->getById($id);
        if (!$role) {
            return ['success' => false, 'message' => 'Rol no encontrado'];
        }

        // Dept admin checking
        if (!$is_superadmin && $role['department_id'] != $managerInfo['id']) {
            return ['success' => false, 'message' => 'No puede editar roles de otros departamentos'];
        }

        if (isset($params['_method']) && $params['_method'] === 'POST') {
            // Extract menu_permissions array (which modules are checked)
            $menu_permissions = isset($params['menu_permissions']) ? $params['menu_permissions'] : [];
            $menu_json = !empty($menu_permissions) ? json_encode($menu_permissions) : null;

            if ($is_superadmin) {
                $data = [
                    'name' => trim($params['name'] ?? $role['name']),
                    'description' => trim($params['description'] ?? $role['description']),
                    'can_read' => isset($params['can_read']) ? 1 : 0,
                    'can_write' => isset($params['can_write']) ? 1 : 0,
                    'can_modify' => isset($params['can_modify']) ? 1 : 0,
                    'can_delete' => isset($params['can_delete']) ? 1 : 0,
                    'menu_json' => $menu_json
                ];
                $result = $this->roleModel->update($id, $data);
            } else {
                // Dept admin can only update permissions and menus
                $can_read = isset($params['can_read']) ? 1 : 0;
                $can_write = isset($params['can_write']) ? 1 : 0;
                $can_modify = isset($params['can_modify']) ? 1 : 0;
                $can_delete = isset($params['can_delete']) ? 1 : 0;
                $result = $this->roleModel->updatePermissions($id, $can_read, $can_write, $can_modify, $can_delete, $menu_json);
            }

            if ($result) {
                return ['success' => true, 'message' => 'Rol actualizado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar (quizás intentó editar al admin)'];
            }
        }
        return ['success' => false, 'message' => 'Method not allowed'];
    }

    /**
     * Delete role
     */
    public function delete($id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user_id = $_SESSION['user_id'];
        $is_superadmin = $this->userModel->isSuperadmin($user_id);

        if (!$is_superadmin) {
            return ['success' => false, 'message' => 'Solo superadmin puede eliminar roles'];
        }

        if ($this->roleModel->delete($id)) {
            return ['success' => true, 'message' => 'Rol eliminado correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar el rol'];
        }
    }

    /**
     * Helper to get the master menu structure for rendering checkboxes in the view
     */
    public function getMasterMenuNodes($department_id) {
        // Fetch department name to match UserModel map
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT name FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $department_id);
        $stmt->execute();
        $dept = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dept) {
            return [];
        }

        // TrickUserModel to bypass role filtering and just get the RAW master menu
        // Currently, getMenusByDepartment will return full if we pretend we have superadmin
        // or we bypass the filtering
        $tempAuth = false;
        if (empty($_SESSION['is_superadmin'])) {
            $tempAuth = true;
            $_SESSION['is_superadmin'] = 1; // Temporarily trick the model to get full layout
        }
        
        $userModel = new UserModel();
        $menus = $userModel->getMenusByDepartment($dept['name']);
        
        if ($tempAuth) {
            unset($_SESSION['is_superadmin']); // Restore
        }
        
        return $menus;
    }

    /**
     * Compatibility layer for legacy permission checks in views.
     * Maps module/action strings to the new AuthMiddleware departmental checks.
     * 
     * @param string $module The module identifier (e.g. 'INFRACTIONS', 'AWARDEES')
     * @param string $action The action ('r', 'w', 'm', 'd')
     * @return bool
     */
    public function hasPermission($module, $action) {
        // Map module to department
        $module_map = [
            'INFRACTIONS' => 'Fiscalizacion',
            'SANCTIONS' => 'Fiscalizacion',
            'INSPECTIONS' => 'Fiscalizacion',
            'CONCILIATIONS' => 'Fiscalizacion',
            'AWARDEES' => 'Liquidacion',
            'CONTRACTS' => 'Liquidacion',
            'RATES' => 'Liquidacion',
            'INTERNAL_CATEGORIES' => 'Liquidacion',
            'ZONES' => 'Liquidacion',
            'SECTORS' => 'Liquidacion',
            'STALLS' => 'Liquidacion',
            'PAYMENT_METHODS' => 'Liquidacion',
            'FISCAL_YEAR' => 'Liquidacion',
            'COMPLAINTS' => 'Recursos Humanos'
        ];
        
        $department = $module_map[strtoupper($module)] ?? $module;
        
        // Map action to permission column
        $action_map = [
            'r' => 'can_read',
            'w' => 'can_write',
            'm' => 'can_modify',
            'd' => 'can_delete'
        ];
        
        $permission = $action_map[strtolower($action)] ?? $action;
        
        return AuthMiddleware::hasPermission($department, $permission);
    }
}