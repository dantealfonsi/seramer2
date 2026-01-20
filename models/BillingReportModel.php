<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/SanctionsModel.php';

/**
 * Model for billing statistical reports and analytics
 */
class BillingReportModel extends Model {
    
    /**
     * Get collection effectiveness (Imposed vs Collected amounts).
     * @param array $dateRange ['start' => 'Y-m-d', 'end' => 'Y-m-d']
     * @param string $type 'all', 'contracts', 'fines'
     * @return array
     */
    public function getCollectionEffectiveness($dateRange = [], $type = 'all') {
        $data = [
            'imposed' => 0,
            'collected' => 0,
            'pending' => 0,
            'effectiveness_percentage' => 0
        ];
        
        // Get fines data
        if ($type === 'all' || $type === 'fines') {
            $finesData = $this->getFinesEffectiveness($dateRange);
            $data['imposed'] += $finesData['imposed'];
            $data['collected'] += $finesData['collected'];
        }
        
        // Get contracts data
        if ($type === 'all' || $type === 'contracts') {
            $contractsData = $this->getContractsEffectiveness($dateRange);
            $data['imposed'] += $contractsData['imposed'];
            $data['collected'] += $contractsData['collected'];
        }
        
        $data['pending'] = $data['imposed'] - $data['collected'];
        $data['effectiveness_percentage'] = $data['imposed'] > 0 
            ? round(($data['collected'] / $data['imposed']) * 100, 2) 
            : 0;
        
        return $data;
    }
    
    /**
     * Get fines effectiveness data.
     * @param array $dateRange
     * @return array
     */
    private function getFinesEffectiveness($dateRange = []) {
        $query = "SELECT 
                    SUM(s.fine_amount) as imposed,
                    COALESCE(SUM(fp.amount_paid), 0) as collected
                  FROM sanctions s
                  LEFT JOIN fine_payments fp ON s.sanction_id = fp.sanction_id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($dateRange['start'])) {
            $query .= " AND DATE(s.imposition_date) >= :start_date";
            $params['start_date'] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $query .= " AND DATE(s.imposition_date) <= :end_date";
            $params['end_date'] = $dateRange['end'];
        }
        
        $result = $this->queryOne($query, $params);
        
