<?php
require_once __DIR__ . '/../../models/SectorModel.php';
require_once __DIR__ . '/../../models/MarketStallModel.php';
require_once __DIR__ . '/../../models/AwardeeModel.php';
require_once __DIR__ . '/../../controllers/ContractController.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_sectors':
        $zoneId = $_GET['zone_id'] ?? 0;
        $model = new SectorModel();
        echo json_encode($model->getAll(['zone_id' => $zoneId]));
        break;
        
    case 'get_stalls':
        $sectorId = $_GET['sector_id'] ?? 0;
        $model = new MarketStallModel();
        echo json_encode($model->getAll(['sector_id' => $sectorId, 'status' => 'vacant']));
        break;
        
    case 'create_stall':
        $data = json_decode(file_get_contents('php://input'), true);
        $model = new MarketStallModel();
        $id = $model->create([
            'sector_id' => $data['sector_id'],
            'stall_number' => $data['stall_number'],
            'location_description' => $data['description'] ?? '',
            'status' => 'vacant'
        ]);
        echo json_encode(['success' => (bool)$id, 'id' => $id]);
        break;
        
    case 'get_awardees':
        $model = new AwardeeModel();
        echo json_encode($model->getAll());
        break;

    case 'create_awardee':
        $data = json_decode(file_get_contents('php://input'), true);
        $model = new AwardeeModel();
        $id = $model->create($data);
        if ($id) {
            $awardee = $model->getById($id);
            echo json_encode(['success' => true, 'id' => $id, 'awardee' => $awardee]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear adjudicatario (posible cédula duplicada)']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
exit;
