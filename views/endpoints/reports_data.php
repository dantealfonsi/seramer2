<?php
// views/endpoints/report_data/activity_history_data.php
// Este archivo solo debe devolver datos JSON

header('Content-Type: application/json');

// La ruta hacia el modelo debe ser la correcta
require_once __DIR__ . '../../models/UserRecordsModel.php'; 

// Cargar el modelo
$userRecordsModel = new UserRecordsModel();

// 1. Obtener filtros de la solicitud GET (vienen de AJAX)
$filters = [
    'user_id'    => $_GET['filter_user'] ?? null,
    'start_date' => $_GET['start_date'] ?? null,
    'end_date'   => $_GET['end_date'] ?? null,
];

try {
    // 2. Obtener los datos del modelo
    $records = $userRecordsModel->getRecords(array_filter($filters));
    
    // 3. Devolver la respuesta JSON de éxito
    echo json_encode([
        'success' => true,
        'data' => $records,
        'message' => 'Reporte cargado con éxito.'
    ]);

} catch (Exception $e) {
    // 4. Devolver la respuesta JSON de error
    echo json_encode([
        'success' => false,
        'data' => [],
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
}
// Asegúrate de que no haya NADA más que se imprima antes o después de este JSON.
?>