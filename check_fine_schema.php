<?php
require_once __DIR__ . '/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("DESCRIBE fine_payments");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
