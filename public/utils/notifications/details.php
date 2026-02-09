<?php
// details.php - Router central para ver detalles de entidades

// 1. Obtener los parámetros de la URL de forma segura
$entity_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$entity_type_raw = filter_input(INPUT_GET, 'type', FILTER_DEFAULT);
$notif_id = filter_input(INPUT_GET, 'notif_id', FILTER_VALIDATE_INT);

if ($notif_id) {
    require_once __DIR__ . '/../../../models/NotificationModel.php';
    (new NotificationModel())->markAsRead($notif_id);
}

// Normalizar el tipo a la entidad base (ej: 'infraction_new' -> 'infraction')
$entity_type = null;

if ($entity_type_raw) {
    // Tomamos la primera parte si usa guiones bajos (ej: infraction_new -> infraction)
    $parts = explode('_', $entity_type_raw);
    $entity_type = strtolower($parts[0]);
}

// 2. Validar Parámetros
if (!$entity_id || $entity_id <= 0 || !$entity_type) {
    // Si falta el ID o el tipo, o son inválidos, redirigir al index del dashboard
    header("Location: /seramer2/views/dashboard/index.php?error=invalid_notification");
    exit;
}

// 3. Mapeo del Enrutamiento
// Define dónde se encuentra la vista real para cada tipo de entidad
$routes = [
    'infraction' => '/seramer2/views/infractions/view.php',
    'complaint' => '/seramer2/views/complaints/view.php',
    'alert'     => '/seramer2/views/alerts/view.php',
    'citation'  => '/seramer2/views/citations/view.php',
    'sanction'  => '/seramer2/views/billing/fine_details.php',
];

// 4. Determinar la URL de destino
if (isset($routes[$entity_type])) {
    // La URL de destino real con el parámetro 'id'
    $target_url = $routes[$entity_type] . "?id=" . $entity_id;
    
    // 5. Redirigir al destino específico
    header("Location: " . $target_url);
    exit;

} else {
    // Si el tipo de entidad no está mapeado, volver al dashboard
    header("Location: /seramer2/views/dashboard/index.php?error=unknown_type");
    exit;
}
?>