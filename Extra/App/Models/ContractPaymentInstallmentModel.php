<?php
/**
 * Modelo ContractPaymentInstallment
 * 
 * Gestiona los abonos o pagos parciales de contratos
 * Implementa RF17: Manejo de Abonos (Pagos Parciales)
 * Implementa RF18: Cierre y Estatus de Pago Total
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;
use Core\Audit;
use Core\Session;

class ContractPaymentInstallmentModel extends Model {
    protected string $table = 'contract_payment_installments';
    
    /**
     * Obtiene todos los abonos de un pago
     * 
     * @param int $contractPaymentId ID del pago
     * @return array Lista de abonos
     */
    public function getByPayment(int $contractPaymentId): array {
        $query = "SELECT cpi.*, pm.name as payment_method_name
                  FROM {$this->table} cpi
                  LEFT JOIN payment_methods pm ON cpi.payment_method_id = pm.id
                  WHERE cpi.contract_payment_id = :contract_payment_id
                  ORDER BY cpi.date DESC";
        
        return $this->query($query, ['contract_payment_id' => $contractPaymentId]);
    }
    
    /**
     * Registra un abono/pago (RF16 + RF17 + RF18)
     * 
     * Registra un pago parcial o total y actualiza el estatus del payment si corresponde
     * 
     * @param array $data Datos del abono
     * @return int|false ID del abono creado o false
     */
    public function create(array $data): int|false {
        try {
            $this->beginTransaction();
            
            // Validar que daily_cash_register_id esté presente (obligatorio para trazabilidad)
            if (empty($data['daily_cash_register_id'])) {
                error_log("Error: daily_cash_register_id es requerido para registrar un abono");
                $this->rollback();
                return false;
            }
            
            // Insertar el abono
            $query = "INSERT INTO {$this->table} 
                      (contract_payment_id, payment_method_id, date, amount, concept, daily_cash_register_id) 
                      VALUES 
                      (:contract_payment_id, :payment_method_id, :date, :amount, :concept, :daily_cash_register_id)";
            
            $success = $this->execute($query, [
                'contract_payment_id' => $data['contract_payment_id'],
                'payment_method_id' => $data['payment_method_id'],
                'date' => $data['date'] ?? date('Y-m-d'),
                'amount' => $data['amount'],
                'concept' => $data['concept'] ?? 'Pago de mensualidad',
                'daily_cash_register_id' => $data['daily_cash_register_id']
            ]);
            
            if (!$success) {
                $this->rollback();
                return false;
            }
            
            $installmentId = $this->lastInsertId();
            
            // Registrar en auditoría
            Audit::logInsert('contract_payment_installments', $installmentId, $data);
            
            // Verificar si el pago está completo (RF18)
            $this->checkAndUpdatePaymentStatus($data['contract_payment_id']);
            
            $this->commit();
            return $installmentId;
            
        } catch (\PDOException $e) {
            $this->rollback();
            error_log("Error al crear abono: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica y actualiza el estatus del pago si está completo (RF18)
     * 
     * Usa amount_bs (monto en bolívares) para comparar con los abonos que están en Bs
     * 
     * @param int $contractPaymentId ID del pago
     * @return bool True si se actualizó el estatus
     */
    private function checkAndUpdatePaymentStatus(int $contractPaymentId): bool {
        // Obtener el monto total del pago en Bs (amount_bs)
        // Los abonos se registran en Bs, por lo que debemos comparar con amount_bs
        $paymentModel = new \App\Models\ContractPaymentModel();
        $paymentWithRate = $paymentModel->getPaymentWithRateInfo($contractPaymentId);
        
        if (!$paymentWithRate || empty($paymentWithRate['amount_bs'])) {
            return false;
        }
        
        $totalAmountBs = (float) $paymentWithRate['amount_bs'];
        
        // Calcular la suma de los abonos (que están en Bs)
        $installmentsQuery = "SELECT SUM(amount) as total_paid 
                              FROM {$this->table} 
                              WHERE contract_payment_id = :contract_payment_id";
        
        $result = $this->queryOne($installmentsQuery, ['contract_payment_id' => $contractPaymentId]);
        $totalPaid = (float) ($result['total_paid'] ?? 0);
        
        // Si la suma de los abonos es igual o mayor al monto total en Bs, marcar como pagado (RF18)
        // Usamos <= 0.01 para manejar diferencias por redondeo
        if ($totalPaid >= ($totalAmountBs - 0.01)) {
            $updateQuery = "UPDATE contract_payments 
                            SET status = 'paid' 
                            WHERE id = :id";
            
            return $this->execute($updateQuery, ['id' => $contractPaymentId]);
        }
        
        return true;
    }
    
    /**
     * Obtiene el total pagado de un payment
     * 
     * @param int $contractPaymentId ID del pago
     * @return float Total pagado
     */
    public function getTotalPaid(int $contractPaymentId): float {
        $query = "SELECT SUM(amount) as total_paid 
                  FROM {$this->table} 
                  WHERE contract_payment_id = :contract_payment_id";
        
        $result = $this->queryOne($query, ['contract_payment_id' => $contractPaymentId]);
        return (float) ($result['total_paid'] ?? 0);
    }
    
    /**
     * Obtiene el saldo restante de un pago (RF17)
     * 
     * Usa amount_bs (monto en bolívares) para calcular el saldo restante
     * ya que los abonos se registran en Bs
     * 
     * @param int $contractPaymentId ID del pago
     * @return float Saldo restante en Bs
     */
    public function getRemainingBalance(int $contractPaymentId): float {
        // Obtener el monto total del pago en Bs (amount_bs)
        $paymentModel = new \App\Models\ContractPaymentModel();
        $paymentWithRate = $paymentModel->getPaymentWithRateInfo($contractPaymentId);
        
        if (!$paymentWithRate || empty($paymentWithRate['amount_bs'])) {
            return 0;
        }
        
        $totalAmountBs = (float) $paymentWithRate['amount_bs'];
        $totalPaid = $this->getTotalPaid($contractPaymentId);
        
        return max(0, $totalAmountBs - $totalPaid);
    }
}

