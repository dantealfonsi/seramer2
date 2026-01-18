<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Audit.php';
require_once __DIR__ . '/CashRegisterModel.php';

class DailyCashRegisterModel extends Model {
    protected $table = 'cash_registers'; // Base table is now cash_registers
    
    /**
     * Get report data for Daily Cash view.
     * Groups activity by Cash Register and Date.
     */
    public function getDailyReport(array $filters = []): array {
        $params = [];
        $dateFrom = $filters['date_from'] ?? date('Y-m-d');
        $dateTo = $filters['date_to'] ?? date('Y-m-d');
        
        $params['date_from'] = $dateFrom;
        $params['date_to'] = $dateTo;

        // Subquery to union all payments and group them by register and date
        $paymentsSubquery = "
            SELECT daily_cash_register_id as register_id, 
                   DATE(payment_date) as p_date,
                   SUM(amount_paid) as total
            FROM (
                SELECT daily_cash_register_id, payment_date, amount_paid FROM fee_payments
                UNION ALL
                SELECT daily_cash_register_id, payment_date, amount_paid FROM fine_payments
            ) all_p
            WHERE DATE(payment_date) BETWEEN :date_from AND :date_to
            GROUP BY daily_cash_register_id, DATE(payment_date)
        ";

        $query = "SELECT 
                    cr.id as register_id,
                    cr.name as register_name,
                    cr.status as register_status,
                    u.username,
                    CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as staff_name,
                    COALESCE(p.p_date, :date_from) as open_date,
                    0 as initial_amount, -- No session table means no initial amount tracking
                    COALESCE(p.total, 0) as total_collected
                  FROM cash_registers cr
                  LEFT JOIN users u ON cr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  LEFT JOIN ($paymentsSubquery) p ON p.register_id = cr.id
                  WHERE 1=1";

        if (!empty($filters['status'])) {
            $query .= " AND cr.status = :status";
            $params['status'] = $filters['status'];
        }

        // If we have a date range, we might have multiple rows per register if they had activity on different days
        $query .= " ORDER BY open_date DESC, cr.name ASC";

        return $this->query($query, $params);
    }

    /**
     * Calculate total collected for a specific register and date.
     */
    public function getTotalByDate(int $registerId, string $date): float {
        $query = "
            SELECT SUM(amount_paid) as total
            FROM (
                SELECT amount_paid FROM fee_payments WHERE daily_cash_register_id = :rid AND DATE(payment_date) = :date
                UNION ALL
                SELECT amount_paid FROM fine_payments WHERE daily_cash_register_id = :rid AND DATE(payment_date) = :date
            ) all_p
        ";
        $res = $this->queryOne($query, ['rid' => $registerId, 'date' => $date]);
        return (float)($res['total'] ?? 0);
    }

    // Helper to check if a register has any payments at all
    public function hasOpenings(int $cashRegisterId): bool {
        $query = "SELECT (
            (SELECT COUNT(*) FROM fee_payments WHERE daily_cash_register_id = :id) +
            (SELECT COUNT(*) FROM fine_payments WHERE daily_cash_register_id = :id)
        ) as count";
        $res = $this->queryOne($query, ['id' => $cashRegisterId]);
        return (int)($res['count'] ?? 0) > 0;
    }

    // The following methods are kept as stubs to prevent crashes but they do nothing 
    // since the user removed the daily_cash_registers table.
    public function openCash($cashRegisterId, $userId, $initialAmount) { return true; }
    public function closeCash($dailyCashId, $finalAmount) { return true; }
    public function isCashOpen($cashRegisterId, $date = null) { return true; }
    public function getOpenCashByRegister($cashRegisterId) { return null; }
    public function getCashHistory($cashRegisterId, $limit = 30) { return []; }
    public function getById($id) { return null; }
}
