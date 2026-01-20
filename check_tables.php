<?php
require_once __DIR__ . '/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$tables = ['cash_registers', 'daily_cash_registers', 'daily_cash', 'fee_payments', 'fine_payments'];
foreach ($tables as $t) {
    echo "$t: ";
    try {
        $conn->query("SELECT 1 FROM $t LIMIT 1");
        echo "EXISTS\n";
    } catch (Exception $e) {
        echo "MISSING (" . $e->getMessage() . ")\n";
    }
}
?>
