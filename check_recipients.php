<?php
require 'c:/xampp/htdocs/seramer2/config/Database.php';
$db = new Database();
try {
    $res = $db->fetchAll('SELECT DISTINCT recipient_user_id FROM notifications');
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
