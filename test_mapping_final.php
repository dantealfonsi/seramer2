<?php
require_once 'config/Database.php';
require_once 'models/InspectionModel.php';
$model = new InspectionModel();
$mapping = $model->getStallAwardeeMapping();
echo "Mapping Data:\n";
print_r($mapping);
?>
