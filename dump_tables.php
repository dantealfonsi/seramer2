<?php
require_once 'config/Database.php';
$db = new Database();
echo "Contract Locations:\n";
print_r($db->fetchAll("SELECT * FROM contract_locations LIMIT 10"));
echo "\nContracts:\n";
print_r($db->fetchAll("SELECT * FROM contracts LIMIT 10"));
?>
