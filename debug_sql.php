<?php
require_once 'config/Database.php';
$db = new Database();
$sql = "SELECT 
            cl.stall_id, 
            a.id as awardee_id, 
            CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
            c.status,
            c.end_date
        FROM 
            contract_locations cl
        INNER JOIN 
            contracts c ON cl.contract_id = c.id
        INNER JOIN 
            awardees a ON c.awardee_id = a.id";

try {
    $results = $db->fetchAll($sql);
    echo "Results count: " . count($results) . "\n";
    print_r($results);
} catch (Exception $e) {
    echo "SQL Error: " . $e->getMessage() . "\n";
}
?>
