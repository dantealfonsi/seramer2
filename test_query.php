<?php
require 'c:/xampp/htdocs/seramer2/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT * FROM users WHERE username='renebello'");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
