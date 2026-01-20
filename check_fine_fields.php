<?php
require_once __DIR__ . '/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("DESCRIBE fine_payments");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) echo $row['Field'] . "\n";
?>
