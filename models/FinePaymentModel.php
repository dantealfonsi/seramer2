<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/SanctionsModel.php';

/**
 * Model for managing fine payments (multas/infractions)
 */
class FinePaymentModel extends Model {
    protected $table = 'fine_payments';
    
    /**
     * Create a new fine payment and update sanction status.
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            $this->beginTransaction();
            
            // Insert payment record
            $query = "INSERT INTO {$this->table} 
                      (sanction_id, payment_date, amount_paid, transaction_reference, payment_status, payment_method_id, daily_cash_register_id, payment_type)
                      VALUES 
                      (:sanction_id, :payment_date, :amount_paid, :transaction_reference, :payment_status, :payment_method_id, :daily_cash_register_id, :payment_type)";
            
            $success = $this->execute($query, [
                'sanction_id' => $data['sanction_id'],
                'payment_date' => $data['payment_date'] ?? date('Y-m-d H:i:s'),
                'amount_paid' => $data['amount_paid'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'payment_status' => 'Paid',
                'payment_method_id' => $data['payment_method_id'],
                'daily_cash_register_id' => $data['daily_cash_register_id'] ?? null,
                'payment_type' => $data['payment_type'] ?? 'General'
            ]);
            
            if (!$success) {
                $this->rollback();
                return ['success' => false, 'message' => 'Error al registrar el pago'];
            }
            
            $paymentId = $this->lastInsertId();
            
            // Update sanction status to 'Paid'
            $sanctionsModel = new SanctionsModel();
            $statusUpdated = $sanctionsModel->updatePaymentStatus($data['sanction_id'], 'Paid');
            
            if (!$statusUpdated) {
                $this->rollback();
                return ['success' => false, 'message' => 'Error al actualizar el estado de la sanción'];
            }
            
            $this->commit();
            
            return [
                'success' => true,
                'message' => 'Pago registrado correctamente',
                'payment_id' => $paymentId
            ];
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error creating fine payment: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all payments for a specific sanction.
     * @param int $sanctionId
     * @return array
     */
    public function getBySanction($sanctionId) {
        $query = "SELECT fp.*, 
                         pm.name as payment_method_name
                  FROM {$this->table} fp
                  LEFT JOIN payment_methods pm ON fp.payment_method_id = pm.id
                  WHERE fp.sanction_id = :sanction_id
                  ORDER BY fp.payment_date DESC";
        
        return $this->query($query, ['sanction_id' => $sanctionId]);
    }
    
    /**
     * Get all fine payments for a specific awardee.
     * @param int $awardeeId
     * @return array
     */
    public function getByAwardee($awardeeId) {
        $query = "SELECT fp.*, 
                         s.sanction_id,
                         s.fine_amount,
                         i.infraction_description,
                         it.infraction_type_name,
                         pm.name as payment_method_name
                  FROM {$this->table} fp
                  JOIN sanctions s ON fp.sanction_id = s.sanction_id
                  JOIN infractions i ON s.infraction_id = i.infraction_id
                  JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                  LEFT JOIN payment_methods pm ON fp.payment_method_id = pm.id
                  WHERE i.awardee_id = :awardee_id
                  ORDER BY fp.payment_date DESC";
        
        return $this->query($query, ['awardee_id' => $awardeeId]);
    }
    
    /**
     * Get payment history with pagination and filters.
     * @param int $limit
     * @param array $filters
     * @return array
     */
    public function getPaymentHistory($limit = 50, $filters = []) {
        $query = "SELECT fp.*, 
                         s.sanction_id,
                         s.fine_amount,
                         s.fine_currency,
                         i.infraction_id,
                         i.infraction_description,
                         it.infraction_type_name,
                         a.first_name,
                         a.last_name,
                         a.id_number,
                         pm.name as payment_method_name
                  FROM {$this->table} fp
                  JOIN sanctions s ON fp.sanction_id = s.sanction_id
                  JOIN infractions i ON s.infraction_id = i.infraction_id
                  JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                  JOIN awardees a ON i.awardee_id = a.id
                  LEFT JOIN payment_methods pm ON fp.payment_method_id = pm.id
                  WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(fp.payment_date) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(fp.payment_date) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['payment_method_id'])) {
            $query .= " AND fp.payment_method_id = :payment_method_id";
            $params['payment_method_id'] = $filters['payment_method_id'];
        }
        
        $query .= " ORDER BY fp.payment_date DESC LIMIT :limit";
        $params['limit'] = $limit;
        
        return $this->query($query, $params);
    }
    
    /**
     * Get total amount collected in a date range.
     * @param array $dateRange ['start' => 'Y-m-d', 'end' => 'Y-m-d']
     * @return float
     */
    public function getTotalCollected($dateRange = []) {
        $query = "SELECT SUM(amount_paid) as total
                  FROM {$this->table}
                  WHERE payment_status = 'Paid'";
        
        $params = [];
        
        if (!empty($dateRange['start'])) {
            $query .= " AND DATE(payment_date) >= :start_date";
            $params['start_date'] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $query .= " AND DATE(payment_date) <= :end_date";
            $params['end_date'] = $dateRange['end'];
        }
        
        $result = $this->queryOne($query, $params);
        return (float)($result['total'] ?? 0);
    }
    
    /**
     * Get payment details by ID.
     * @param int $paymentId
     * @return array|null
     */
    public function getById($paymentId) {
        $query = "SELECT fp.*, 
                         s.sanction_id,
                         s.fine_amount,
                         s.fine_currency,
                         i.infraction_id,
                         i.infraction_description,
                         i.infraction_datetime,
                         it.infraction_type_name,
                         a.first_name,
                         a.last_name,
                         a.id_number,
                         a.phone,
                         a.email,
                         pm.name as payment_method_name
                  FROM {$this->table} fp
                  JOIN sanctions s ON fp.sanction_id = s.sanction_id
                  JOIN infractions i ON s.infraction_id = i.infraction_id
                  JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                  JOIN awardees a ON i.awardee_id = a.id
                  LEFT JOIN payment_methods pm ON fp.payment_method_id = pm.id
                  WHERE fp.payment_id = :payment_id
                  LIMIT 1";
        
        return $this->queryOne($query, ['payment_id' => $paymentId]);
    }
}
