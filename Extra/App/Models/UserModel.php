<?php
/**
 * Modelo User
 * 
 * Gestiona los usuarios del sistema
 * Implementa autenticación y seguridad
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Security;
use Core\Audit;

class UserModel extends Model {
    protected string $table = 'users';
    
    /**
     * Obtiene un usuario por username
     * 
     * @param string $username Nombre de usuario
     * @return array|null Usuario o null
     */
    public function getByUsername(string $username): ?array {
        $query = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        return $this->queryOne($query, ['username' => $username]);
    }
    
    /**
     * Obtiene un usuario por ID
     * 
     * @param int $id ID del usuario
     * @return array|null Usuario o null
     */
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    /**
     * Autentica un usuario
     * 
     * @param string $username Nombre de usuario
     * @param string $password Contraseña
     * @return array|false Datos del usuario o false
     */
    public function authenticate(string $username, string $password): array|false {
        $user = $this->getByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        // Verificar contraseña
        if (!Security::verifyPassword($password, $user['password_hash'])) {
            return false;
        }
        
        // Actualizar último login
        $this->execute(
            "UPDATE {$this->table} SET last_login = NOW() WHERE id = :id",
            ['id' => $user['id']]
        );
        
        // Registrar login en auditoría
        Audit::logLogin($user['id']);
        
        // No retornar el hash de contraseña
        unset($user['password_hash']);
        
        return $user;
    }
    
    /**
     * Crea un nuevo usuario
     * 
     * @param array $data Datos del usuario
     * @return int|false ID del usuario creado o false
     */
    public function create(array $data): int|false {
        // Verificar que el username no exista
        if ($this->usernameExists($data['username'])) {
            return false;
        }
        
        // Hash de la contraseña
        $passwordHash = Security::hashPassword($data['password']);
        
        $query = "INSERT INTO {$this->table} 
                  (staff_id, username, password_hash, email, status) 
                  VALUES 
                  (:staff_id, :username, :password_hash, :email, :status)";
        
        $success = $this->execute($query, [
            'staff_id' => $data['staff_id'] ?? null,
            'username' => $data['username'],
            'password_hash' => $passwordHash,
            'email' => $data['email'],
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            $dataToLog = $data;
            unset($dataToLog['password']); // No registrar la contraseña en auditoría
            Audit::logInsert('users', $id, $dataToLog);
            return $id;
        }
        
        return false;
    }
    
    /**
     * Actualiza la contraseña de un usuario
     * 
     * @param int $id ID del usuario
     * @param string $newPassword Nueva contraseña
     * @return bool True si tuvo éxito
     */
    public function updatePassword(int $id, string $newPassword): bool {
        $passwordHash = Security::hashPassword($newPassword);
        
        $query = "UPDATE {$this->table} SET password_hash = :password_hash WHERE id = :id";
        
        return $this->execute($query, [
            'password_hash' => $passwordHash,
            'id' => $id
        ]);
    }
    
    /**
     * Obtiene todos los usuarios
     * 
     * @return array Lista de usuarios
     */
    public function getAll(): array {
        $query = "SELECT u.*, s.first_name as staff_first_name, s.last_name as staff_last_name
                  FROM {$this->table} u
                  LEFT JOIN staff s ON u.staff_id = s.id
                  ORDER BY u.id DESC";
        return $this->query($query);
    }
    
    /**
     * Actualiza un usuario
     * 
     * @param int $id ID del usuario
     * @param array $data Datos a actualizar
     * @return bool True si tuvo éxito
     */
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        $query = "UPDATE {$this->table} 
                  SET staff_id = :staff_id,
                      username = :username,
                      email = :email,
                      status = :status
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'staff_id' => $data['staff_id'] ?? null,
            'username' => $data['username'],
            'email' => $data['email'],
            'status' => $data['status'] ?? 'active',
            'id' => $id
        ]);
        
        if ($success) {
            $dataToLog = $data;
            unset($dataToLog['password']); // No registrar la contraseña
            Audit::logUpdate('users', $id, $old, $dataToLog);
        }
        
        return $success;
    }
    
    /**
     * Elimina un usuario
     * 
     * @param int $id ID del usuario
     * @return bool True si tuvo éxito
     */
    public function deleteUser(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        // No se puede eliminar el usuario actual
        $currentUser = \Core\Session::get('user');
        if ($currentUser && $currentUser['id'] == $id) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('users', $id, $old);
        }
        
        return $success;
    }
    
    /**
     * Verifica si un username ya existe
     * 
     * @param string $username Nombre de usuario
     * @param int|null $excludeId ID a excluir de la búsqueda
     * @return bool True si existe
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = :username";
        $params = ['username' => $username];
        
        if ($excludeId !== null) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($query, $params);
        return ($result['count'] ?? 0) > 0;
    }
}

