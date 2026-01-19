<?php
require_once 'config/Database.php';
$db = new Database();
echo "Awardees Count: " . $db->fetchOne("SELECT COUNT(*) FROM awardees") . "\n";
echo "Specific Awardee (ID 1): \n";
print_r($db->fetchOne("SELECT * FROM awardees WHERE id = 1"));
?>
