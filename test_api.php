<?php
// Simulate the GET request
$_GET['getInfractionsByAwardee'] = 1;
$_GET['awardeeId'] = 2;

// Capture output
ob_start();
require_once 'views/infractions/api_infractions.php';
$output = ob_get_clean();

echo "API Output for awardeeId=2:\n";
echo $output;
?>
