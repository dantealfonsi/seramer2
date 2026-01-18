<?php
require_once __DIR__ . '/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$tables = ['fine_payments', 'fee_payments'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    $stmt = $conn->query("DESCRIBE $t");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
