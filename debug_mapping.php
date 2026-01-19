<?php
require_once 'config/Database.php';
$db = new Database();

echo "Contracts count: " . $db->fetchOne("SELECT COUNT(*) FROM contracts") . "\n";
echo "Contract locations count: " . $db->fetchOne("SELECT COUNT(*) FROM contract_locations") . "\n";
echo "Awardees count: " . $db->fetchOne("SELECT COUNT(*) FROM awardees") . "\n";

$sql = "SELECT 
            cl.stall_id, 
            c.awardee_id,
            c.status,
            c.end_date
        FROM 
            contract_locations cl
        INNER JOIN 
            contracts c ON cl.contract_id = c.id";

$results = $db->fetchAll($sql);
echo "Raw Mapping (without awardee join):\n";
print_r($results);
?>
