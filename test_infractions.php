<?php
require_once 'config/Database.php';
require_once 'models/InfractionsModel.php';

$model = new InfractionsModel();

echo "Testing getStallsList()...\n";
$stalls = $model->getStallsList();
if (empty($stalls)) {
    echo "Stalls list is empty.\n";
} else {
    echo "First stall:\n";
    print_r($stalls[0]);
}

echo "\nTesting getAwardeesList()...\n";
$awardees = $model->getAwardeesList();
if (empty($awardees)) {
    echo "Awardees list is empty.\n";
} else {
    echo "First awardee:\n";
    print_r($awardees[0]);
}

echo "\nTesting getAll()...\n";
$infractions = $model->getAll(1, 1);
if (empty($infractions)) {
    echo "Infractions list is empty.\n";
} else {
    echo "First infraction:\n";
    // Show only relevant fields
    $first = $infractions[0];
    echo "Adjudicatory Name: " . $first['adjudicatory_name'] . "\n";
}
?>
