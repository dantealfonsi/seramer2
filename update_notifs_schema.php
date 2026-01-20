<?php
require_once __DIR__ . '/config/Database.php';

$db = new Database();
$pdo = $db->getConnection();

echo "Actualizando tabla notifications...\n";

try {
    // 1. Alter recipient_user_id to be nullable
    $pdo->exec("ALTER TABLE notifications MODIFY recipient_user_id INT(11) NULL");
    echo " recipient_user_id modificado a NULLABLE.\n";

    // 2. Add target_role_id (assuming fiscalization_roles uses role_id)
    // Checking if column exists first (simple check via try/catch catch-all usually, but here we just try ADD)
    try {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN target_role_id INT(11) NULL DEFAULT NULL AFTER recipient_user_id");
        echo " Columna target_role_id agregada.\n";
    } catch (PDOException $e) {
        echo " Columna target_role_id ya existía o error: " . $e->getMessage() . "\n";
    }

    // 3. Add target_department_id
    try {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN target_department_id INT(11) NULL DEFAULT NULL AFTER target_role_id");
        echo " Columna target_department_id agregada.\n";
    } catch (PDOException $e) {
        echo " Columna target_department_id ya existía o error: " . $e->getMessage() . "\n";
    }

    // 4. Add is_global
    try {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN is_global TINYINT(1) NOT NULL DEFAULT 0 AFTER target_department_id");
        echo " Columna is_global agregada.\n";
    } catch (PDOException $e) {
        echo " Columna is_global ya existía o error: " . $e->getMessage() . "\n";
    }

    // 5. Add Indexes
    try {
        $pdo->exec("CREATE INDEX idx_recipient_user ON notifications(recipient_user_id)");
        $pdo->exec("CREATE INDEX idx_target_role ON notifications(target_role_id)");
        $pdo->exec("CREATE INDEX idx_target_dept ON notifications(target_department_id)");
        $pdo->exec("CREATE INDEX idx_is_global ON notifications(is_global)");
         echo " Índices creados.\n";
    } catch (PDOException $e) {
         echo " Índices ya existían o error: " . $e->getMessage() . "\n";
    }

    echo "Actualización completada.\n";

} catch (PDOException $e) {
    die("Error fatal: " . $e->getMessage());
}