        return [
            'imposed' => (float)($result['imposed'] ?? 0),
            'collected' => (float)($result['collected'] ?? 0)
        ];
    }
    
    /**
     * Get contracts effectiveness data.
     * @param array $dateRange
     * @return array
     */
    private function getContractsEffectiveness($dateRange = []) {
        $query = "SELECT 
                    SUM(cp.amount) as imposed,
                    COALESCE(SUM(cpi.amount), 0) as collected
                  FROM contract_payments cp
                  LEFT JOIN contract_payment_installments cpi ON cp.id = cpi.contract_payment_id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($dateRange['start'])) {
            $query .= " AND DATE(cp.payment_date) >= :start_date";
            $params['start_date'] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $query .= " AND DATE(cp.payment_date) <= :end_date";
            $params['end_date'] = $dateRange['end'];
        }
        
        $result = $this->queryOne($query, $params);
        
        return [
            'imposed' => (float)($result['imposed'] ?? 0),
            'collected' => (float)($result['collected'] ?? 0)
        ];
    }
    
    /**
     * Get delinquency by sector.
     * @return array
     */
    public function getDelinquencyBySector() {
        // Sanctions delinquency
        $sanctionsQuery = "SELECT 
                            sec.name as sector_name,
                            SUM(s.fine_amount) as total_debt,
                            COUNT(DISTINCT i.awardee_id) as delinquent_count
                          FROM sanctions s
                          JOIN infractions i ON s.infraction_id = i.infraction_id
                          LEFT JOIN market_stalls ms ON i.stall_id = ms.id
                          LEFT JOIN sectors sec ON ms.sector_id = sec.id
                          WHERE s.sanction_status IN ('Imposed', 'Pending')
                          AND i.status_logical = 'active'
                          GROUP BY sec.id, sec.name";
        
        $sanctionsData = $this->query($sanctionsQuery);
        
        // Contract payments delinquency
        $contractsQuery = "SELECT 
                            sec.name as sector_name,
                            SUM(cp.amount) as total_debt,
                            COUNT(DISTINCT c.awardee_id) as delinquent_count
                          FROM contract_payments cp
                          JOIN contracts c ON cp.contract_id = c.id
                          LEFT JOIN contract_locations cl ON c.id = cl.contract_id
                          LEFT JOIN market_stalls ms ON cl.stall_id = ms.id
                          LEFT JOIN sectors sec ON ms.sector_id = sec.id
                          WHERE cp.status = 'pending'
                          AND cp.payment_date < CURDATE()
                          GROUP BY sec.id, sec.name";
        
        $contractsData = $this->query($contractsQuery);
        
        // Merge data by sector
        $sectors = [];
        
        foreach ($sanctionsData as $row) {
            $sectorName = $row['sector_name'] ?? 'Sin Sector';
            if (!isset($sectors[$sectorName])) {
                $sectors[$sectorName] = ['total_debt' => 0, 'delinquent_count' => 0];
            }
            $sectors[$sectorName]['total_debt'] += (float)$row['total_debt'];
            $sectors[$sectorName]['delinquent_count'] += (int)$row['delinquent_count'];
        }
        
        foreach ($contractsData as $row) {
            $sectorName = $row['sector_name'] ?? 'Sin Sector';
            if (!isset($sectors[$sectorName])) {
                $sectors[$sectorName] = ['total_debt' => 0, 'delinquent_count' => 0];
            }
            $sectors[$sectorName]['total_debt'] += (float)$row['total_debt'];
            $sectors[$sectorName]['delinquent_count'] += (int)$row['delinquent_count'];
        }
        
        // Format for Chart.js
        $labels = [];
        $data = [];
        
        foreach ($sectors as $sectorName => $values) {
            $labels[] = $sectorName;
            $data[] = $values['total_debt'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Get delinquency by zone.
     * @return array
     */
    public function getDelinquencyByZone() {
        // Similar to getDelinquencyBySector but grouped by zones
        $query = "SELECT 
                    z.name as zone_name,
                    SUM(s.fine_amount) as sanctions_debt,
                    SUM(cp.amount) as contracts_debt
                  FROM zones z
                  LEFT JOIN sectors sec ON z.id = sec.zone_id
                  LEFT JOIN market_stalls ms ON sec.id = ms.sector_id
                  LEFT JOIN infractions i ON ms.id = i.stall_id
                  LEFT JOIN sanctions s ON i.infraction_id = s.infraction_id AND s.sanction_status IN ('Imposed', 'Pending')
                  LEFT JOIN contract_locations cl ON ms.id = cl.stall_id
                  LEFT JOIN contracts c ON cl.contract_id = c.id
                  LEFT JOIN contract_payments cp ON c.id = cp.contract_id AND cp.status = 'pending' AND cp.payment_date < CURDATE()
                  GROUP BY z.id, z.name
                  HAVING sanctions_debt > 0 OR contracts_debt > 0";
        
        $results = $this->query($query);
        
        $labels = [];
        $data = [];
        
        foreach ($results as $row) {
            $labels[] = $row['zone_name'];
            $totalDebt = (float)($row['sanctions_debt'] ?? 0) + (float)($row['contracts_debt'] ?? 0);
            $data[] = $totalDebt;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Get payment method distribution (mix).
     * @param array $dateRange
     * @return array
     */
    public function getPaymentMethodMix($dateRange = []) {
        // Fine payments
        $finesQuery = "SELECT 
                        pm.name as method_name,
                        SUM(fp.amount_paid) as total_amount
                      FROM fine_payments fp
                      JOIN payment_methods pm ON fp.payment_method_id = pm.id
                      WHERE 1=1";
        
        $params = [];
        
        if (!empty($dateRange['start'])) {
            $finesQuery .= " AND DATE(fp.payment_date) >= :start_date";
            $params['start_date'] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $finesQuery .= " AND DATE(fp.payment_date) <= :end_date";
            $params['end_date'] = $dateRange['end'];
        }
        
        $finesQuery .= " GROUP BY pm.id, pm.name";
        
        $finesData = $this->query($finesQuery, $params);
        
        // Contract payments
        $contractsQuery = "SELECT 
                            pm.name as method_name,
                            SUM(cpi.amount) as total_amount
                          FROM contract_payment_installments cpi
                          JOIN payment_methods pm ON cpi.payment_method_id = pm.id
                          WHERE 1=1";
        
        $params2 = [];
        
        if (!empty($dateRange['start'])) {
            $contractsQuery .= " AND DATE(cpi.date) >= :start_date";
            $params2['start_date'] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $contractsQuery .= " AND DATE(cpi.date) <= :end_date";
            $params2['end_date'] = $dateRange['end'];
        }
        
        $contractsQuery .= " GROUP BY pm.id, pm.name";
        
        $contractsData = $this->query($contractsQuery, $params2);
        
        // Merge data
        $methods = [];
        
        foreach ($finesData as $row) {
            $methodName = $row['method_name'];
            if (!isset($methods[$methodName])) {
                $methods[$methodName] = 0;
            }
            $methods[$methodName] += (float)$row['total_amount'];
        }
        
        foreach ($contractsData as $row) {
            $methodName = $row['method_name'];
            if (!isset($methods[$methodName])) {
                $methods[$methodName] = 0;
            }
            $methods[$methodName] += (float)$row['total_amount'];
        }
        
        // Format for Chart.js
        $labels = array_keys($methods);
        $data = array_values($methods);
        
        // Generate colors
        $colors = [
            ['rgba(75, 192, 192, 0.6)', 'rgba(75, 192, 192, 1)'],
            ['rgba(255, 99, 132, 0.6)', 'rgba(255, 99, 132, 1)'],
            ['rgba(54, 162, 235, 0.6)', 'rgba(54, 162, 235, 1)'],
            ['rgba(255, 206, 86, 0.6)', 'rgba(255, 206, 86, 1)'],
            ['rgba(153, 102, 255, 0.6)', 'rgba(153, 102, 255, 1)'],
        ];
        
        $backgroundColor = [];
        $borderColor = [];
        
        foreach ($data as $index => $value) {
            $colorIndex = $index % count($colors);
            $backgroundColor[] = $colors[$colorIndex][0];
            $borderColor[] = $colors[$colorIndex][1];
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => $backgroundColor,
            'borderColor' => $borderColor
        ];
    }
    
    /**
     * Get revenue by period (day, week, month, year).
     * @param string $startDate
     * @param string $endDate
     * @param string $groupBy 'day', 'week', 'month', 'year'
     * @param string $type 'all', 'contracts', 'fines'
     * @return array
     */
    public function getRevenueByPeriod($startDate, $endDate, $groupBy = 'month', $type = 'all') {
        $dateFormat = $this->getDateFormat($groupBy);
        
        $data = [];
        
        // Get fines revenue
        if ($type === 'all' || $type === 'fines') {
            $finesQuery = "SELECT 
                            DATE_FORMAT(payment_date, '$dateFormat') as period,
                            SUM(amount_paid) as total
                          FROM fine_payments
                          WHERE DATE(payment_date) BETWEEN :start_date AND :end_date
                          GROUP BY period
                          ORDER BY payment_date ASC";
            
            $finesData = $this->query($finesQuery, [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            foreach ($finesData as $row) {
                $period = $row['period'];
                if (!isset($data[$period])) {
                    $data[$period] = 0;
                }
                $data[$period] += (float)$row['total'];
            }
        }
        
        // Get contracts revenue
        if ($type === 'all' || $type === 'contracts') {
            $contractsQuery = "SELECT 
                                DATE_FORMAT(date, '$dateFormat') as period,
                                SUM(amount) as total
                              FROM contract_payment_installments
                              WHERE DATE(date) BETWEEN :start_date AND :end_date
                              GROUP BY period
                              ORDER BY date ASC";
            
            $contractsData = $this->query($contractsQuery, [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            foreach ($contractsData as $row) {
                $period = $row['period'];
                if (!isset($data[$period])) {
                    $data[$period] = 0;
                }
                $data[$period] += (float)$row['total'];
            }
        }
        
        // Format for Chart.js
        ksort($data); // Sort by period
        
        return [
            'labels' => array_keys($data),
            'data' => array_values($data)
        ];
    }
    
    /**
     * Get MySQL date format string based on grouping.
     * @param string $groupBy
     * @return string
     */
    private function getDateFormat($groupBy) {
        switch ($groupBy) {
            case 'day':
                return '%Y-%m-%d';
            case 'week':
                return '%Y-%u'; // Year-Week
            case 'year':
                return '%Y';
            case 'month':
            default:
                return '%Y-%m';
        }
    }
    
    /**
     * Get delinquent accounts with filters.
     * @param array $filters
     * @return array
     */
    public function getDelinquentAccounts($filters = []) {
        // This combines both sanctions and contract payments delinquency
        $accounts = [];
        
        // Get sanctions delinquency
        $sanctionsModel = new SanctionsModel();
        $sanctionsDelinquent = $sanctionsModel->getDelinquentSanctions($filters);
        
        foreach ($sanctionsDelinquent as $sanction) {
            $awardeeId = $sanction['awardee_id'];
            if (!isset($accounts[$awardeeId])) {
                $accounts[$awardeeId] = [
                    'awardee_id' => $awardeeId,
                    'first_name' => $sanction['first_name'],
                    'last_name' => $sanction['last_name'],
                    'id_number' => $sanction['id_number'],
                    'stall_number' => $sanction['stall_number'] ?? 'N/A',
                    'sector_name' => $sanction['sector_name'] ?? 'N/A',
                    'zone_name' => $sanction['zone_name'] ?? 'N/A',
                    'sanctions_debt' => 0,
                    'contracts_debt' => 0,
                    'total_debt' => 0,
                    'days_overdue' => 0
                ];
            }
            $accounts[$awardeeId]['sanctions_debt'] += (float)$sanction['fine_amount'];
            $accounts[$awardeeId]['days_overdue'] = max($accounts[$awardeeId]['days_overdue'], (int)$sanction['days_overdue']);
        }
        
        // Get contracts delinquency
        $contractsQuery = "SELECT 
                            a.id as awardee_id,
                            a.first_name,
                            a.last_name,
                            a.id_number,
                            ms.stall_number,
                            sec.name as sector_name,
                            z.name as zone_name,
                            SUM(cp.amount) as total_debt,
                            DATEDIFF(CURDATE(), cp.payment_date) as days_overdue
                          FROM contract_payments cp
                          JOIN contracts c ON cp.contract_id = c.id
                          JOIN awardees a ON c.awardee_id = a.id
                          LEFT JOIN contract_locations cl ON c.id = cl.contract_id
                          LEFT JOIN market_stalls ms ON cl.stall_id = ms.id
                          LEFT JOIN sectors sec ON ms.sector_id = sec.id
                          LEFT JOIN zones z ON sec.zone_id = z.id
                          WHERE cp.status = 'pending'
                          AND cp.payment_date < CURDATE()
                          GROUP BY a.id
                          ORDER BY days_overdue DESC";
        
        $contractsDelinquent = $this->query($contractsQuery);
        
        foreach ($contractsDelinquent as $contract) {
            $awardeeId = $contract['awardee_id'];
            if (!isset($accounts[$awardeeId])) {
                $accounts[$awardeeId] = [
                    'awardee_id' => $awardeeId,
                    'first_name' => $contract['first_name'],
                    'last_name' => $contract['last_name'],
                    'id_number' => $contract['id_number'],
                    'stall_number' => $contract['stall_number'] ?? 'N/A',
                    'sector_name' => $contract['sector_name'] ?? 'N/A',
                    'zone_name' => $contract['zone_name'] ?? 'N/A',
                    'sanctions_debt' => 0,
                    'contracts_debt' => 0,
                    'total_debt' => 0,
                    'days_overdue' => 0
                ];
            }
            $accounts[$awardeeId]['contracts_debt'] += (float)$contract['total_debt'];
            $accounts[$awardeeId]['days_overdue'] = max($accounts[$awardeeId]['days_overdue'], (int)$contract['days_overdue']);
        }
        
        // Calculate total debt
        foreach ($accounts as &$account) {
            $account['total_debt'] = $account['sanctions_debt'] + $account['contracts_debt'];
        }
        
        return array_values($accounts);
    }
}
