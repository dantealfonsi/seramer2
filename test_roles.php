<?php
require 'c:/xampp/htdocs/seramer2/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("DESCRIBE roles");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
