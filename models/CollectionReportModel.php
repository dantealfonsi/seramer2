<?php
require_once __DIR__ . '/Model.php';

class CollectionReportModel extends Model {

    /**
     * Get total revenue grouped by date for a specific range.
     * Combines fee_payments and fine_payments.
     * @param string $startDate 'Y-m-d'
     * @param string $endDate 'Y-m-d'
     * @return array Data formatted for Chart.js
     */
    public function getRevenueByDateRange($startDate, $endDate) {
        // Union for both payment types, assuming filtered by valid status 'Paid'
        $sql = "
            SELECT 
                payment_date_formatted,
                SUM(amount) as total_revenue
            FROM (
                SELECT DATE(payment_date) as payment_date_formatted, amount_paid as amount 
                FROM fee_payments 
                WHERE DATE(payment_date) BETWEEN :start_date1 AND :end_date1
                AND payment_status = 'Paid'
                
                UNION ALL
                
                SELECT DATE(payment_date) as payment_date_formatted, amount_paid as amount 
                FROM fine_payments 
                WHERE DATE(payment_date) BETWEEN :start_date2 AND :end_date2
                AND payment_status = 'Paid'
            ) combined_revenue
            GROUP BY payment_date_formatted
            ORDER BY payment_date_formatted ASC
        ";

        $results = $this->query($sql, [
            ':start_date1' => $startDate, ':end_date1' => $endDate,
            ':start_date2' => $startDate, ':end_date2' => $endDate
        ]);

        $labels = [];
        $data = [];

        foreach ($results as $row) {
            $labels[] = date('d/m/Y', strtotime($row['payment_date_formatted']));
            $data[] = (float)$row['total_revenue'];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get top payers (Awardees and Stalls info) based on total paid amount.
     * @param int $limit
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTopPayers($limit = 10, $startDate = null, $endDate = null) {
        // We need to link payments to Awardees.
        // Fees: fee_payments -> contracts -> awardees
        // Fines: fine_payments -> sanctions -> infractions -> awardees
        
        $dateFilterFees = "";
        $dateFilterFines = "";
        $params = [];

        if ($startDate && $endDate) {
            $dateFilterFees = "AND DATE(fp.payment_date) BETWEEN :start_date1 AND :end_date1";
            $dateFilterFines = "AND DATE(fnp.payment_date) BETWEEN :start_date2 AND :end_date2";
            $params = [
                ':start_date1' => $startDate, ':end_date1' => $endDate,
                ':start_date2' => $startDate, ':end_date2' => $endDate
            ];
        }

        $sql = "
            SELECT 
                awardee_id,
                MAX(awardee_name) as awardee_name, -- Use MAX to pick the name (should be same per ID)
                MAX(id_number) as id_number,
                SUM(amount) as total_paid
            FROM (
                -- Fee Payments
                SELECT 
                    c.awardee_id,
                    CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                    a.id_number,
                    fp.amount_paid as amount
                FROM fee_payments fp
                JOIN contracts c ON fp.contract_id = c.id
                JOIN awardees a ON c.awardee_id = a.id
                WHERE fp.payment_status = 'Paid' $dateFilterFees

                UNION ALL

                -- Fine Payments
                SELECT 
                    i.awardee_id,
                    CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                    a.id_number,
                    fnp.amount_paid as amount
                FROM fine_payments fnp
                JOIN sanctions s ON fnp.sanction_id = s.sanction_id
                JOIN infractions i ON s.infraction_id = i.infraction_id
                JOIN awardees a ON i.awardee_id = a.id
                WHERE fnp.payment_status = 'Paid' $dateFilterFines
            ) combined_payments
            GROUP BY awardee_id
            ORDER BY total_paid DESC
            LIMIT " . (int)$limit;

        return $this->query($sql, $params);
    }

    /**
     * Get Revenue by Zone.
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array Chart.js data
     */
    public function getRevenueByZone($startDate = null, $endDate = null) {
        $dateFilterFees = "";
        $dateFilterFines = "";
        $params = [];

        if ($startDate && $endDate) {
            $dateFilterFees = "AND DATE(fp.payment_date) BETWEEN :start_date1 AND :end_date1";
            $dateFilterFines = "AND DATE(fnp.payment_date) BETWEEN :start_date2 AND :end_date2";
            $params = [
                ':start_date1' => $startDate, ':end_date1' => $endDate,
                ':start_date2' => $startDate, ':end_date2' => $endDate
            ];
        }

        // Logic:
        // Fee Payments -> Contract -> Contract Locations -> Market Stalls -> Sectors -> Zones
        // Warning: Contracts can have multiple locations. If a contract has 2 locations, how do we split revenue?
        // For simplicity and common business logic: we attribute revenue to the Zone of the FIRST location found for that contract, 
        // OR we can't easily split it. 
        // Let's assume we attribute to the 'Primary' zone if multiple exist, or just join distinct zones.
        // Better approach for aggregation: Join Market Stalls directly if possible.
        // Actually, contract -> contract_locations is 1:N.
        // If we duplicate the rows, we duplicate revenue.
        // Strategy: Use a subquery to get ONE Zone per contract (e.g., limit 1 location) to avoid inflation, 
        // OR accept that revenue is "associated" with that zone.
        // Re-reading request: "Dinero recaudado por zona".
        // Let's try to get distinct location for the contract.
        
        $sql = "
            SELECT 
                COALESCE(zone_name, 'Sin Zona / Otros') as zone_name,
                SUM(amount) as total_revenue
            FROM (
                -- Fee Payments
                SELECT 
                    (
                        SELECT z.name 
                        FROM contract_locations cl
                        JOIN market_stalls ms ON cl.stall_id = ms.id
                        JOIN sectors sec ON ms.sector_id = sec.id
                        JOIN zones z ON sec.zone_id = z.id
                        WHERE cl.contract_id = fp.contract_id
                        LIMIT 1
                    ) as zone_name,
                    fp.amount_paid as amount
                FROM fee_payments fp
                WHERE fp.payment_status = 'Paid' $dateFilterFees

                UNION ALL

                -- Fine Payments
                -- Fines are linked to specific Infraction -> Stall -> Zone
                SELECT 
                    z.name as zone_name,
                    fnp.amount_paid as amount
                FROM fine_payments fnp
                JOIN sanctions s ON fnp.sanction_id = s.sanction_id
                JOIN infractions i ON s.infraction_id = i.infraction_id
                JOIN market_stalls ms ON i.stall_id = ms.id
                JOIN sectors sec ON ms.sector_id = sec.id
                JOIN zones z ON sec.zone_id = z.id
                WHERE fnp.payment_status = 'Paid' $dateFilterFines
            ) combined_geo_revenue
            GROUP BY zone_name
            ORDER BY total_revenue DESC
        ";

        $results = $this->query($sql, $params);

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];
        
        $colors = [
            ['rgba(54, 162, 235, 0.7)', 'rgba(54, 162, 235, 1)'],
            ['rgba(255, 99, 132, 0.7)', 'rgba(255, 99, 132, 1)'],
            ['rgba(255, 206, 86, 0.7)', 'rgba(255, 206, 86, 1)'],
            ['rgba(75, 192, 192, 0.7)', 'rgba(75, 192, 192, 1)'],
            ['rgba(153, 102, 255, 0.7)', 'rgba(153, 102, 255, 1)'],
        ];

        foreach ($results as $index => $row) {
            $labels[] = $row['zone_name'];
            $data[] = (float)$row['total_revenue'];
            
            $colorIndex = $index % count($colors);
            $backgroundColors[] = $colors[$colorIndex][0];
            $borderColors[] = $colors[$colorIndex][1];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => $backgroundColors,
            'borderColor' => $borderColors
        ];
    }
}
