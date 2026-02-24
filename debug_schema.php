<?php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$result = $conn->query("DESCRIBE market_stalls")->fetchAll();
echo json_encode($result, JSON_PRETTY_PRINT);
