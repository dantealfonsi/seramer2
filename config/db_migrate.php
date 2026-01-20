<?php
require_once __DIR__ . '/Database.php';

class DBMigrator {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    private function tableExists($table) {
        try {
            $result = $this->pdo->query("SELECT 1 FROM $table LIMIT 1");
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    private function columnExists($table, $column) {
        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
            $stmt->execute([$column]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function migrate() {
        echo "Starting migration...\n";

        // 1. New Tables (from Extra)
        // Using definitions from seramer29-11-2025.sql
        $tables = [
            'academic_degrees' => "CREATE TABLE `academic_degrees` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            
            'academic_specializations' => "CREATE TABLE `academic_specializations` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

            'attendance' => "CREATE TABLE `attendance` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `staff_id` int(11) NOT NULL,
              `date` date NOT NULL,
              `check_in` time DEFAULT NULL,
              `check_out` time DEFAULT NULL,
              `is_special` tinyint(1) DEFAULT 0,
              `created_at` timestamp NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `idx_staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'audit_log' => "CREATE TABLE `audit_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `action` varchar(50) NOT NULL,
              `table_affected` varchar(50) NOT NULL,
              `record_id` int(11) DEFAULT NULL,
              `old_values` longtext,
              `new_values` longtext,
              `ip_address` varchar(45) DEFAULT NULL,
              `user_agent` text DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

            'cash_registers' => "CREATE TABLE `cash_registers` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `name` varchar(100) NOT NULL,
              `status` enum('active','inactive','maintenance') DEFAULT 'active',
              `created_at` timestamp NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

            'contracts' => "CREATE TABLE `contracts` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `awardee_id` int(11) NOT NULL,
              `fiscal_year_id` int(11) NOT NULL,
              `start_date` date NOT NULL,
              `end_date` date NOT NULL,
              `type` enum('simultaneous','advance') DEFAULT NULL,
              `contract_mode` enum('monthly','weekly') DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            
            'contract_locations' => "CREATE TABLE `contract_locations` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `contract_id` int(11) NOT NULL,
              `stall_id` int(11) NOT NULL,
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
             
             'contract_payments' => "CREATE TABLE `contract_payments` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `contract_id` int(11) NOT NULL,
              `payment_reference` varchar(50) DEFAULT NULL,
              `euro_rate_id` int(11) DEFAULT NULL,
              `payment_date` date NOT NULL,
              `amount` decimal(12,2) NOT NULL,
              `status` enum('pending','paid','cancelled','refunded') DEFAULT 'pending',
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'divisions' => "CREATE TABLE `divisions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `department_id` int(11) NOT NULL,
              `name` varchar(255) NOT NULL,
              `description` text DEFAULT NULL,
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'euro_rates' => "CREATE TABLE `euro_rates` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `bs_value` decimal(10,2) NOT NULL,
              `month` varchar(20) DEFAULT NULL,
              `year` varchar(4) DEFAULT NULL,
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
             
             'fiscal_year' => "CREATE TABLE `fiscal_year` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `start_date` date DEFAULT NULL,
              `end_date` date DEFAULT NULL,
              `year` varchar(4) DEFAULT NULL,
              `status` enum('active','inactive') DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'job_positions' => "CREATE TABLE `job_positions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'market_stalls' => "CREATE TABLE `market_stalls` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `sector_id` int(11) NOT NULL,
              `stall_number` varchar(50) NOT NULL,
              `location_description` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'sectors' => "CREATE TABLE `sectors` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `zone_id` int(11) NOT NULL,
              `name` varchar(100) NOT NULL,
              `description` text DEFAULT NULL,
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
             
             'staff_department_history' => "CREATE TABLE `staff_department_history` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `staff_id` int(11) NOT NULL,
              `department_id` int(11) NOT NULL,
              `start_date` date NOT NULL,
              `end_date` date DEFAULT NULL,
              `reason` text DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'user_departments' => "CREATE TABLE `user_departments` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `department_id` int(11) NOT NULL,
              `status` enum('active','inactive') DEFAULT 'inactive',
              `created_at` timestamp NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'vacations' => "CREATE TABLE `vacations` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `staff_id` int(11) NOT NULL,
              `start_date` date NOT NULL,
              `end_date` date NOT NULL,
              `status` enum('requested','approved','rejected') DEFAULT 'requested',
              `created_at` timestamp NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",

             'zones' => "CREATE TABLE `zones` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `description` text DEFAULT NULL,
              PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
        ];

        foreach ($tables as $name => $sql) {
            if (!$this->tableExists($name)) {
                echo "Creating table: $name... ";
                try {
                    $this->pdo->exec($sql);
                    echo "DONE.\n";
                } catch (PDOException $e) {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            } else {
                echo "Table $name already exists. Skipping.\n";
            }
        }

        // 2. ALTER Existing Tables
        $this->updateUsersTable();
        $this->updateStaffTable();
        $this->updateDepartmentsTable();
        
        echo "Migration completed.\n";
    }

    private function updateUsersTable() {
        echo "Checking 'users' table columns... ";
        $updates = [];
         if (!$this->columnExists('users', 'staff_id')) {
            $updates[] = "ADD COLUMN `staff_id` int(11) DEFAULT NULL";
        }
        if (!$this->columnExists('users', 'password_hash')) {
             // Check if using 'password' instead, maybe rename or add alias? 
             // We'll add password_hash if missing.
             $updates[] = "ADD COLUMN `password_hash` varchar(255) NOT NULL DEFAULT ''";
        }
        if (!$this->columnExists('users', 'last_login')) {
            $updates[] = "ADD COLUMN `last_login` datetime DEFAULT NULL";
        }
        if (!$this->columnExists('users', 'password_reset_token')) {
            $updates[] = "ADD COLUMN `password_reset_token` varchar(255) DEFAULT NULL";
        }
        if (!$this->columnExists('users', 'password_reset_expires')) {
            $updates[] = "ADD COLUMN `password_reset_expires` datetime DEFAULT NULL";
        }
         if (!$this->columnExists('users', 'status')) {
            $updates[] = "ADD COLUMN `status` enum('active','inactive') DEFAULT 'inactive'";
        }
        
        if (!empty($updates)) {
            $sql = "ALTER TABLE `users` " . implode(", ", $updates);
            try {
                $this->pdo->exec($sql);
                echo "Updated with new columns.\n";
            } catch(PDOException $e) {
                echo "ERROR updating users: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Up to date.\n";
        }
    }

    private function updateStaffTable() {
        echo "Checking 'staff' table columns... ";
        if (!$this->tableExists('staff')) {
             $sql = "CREATE TABLE `staff` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `academic_degree_id` int(11) DEFAULT NULL,
              `academic_specialization_id` int(11) DEFAULT NULL,
              `job_position_id` int(11) NOT NULL,
              `department_id` int(11) NOT NULL,
              `division_id` int(11) DEFAULT NULL,
              `id_number` varchar(20) NOT NULL,
              `first_name` varchar(100) NOT NULL,
              `middle_name` varchar(100) DEFAULT NULL,
              `last_name` varchar(100) NOT NULL,
              `second_last_name` varchar(100) DEFAULT NULL,
              `birth_date` date DEFAULT NULL,
              `gender` tinyint(1) DEFAULT NULL,
              `hire_date` date NOT NULL,
              `termination_date` date DEFAULT NULL,
              `status` enum('active','inactive','vacation','leave','suspended') DEFAULT 'active',
              `created_at` timestamp NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
             try {
                $this->pdo->exec($sql);
                echo "Created 'staff' table.\n";
            } catch(PDOException $e) {
                echo "ERROR creating staff: " . $e->getMessage() . "\n";
            }
            return;
        }

        $updates = [];
        if (!$this->columnExists('staff', 'academic_degree_id')) $updates[] = "ADD COLUMN `academic_degree_id` int(11) DEFAULT NULL";
        if (!$this->columnExists('staff', 'division_id')) $updates[] = "ADD COLUMN `division_id` int(11) DEFAULT NULL";
        if (!$this->columnExists('staff', 'hire_date')) $updates[] = "ADD COLUMN `hire_date` date NOT NULL DEFAULT '2000-01-01'";

        if (!empty($updates)) {
             $sql = "ALTER TABLE `staff` " . implode(", ", $updates);
            try {
                $this->pdo->exec($sql);
                echo "Updated with new columns.\n";
            } catch(PDOException $e) {
                echo "ERROR updating staff: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Up to date.\n";
        }
    }

    private function updateDepartmentsTable() {
         echo "Checking 'departments' table columns... ";
         $updates = [];
         if (!$this->columnExists('departments', 'manager_id')) {
             $updates[] = "ADD COLUMN `manager_id` int(11) DEFAULT NULL";
         }
         if (!$this->columnExists('departments', 'shift_type')) {
             $updates[] = "ADD COLUMN `shift_type` varchar(50) DEFAULT NULL";
         }
         
          if (!empty($updates)) {
             $sql = "ALTER TABLE `departments` " . implode(", ", $updates);
            try {
                $this->pdo->exec($sql);
                echo "Updated with new columns.\n";
            } catch(PDOException $e) {
                echo "ERROR updating departments: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Up to date.\n";
        }
    }
}

$migrator = new DBMigrator();
$migrator->migrate();
