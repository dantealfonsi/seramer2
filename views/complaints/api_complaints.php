<?php
session_start();
require_once __DIR__ . '/../../controllers/ComplaintsController.php';

$complaintsController = new ComplaintsController();

if (isset($_GET['getAwardeeByStall']) && isset($_GET['stallId'])) {
    $stallId = (int)$_GET['stallId'];
    $awardee = $complaintsController->getAwardeeByStall($stallId);
    echo json_encode($awardee);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addHistory') {
    $data = [
        'complaint_id' => (int)$_POST['complaint_id'],
        'admin_user_id' => $_SESSION['user_id'],
        'action_type' => $_POST['action_type'],
        'action_description' => $_POST['action_description'],
        'action_result' => $_POST['action_result']
    ];
    $result = $complaintsController->addHistory($data);
    echo json_encode($result);
    exit;
}
?>
