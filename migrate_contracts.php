<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=seramermvc', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if columns exist first
    $cols = $db->query('DESCRIBE contracts')->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('status', $cols)) {
        $db->exec("ALTER TABLE contracts ADD COLUMN status ENUM('active', 'renewed', 'canceled') NOT NULL DEFAULT 'active' AFTER contract_mode");
        echo "Column 'status' added.\n";
    }
    
    if (!in_array('status_payment', $cols)) {
        $db->exec("ALTER TABLE contracts ADD COLUMN status_payment ENUM('up to date', 'delinquent', 'unable to pay') NOT NULL DEFAULT 'up to date' AFTER status");
        echo "Column 'status_payment' added.\n";
    }
    
    echo "Migration completed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
