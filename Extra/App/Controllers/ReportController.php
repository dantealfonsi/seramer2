<?php
/**
 * Controlador de Reportes
 * 
 * Gestiona los reportes del sistema
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use App\Models\ReportModel;

class ReportController extends Controller {
    private ReportModel $reportModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->reportModel = new ReportModel();
    }
    
    /**
     * Muestra el menú principal de reportes
     */
    public function index(): void {
        $data = [
            'title' => 'Reportes'
        ];
        
        $this->view('Report/Index', $data);
    }
    
    /**
     * Muestra el reporte de contratos morosos
     */
    public function delinquentContracts(): void {
        $contracts = $this->reportModel->getDelinquentContracts();
        
        $data = [
            'title' => 'Reporte de Contratos Morosos',
            'contracts' => $contracts
        ];
        
        $this->view('Report/DelinquentContracts', $data);
    }
    
    /**
     * Muestra el reporte de total acumulado por zona
     */
    public function zoneAccumulated(): void {
        // Obtener filtros
        $startDate = $this->get('start_date') ?? date('Y-m-01'); // Primer día del mes actual
        $endDate = $this->get('end_date') ?? date('Y-m-t'); // Último día del mes actual
        
        // Validar fechas
        if (empty($startDate) || empty($endDate)) {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }
        
        $zones = $this->reportModel->getZoneAccumulated($startDate, $endDate);
        
        $data = [
            'title' => 'Reporte de Total Acumulado por Zona',
            'zones' => $zones,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        
        $this->view('Report/ZoneAccumulated', $data);
    }
}

