<?php
/**
 * Controlador de Planificación de Cobros
 * 
 * Gestiona la planificación mensual de cobros
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\ContractPaymentModel;
use App\Models\ZoneModel;
use App\Models\SectorModel;

class PlanningController extends Controller {
    private ContractPaymentModel $paymentModel;
    private ZoneModel $zoneModel;
    private SectorModel $sectorModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->paymentModel = new ContractPaymentModel();
        $this->zoneModel = new ZoneModel();
        $this->sectorModel = new SectorModel();
    }
    
    /**
     * Planificación de contratos anticipados
     */
    public function anticipados(): void {
        $this->planningByType('advance', 'Planificación de Anticipados');
    }
    
    /**
     * Planificación de contratos simultáneos
     */
    public function simultaneos(): void {
        $this->planningByType('simultaneous', 'Planificación de Simultáneos');
    }
    
    /**
     * Método privado para obtener la planificación por tipo de contrato
     */
    private function planningByType(string $contractType, string $title): void {
        // Obtener filtros de la URL
        $zoneFilter = $this->get('zone_id', '');
        $sectorFilter = $this->get('sector_id', '');
        $showDelinquent = $this->get('show_delinquent', '');
        $monthFilter = $this->get('month', '');
        $yearFilter = $this->get('year', '');
        
        $filters = [
            'contract_type' => $contractType
        ];
        
        if (!empty($zoneFilter)) {
            $filters['zone_id'] = (int)$zoneFilter;
        }
        if (!empty($sectorFilter)) {
            $filters['sector_id'] = (int)$sectorFilter;
        }
        if (!empty($showDelinquent)) {
            $filters['show_delinquent'] = '1';
        }
        
        // Determinar mes y año para la consulta
        $month = !empty($monthFilter) ? (int)$monthFilter : null;
        $year = !empty($yearFilter) ? (int)$yearFilter : null;
        
        // Obtener pagos del mes seleccionado o actual
        $contracts = $this->paymentModel->getMonthlyPayments($month, $year, $filters);
        
        // Formatear datos de contratos
        foreach ($contracts as &$contract) {
            $contract['calculated_amount'] = (float)($contract['calculated_amount'] ?? 0);
            $contract['multiplier_factor'] = (float)($contract['multiplier_factor'] ?? 0);
            $contract['euro_rate_value'] = (float)($contract['euro_rate_value'] ?? 0);
            
            // Determinar estado de pago
            $contract['payment_status_text'] = $this->getPaymentStatusText($contract['payment_status'], $contract['payment_date']);
        }
        
        // Obtener estadísticas del mes seleccionado
        $statistics = $this->paymentModel->getMonthlyStatistics($month, $year, $filters);
        $statistics['total_amount'] = (float)($statistics['total_amount'] ?? 0);
        
        // Obtener zonas
        $zones = $this->zoneModel->getAll();
        
        // Obtener sectores si hay filtro de zona
        $sectors = [];
        if (!empty($zoneFilter)) {
            $sectors = $this->sectorModel->getByZone((int)$zoneFilter);
        }
        
        // Mes y año para mostrar en la vista
        $displayMonth = $month ?? (int)date('m');
        $displayYear = $year ?? (int)date('Y');
        
        // Nombres de meses en español
        $monthsSpanish = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $currentMonthSpanish = $monthsSpanish[$displayMonth];
        
        // Obtener años fiscales disponibles para el selector
        $fiscalYearModel = new \App\Models\FiscalYearModel();
        $fiscalYears = $fiscalYearModel->getAll();
        
        $data = [
            'title' => $title,
            'contracts' => $contracts,
            'statistics' => $statistics,
            'zones' => $zones,
            'sectors' => $sectors,
            'fiscal_years' => $fiscalYears,
            'months' => $monthsSpanish,
            'contract_type' => $contractType,
            'filters' => [
                'zone_id' => $zoneFilter,
                'sector_id' => $sectorFilter,
                'show_delinquent' => $showDelinquent,
                'month' => $monthFilter,
                'year' => $yearFilter
            ],
            'current_month' => $displayMonth,
            'current_month_spanish' => $currentMonthSpanish,
            'current_year' => $displayYear
        ];
        
        $this->view('Planning/Index', $data);
    }
    
    /**
     * Determina el texto del estado de pago
     */
    private function getPaymentStatusText(string $status, string $paymentDate): string {
        if ($status === 'paid') {
            return 'Pagado';
        }
        
        if ($status === 'cancelled') {
            return 'Cancelado';
        }
        
        if ($status === 'pending') {
            $now = new \DateTime();
            $dueDate = new \DateTime($paymentDate);
            
            if ($dueDate < $now) {
                return 'Moroso';
            } else {
                return 'Pendiente';
            }
        }
        
        return ucfirst($status);
    }
    
    /**
     * Obtiene los sectores de una zona (AJAX)
     */
    public function getSectorsByZone(int $zoneId): void {
        $sectors = $this->sectorModel->getByZone($zoneId);
        
        $this->json([
            'success' => true,
            'sectors' => $sectors
        ]);
    }
}

