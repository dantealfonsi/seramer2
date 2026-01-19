<?php
require_once __DIR__ . '/../models/CashRegisterModel.php';
require_once __DIR__ . '/../models/DailyCashRegisterModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class DailyCashController {
    private $cashRegisterModel;
    private $dailyCashModel;
    
    public function __construct() {
        $this->cashRegisterModel = new CashRegisterModel();
        $this->dailyCashModel = new DailyCashRegisterModel();
    }
    
    public function index() {
        // Filters
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? date('Y-m-d'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
        ];
        
        // Get Report Data
        $reportData = $this->dailyCashModel->getDailyReport($filters);
        
        // Calculate Totals for Cards
        $totalInitial = 0;
        $totalCollected = 0;
        $maxTransaction = 0;
        
        foreach ($reportData as $row) {
            $totalInitial += (float)$row['initial_amount'];
            $totalCollected += (float)$row['total_collected'];
            if ((float)$row['max_amount'] > $maxTransaction) {
                $maxTransaction = (float)$row['max_amount'];
            }
        }
        
        return [
            'page_title' => 'Cierre de Caja',
            'reportData' => $reportData,
            'filters' => $filters,
            'summary' => [
                'total_initial' => $totalInitial,
                'total_final' => $totalCollected, 
                'total_max' => $maxTransaction
            ]
        ];
    }
    
    // Legacy methods kept valid to prevent 404s but should be unused 
    public function openForm($cashRegisterId) { header('Location: index.php'); exit; }
    public function storeOpen($data) { header('Location: index.php'); exit; }
    public function closeForm($id) { header('Location: index.php'); exit; }
    public function storeClose($data) { header('Location: index.php'); exit; }
}
