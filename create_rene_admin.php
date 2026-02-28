<?php
require_once 'config/Database.php';
$db = new Database();
$pdo = $db->getConnection();

// --- CONFIG ---
$firstName = 'Rene';
$lastName = 'Bello';
$username = 'renebello';
$email = 'rene.bello@seramer.com';
$passwordText = 'password123';
$passwordHash = password_hash($passwordText, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    // 1. Find valid department and job position
    $deptStmt = $pdo->query("SELECT id FROM departments LIMIT 1");
    $dept = $deptStmt->fetch(PDO::FETCH_ASSOC);
    if (!$dept) throw new Exception("No departments found.");
    $departmentId = $dept['id'];

    $jobStmt = $pdo->query("SELECT id FROM job_positions LIMIT 1");
    $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
    if (!$job) throw new Exception("No job positions found.");
    $jobPositionId = $job['id'];

    // 2. Create Staff
    $stmt = $pdo->prepare("INSERT INTO staff (first_name, last_name, department_id, job_position_id, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
    $stmt->execute([$firstName, $lastName, $departmentId, $jobPositionId]);
    $staffId = $pdo->lastInsertId();

    // 3. Create User
    $stmt = $pdo->prepare("INSERT INTO users (staff_id, username, password_hash, email, status, is_superadmin, created_at) VALUES (?, ?, ?, ?, 'active', 1, NOW())");
    $stmt->execute([$staffId, $username, $passwordHash, $email]);
    $userId = $pdo->lastInsertId();

    // 4. Assign department to user_departments as well
    $stmt = $pdo->prepare("INSERT INTO user_departments (user_id, department_id, status, created_at) VALUES (?, ?, 'active', NOW())");
    $stmt->execute([$userId, $departmentId]);

    $pdo->commit();
    echo "SUCCESS: Superadmin user created for $firstName $lastName (username: $username, password: $passwordText)\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
