<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/FiscalYearController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $controller = new FiscalYearController();
    $result = $controller->delete((int)$_POST['id']);
    
    // Using session to communicate result back to index (index.php could check this)
    // Actually, following the project pattern, we might just redirect.
    // I'll assume simple redirect for now, but adding a flash message if possible.
    if ($result['success']) {
        // Redirección exitosa
    }
}

header('Location: index.php');
exit;
