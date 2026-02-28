<?php

require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $conn;
    private $table = 'users';
    
    // Propiedades del usuario
    public $id;
    public $staff_id;
    public $username;
    public $password_hash;
    public $email;
    public $last_login;
    public $password_reset_token;
    public $password_reset_expires;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }


    /**
     * Obtiene la máscara de permisos del usuario dado su ID.
     * @param int $userId ID del usuario activo.
     * @return string|null La máscara de permisos (ej: 'rwx-rwx-rwx') o null si no se encuentra.
     */
    public function getUserPermissionsMask(int $userId): ?string {
        $query = "
            SELECT 
                fr.permissions_mask
            FROM 
                fiscalization_user_level fule
            INNER JOIN 
                fiscalization_roles fr ON fule.role_id = fr.role_id
            WHERE 
                fule.user_id = :user_id;
        ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            // Retorna la máscara o null
            return $result['permissions_mask'] ?? null;
            
        } catch (PDOException $e) {
            error_log("Error al obtener máscara de permisos para el usuario ID {$userId}: " . $e->getMessage());
            return null;
        }
    }    

    /**
     * Obtiene el ID único de la asignación de nivel del usuario (role_id).
     *
     * @param int $userId ID del usuario activo de la tabla 'users'.
     * @return int|null El role_id o null si el usuario no tiene una asignación en la tabla.
     */
    public function getUserLevelId(int $userId): ?int {
        $query = "
            SELECT 
                user_level_id,
                role_id 
            FROM 
                fiscalization_user_level
            WHERE 
                user_id = :user_id;
        ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            // Retorna el ID como entero, o null si no se encuentra
            return $result['role_id'] ? (int)$result['role_id'] : null;
            
        } catch (PDOException $e) {
            error_log("Error al obtener role_id para el usuario ID {$userId}: " . $e->getMessage());
            return null;
        }
    }    

    /**
     * Autenticar usuario por username y password
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function authenticate($username, $password) {
        try {
            $query = "SELECT u.*, s.first_name, s.last_name, s.department_id, 
                             d.name as department_name, d.shift_type 
                      FROM " . $this->table . " u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      WHERE u.username = :username AND u.status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Actualizar último login
                $this->updateLastLogin($user['id']);
                
                // Obtener departamentos y roles del usuario
                $user['departments'] = $this->getUserDepartments($user['id']);
                
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los departamentos
     * @return array
     */
    public function getAllDepartments() {
        try {
            $query = "SELECT * FROM departments ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo departamentos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener departamentos y roles asignados a un usuario
     * @param int $user_id
     * @return array
     */
    public function getUserDepartments($user_id) {
        try {
            $query = "SELECT d.*, ud.status as assignment_status, 
                             r.name as role_name, r.can_read, r.can_write, 
                             r.can_modify, r.can_delete, r.id as role_id, r.menu_json
                      FROM departments d 
                      INNER JOIN user_departments ud ON d.id = ud.department_id 
                      LEFT JOIN roles r ON ud.role_id = r.id 
                      WHERE ud.user_id = :user_id AND ud.status = 'active'
                      ORDER BY d.name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo departamentos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los usuarios activos de un departamento específico
     * @param int $department_id
     * @return array Array de user_ids
     */
    public function getUsersByDepartment($department_id) {
        return $this->getUsersByDepartmentId($department_id);
    }

    /**
     * Crear nuevo usuario
     * @param array $data
     * @return bool|int
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (staff_id, username, password_hash, email, status) 
                      VALUES (:staff_id, :username, :password_hash, :email, :status)";
            
            $stmt = $this->conn->prepare($query);
            
            // Hash de la contraseña
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt->bindParam(':staff_id', $data['staff_id']);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':status', $data['status']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creando usuario: " . $e->getMessage());
            return false;
        }
    }



    /**
     * Eliminar usuario (soft delete - cambiar status)
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $query = "UPDATE " . $this->table . " SET status = 'inactive' WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminando usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT u.*, s.first_name, s.last_name, s.id_number, 
                             d.name as department_name, d.id as department_id
                      FROM " . $this->table . " u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      WHERE u.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user['departments'] = $this->getUserDepartments($user['id']);
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario por username
     * @param string $username
     * @return array|false
     */
    public function getByUsername($username) {
        try {
            $query = "SELECT u.*, s.first_name, s.last_name, s.id_number, 
                             d.name as department_name, d.id as department_id
                      FROM " . $this->table . " u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      WHERE u.username = :username";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario por username: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los usuarios activos para selects
     * @return array
     */
    public function getAllForSelect() {
        try {
            $query = "SELECT u.id, u.username, s.first_name as staff_first_name, s.last_name as staff_last_name
                      FROM " . $this->table . " u
                      LEFT JOIN staff s ON u.staff_id = s.id
                      WHERE u.status = 'active'
                      ORDER BY u.username ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuarios para select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los usuarios con paginación
     * @param int $page
     * @param int $limit
     * @param string $department_filter
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $department_filter = '', $status_filter = '', $role_filter = '') {
        try {
            $offset = ($page - 1) * $limit;

            $where_clause = "WHERE 1=1";
            $params = [];

            if (!empty($department_filter)) {
                if (is_numeric($department_filter)) {
                    $where_clause .= " AND (d.id = :dept_id OR s.department_id = :dept_id)";
                    $params[':dept_id'] = $department_filter;
                } else {
                    $where_clause .= " AND d.name = :department";
                    $params[':department'] = $department_filter;
                }
            }

            if (!empty($status_filter)) {
                $where_clause .= " AND u.status = :status";
                $params[':status'] = $status_filter;
            }

            if (!empty($role_filter)) {
                $where_clause .= " AND u.id IN (SELECT user_id FROM user_departments WHERE role_id = :role_id AND status = 'active')";
                $params[':role_id'] = $role_filter;
            }

            $query = "SELECT u.*, s.first_name as staff_first_name, s.last_name as staff_last_name, s.id_number,
                             d.name as department_name, d.id as department_id, jp.name as staff_job_position,
                             (SELECT GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ') 
                              FROM user_departments ud2 
                              INNER JOIN roles r ON ud2.role_id = r.id 
                              WHERE ud2.user_id = u.id AND ud2.status = 'active') as role_names
                      FROM " . $this->table . " u
                      LEFT JOIN staff s ON u.staff_id = s.id
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      $where_clause
                      ORDER BY u.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Asignar departamento y rol a usuario
     * @param int $user_id
     * @param int $department_id
     * @param int|null $role_id
     * @return bool
     */
    public function assignDepartment($user_id, $department_id, $role_id = null) {
        try {
            // Verificar si ya existe la asignación
            $check_query = "SELECT id FROM user_departments 
                           WHERE user_id = :user_id AND department_id = :department_id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':user_id', $user_id);
            $check_stmt->bindParam(':department_id', $department_id);
            $check_stmt->execute();
            
            if ($check_stmt->fetch()) {
                // Si existe, actualizar status y role
                $query = "UPDATE user_departments SET status = 'active', role_id = :role_id 
                         WHERE user_id = :user_id AND department_id = :department_id";
            } else {
                // Si no existe, crear nueva asignación
                $query = "INSERT INTO user_departments (user_id, department_id, role_id, status) 
                         VALUES (:user_id, :department_id, :role_id, 'active')";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error asignando departamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remover departamento de usuario
     * @param int $user_id
     * @param int $department_id
     * @return bool
     */
    public function removeDepartment($user_id, $department_id) {
        try {
            $query = "UPDATE user_departments SET status = 'inactive' 
                     WHERE user_id = :user_id AND department_id = :department_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':department_id', $department_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error removiendo departamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un usuario pertenece a un departamento (vía staff o user_departments)
     * @param int $user_id
     * @param int $department_id
     * @return bool
     */
    public function isUserInDepartment($user_id, $department_id) {
        try {
            // Check in user_departments (New system)
            $query = "SELECT COUNT(*) FROM user_departments 
                      WHERE user_id = :user_id AND department_id = :department_id AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) return true;

            // Check in staff (Legacy/Fallback)
            $query = "SELECT COUNT(*) FROM " . $this->table . " u
                      JOIN staff s ON u.staff_id = s.id
                      WHERE u.id = :user_id AND s.department_id = :department_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            error_log("Error verificando pertenencia a departamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remover todos los departamentos de un usuario
     * @param int $user_id
     * @return bool
     */
    public function removeAllDepartments($user_id) {
        try {
            $query = "DELETE FROM user_departments WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminando departamentos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si usuario tiene acceso a un departamento específico y retornar sus permisos si los tiene
     * @param int $user_id
     * @param string $department_name
     * @return array|bool false si no tiene acceso, arreglo con acceso y permisos si lo tiene
     */
    public function hasAccessToDepartment($user_id, $department_name) {
        try {
            $query = "SELECT ud.id, r.name as role_name, r.can_read, r.can_write, r.can_modify, r.can_delete 
                      FROM user_departments ud
                      INNER JOIN departments d ON ud.department_id = d.id
                      LEFT JOIN roles r ON ud.role_id = r.id
                      WHERE ud.user_id = :user_id 
                      AND d.name = :department_name 
                      AND ud.status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':department_name', $department_name);
            $stmt->execute();
            
            // Si es superadmin, tiene acceso total a todos los departamentos automáticamente
            if ($this->isSuperadmin($user_id)) {
                return [
                    'id' => 0,
                    'role_name' => 'admin',
                    'can_read' => 1,
                    'can_write' => 1,
                    'can_modify' => 1,
                    'can_delete' => 1
                ];
            }

            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res !== false) {
                return $res;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error verificando acceso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si es el superadmin global
     */
    public function isSuperadmin($user_id) {
        try {
            $query = "SELECT is_superadmin FROM {$this->table} WHERE id = :user_id AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user && $user['is_superadmin'] == 1;
        } catch (PDOException $e) {
            error_log("Error verificando superadmin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los IDs de los superadmins activos.
     */
    public function getSuperadminsIds() {
        try {
            $query = "SELECT id FROM {$this->table} WHERE is_superadmin = 1 AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $users ? $users : [];
        } catch (PDOException $e) {
            error_log("Error obteniendo superadmins: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar último login
     * @param int $user_id
     * @return bool
     */
    private function updateLastLogin($user_id) {
        try {
            $query = "UPDATE " . $this->table . " SET last_login = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando último login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resetear contraseña usando token
     * @param string $token
     * @param string $new_password
     * @return bool
     */
    public function resetPassword($token, $new_password) {
        try {
            // Verificar token válido y no expirado
            $query = "SELECT id FROM " . $this->table . " 
                     WHERE password_reset_token = :token 
                     AND password_reset_expires > NOW() 
                     AND status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            // Actualizar contraseña y limpiar token
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_query = "UPDATE " . $this->table . " 
                            SET password_hash = :password_hash, 
                                password_reset_token = NULL, 
                                password_reset_expires = NULL 
                            WHERE id = :user_id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':password_hash', $password_hash);
            $update_stmt->bindParam(':user_id', $user['id']);
            
            return $update_stmt->execute();
        } catch (PDOException $e) {
            error_log("Error reseteando contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener menús por departamento filtrados por los permisos del rol del usuario (si está en sesión)
     * @param string $department_name
     * @param array $user_departments Lista de departamentos del usuario desde la sesión
     * @return array
     */
    public function getMasterMenus() {
        return [
            'Recursos Humanos' => [
                [
                    'title' => 'Quejas',
                    'icon' => 'ri-chat-3-line',
                    'submenu' => [
                        ['title' => 'Quejas (Registrar)', 'url' => 'views/complaints/create.php'],
                        ['title' => 'Historial de Quejas', 'url' => 'views/complaints/index.php'],
                    ]
                ],
                [
                    'title' => 'Control de Acceso',
                    'icon' => 'ri-lock-line',
                    'submenu' => [
                        ['title' => 'Gestión de Roles', 'url' => 'views/roles/index.php'],
                        ['title' => 'Usuarios y Permisos', 'url' => 'views/users/index.php']
                    ]
                ],
            ],
            'Liquidacion' => [
                [
                    'title' => 'Adjudicatarios',
                    'icon' => 'ri-group-line',
                    'url' => 'views/awardees/index.php'
                ],
                [
                    'title' => 'Contratos',
                    'icon' => 'ri-file-text-line',
                    'url' => 'views/contracts/index.php'
                ],
                [
                    'title' => 'Planificación',
                    'icon' => 'ri-calendar-check-line',
                    'submenu' => [
                        ['title' => 'Simultáneos', 'url' => 'views/contracts/planning_simultaneos.php'],
                        ['title' => 'Anticipados', 'url' => 'views/contracts/planning_anticipados.php']
                    ]
                ],
                [
                    'title' => 'Año Fiscal',
                    'icon' => 'ri-calendar-event-line',
                    'submenu' => [
                        ['title' => 'Configuración de Año', 'url' => 'views/fiscal_year/index.php'],
                        ['title' => 'Tasa del Euro', 'url' => 'views/rates/index.php']
                    ]
                ],
                [
                    'title' => 'Catálogo',
                    'icon' => 'ri-menu-line',
                    'submenu' => [
                        ['title' => 'Rubros Internos', 'url' => 'views/internal_categories/index.php'],
                        ['title' => 'Zonas', 'url' => 'views/zones/index.php'],
                        ['title' => 'Sectores', 'url' => 'views/sectors/index.php'],
                        ['title' => 'Locales', 'url' => 'views/market_stalls/index.php'],
                        ['title' => 'Métodos de Pago', 'url' => 'views/payment_methods/index.php']
                    ]
                ],
                [
                    'title' => 'Reportes',
                    'icon' => 'ri-file-chart-line',
                    'url' => 'views/liquidacion_reports/index.php'
                ],
                [
                    'title' => 'Control de Acceso',
                    'icon' => 'ri-lock-line',
                    'submenu' => [
                        ['title' => 'Gestión de Roles', 'url' => 'views/roles/index.php'],
                        ['title' => 'Usuarios y Permisos', 'url' => 'views/users/index.php']
                    ]
                ],
            ],
            'Fiscalizacion' => [
                [
                    'title' => 'Inspecciones',
                    'icon' => 'ri-search-eye-line',
                    'submenu' => [
                        ['title' => ' Ver Inspecciones', 'url' => 'views/inspections/index.php'],
                        ['title' => ' Listado de Inspectores', 'url' => 'views/inspectors/index.php']
                    ]
                ],
                [
                    'title' => 'Infracciones',
                    'icon' => 'ri-alert-line',
                    'submenu' => [
                        ['title' => 'Historial de Infracciones', 'url' => 'views/infractions/index.php'],
                        ['title' => 'Tipos de Infracciones', 'url' => 'views/infractions-type/index.php'],                        
                        ['title' => 'Gestinar Tasas UT/EURO', 'url' => 'views/infractions/tasas.php'],
                    ]
                ],
                [
                    'title' => 'Sanciones',
                    'icon' => 'ri-hammer-line',
                    'submenu' => [
                        ['title' => 'Seguimiento de Sanciones', 'url' => 'views/sanctions/index.php'],
                        ['title' => 'Tipos de Sanciones', 'url' => 'views/sanctionsType/index.php']
                    ]
                ],
                [
                    'title' => 'Conciliación',
                    'icon' => 'ri-discuss-line',
                    'url' => 'views/citations/index.php'
                ],
                [
                    'title' => 'Historial de Quejas',
                    'icon' => 'ri-chat-history-line',
                    'url' => 'views/complaints/index.php'
                ],
                [
                    'title' => 'Reportes',
                    'icon' => 'ri-bar-chart-box-line',
                    'submenu' => [
                        ['title' => 'Editor de Reportes', 'url' => 'views/reports/index.php'],
                        ['title' => 'Reportes Estadisticos', 'url' => 'views/statistical-reports/index.php']
                    ]
                ],
                [
                    'title' => 'Control de Acceso',
                    'icon' => 'ri-lock-line',
                    'submenu' => [
                        ['title' => 'Gestión de Roles', 'url' => 'views/roles/index.php'],
                        ['title' => 'Usuarios y Permisos', 'url' => 'views/users/index.php']
                    ]
                ]
            ],
            'Cobranza' => [
                 [
                    'title' => 'Gestión de Cobros',
                    'icon' => 'ri-money-cny-circle-line',
                    'submenu' => [
                        ['title' => 'Cuentas por Cobrar', 'url' => 'views/billing/receivable.php'],
                        ['title' => 'Gestión de Multas', 'url' => 'views/billing/fines.php'],
                        ['title' => 'Control de Morosidad', 'url' => 'views/billing/delinquency.php'],
                        ['title' => 'Pagos Recibidos', 'url' => 'views/billing/payments.php'],
                    ]
                ],
                [
                    'title' => 'Cajas',
                    'icon' => 'ri-money-dollar-circle-line',
                    'submenu' => [
                        ['title' => 'Cierre de Caja', 'url' => 'views/daily_cash/index.php'],
                        ['title' => 'Administrar Cajas', 'url' => 'views/cash_registers/index.php']
                    ]
                ],
                [
                    'title' => 'Reportes',
                    'icon' => 'ri-bar-chart-box-line',
                    'submenu' => [
                         ['title' => 'Reportes de Cobranza', 'url' => 'views/collection-reports/index.php']
                    ]
                ]
            ]
        ];
    }

    public function getMenusByDepartment($department_name, $user_departments = null) {
        if ($user_departments === null && isset($_SESSION['user_id'])) {
            $user_departments = $this->getUserDepartments($_SESSION['user_id']);
        }
        
        $menus = $this->getMasterMenus();

        $dept_menus = isset($menus[$department_name]) ? $menus[$department_name] : [];

        // Si somos superadmin, vemos todo el de ese departamento sin bloqueos (usualmente lo saltamos pero por si acaso)
        if (!empty($_SESSION['is_superadmin'])) {
            return $dept_menus;
        }

        // Si tenemos un arreglo de departamentos en sesión, filtramos por menu_json
        if ($user_departments && is_array($user_departments)) {
            $current_role_menu = null;
            // Buscar la configuración json del rol para el departamento actual
            foreach ($user_departments as $ud) {
                if ($ud['name'] === $department_name && isset($ud['role_id'])) {
                    // Si el rol es admin, forzamos a ver todo
                    if ($ud['role_name'] === 'admin') {
                        return $dept_menus;
                    }
                    
                    if (!empty($ud['menu_json'])) {
                        $current_role_menu = json_decode($ud['menu_json'], true);
                    }
                    break;
                }
            }

            // Si el rol tiene un menu_json definido, filtramos el master
            if (is_array($current_role_menu)) {
                $filtered_menus = [];
                foreach ($dept_menus as $menu) {
                    // Si el titulo del menu principal está en el array permitido
                    if (in_array($menu['title'], $current_role_menu)) {
                        $filtered_menu = $menu;
                        
                        // Si tiene submenú, verificar si algún submenú también está permitido
                        if (isset($menu['submenu'])) {
                            $filtered_submenu = [];
                            foreach ($menu['submenu'] as $sub) {
                                // Guardamos con un prefijo para identificar submenus, ej: "Infracciones - Historial de Infracciones"
                                $submenu_key = $menu['title'] . '::' . $sub['title'];
                                if (in_array($submenu_key, $current_role_menu)) {
                                    $filtered_submenu[] = $sub;
                                }
                            }
                            // Si no hay submenus permitidos, pasamos (a menos que quieras mostrar el padre vacío)
                            if (empty($filtered_submenu)) {
                                continue;
                            }
                            $filtered_menu['submenu'] = $filtered_submenu;
                        }
                        
                        $filtered_menus[] = $filtered_menu;
                    }
                }
                return $filtered_menus;
            }
        }

        return $dept_menus;
    }

    /**
     * Contar total de usuarios
     * @param string $department_filter
     * @return int
     */
    public function countUsers($department_filter = '') {
        try {
            $where_clause = "WHERE 1=1";
            $params = [];
            
            if (!empty($department_filter)) {
                $where_clause .= " AND d.name = :department";
                $params[':department'] = $department_filter;
            }
            
            $query = "SELECT COUNT(*) as total 
                     FROM " . $this->table . " u 
                     LEFT JOIN staff s ON u.staff_id = s.id 
                     LEFT JOIN departments d ON s.department_id = d.id 
                     $where_clause";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("Error contando usuarios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verificar si un usuario es jefe de departamento
     * @param int $user_id
     * @return array|false - Retorna información del departamento si es jefe, false si no
     */
    public function isManager($user_id) {
        try {
            // First check legacy manager field in departments
            $query = "SELECT d.*, s.first_name, s.last_name 
                      FROM departments d
                      INNER JOIN staff s ON d.manager_id = s.id
                      INNER JOIN users u ON s.id = u.staff_id
                      WHERE u.id = :user_id AND u.status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $manager = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($manager) return $manager;

            // Then check new user_departments for 'admin' role
            $query = "SELECT d.*, r.name as role_name
                      FROM user_departments ud
                      INNER JOIN departments d ON ud.department_id = d.id
                      INNER JOIN roles r ON ud.role_id = r.id
                      WHERE ud.user_id = :user_id 
                      AND (r.name = 'admin' OR r.name = 'administrador')
                      AND ud.status = 'active'
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error verificando si es jefe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuarios del departamento de un jefe
     * @param int $manager_user_id
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getUsersByManagerDepartment($manager_user_id, $page = 1, $limit = 10, $status_filter = '', $role_filter = '') {
        try {
            // Primero verificar que es jefe y obtener su departamento
            $manager_info = $this->isManager($manager_user_id);
            if (!$manager_info) {
                return [];
            }

            $offset = ($page - 1) * $limit;
            $params = [
                ':dept_id1' => $manager_info['id'],
                ':dept_id2' => $manager_info['id']
            ];
            
            $extra_where = "";
            if (!empty($status_filter)) {
                $extra_where .= " AND u.status = :status";
                $params[':status'] = $status_filter;
            }
            if (!empty($role_filter)) {
                $extra_where .= " AND u.id IN (SELECT user_id FROM user_departments WHERE role_id = :role_id AND status = 'active')";
                $params[':role_id'] = $role_filter;
            }

            $query = "SELECT DISTINCT u.id, u.username, u.email, u.status, u.created_at, u.is_superadmin,
                             s.first_name as staff_first_name, s.last_name as staff_last_name, s.id_number,
                             d_list.name as department_name, d_list.id as department_id, jp.name as staff_job_position,
                             (SELECT GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ') 
                              FROM user_departments ud2 
                              INNER JOIN roles r ON ud2.role_id = r.id 
                              WHERE ud2.user_id = u.id AND ud2.status = 'active') as role_names
                      FROM " . $this->table . " u
                      LEFT JOIN staff s ON u.staff_id = s.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      LEFT JOIN user_departments ud ON u.id = ud.user_id
                      JOIN departments d_list ON (s.department_id = d_list.id OR (ud.department_id = d_list.id AND ud.status = 'active'))
                      WHERE (d_list.id = :dept_id1 OR (ud.department_id = :dept_id2 AND ud.status = 'active'))
                      $extra_where
                      ORDER BY u.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuarios del departamento: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar usuarios del departamento de un jefe
     * @param int $manager_user_id
     * @return int
     */
    public function countUsersByManagerDepartment($manager_user_id) {
        try {
            // Primero verificar que es jefe y obtener su departamento
            $manager_info = $this->isManager($manager_user_id);
            if (!$manager_info) {
                return 0;
            }
            
            $query = "SELECT COUNT(DISTINCT u.id) as total 
                     FROM " . $this->table . " u 
                     LEFT JOIN staff s ON u.staff_id = s.id 
                     LEFT JOIN user_departments ud ON u.id = ud.user_id
                     WHERE (s.department_id = :dept_id1 OR (ud.department_id = :dept_id2 AND ud.status = 'active'))";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':dept_id1', $manager_info['id']);
            $stmt->bindParam(':dept_id2', $manager_info['id']);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("Error contando usuarios del departamento: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener usuario por email
     * @param string $email
     * @return array|false
     */
    public function getByEmail($email) {
        try {
            $query = "SELECT u.*, s.first_name, s.last_name, s.id_number, 
                             d.name as department_name, d.id as department_id
                      FROM " . $this->table . " u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      WHERE u.email = :email";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario por email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $set_clauses = [];
            $params = [':id' => $id];
            
            if (isset($data['username'])) {
                $set_clauses[] = "username = :username";
                $params[':username'] = $data['username'];
            }
            
            if (isset($data['email'])) {
                $set_clauses[] = "email = :email";
                $params[':email'] = $data['email'];
            }
            
            if (isset($data['status'])) {
                $set_clauses[] = "status = :status";
                $params[':status'] = $data['status'];
            }
            
            if (isset($data['password'])) {
                $set_clauses[] = "password_hash = :password_hash";
                $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $set_clauses[] = "updated_at = NOW()";
            
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $set_clauses) . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener personal sin usuario asignado por departamento
     * @param int $department_id
     * @return array
     */
    public function getStaffWithoutUserByDepartment($department_id) {
        try {
            $query = "SELECT s.*, d.name as department_name, jp.name as job_position_name,
                             ad.name as academic_degree_name, asp.name as academic_specialization_name
                      FROM staff s
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      LEFT JOIN academic_degrees ad ON s.academic_degree_id = ad.id
                      LEFT JOIN academic_specializations asp ON s.academic_specialization_id = asp.id
                      LEFT JOIN users u ON s.id = u.staff_id
                      WHERE s.department_id = :department_id 
                      AND s.status = 'active'
                      AND u.staff_id IS NULL
                      ORDER BY s.first_name, s.last_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo personal sin usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todo el personal sin usuario asignado (para RRHH)
     * @return array
     */
    public function getAllStaffWithoutUser() {
        try {
            $query = "SELECT s.*, d.name as department_name, jp.name as job_position_name,
                             ad.name as academic_degree_name, asp.name as academic_specialization_name
                      FROM staff s
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      LEFT JOIN academic_degrees ad ON s.academic_degree_id = ad.id
                      LEFT JOIN academic_specializations asp ON s.academic_specialization_id = asp.id
                      LEFT JOIN users u ON s.id = u.staff_id
                      WHERE s.status = 'active'
                      AND u.staff_id IS NULL
                      ORDER BY d.name, s.first_name, s.last_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo todo el personal sin usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear usuario para un staff específico
     * @param int $staff_id
     * @param string $username
     * @param string $password
     * @param string $email
     * @param array $department_roles Array format [ dept_id => role_id ]
     * @return array
     */
    public function createUserForStaff($staff_id, $username, $password, $email, $department_roles = []) {
        try {
            // Verificar que el staff existe y no tiene usuario
            $staff_query = "SELECT s.*, u.staff_id as existing_user 
                           FROM staff s 
                           LEFT JOIN users u ON s.id = u.staff_id 
                           WHERE s.id = :staff_id AND s.status = 'active'";
            
            $stmt = $this->conn->prepare($staff_query);
            $stmt->bindParam(':staff_id', $staff_id);
            $stmt->execute();
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$staff) {
                return ['success' => false, 'message' => 'Personal no encontrado o inactivo'];
            }
            
            if ($staff['existing_user']) {
                return ['success' => false, 'message' => 'Este personal ya tiene un usuario asignado'];
            }
            
            // Verificar que el username no exista
            $username_check = "SELECT id FROM users WHERE username = :username";
            $stmt = $this->conn->prepare($username_check);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'El nombre de usuario ya existe'];
            }
            
            // Verificar que el email no exista
            $email_check = "SELECT id FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($email_check);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'El email ya está en uso'];
            }
            
            // Crear el usuario
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO users (staff_id, username, password_hash, email, status, created_at) 
                            VALUES (:staff_id, :username, :password_hash, :email, 'active', NOW())";
            
            $stmt = $this->conn->prepare($insert_query);
            $stmt->bindParam(':staff_id', $staff_id);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                
                // Asignar los departamentos y roles seleccionados
                if (!empty($department_roles)) {
                    $dept_query = "INSERT INTO user_departments (user_id, department_id, role_id, status, created_at) 
                                   VALUES (:user_id, :department_id, :role_id, 'active', NOW())";
                    $stmt = $this->conn->prepare($dept_query);
                    foreach ($department_roles as $dept_id => $r_id) {
                        if (empty($r_id)) continue;
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->bindParam(':department_id', $dept_id);
                        $stmt->bindParam(':role_id', $r_id, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                } else {
                    // Fallback to staff department if nothing selected (should be blocked by validation but safe is better)
                    $dept_query = "INSERT INTO user_departments (user_id, department_id, status, created_at) 
                                  VALUES (:user_id, :department_id, 'active', NOW())";
                    $stmt = $this->conn->prepare($dept_query);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':department_id', $staff['department_id']);
                    $stmt->execute();
                }
                
                return [
                    'success' => true, 
                    'message' => 'Usuario creado exitosamente',
                    'user_id' => $user_id
                ];
            } else {
                return ['success' => false, 'message' => 'Error al crear el usuario'];
            }
            
        } catch (PDOException $e) {
            error_log("Error creando usuario para staff: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    /**
     * Desactivar usuario (no eliminar)
     * @param int $user_id
     * @return bool
     */
    public function deactivateUser($user_id) {
        try {
            $query = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error desactivando usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reactivar usuario
     * @param int $user_id
     * @return bool
     */
    public function reactivateUser($user_id) {
        try {
            $query = "UPDATE users SET status = 'active', updated_at = NOW() WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error reactivando usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener información completa del usuario con datos del personal
     * @param int $user_id
     * @return array|false
     */
    public function getUserWithStaffDetails($user_id) {
        try {
            // Primero verificar que el usuario existe
            $user_check = "SELECT * FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($user_check);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $basic_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$basic_user) {
                error_log("getUserWithStaffDetails: Usuario $user_id no existe");
                return false; // Usuario no existe
            }
            
            // Si el usuario no tiene staff_id, retornar solo datos del usuario
            if (!$basic_user['staff_id']) {
                error_log("getUserWithStaffDetails: Usuario $user_id sin staff_id, retornando datos básicos");
                try {
                    $basic_user['departments'] = $this->getUserDepartments($basic_user['id']);
                    $basic_user['activity_log'] = $this->getUserActivityLog($basic_user['id']);
                } catch (Exception $e) {
                    error_log("getUserWithStaffDetails: Error en getUserDepartments/getUserActivityLog: " . $e->getMessage());
                    // Continuar sin estos datos adicionales
                }
                return $basic_user;
            }
            
            // Si tiene staff_id, intentar obtener datos completos con consulta completa
            $query = "SELECT u.*, s.first_name, s.last_name, s.id_number, s.department_id,
                             d.name as department_name, jp.name as job_position_name,
                             ad.name as academic_degree_name, asp.name as academic_specialization_name
                      FROM users u
                      LEFT JOIN staff s ON u.staff_id = s.id
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      LEFT JOIN academic_degrees ad ON s.academic_degree_id = ad.id
                      LEFT JOIN academic_specializations asp ON s.academic_specialization_id = asp.id
                      WHERE u.id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si la consulta completa falla, usar datos básicos
            if (!$user) {
                error_log("getUserWithStaffDetails: Consulta completa falló para usuario $user_id con staff_id " . $basic_user['staff_id']);
                try {
                    $basic_user['departments'] = $this->getUserDepartments($basic_user['id']);
                    $basic_user['activity_log'] = $this->getUserActivityLog($basic_user['id']);
                } catch (Exception $e) {
                    error_log("getUserWithStaffDetails: Error en métodos adicionales: " . $e->getMessage());
                }
                return $basic_user;
            }
            
            // Si todo está bien, agregar datos adicionales
            error_log("getUserWithStaffDetails: Éxito para usuario $user_id");
            try {
                $user['departments'] = $this->getUserDepartments($user['id']);
                $user['activity_log'] = $this->getUserActivityLog($user['id']);
            } catch (Exception $e) {
                error_log("getUserWithStaffDetails: Error en métodos adicionales (caso exitoso): " . $e->getMessage());
                // Continuar sin estos datos adicionales
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("getUserWithStaffDetails: Error PDO para usuario $user_id: " . $e->getMessage());
            return false;
        }
    }



    /**
     * Obtener historial de actividad del usuario
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function getUserActivityLog($user_id, $limit = 50) {
        try {
            $query = "SELECT * FROM audit_log 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo historial de actividad: " . $e->getMessage());
            return [];
        }
    }


        /**
     * Genera un token de recuperación y lo guarda en la base de datos.
     * @param int $user_id
     * @return string|false
     */
    public function generatePasswordResetToken($user_id) {
        try {
            $token = bin2hex(random_bytes(32)); // Genera un token aleatorio de 64 caracteres
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expira en 1 hora

            $query = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expires_at);

            if ($stmt->execute()) {
                return $token;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error generando token de recuperación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el ID de usuario a partir de un token de recuperación válido.
     * @param string $token
     * @return int|false
     */
    public function getUserIdByResetToken($token) {
        try {
            $query = "SELECT id FROM users WHERE password_reset_token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (int) $result['id'] : false;
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario por token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un token de recuperación después de ser usado.
     * @param string $token
     * @return bool
     */
    public function deleteResetToken($token) {
        try {
            $query = "DELETE FROM password_resets WHERE token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminando token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza la contraseña del usuario.
     * @param int $user_id
     * @param string $new_password
     * @return bool
     */
    public function updatePassword($user_id, $new_password) {
        try {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando contraseña: " . $e->getMessage());
            return false;
        }
    }


    
    /**
     * Obtiene una lista de usuarios que tienen un rol específico por nombre.
     * @param string $roleName Nombre del rol (ej: 'Cobranzas', 'administrador').
     * @return array Lista de IDs de usuarios.
     */
    public function getUsersByRoleName(string $roleName): array {
        $query = "
            SELECT 
                u.id
            FROM 
                " . $this->table . " u
            INNER JOIN 
                fiscalization_user_level fule ON u.id = fule.user_id
            INNER JOIN 
                fiscalization_roles fr ON fule.role_id = fr.role_id
            WHERE 
                fr.role_name = :role_name;
        ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':role_name', $roleName, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios para el rol {$roleName}: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Obtiene una lista de usuarios que pertenecen a un departamento específico.
     * @param int $departmentId
     * @return array IDs de usuarios
     */
    public function getUsersByDepartmentId(int $departmentId): array {
        $query = "
            SELECT user_id 
            FROM user_departments 
            WHERE department_id = :dept_id 
            AND status = 'active'
        ";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':dept_id', $departmentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios por departamento {$departmentId}: " . $e->getMessage());
            return [];
        }
    }
}
?>
