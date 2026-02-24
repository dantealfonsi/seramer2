<?php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();
try {
    $conn->exec("ALTER TABLE market_stalls ADD COLUMN status ENUM('vacant', 'occupied', 'maintenance', 'closed') DEFAULT 'vacant' AFTER stall_number");
    echo "Column 'status' added successfully.\n";
    
    // Set 'occupied' if awardee_id is not null
    $conn->exec("UPDATE market_stalls SET status = 'occupied' WHERE awardee_id IS NOT NULL");
    echo "Stall statuses updated based on awardee_id.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
