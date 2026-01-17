<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/DepartmentController.php';

$controller = new DepartmentController();
$id = $_POST['id'] ?? null;
if ($id) {
    $controller->delete($id);
} else {
    header('Location: ' . url('views/departments/index.php'));
}
