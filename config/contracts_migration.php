<?php
require_once __DIR__ . '/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Create contracts table if not exists (Important if not imported by SQL)
    // Based on ContractModel fields
    $sql = "CREATE TABLE IF NOT EXISTS contracts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        awardee_id INT NOT NULL,
        fiscal_year_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        type ENUM('temporary', 'permanent') NOT NULL DEFAULT 'temporary',
        contract_mode ENUM('lease', 'concession') NOT NULL DEFAULT 'lease',
        status ENUM('active', 'expired', 'canceled', 'renewed') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (awardee_id) REFERENCES awardees(id),
        FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_year(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $conn->exec($sql);
    echo "Table 'contracts' checked/created successfully.\n";
    
    // Create other tables ...
    // Assuming migration.php or db_migrate.php handled most.
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
