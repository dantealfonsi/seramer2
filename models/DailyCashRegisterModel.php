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
        
        $params['main_date'] = $dateFrom;
        $params['sub_from'] = $dateFrom;
        $params['sub_to'] = $dateTo;

        // Subquery to get totals, max and the exact time of the first payment
        $paymentsSubquery = "
            SELECT register_id, 
                   p_date,
                   SUM(amount_paid) as total,
                   MAX(amount_paid) as max_amt,
                   MIN(p_time) as first_time
            FROM (
                SELECT daily_cash_register_id as register_id, payment_date as p_time, DATE(payment_date) as p_date, amount_paid FROM fee_payments
                UNION ALL
                SELECT daily_cash_register_id as register_id, payment_date as p_time, DATE(payment_date) as p_date, amount_paid FROM fine_payments
            ) all_p
            WHERE p_date BETWEEN :sub_from AND :sub_to
            GROUP BY register_id, p_date
        ";

        $query = "SELECT 
                    cr.id as register_id,
                    cr.name as register_name,
                    cr.status as register_status,
                    u.username,
                    CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) as staff_name,
                    COALESCE(p.p_date, :main_date) as open_date,
                    COALESCE(p.max_amt, 0) as max_amount,
                    COALESCE(p.total, 0) as total_collected,
                    -- Get the amount of the first payment using a correlated subquery
                    COALESCE((
                        SELECT amount_paid FROM (
                            SELECT daily_cash_register_id, payment_date, amount_paid FROM fee_payments
                            UNION ALL
                            SELECT daily_cash_register_id, payment_date, amount_paid FROM fine_payments
                        ) t2 
                        WHERE t2.daily_cash_register_id = cr.id 
                        AND t2.payment_date = p.first_time 
                        LIMIT 1
                    ), 0) as initial_amount
                  FROM cash_registers cr
                  LEFT JOIN users u ON cr.user_id = u.id
                  LEFT JOIN staff s ON u.staff_id = s.id
                  LEFT JOIN ($paymentsSubquery) p ON p.register_id = cr.id
                  WHERE 1=1";

        if (!empty($filters['status'])) {
            $query .= " AND cr.status = :status";
            $params['status'] = $filters['status'];
        }

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
