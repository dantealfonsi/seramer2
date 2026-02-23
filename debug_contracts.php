<?php
require_once __DIR__ . '/models/ContractModel.php';
$model = new ContractModel();
$contracts = $model->getAll();
if (!empty($contracts)) {
    echo "Keys in first contract:\n";
    print_r(array_keys($contracts[0]));
} else {
    echo "No contracts found.\n";
}
