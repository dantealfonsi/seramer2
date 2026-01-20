<?php
require_once __DIR__ . '/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$table = 'daily_cash_registers';
$stmt = $conn->query("DESCRIBE $table");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($result, JSON_PRETTY_PRINT);
?>
