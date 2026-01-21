<?php
/**
 * Helper de Seguridad para Control de Acceso por Contexto
 * Se incluye en el header para validar que el departamento seleccionado
 * tenga permiso de ver la página actual.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkDepartmentAccess() {
    // 1. Obtener el mapa de accesos
    $accessMap = require __DIR__ . '/../config/access_map.php';
    
    // 2. Obtener el departamento seleccionado actual
    $currentDept = $_SESSION['selected_department'] ?? null;
    $userRole = $_SESSION['user_role'] ?? '';

    // Si no hay departamento seleccionado y no es una página pública (login), dejar pasar o redirigir?
    // Asumimos que si llegaron aquí, ya pasaron AuthController::requireAuth() en header.
    // Si $currentDept es null, probablemente es un admin global sin contexto o error.
    if (!$currentDept) {
        return; // O manejar como error
    }

    // 3. Obtener la ruta relativa actual
    // Ejemplo: /seramer2/views/complaints/index.php -> views/complaints/index.php
    $requestUri = $_SERVER['PHP_SELF']; 
    // Normalizar para obtener ruta relativa desde root del proyecto
    // Asumiendo estructura c:\xampp\htdocs\seramer2\...
    // Buscamos 'views/...'
    $relativePath = '';
    if (preg_match('/views\/.*$/', $requestUri, $matches)) {
        $relativePath = $matches[0];
    } else {
        return; // No estamos en una vista controlada
    }

    // 4. Verificar reglas
    foreach ($accessMap as $path => $allowedDepts) {
        // Verificar si la ruta actual comienza con la ruta protegida (ej: views/complaints/create.php comienza con views/complaints)
        if (strpos($relativePath, $path) === 0) {
            
            // Caso especial: Admin global podría tener acceso a todo?
            // El usuario pidió "no se deberian ver cosas entre zonas".
            // Asumimos que incluso admin debe respetar el contexto seleccionado.
            
            if (!in_array($currentDept, $allowedDepts)) {
                // ACCESO DENEGADO
                // Redirigir al dashboard con error
                $_SESSION['flash_error'] = "Acceso Denegado: La zona solicitada no corresponde a tu departamento actual ($currentDept).";
                header("Location: " . url('views/dashboard/dashboard.php'));
                exit;
            }
            
            // Si encontramos una coincidencia y está permitido, terminamos el chequeo (Allow)
            return;
        }
    }
}

// Helper para generar URL absoluta (si no existe ya en app.php, lo agragamos aquí por seguridad o usamos el de app.php)
if (!function_exists('url')) {
    function url($path) {
        // Fallback simple si no está definido
        return "/seramer2/" . ltrim($path, '/');
    }
}

// Ejecutar validación automáticamente al incluir este archivo
checkDepartmentAccess();
