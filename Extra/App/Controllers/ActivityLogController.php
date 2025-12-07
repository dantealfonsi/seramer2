<?php
/**
 * Controlador de Historial de Actividades
 * 
 * Gestiona el historial de actividades de usuarios
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use App\Models\ActivityLogModel;
use App\Models\UserModel;

class ActivityLogController extends Controller {
    private ActivityLogModel $activityLogModel;
    private UserModel $userModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->activityLogModel = new ActivityLogModel();
        $this->userModel = new UserModel();
    }
    
    /**
     * Lista todas las actividades con filtros
     */
    public function index(): void {
        // Obtener filtros del GET
        $filters = [
            'user_id' => $this->get('user_id'),
            'action' => $this->get('action'),
            'table_affected' => $this->get('table_affected'),
            'date_from' => $this->get('date_from'),
            'date_to' => $this->get('date_to')
        ];
        
        // Limpiar filtros vacíos
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Paginación
        $page = max(1, (int) ($this->get('page') ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Obtener actividades
        $activities = $this->activityLogModel->getAll($filters, $perPage, $offset);
        $total = $this->activityLogModel->getTotal($filters);
        $totalPages = ceil($total / $perPage);
        
        // Debug temporal - remover después
        if (empty($activities) && $total > 0) {
            error_log("ActivityLogController: Total = $total pero activities está vacío. Filtros: " . json_encode($filters));
            error_log("ActivityLogController: Page = $page, PerPage = $perPage, Offset = $offset");
        }
        
        // Obtener datos para filtros
        $users = $this->userModel->getAll();
        $actions = $this->activityLogModel->getDistinctActions();
        $tables = $this->activityLogModel->getDistinctTables();
        
        $data = [
            'title' => 'Historial de Actividades',
            'activities' => $activities,
            'users' => $users,
            'actions' => $actions,
            'tables' => $tables,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $total,
                'per_page' => $perPage
            ]
        ];
        
        $this->view('ActivityLog/Index', $data);
    }
}

