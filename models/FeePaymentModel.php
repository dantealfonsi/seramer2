<?php
require_once __DIR__ . '/Model.php';

class FeePaymentModel extends Model {
    protected $table = 'fee_payments';
    
    public function create(array $data) {
        $query = "INSERT INTO {$this->table} 
                  (contract_id, period_month, payment_date, amount_paid, payment_type, payment_status, transaction_reference, payment_method_id, daily_cash_register_id) 
                  VALUES 
                  (:contract_id, :period_month, :payment_date, :amount_paid, :payment_type, :payment_status, :transaction_reference, :payment_method_id, :daily_cash_register_id)";
        
        $success = $this->execute($query, [
            'contract_id' => $data['contract_id'],
            'period_month' => $data['period_month'] ?? date('Y-m-01'),
            'payment_date' => $data['payment_date'] ?? date('Y-m-d H:i:s'),
            'amount_paid' => $data['amount_paid'],
            'payment_type' => $data['payment_type'], // Payment method name
            'payment_status' => $data['payment_status'] ?? 'Paid',
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'payment_method_id' => $data['payment_method_id'] ?? null,
            'daily_cash_register_id' => $data['daily_cash_register_id'] ?? null
        ]);
        
        return $success ? $this->lastInsertId() : false;
    }
}
