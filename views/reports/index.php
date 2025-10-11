<?php
// index.php - Punto de entrada y enrutador simple

// Requerir el controlador
require_once __DIR__ . '/../../controllers/ReportController.php';

// Instanciar el controlador
$controller = new ReportController();

// Determinar la acción a realizar
// Si no se especifica una acción, por defecto se va al editor.
$action = isset($_GET['action']) ? $_GET['action'] : 'edit';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Enrutador simple para llamar a los métodos del controlador
switch ($action) {
    case 'view':
        // Llama a la acción para ver un reporte con un ID específico
        if ($id > 0) {
            $controller->viewAction($id);
        } else {
            echo "Error: Se requiere un ID de infracción para visualizar el reporte.";
        }
        break;
    case 'edit':
    default:
        // Llama a la acción para editar los reportes
        $controller->editAction();
        break;
}
