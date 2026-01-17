<?php
require_once __DIR__ . '/Model.php';

class StatisticalReportModel extends Model {

    /**
     * Get aggregated count of infractions grouped by month for the last N months.
     * @param int $months Number of months to look back.
     * @return array Data formatted for Chart.js labels and dataset.
     */
    public function getInfractionsByMonth($months = 6) {
        $sql = "SELECT 
                    DATE_FORMAT(infraction_datetime, '%Y-%m') as month_key,
                    DATE_FORMAT(infraction_datetime, '%b') as month_label,
                    COUNT(*) as total
                FROM infractions
                WHERE infraction_datetime >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                AND status_logical = 'active'
                GROUP BY month_key
                ORDER BY month_key ASC";
        
        $results = $this->query($sql, [':months' => $months]);

        // Fill in missing months with 0
        $data = [];
        $labels = [];
        $counts = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $label = date('M', strtotime("-$i months")); // Short month name
            
            // Spanish Month Mapping (optional, ideally utilize a localized date library or array)
            $spanish_months = [
                'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr', 'May' => 'May', 'Jun' => 'Jun',
                'Jul' => 'Jul', 'Aug' => 'Ago', 'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
            ];
            $localized_label = $spanish_months[$label] ?? $label;

            $found = false;
            foreach ($results as $row) {
                if ($row['month_key'] === $date) {
                    $counts[] = (int)$row['total'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $counts[] = 0;
            }
            $labels[] = $localized_label;
        }

        return [
            'labels' => $labels,
            'data' => $counts
        ];
    }

    /**
     * Get count of active staff grouped by department.
     * @return array Data formatted for Chart.js labels and dataset.
     */
    public function getEmployeesByDepartment() {
        $sql = "SELECT 
                    d.name as department_name,
                    COUNT(s.id) as total_staff
                FROM departments d
                LEFT JOIN staff s ON d.id = s.department_id AND s.status = 'active'
                GROUP BY d.id
                ORDER BY total_staff DESC";
        
        $results = $this->query($sql);

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];

        // Predefined colors for aesthetics
        $colors = [
            ['rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 1)'],
            ['rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 1)'],
            ['rgba(255, 206, 86, 0.8)', 'rgba(255, 206, 86, 1)'],
            ['rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 1)'],
            ['rgba(153, 102, 255, 0.8)', 'rgba(153, 102, 255, 1)'],
            ['rgba(255, 159, 64, 0.8)', 'rgba(255, 159, 64, 1)']
        ];

        foreach ($results as $index => $row) {
            $labels[] = $row['department_name'];
            $data[] = (int)$row['total_staff'];
            
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

    /**
     * Get count of inspections performed grouped by month for the last N months.
     * @param int $months Number of months to look back.
     * @return array Data formatted for Chart.js labels and dataset.
     */
    public function getInspectionProductivity($months = 12) {
         // Using scheduled_inspections or inspection_reports depending on what counts as "performed".
         // Assuming inspection_reports creation_date or scheduled_inspections.scheduled_date where status is 'Completed'.
         // Let's use inspection_reports (actual reports created) for "Productivity".
         
         $sql = "SELECT 
                    DATE_FORMAT(creation_date, '%Y-%m') as month_key,
                    DATE_FORMAT(creation_date, '%b') as month_label,
                    COUNT(*) as total
                FROM inspection_reports
                WHERE creation_date >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                GROUP BY month_key
                ORDER BY month_key ASC";

        $results = $this->query($sql, [':months' => $months]);

        $data = [];
        $labels = [];
        $counts = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $label = date('M', strtotime("-$i months"));
            
            $spanish_months = [
                'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr', 'May' => 'May', 'Jun' => 'Jun',
                'Jul' => 'Jul', 'Aug' => 'Ago', 'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
            ];
            $localized_label = $spanish_months[$label] ?? $label;

            $found = false;
            foreach ($results as $row) {
                if ($row['month_key'] === $date) {
                    $counts[] = (int)$row['total'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $counts[] = 0;
            }
            $labels[] = $localized_label;
        }

        return [
            'labels' => $labels,
            'data' => $counts
        ];
    }
    /**
     * Get inspection productivity leaderboard or specific inspector performance.
     * @param string $period 'annual' or 'monthly'.
     * @param int|null $inspectorId Optional inspector ID to filter by.
     * @return array Data formatted for Chart.js.
     */
    public function getInspectorPerformance($period = 'annual', $inspectorId = null) {
        // We count completed inspections.
        // Assuming 'Completed' status in scheduled_inspections or just counting reports in inspection_reports
        // Let's use inspection_reports as "completed" inspections.
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($period === 'monthly') {
            $whereClause .= " AND ir.creation_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        } else {
            // Annual default
            $whereClause .= " AND ir.creation_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
        }

        if ($inspectorId) {
            $whereClause .= " AND ir.main_inspector_id = :inspector_id";
            $params[':inspector_id'] = $inspectorId;
        }

        // Only count active inspectors or relevant records
        $sql = "SELECT 
                    i.full_name as inspector_name,
                    COUNT(ir.report_id) as total_inspections
                FROM inspection_reports ir
                JOIN inspectors i ON ir.main_inspector_id = i.inspector_id
                $whereClause
                GROUP BY ir.main_inspector_id
                ORDER BY total_inspections DESC
                LIMIT 10"; // Top 10 for leaderboard

        $results = $this->query($sql, $params);

        $labels = [];
        $data = [];
        
        foreach ($results as $row) {
            $labels[] = $row['inspector_name'];
            $data[] = (int)$row['total_inspections'];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get total revenue generated by infraction type.
     * @return array Data formatted for Chart.js.
     */
    public function getRevenueByInfractionType() {
        $sql = "SELECT 
                    it.infraction_type_name,
                    SUM(s.fine_amount) as total_revenue
                FROM sanctions s
                JOIN infractions i ON s.infraction_id = i.infraction_id
                JOIN infraction_types it ON i.infraction_type_id = it.infraction_type_id
                WHERE s.sanction_status = 'Paid' OR s.sanction_status = 'Pending' 
                AND s.status_logical = 'active'
                GROUP BY it.infraction_type_id
                ORDER BY total_revenue DESC
                LIMIT 10";

        $results = $this->query($sql);

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];
        
        $colors = [
            ['rgba(75, 192, 192, 0.6)', 'rgba(75, 192, 192, 1)'],
            ['rgba(255, 99, 132, 0.6)', 'rgba(255, 99, 132, 1)'],
            ['rgba(54, 162, 235, 0.6)', 'rgba(54, 162, 235, 1)'],
            ['rgba(255, 206, 86, 0.6)', 'rgba(255, 206, 86, 1)'],
            ['rgba(153, 102, 255, 0.6)', 'rgba(153, 102, 255, 1)'],
        ];

        foreach ($results as $index => $row) {
            $labels[] = $row['infraction_type_name'];
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

    /**
     * Get list of active inspectors for dropdowns.
     * @return array
     */
    public function getInspectorsList() {
        $sql = "SELECT inspector_id, full_name FROM inspectors WHERE is_active = 1 ORDER BY full_name";
        return $this->query($sql);
    }

    public function getDashboardStats() {
        $activeInfractions = $this->queryOne("SELECT COUNT(*) as total FROM infractions WHERE infraction_status IN ('Reported', 'In Process') AND status_logical = 'active'");
        $resolvedInfractions = $this->queryOne("SELECT COUNT(*) as total FROM infractions WHERE infraction_status = 'Resolved' AND status_logical = 'active'");
        $awardeesCount = $this->queryOne("SELECT COUNT(*) as total FROM awardees");
        $stallsCount = $this->queryOne("SELECT COUNT(*) as total FROM market_stalls");

        return [
            'active_infractions' => (int)($activeInfractions['total'] ?? 0),
            'resolved_infractions' => (int)($resolvedInfractions['total'] ?? 0),
            'awardees' => (int)($awardeesCount['total'] ?? 0),
            'stalls' => (int)($stallsCount['total'] ?? 0)
        ];
    }
}
