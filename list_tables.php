<?php
require_once 'config/Database.php';
$db = new Database();
$tables = $db->fetchAll("SHOW TABLES");
print_r($tables);
?>
