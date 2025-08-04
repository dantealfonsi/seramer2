<?php
/**
 * Controlador Frontal (Router) de la Aplicación
 *
 * Este archivo es el único punto de entrada a la aplicación.
 * 1. Analiza la URL para determinar el controlador y el método.
 * 2. Verifica la autenticación y los permisos.
 * 3. Carga el controlador correspondiente para manejar la solicitud.
 */

// Incluir configuración y funciones globales
require_once __DIR__ . '/config/app.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ===================================================================
// FUNCIONES DE AUTENTICACIÓN (Tu lógica original)
// ===================================================================

/**
 * Verifica si el usuario está autenticado.
 * @return bool
 */
function isUserAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verifica si la sesión no ha expirado.
 * @return bool
 */
function isSessionValid() {
    if (!isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Tiempo de expiración en segundos (30 minutos)
    $session_timeout = 1800;
    
    if ((time() - $_SESSION['last_activity']) > $session_timeout) {
        session_unset();
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}


// ===================================================================
// LÓGICA DEL ROUTER
// ===================================================================

// 1. PARSEO DE LA URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home/index';
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

// 2. DETERMINAR CONTROLADOR Y MÉTODO
$controllerName = !empty($urlParts[0]) ? ucwords($urlParts[0]) . 'Controller' : 'HomeController';
$methodName = isset($urlParts[1]) ? $urlParts[1] : 'index';


// 3. VERIFICACIÓN DE PERMISOS
// Define las rutas que no requieren que el usuario esté logueado.
$publicRoutes = [
    'AuthController' => ['index', 'login', 'processLogin'], // Métodos públicos del AuthController
];

$isPublicRoute = false;
if (isset($publicRoutes[$controllerName]) && in_array($methodName, $publicRoutes[$controllerName])) {
    $isPublicRoute = true;
}

// Si la ruta NO es pública, verificamos la sesión
if (!$isPublicRoute) {
    if (!isUserAuthenticated() || !isSessionValid()) {
        // Si no está autenticado, redirigir al login
        // Usamos la URL base de tu config/app.php
        header('Location: ' . BASE_URL . 'auth/login');
        exit();
    }
}


// 4. CARGAR Y EJECUTAR EL CONTROLADOR
$controllerFile = 'controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();

        if (method_exists($controller, $methodName)) {
            // Obtener los parámetros de la URL (ej: /complaints/edit/15)
            $params = array_slice($urlParts, 2);
            // Llamar al método del controlador
            call_user_func_array([$controller, $methodName], $params);
        } else {
            // Error 404: Método no encontrado
            http_response_code(404);
            echo "Error 404: Recurso no encontrado (el método '$methodName' no existe en $controllerName).";
        }
    } else {
        // Error 500: Clase del controlador no encontrada en el archivo
        http_response_code(500);
        echo "Error del servidor: La clase del controlador '$controllerName' no se encontró.";
    }
} else {
    // Error 404: Archivo del controlador no encontrado
    http_response_code(404);
    echo "Error 404: Recurso no encontrado (el archivo del controlador '$controllerFile' no existe).";
}
