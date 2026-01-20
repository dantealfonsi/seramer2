<?php
/**
 * Modelo Awardee
 * 
 * Gestiona los adjudicatarios del mercado
 * Implementa RF07: Registro de Adjudicatarios
 * Implementa RF11: Consulta de Adjudicatario por Cédula
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;

class AwardeeModel extends Model {
    protected string $table = 'awardees';
    
    /**
     * Obtiene todos los adjudicatarios
     * 
     * @return array Lista de adjudicatarios
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY last_name, first_name";
        return $this->query($query);
    }
    
    /**
     * Obtiene un adjudicatario por ID
     * 
     * @param int $id ID del adjudicatario
     * @return array|null Adjudicatario o null
     */
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    
    /**
     * Busca un adjudicatario por cédula (RF11)
     * 
     * @param string $idNumber Número de cédula
     * @return array|null Adjudicatario o null
     */
    public function getByIdNumber(string $idNumber): ?array {
        $query = "SELECT * FROM {$this->table} WHERE id_number = :id_number LIMIT 1";
        return $this->queryOne($query, ['id_number' => $idNumber]);
    }
    
    /**
     * Busca adjudicatarios por nombre
     * 
     * @param string $search Término de búsqueda
     * @return array Lista de adjudicatarios encontrados
     */
    public function search(string $search): array {
        $query = "SELECT * FROM {$this->table} 
                  WHERE CONCAT(first_name, ' ', last_name) LIKE :search
                  OR id_number LIKE :search
                  ORDER BY last_name, first_name";
        
        return $this->query($query, ['search' => "%{$search}%"]);
    }
    
    /**
     * Crea un nuevo adjudicatario (RF07)
     * 
     * @param array $data Datos del adjudicatario
     * @return int|false ID del adjudicatario creado o false
     */
    public function create(array $data): int|false {
        // Verificar que la cédula no exista
        if ($this->idNumberExists($data['id_number'])) {
            return false;
        }
        
        $query = "INSERT INTO {$this->table} 
                  (first_name, middle_name, last_name, second_last_name, id_number, phone, email, address) 
                  VALUES 
                  (:first_name, :middle_name, :last_name, :second_last_name, :id_number, :phone, :email, :address)";
        
        $success = $this->execute($query, [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'second_last_name' => $data['second_last_name'] ?? null,
            'id_number' => $data['id_number'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null
        ]);
        
        if ($success) {
            $id = $this->lastInsertId();
            Audit::logInsert('awardees', $id, $data);
            return $id;
        }
        
        return false;
    }
    
    /**
     * Actualiza un adjudicatario (RF07)
     * 
     * @param int $id ID del adjudicatario
     * @param array $data Datos a actualizar
     * @return bool True si tuvo éxito
     */
    public function update(int $id, array $data): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        // Verificar que la cédula no exista (excepto la del adjudicatario actual)
        if (isset($data['id_number']) && $this->idNumberExists($data['id_number'], $id)) {
            return false;
        }
        
        $query = "UPDATE {$this->table} 
                  SET first_name = :first_name,
                      middle_name = :middle_name,
                      last_name = :last_name,
                      second_last_name = :second_last_name,
                      id_number = :id_number,
                      phone = :phone,
                      email = :email,
                      address = :address
                  WHERE id = :id";
        
        $success = $this->execute($query, [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'second_last_name' => $data['second_last_name'] ?? null,
            'id_number' => $data['id_number'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'id' => $id
        ]);
        
        if ($success) {
            Audit::logUpdate('awardees', $id, $old, $data);
        }
        
        return $success;
    }
    
    /**
     * Verifica si un adjudicatario puede ser eliminado
     * 
     * @param int $id ID del adjudicatario
     * @return array ['can_delete' => bool, 'relations' => array, 'message' => string]
     */
    public function canDeleteAwardee(int $id): array {
        $relations = [
            'contracts' => 'awardee_id'
        ];
        
        return $this->canDelete($id, $relations);
    }
    
    /**
     * Elimina un adjudicatario
     * 
     * @param int $id ID del adjudicatario
     * @return bool True si tuvo éxito
     */
    public function deleteAwardee(int $id): bool {
        $old = $this->getById($id);
        
        if (!$old) {
            return false;
        }
        
        // Verificar que no tenga relaciones
        $validation = $this->canDeleteAwardee($id);
        if (!$validation['can_delete']) {
            return false;
        }
        
        $success = $this->delete($id);
        
        if ($success) {
            Audit::logDelete('awardees', $id, $old);
        }
        
        return $success;
    }
    
    /**
     * Verifica si una cédula ya existe
     * 
     * @param string $idNumber Número de cédula
     * @param int|null $excludeId ID a excluir de la búsqueda
     * @return bool True si existe
     */
    public function idNumberExists(string $idNumber, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE id_number = :id_number";
        $params = ['id_number' => $idNumber];
        
        if ($excludeId !== null) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->queryOne($query, $params);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Obtiene el nombre completo del adjudicatario
     * 
     * @param array $awardee Datos del adjudicatario
     * @return string Nombre completo
     */
    public static function getFullName(array $awardee): string {
        $parts = [
            $awardee['first_name'],
            $awardee['middle_name'] ?? '',
            $awardee['last_name'],
            $awardee['second_last_name'] ?? ''
        ];
        
        return trim(implode(' ', array_filter($parts)));
    }
}

