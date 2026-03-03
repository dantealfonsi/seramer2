<?php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT id, username, is_superadmin FROM users WHERE is_superadmin = 1");
$superadmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Superadmins found: " . count($superadmins) . "\n";
print_r($superadmins);
