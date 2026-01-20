<?php
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://apis.google.com");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header('Access-Control-Allow-Methods: GET, POST');
header('Allow: GET, POST');

// Incluir el controlador y los modelos para cargar los datos de las listas
require_once __DIR__ . '/../../controllers/InfractionsController.php';
require_once __DIR__ . '/../../controllers/SanctionTypesController.php';
require_once __DIR__ . '/../../models/InfractionsModel.php';

$infractionsController = new InfractionsController();

if(isset($_POST['listAwardeeId'])){
    $result = [];
    echo json_encode($result);
}
    
if(isset($_GET['contarSancionesPorSeveridad']) && isset($_GET['awardeeId'])){
    echo json_encode ($infractionsController->contarSancionesPorSeveridad((int)$_GET['awardeeId']));
}

// endpoint para el conteo específico
if (isset($_GET['contarInfraccionAnual']) && isset($_GET['awardeeId']) && isset($_GET['infractionTypeId'])) {
    $awardeeId = (int)$_GET['awardeeId'];
    $infractionTypeId = (int)$_GET['infractionTypeId'];
    
    $count = $infractionsController->contarTipoInfraccionEspecificoAnual($awardeeId, $infractionTypeId);
        
    // Devolver el conteo como un simple JSON
    echo json_encode(['count' => $count]);
    exit;
}  

if (isset($_GET['getLatestEconomicIndicators'])) {
    echo json_encode($infractionsController->getLatestEconomicIndicators());
    exit;
}

// endpoint para obtener infracciones por adjudicatario
if (isset($_GET['getInfractionsByAwardee']) && isset($_GET['awardeeId'])) {
    $awardeeId = (int)$_GET['awardeeId'];
    $infractions = $infractionsController->getInfractionsByAwardee($awardeeId);
    echo json_encode($infractions);
    exit;
}

?>
