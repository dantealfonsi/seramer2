<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/DepartmentController.php';

$controller = new DepartmentController();
// Get ID from query param as it acts as route param
$id = $_GET['id'] ?? null;
if ($id) {
    $controller->update($id);
} else {
    // Handle error or redirect
    header('Location: ' . url('views/departments/index.php'));
}
