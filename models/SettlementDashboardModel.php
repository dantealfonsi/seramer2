<?php
require_once __DIR__ . '/Model.php';

class SettlementDashboardModel extends Model {
    
    /**
     * Obtiene métricas de adjudicatarios
     */
    public function getAwardeeMetrics(): array {
        $query = "SELECT COUNT(*) as total FROM awardees";
        $result = $this->queryOne($query);
        return [
            'total' => (int) ($result['total'] ?? 0)
        ];
    }
    
    /**
     * Obtiene métricas de contratos
     */
    public function getContractMetrics(): array {
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active
                  FROM contracts";
        $result = $this->queryOne($query);
        return [
            'total' => (int) ($result['total'] ?? 0),
            'active' => (int) ($result['active'] ?? 0)
        ];
    }
    
    /**
     * Obtiene estadísticas de pagos del mes actual
     */
    public function getMonthlyStatistics(): array {
        $month = (int)date('m');
        $year = (int)date('Y');
        $monthYear = sprintf('%04d-%02d', $year, $month);
        
        $query = "SELECT 
                    COUNT(CASE WHEN cp.status = 'pending' THEN 1 END) as pending_payments
                  FROM contract_payments cp
                  INNER JOIN contracts c ON cp.contract_id = c.id
                  WHERE DATE_FORMAT(cp.payment_date, '%Y-%m') = :month_year
                    AND c.status != 'canceled'";
        
        $result = $this->queryOne($query, ['month_year' => $monthYear]);
        return [
            'pending_payments' => (int) ($result['pending_payments'] ?? 0)
        ];
    }
    
    /**
     * Obtiene el total recaudado en el mes actual
     */
    public function getTotalRevenue(string $startDate, string $endDate): float {
        $query = "
            SELECT SUM(cpi.amount) as total
            FROM contract_payment_installments cpi
            WHERE DATE(cpi.date) BETWEEN :start_date AND :end_date
        ";
        $result = $this->queryOne($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return (float) ($result['total'] ?? 0);
    }
    
    /**
     * Obtiene la cantidad de adjudicatarios por zona
     */
    public function getAwardeesPerZone(): array {
        $query = "
            SELECT 
                z.name as label,
                COUNT(DISTINCT c.awardee_id) as value
            FROM contracts c
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            WHERE c.status = 'active'
            GROUP BY z.id, z.name
            ORDER BY value DESC
        ";
        return $this->query($query);
    }
    
    /**
     * Obtiene los ingresos mensuales para un año específico
     */
    public function getMonthlyIncome(int $year): array {
        $query = "
            SELECT 
                MONTH(date) as month,
                SUM(amount) as total
            FROM contract_payment_installments
            WHERE YEAR(date) = :year
            GROUP BY MONTH(date)
            ORDER BY month ASC
        ";
        $results = $this->query($query, ['year' => $year]);
        
        $income = array_fill(1, 12, 0);
        foreach ($results as $row) {
            $income[(int)$row['month']] = (float)$row['total'];
        }
        return $income;
    }
}
