<?php
require 'c:/xampp/htdocs/seramer2/config/Database.php';
$db = new Database();
try {
    $res = $db->fetchAll('DESCRIBE notifications');
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
