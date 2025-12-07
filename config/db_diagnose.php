<?php
$hosts = ['localhost', '127.0.0.1'];
$ports = [3306, 3307, 3308, 4306, 8889];
$username = 'root';
$password = '';
$dbname = 'seramermvc';

echo "Diagnosing DB Connection...\n";

$success = false;
foreach ($hosts as $host) {
    foreach ($ports as $port) {
        echo "Trying $host:$port... ";
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
            $conn = new PDO($dsn, $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "SUCCESS!\n";
            $success = true;
            
            // If success, identifying users
            echo "\n--- Users in Liquidacion (Dept 1) ---\n";
            $stmt = $conn->query("
                SELECT u.username, u.email, d.name as dept_name 
                FROM users u 
                JOIN user_departments ud ON u.id = ud.user_id 
                JOIN departments d ON ud.department_id = d.id 
                WHERE d.name LIKE '%Liquidacion%' OR d.id = 1
            ");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($users as $u) {
                echo "User: {$u['username']} (Email: {$u['email']})\n";
            }
            if (empty($users)) echo "No users found for Liquidacion.\n";

            echo "\n--- Users in Cobranza (Dept 2) ---\n";
            $stmt = $conn->query("
                SELECT u.username, u.email, d.name as dept_name 
                FROM users u 
                JOIN user_departments ud ON u.id = ud.user_id 
                JOIN departments d ON ud.department_id = d.id 
                WHERE d.name LIKE '%Cobranza%' OR d.id = 2
            ");
             $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($users as $u) {
                echo "User: {$u['username']} (Email: {$u['email']})\n";
            }
            if (empty($users)) echo "No users found for Cobranza.\n";
            
            // Reset 'devliq' and 'devcob' passwords to '123456'
            $newHash = password_hash('123456', PASSWORD_DEFAULT);
            $conn->exec("UPDATE users SET password_hash = '$newHash' WHERE username IN ('devliq', 'devcob', 'mmaria', 'cperez')");
            echo "\nPasswords for 'devliq', 'devcob', 'mmaria', 'cperez' reset to '123456'.\n";

            break 2;
        } catch (PDOException $e) {
            echo "Failed (" . $e->getMessage() . ")\n";
        }
    }
}

if (!$success) {
    echo "\nAll connection attempts failed.\n";
}
