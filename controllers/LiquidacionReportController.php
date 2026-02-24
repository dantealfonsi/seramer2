<?php
/**
 * Controlador de Reportes de Liquidación
 * 
 * Proporciona datos para los reportes del área de Liquidación.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/LiquidacionReportModel.php';
require_once __DIR__ . '/../models/FiscalYearModel.php';
require_once __DIR__ . '/../models/ZoneModel.php';

class LiquidacionReportController {

    private LiquidacionReportModel $reportModel;
    private FiscalYearModel $fiscalYearModel;
    private ZoneModel $zoneModel;

    private array $monthNames = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    public function __construct() {
        $this->reportModel    = new LiquidacionReportModel();
        $this->fiscalYearModel = new FiscalYearModel();
        $this->zoneModel      = new ZoneModel();
    }

    // ------------------------------------------------------------------
    // Helpers de acceso a parámetros GET (para uso interno si se llama directo)
    // ------------------------------------------------------------------
    private function getParam(string $key, $default = null) {
        return isset($_GET[$key]) && $_GET[$key] !== '' ? $_GET[$key] : $default;
    }

    // ==================================================================
    // 1. CONTRATOS MOROSOS
    // ==================================================================
    public function delinquentcontracts(): array {
        $contracts = $this->reportModel->getDelinquentContracts();
        return [
            'pageTitle' => 'Contratos Morosos',
            'contracts' => $contracts
        ];
    }

    // ==================================================================
    // 2. TOTAL ACUMULADO POR ZONA
    // ==================================================================
    public function zoneaccumulated(): array {
        $startDate    = $this->getParam('start_date', date('Y-m-01'));
        $endDate      = $this->getParam('end_date', date('Y-m-t'));
        $fiscalYearId = $this->getParam('fiscal_year_id') ? (int)$this->getParam('fiscal_year_id') : null;

        $zones       = $this->reportModel->getZoneAccumulated($startDate, $endDate, $fiscalYearId);
        $fiscalYears = $this->fiscalYearModel->getAll();

        return [
            'pageTitle'    => 'Total por Zona',
            'zones'        => $zones,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'fiscalYearId' => $fiscalYearId,
            'fiscalYears'  => $fiscalYears
        ];
    }

    // ==================================================================
    // 3. REPORTE DE CAJA
    // ==================================================================
    public function cashreport(): array {
        $startDate    = $this->getParam('start_date', date('Y-m-01'));
        $endDate      = $this->getParam('end_date', date('Y-m-t'));
        $fiscalYearId = $this->getParam('fiscal_year_id') ? (int)$this->getParam('fiscal_year_id') : null;

        $data         = $this->reportModel->getCashReport($startDate, $endDate, $fiscalYearId);
        $totalsByZone = $this->reportModel->getCashReportTotalsByZone($startDate, $endDate, $fiscalYearId);
        $fiscalYears  = $this->fiscalYearModel->getAll();

        return [
            'pageTitle'    => 'Reporte de Caja',
            'data'         => $data,
            'totalsByZone' => $totalsByZone,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'fiscalYearId' => $fiscalYearId,
            'fiscalYears'  => $fiscalYears
        ];
    }

    // ==================================================================
    // 4. CAJA POR MÉTODOS DE PAGO
    // ==================================================================
    public function cashreportbypaymentmethod(): array {
        $startDate    = $this->getParam('start_date', date('Y-m-01'));
        $endDate      = $this->getParam('end_date', date('Y-m-t'));
        $fiscalYearId = $this->getParam('fiscal_year_id') ? (int)$this->getParam('fiscal_year_id') : null;
        $zoneIds      = $this->getParam('zone_ids');

        $zoneIdsArray = null;
        if ($zoneIds) {
            if (is_array($zoneIds)) {
                $zoneIdsArray = array_filter(array_map('intval', $zoneIds), fn($id) => $id > 0);
            } else {
                $zoneIdsArray = [(int)$zoneIds];
            }
        }

        $data                 = $this->reportModel->getCashReportByPaymentMethod($startDate, $endDate, $fiscalYearId, $zoneIdsArray);
        $totalsByPaymentMethod = $this->reportModel->getCashReportPaymentMethodTotals($startDate, $endDate, $fiscalYearId, $zoneIdsArray);
        $fiscalYears          = $this->fiscalYearModel->getAll();
        $zones                = $this->zoneModel->getAll();

        return [
            'pageTitle'            => 'Caja por Métodos de Pago',
            'data'                 => $data,
            'totalsByPaymentMethod' => $totalsByPaymentMethod,
            'startDate'            => $startDate,
            'endDate'              => $endDate,
            'fiscalYearId'         => $fiscalYearId,
            'zoneIds'              => $zoneIdsArray,
            'fiscalYears'          => $fiscalYears,
            'zones'                => $zones
        ];
    }

    // ==================================================================
    // 5. INGRESOS POR ZONA
    // ==================================================================
    public function incomebyzone(): array {
        $month        = $this->getParam('month') ? (int)$this->getParam('month') : (int)date('n');
        $year         = $this->getParam('year')  ? (int)$this->getParam('year')  : (int)date('Y');
        $zoneId       = $this->getParam('zone_id') ? (int)$this->getParam('zone_id') : null;
        $contractType = $this->getParam('contract_type');

        $month = ($month < 1 || $month > 12) ? (int)date('n') : $month;
        $year  = ($year < 2020 || $year > 2050) ? (int)date('Y') : $year;

        $data   = $this->reportModel->getIncomeByZoneReport($month, $year, $zoneId, $contractType);
        $totals = $this->reportModel->getIncomeByZoneTotals($month, $year, $zoneId, $contractType);
        $zones  = $this->zoneModel->getAll();

        return [
            'pageTitle'         => 'Ingresos por Zona',
            'data'              => $data,
            'totals'            => $totals,
            'month'             => $month,
            'year'              => $year,
            'zoneId'            => $zoneId,
            'contractType'      => $contractType,
            'zones'             => $zones,
            'monthNames'        => $this->monthNames,
            'selectedMonthName' => $this->monthNames[$month] ?? 'Desconocido'
        ];
    }

    // ==================================================================
    // 6. INGRESOS POR RUBRO
    // ==================================================================
    public function incomebycategory(): array {
        $month        = $this->getParam('month') ? (int)$this->getParam('month') : (int)date('n');
        $year         = $this->getParam('year')  ? (int)$this->getParam('year')  : (int)date('Y');
        $zoneId       = $this->getParam('zone_id') ? (int)$this->getParam('zone_id') : null;
        $contractType = $this->getParam('contract_type');

        $month = ($month < 1 || $month > 12) ? (int)date('n') : $month;
        $year  = ($year < 2020 || $year > 2050) ? (int)date('Y') : $year;

        $data   = $this->reportModel->getIncomeByCategoryReport($month, $year, $zoneId, $contractType);
        $totals = $this->reportModel->getIncomeByCategoryTotals($month, $year, $zoneId, $contractType);
        $zones  = $this->zoneModel->getAll();

        // Agrupar por rubro
        $dataByCategory = [];
        foreach ($data as $row) {
            $rubro = $row['rubro'] ?? 'Sin Rubro';
            if (!isset($dataByCategory[$rubro])) $dataByCategory[$rubro] = [];
            $dataByCategory[$rubro][] = $row;
        }

        return [
            'pageTitle'         => 'Ingresos por Rubro',
            'dataByCategory'    => $dataByCategory,
            'totals'            => $totals,
            'month'             => $month,
            'year'              => $year,
            'zoneId'            => $zoneId,
            'contractType'      => $contractType,
            'zones'             => $zones,
            'monthNames'        => $this->monthNames,
            'selectedMonthName' => $this->monthNames[$month] ?? 'Desconocido'
        ];
    }

    // ==================================================================
    // 7. RESUMEN DE INGRESOS (por zona y sector)
    // ==================================================================
    public function incomesummary(): array {
        $month        = $this->getParam('month')        ? (int)$this->getParam('month')     : null;
        $year         = $this->getParam('year')         ? (int)$this->getParam('year')      : null;
        $fiscalYearId = $this->getParam('fiscal_year_id') ? (int)$this->getParam('fiscal_year_id') : null;
        $contractType = $this->getParam('contract_type');

        if ($month !== null && ($month < 1 || $month > 12)) $month = null;
        if ($year  !== null && ($year < 2020 || $year > 2050)) $year = null;

        $data        = $this->reportModel->getIncomeSummaryByZoneSector($month, $year, $fiscalYearId, $contractType);
        $totals      = $this->reportModel->getIncomeSummaryTotals($month, $year, $fiscalYearId, $contractType);
        $fiscalYears = $this->fiscalYearModel->getAll();

        // Agrupar por zona
        $dataByZone = [];
        foreach ($data as $row) {
            $zoneId = $row['zone_id'];
            if (!isset($dataByZone[$zoneId])) {
                $dataByZone[$zoneId] = ['zona' => $row['zona'], 'sectores' => []];
            }
            $dataByZone[$zoneId]['sectores'][] = $row;
        }

        $totalsByZoneMap = [];
        if (isset($totals['totals_by_zone'])) {
            foreach ($totals['totals_by_zone'] as $zt) {
                $totalsByZoneMap[$zt['zone_id']] = $zt;
            }
        }

        return [
            'pageTitle'       => 'Resumen de Ingresos',
            'dataByZone'      => $dataByZone,
            'totals'          => $totals,
            'totalsByZoneMap' => $totalsByZoneMap,
            'month'           => $month,
            'year'            => $year,
            'fiscalYearId'    => $fiscalYearId,
            'contractType'    => $contractType,
            'fiscalYears'     => $fiscalYears,
            'monthNames'      => $this->monthNames
        ];
    }

    // ==================================================================
    // 8. RESUMEN DE INGRESOS POR RUBRO
    // ==================================================================
    public function incomesummarybycategory(): array {
        $month        = $this->getParam('month')        ? (int)$this->getParam('month')     : null;
        $year         = $this->getParam('year')         ? (int)$this->getParam('year')      : null;
        $fiscalYearId = $this->getParam('fiscal_year_id') ? (int)$this->getParam('fiscal_year_id') : null;
        $contractType = $this->getParam('contract_type');

        if ($month !== null && ($month < 1 || $month > 12)) $month = null;
        if ($year  !== null && ($year < 2020 || $year > 2050)) $year = null;

        $data        = $this->reportModel->getIncomeSummaryByCategory($month, $year, $fiscalYearId, $contractType);
        $total       = $this->reportModel->getIncomeSummaryByCategoryTotals($month, $year, $fiscalYearId, $contractType);
        $fiscalYears = $this->fiscalYearModel->getAll();

        return [
            'pageTitle'    => 'Resumen de Ingresos por Rubro',
            'data'         => $data,
            'total'        => $total,
            'month'        => $month,
            'year'         => $year,
            'fiscalYearId' => $fiscalYearId,
            'contractType' => $contractType,
            'fiscalYears'  => $fiscalYears,
            'monthNames'   => $this->monthNames
        ];
    }
}
