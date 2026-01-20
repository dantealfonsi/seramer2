<?php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();

$out = "";

$out .= "--- STEP 1: Search Awardee Daniel ---\n";
$stmt = $conn->prepare("SELECT id, first_name, last_name FROM awardees WHERE first_name LIKE 'Daniel%'");
$stmt->execute();
$awardees = $stmt->fetchAll();
foreach ($awardees as $a) {
    $out .= "ID: " . $a['id'] . " | Name: " . $a['first_name'] . " " . $a['last_name'] . "\n";
}

if (!empty($awardees)) {
    $id = $awardees[0]['id'];
    $out .= "\n--- STEP 2: Infractions for ID $id ---\n";
    $stmt = $conn->prepare("SELECT infraction_id, infraction_description, infraction_status, status_logical, infraction_type_id FROM infractions WHERE awardee_id = ?");
    $stmt->execute([$id]);
    $infractions = $stmt->fetchAll();
    
    if (empty($infractions)) {
        $out .= "NO INFRACTIONS FOUND in table 'infractions' for awardee_id = $id\n";
    } else {
        foreach ($infractions as $i) {
            $out .= "ID: " . $i['infraction_id'] . " | Status: " . $i['infraction_status'] . " | Logical: " . $i['status_logical'] . " | TypeID: " . $i['infraction_type_id'] . " | Desc: " . $i['infraction_description'] . "\n";
            
            if ($i['infraction_type_id']) {
                $stmt2 = $conn->prepare("SELECT infraction_type_name FROM infraction_types WHERE infraction_type_id = ?");
                $stmt2->execute([$i['infraction_type_id']]);
                $type = $stmt2->fetch();
                $out .= "   -> Type Name: " . ($type ? $type['infraction_type_name'] : "NOT FOUND") . "\n";
            } else {
                $out .= "   -> Type ID is NULL/0\n";
            }
        }
    }
} else {
    $out .= "Awardee Daniel not found.\n";
}

file_put_contents('diag_output.txt', $out);
echo "Done saving to diag_output.txt\n";
?>
