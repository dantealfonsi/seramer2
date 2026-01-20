<?php
require_once 'config/Database.php';
$db = new Database();
$columns = $db->fetchAll("DESCRIBE market_stalls");
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
