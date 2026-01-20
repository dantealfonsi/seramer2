<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/DailyCashRegisterModel.php';

$model = new DailyCashRegisterModel();
$filters = [
    'date_from' => date('Y-m-d'),
    'date_to' => date('Y-m-d')
];
$data = $model->getDailyReport($filters);
echo "Count: " . count($data) . "\n";
print_r($data);
?>
