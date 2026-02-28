<?php
session_start();
$_SESSION['user_id'] = 2; // cperez (Admin de Cobranza)
// We need to clear superadmin manually because our script doesn't authenticate via AuthController which does that
$_SESSION['is_superadmin'] = 0; 
require_once __DIR__ . '/controllers/UserController.php';
$userController = new UserController();
$result = $userController->index();
print_r($result);
