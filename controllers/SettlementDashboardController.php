<?php
/**
 * SettlementDashboardController
 * 
 * Controlador para el dashboard del módulo de liquidación
 */
class SettlementDashboardController {
    
    public function __construct() {
        // La protección de sesión ya se maneja en index.php
        // Pero podemos agregar lógica adicional si es necesario
    }
    
    public function index() {
        // Cargar el dashboard de liquidación
        require_once 'views/dashboard/settlement.php';
    }
}
