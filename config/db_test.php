<?php
$host = '127.0.0.1';
$db_name = 'seramermvc';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to $db_name at $host";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
