<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/DepartmentController.php';

$controller = new DepartmentController();
$controller->store();
