<?php
// views/endpoints/report_data/activity_history_data.php
// Este archivo solo debe devolver datos JSON.

// 1. Configuración de encabezado para JSON
header('Content-Type: application/json');

// Deshabilitar cualquier salida HTML accidental o error de PHP que no sea JSON.
// Esto ayuda a prevenir errores de parsing JSON en el cliente.
ob_clean();

// Habilitar manejo de errores para depuración (opcional, eliminar en producción)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// 2. Cargar el Modelo (Asegúrate que la ruta sea correcta)
// Subimos 3 niveles para llegar a la raíz del proyecto, luego entramos a models/
require_once __DIR__ . '../../models/UserRecordsModel.php'; 

// Cargar el modelo
$userRecordsModel = new UserRecordsModel();

// 3. Obtener filtros de la solicitud GET (vienen del formulario AJAX)
$filters = [
    // El 'filter_user' viene del select que creamos
    'user_id'    => $_GET['filter_user'] ?? null, 
    // Las fechas vienen de los inputs type="date"
    'start_date' => $_GET['start_date'] ?? null,
    'end_date'   => $_GET['end_date'] ?? null,
];

try {
    // 4. Obtener los datos del modelo, eliminando los filtros nulos
    $records = $userRecordsModel->getRecords(array_filter($filters));
    
    // 5. Devolver la respuesta JSON de éxito
    echo json_encode([
        'success' => true,
        'data' => $records,
        'message' => 'Reporte cargado con éxito. ' . count($records) . ' registros encontrados.'
    ]);

} catch (Exception $e) {
    // 6. Devolver la respuesta JSON de error si hay un problema en PHP o SQL
    // Esto es vital para que la función 'error' de AJAX sepa que hubo un fallo
    echo json_encode([
        'success' => false,
        'data' => [],
        'message' => 'Error de servidor al obtener datos: ' . $e->getMessage()
    ]);
    // Deberías registrar $e->getMessage() en un log de servidor.
}
// El script termina aquí sin imprimir nada más.
?>