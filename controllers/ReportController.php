<?php
// controllers/ReportController.php

// Requerir los modelos necesarios
require_once __DIR__. '/../models/ReportModel.php';
require_once __DIR__. '/../models/InfractionsModel.php';

class ReportController {

    private $reportModel;
    private $infractionModel;

    public function __construct() {
        $this->reportModel = new ReportModel();
        $this->infractionModel = new InfractionsModel(); 
    }

    /**
     * Acción para mostrar el editor de reportes.
     * Maneja la carga y guardado de los archivos .rep
     */
    public function editAction() {
        $reportFiles = $this->reportModel->getReportFiles();
        $currentReport = 'print_infraction.rep'; // Reporte por defecto
        $reportContent = '';
        $message = '';

        // Si se está guardando el contenido del reporte
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_content'])) {
            $contentToSave = $_POST['report_content'];
            $fileToSave = $_POST['report_file'];
            if ($this->reportModel->saveReportContent($fileToSave, $contentToSave)) {
                $message = "¡Archivo '$fileToSave' guardado con éxito!";
            } else {
                $message = "Error al guardar el archivo '$fileToSave'.";
            }
            $currentReport = $fileToSave;
            $reportContent = $contentToSave;
        } 
        // Si se está cargando un reporte seleccionado
        else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_file'])) {
            $currentReport = $_POST['report_file'];
            $reportContent = $this->reportModel->getReportContent($currentReport);
        } 
        // Carga inicial del reporte por defecto
        else {
            $reportContent = $this->reportModel->getReportContent($currentReport);
        }
        
        // Cargar la configuración y los campos disponibles para la plantilla actual
        $reportConfig = $this->reportModel->getReportConfig($currentReport);
        $availableFields = [];
        if ($reportConfig) {
            $availableFields = $this->reportModel->getAvailableFields($reportConfig);
        } else {
            $message .= " Advertencia: No se encontró el archivo de configuración (.sys) para '$currentReport'.";
        }

        // Cargar la vista del editor
        require __DIR__ .'/../views/reports/editor.php';
    }

    /**
     * Acción para visualizar un reporte con datos específicos.
     * @param int $id El ID 
     */
    public function viewAction($id) {
        $reportName = isset($_GET['report']) ? $_GET['report'] : 'print_infraction.rep'; // Permitir seleccionar reporte por URL

        // 1. Obtener la configuración del reporte
        $reportConfig = $this->reportModel->getReportConfig($reportName);
        if (!$reportConfig) {
            echo "Error: No se pudo cargar la configuración para el reporte '$reportName'. Asegúrate de que exista un archivo .sys correspondiente.";
            return;
        }

        // 2. Obtener los detalles de la infracción usando la configuración
        $details = $this->reportModel->getDetails($id, $reportConfig);

        if (!$details) {
            echo "No se encontraron datos para el reporte con el ID: $id";
            return;
        }

        // 3. Obtener la plantilla del reporte
        $reportTemplate = $this->reportModel->getReportContent($_GET['report']);

        // 4. Reemplazar los placeholders en la plantilla con los datos reales
        $finalReport = $reportTemplate;
        foreach ($details as $key => $value) {
            $finalReport = str_replace('{{' . $key . '}}', htmlspecialchars($value), $finalReport);
        }

        // 5. Cargar la vista para mostrar el reporte final
        require __DIR__. '/../views/reports/view.php';
    }
}
