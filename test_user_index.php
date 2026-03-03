<?php
session_start();
$_SESSION['user_id'] = 6; // devcob
require_once __DIR__ . '/controllers/UserController.php';
$userController = new UserController();
$result = $userController->index();
print_r($result);
