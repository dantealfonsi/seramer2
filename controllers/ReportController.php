<?php
require_once __DIR__ . '/../models/ReportModel.php';

class ReportController {
    private $reportModel;
    
    public function __construct() {
        $this->reportModel = new ReportModel();
    }
    
    public function billingReport() {
        $contracts = $this->reportModel->getDelinquentContracts();
        return [
            'page_title' => 'Reporte de Contratos Morosos',
            'contracts' => $contracts
        ];
    }
    
    public function liquidacionReport($params = []) {
        $startDate = $params['start_date'] ?? date('Y-m-01');
        $endDate = $params['end_date'] ?? date('Y-m-t');
        
        $zones = $this->reportModel->getZoneAccumulated($startDate, $endDate);
        
        return [
            'page_title' => 'Reporte de Liquidación por Zona',
            'zones' => $zones,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }
}
