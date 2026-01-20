<?php
require 'c:/xampp/htdocs/seramer2/config/Database.php';
$db = new Database();
try {
    $res = $db->fetchAll('DESCRIBE notifications');
    foreach ($res as $row) {
        echo $row['Field'] . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
