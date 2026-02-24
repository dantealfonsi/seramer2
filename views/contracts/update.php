<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ContractController();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)$_POST['id'];
$result = $controller->update($id, $_POST);

if ($result['success']) {
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => $result['message']];
    header('Location: detail.php?id=' . $id);
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
    header('Location: edit.php?id=' . $id);
}
exit;
