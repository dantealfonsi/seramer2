<?php
require_once __DIR__ . '/../models/ContractModel.php';
require_once __DIR__ . '/../models/ZoneModel.php';
require_once __DIR__ . '/../models/SectorModel.php';
require_once __DIR__ . '/../models/ContractPaymentModel.php';
require_once __DIR__ . '/../models/FiscalYearModel.php';

class PlanningController {
    private $contractModel;
    private $zoneModel;
    private $sectorModel;
    private $paymentModel;
    private $fiscalYearModel;

    public function __construct() {
        $this->contractModel = new ContractModel();
        $this->zoneModel = new ZoneModel();
        $this->sectorModel = new SectorModel();
        $this->paymentModel = new ContractPaymentModel();
        $this->fiscalYearModel = new FiscalYearModel();
    }

    public function anticipados() {
        return $this->planningByType('advance', 'Planificación: Contratos Anticipados');
    }

    public function simultaneos() {
        return $this->planningByType('simultaneous', 'Planificación: Contratos Simultáneos');
    }

    private function planningByType($contractType, $title) {
        $zoneId = $_GET['zone_id'] ?? '';
        $sectorId = $_GET['sector_id'] ?? '';
        $showDelinquent = $_GET['show_delinquent'] ?? '';
        $month = $_GET['month'] ?? date('n');
        $year = $_GET['year'] ?? date('Y');

        $filters = [
            'contract_type' => $contractType,
            'zone_id' => $zoneId,
            'sector_id' => $sectorId,
            'show_delinquent' => $showDelinquent
        ];

        $contracts = $this->paymentModel->getMonthlyPayments((int)$month, (int)$year, $filters);
        
        foreach ($contracts as &$contract) {
            $contract['payment_status_text'] = $this->getPaymentStatusText($contract['payment_status'], $contract['payment_date']);
        }

        $statistics = $this->paymentModel->getMonthlyStatistics((int)$month, (int)$year, $filters);
        $zones = $this->zoneModel->getAll();
        
        $sectors = [];
        if (!empty($zoneId)) {
            $sectors = $this->sectorModel->getByZone((int)$zoneId);
        }

        $monthsSpanish = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return [
            'page_title' => $title,
            'contracts' => $contracts,
            'statistics' => $statistics,
            'zones' => $zones,
            'sectors' => $sectors,
            'months' => $monthsSpanish,
            'fiscal_years' => $this->fiscalYearModel->getAll(),
            'current_month' => $month,
            'current_year' => $year,
            'current_month_spanish' => $monthsSpanish[$month],
            'filters' => $filters,
            'contract_type' => $contractType
        ];
    }

    private function getPaymentStatusText($status, $paymentDate) {
        if ($status === 'paid') return 'Pagado';
        if ($status === 'cancelled') return 'Cancelado';
        
        if ($status === 'pending') {
            $now = new DateTime();
            $dueDate = new DateTime($paymentDate);
            return ($dueDate < $now) ? 'Moroso' : 'Pendiente';
        }
        
        return ucfirst($status);
    }
}
