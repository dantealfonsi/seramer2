<?php
require_once __DIR__ . '/../config/Database.php';
class UsersFiscModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
        
    public function getAllRolesWithPermissions(): array {        
        $query = "SELECT role_id, role_name, description, permissions_mask FROM fiscalization_roles ORDER BY role_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener roles: " . $e->getMessage());
            return [];
        }
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
            
            // Retorna la máscara o null
            return $result['permissions_mask'] ?? null;
            
        } catch (PDOException $e) {
            error_log("Error al obtener máscara de permisos para el usuario ID {$userId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza la máscara de permisos de un rol.
     */
    public function updateRolePermissions(int $roleId, string $newPermissionsMask): bool {
        $sql = "
            UPDATE fiscalization_roles
            SET permissions_mask = :mask
            WHERE role_id = :role_id;
        ";
        
        // Asegurar que la máscara tenga el formato esperado (ej. 9 caracteres)
        if (strlen($newPermissionsMask) > 10 || !preg_match('/^([rwx-]{3}){1,3}$/', $newPermissionsMask)) {
            error_log("Máscara de permisos inválida: " . $newPermissionsMask);
            return false;
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':mask', $newPermissionsMask, PDO::PARAM_STR);
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar la máscara de permisos: " . $e->getMessage());
            return false;
        }
    }    

    public function createTablesUsersFisc(): void {        
        $sqlRoles = "
        CREATE TABLE IF NOT EXISTS `fiscalization_roles` (
            `role_id` INT(11) NOT NULL AUTO_INCREMENT,
            `role_name` VARCHAR(50) NOT NULL UNIQUE COMMENT 'administrador, oficina, inspector',
            `description` VARCHAR(255) NULL,
            `permissions_mask` VARCHAR(10) NOT NULL COMMENT 'Cadena de permisos rwx (ej: rwx-r--)',
            PRIMARY KEY (`role_id`)
        )
        COLLATE='utf8mb4_general_ci'
        ENGINE=InnoDB;";

        // 💡 SOLUCIÓN: Usar INSERT IGNORE
        // Esto ignorará la inserción si el valor de role_name (que es UNIQUE) ya existe.
        $insertRoles = "
            INSERT IGNORE INTO `fiscalization_roles` (`role_name`, `description`, `permissions_mask`) VALUES
            ('administrador', 'Acceso total y gestión de usuarios.', 'rwx-rwx-rwx'),
            ('oficina', 'Gestión de reportes, sin poder de modificación de tasas/config.', 'rwx-rw--r--'),
            ('inspector', 'Solo reportar infracciones y ver sus propios casos.', 'rw-------');
        ";

        $sqlFiscalizationUsers = "
        CREATE TABLE IF NOT EXISTS `fiscalization_user_level`  (
            `user_level_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL UNIQUE,  -- La restricción UNIQUE aquí crea el índice
            `role_id` INT(11) NOT NULL,
            `assigned_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`user_level_id`),
            INDEX `role_id` (`role_id`),
            CONSTRAINT `fk_fisc_user_level_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT `fk_fisc_user_level_role` FOREIGN KEY (`role_id`) REFERENCES `fiscalization_roles` (`role_id`) ON UPDATE CASCADE ON DELETE RESTRICT
        )
        COLLATE='utf8mb4_general_ci'
        ENGINE=InnoDB;
        ";
        
        try {
            // Creación de la tabla de Roles
            $this->conn->exec($sqlRoles);
            
            // Inserción de Roles (solo si no existen)
            $this->conn->exec($insertRoles);
            
            // Creación de la tabla de Asignación de Nivel
            $this->conn->exec($sqlFiscalizationUsers);
            
        } catch (PDOException $e) {
            error_log("FATAL ERROR: No se pudo inicializar la estructura de Fiscalización: " . $e->getMessage());
            die("Error crítico al inicializar la base de datos.");
        }
    }

    /**
     * Inserta o ignora los usuarios del departamento de Fiscalización 
     * en la tabla de niveles, asignándoles el rol 'oficina' por defecto.
     * * @param int $fiscalizationDepartmentId ID del departamento a filtrar (3).
     * @param string $defaultRoleName Nombre del rol por defecto ('oficina').
     * @return int El número de filas insertadas o afectadas.
     */
    public function migrateFiscalizationUsers(int $fiscalizationDepartmentId = 3, string $defaultRoleName = 'oficina'): int
    {
        // Usamos INSERT IGNORE INTO ... SELECT para:
        // 1. Obtener el role_id de 'oficina' mediante una subconsulta.
        // 2. Obtener todos los user_id de la tabla users que están asociados a staff.department_id = 3.
        // 3. Insertar la combinación (user_id, role_id) en fiscalization_user_level, 
        //    ignorando cualquier user_id que ya exista (gracias a la restricción UNIQUE).

        $sql = "
            INSERT IGNORE INTO fiscalization_user_level (user_id, role_id)
            SELECT
                u.id AS user_id,
                (
                    SELECT role_id 
                    FROM fiscalization_roles 
                    WHERE role_name = :role_name
                ) AS role_id
            FROM
                users u
            INNER JOIN
                staff s ON u.staff_id = s.id
            WHERE
                s.department_id = :department_id;
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            
            // Asignación de parámetros
            $stmt->bindParam(':role_name', $defaultRoleName, PDO::PARAM_STR);
            $stmt->bindParam(':department_id', $fiscalizationDepartmentId, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // Retorna el número de filas afectadas/insertadas
            return $stmt->rowCount(); 
            
        } catch (PDOException $e) {
            error_log("Error al migrar usuarios de fiscalización: " . $e->getMessage());
            return 0;
        }
    }    

    public function getAllFiscalizationRoles(): array {
        $query = "SELECT role_id, role_name FROM fiscalization_roles ORDER BY role_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
    }  
    
    public function updateUserFiscalizationRole(int $userId, int $roleId): bool {
        // Usamos INSERT ... ON DUPLICATE KEY UPDATE.
        // Si el user_id ya existe, actualiza el role_id. Si no existe, lo inserta.
        $sql = "
            INSERT INTO fiscalization_user_level (user_id, role_id)
            VALUES (:user_id, :role_id)
            ON DUPLICATE KEY UPDATE
                role_id = VALUES(role_id),
                assigned_at = CURRENT_TIMESTAMP;
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar rol de usuario ID {$userId}: " . $e->getMessage());
            return false;
        }
    }    

    /**
     * Obtiene una lista de usuarios que pertenecen al departamento de Fiscalización (ID 3).
     *
     * @return array La lista de usuarios o un array vacío en caso de error o no haber resultados.
     */
    public function getUsersByFiscalizationDepartment(): array {
        // ID del departamento de Fiscalización
        $fiscalization_department_id = 3; 
        
        // La consulta une users con staff y filtra por el department_id
        $query = "
                SELECT
                    u.id AS user_id,
                    u.username,
                    u.email,
                    u.status AS user_status,
                    s.id_number,
                    s.first_name,
                    s.last_name,
                    fule.role_id,                       /* ID del rol asignado */
                    fr.role_name                        /* Nombre del rol asignado */
                FROM
                    users u
                INNER JOIN 
                    staff s ON u.staff_id = s.id
                LEFT JOIN
                    fiscalization_user_level fule ON u.id = fule.user_id /* LEFT JOIN para el rol */
                LEFT JOIN
                    fiscalization_roles fr ON fule.role_id = fr.role_id
                WHERE
                    s.department_id = :department_id
                ORDER BY 
                    s.last_name, s.first_name
            ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':department_id', $fiscalization_department_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en getUsersByFiscalizationDepartment: " . $e->getMessage());
            return []; // Devolver array vacío en caso de error
        }
    }
}