<?php
$db = new PDO('mysql:host=localhost;dbname=seramermvc', 'root', '');
$cols = $db->query('DESCRIBE contracts')->fetchAll(PDO::FETCH_COLUMN);
print_r($cols);
