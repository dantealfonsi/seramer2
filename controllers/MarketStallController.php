<?php
require_once __DIR__ . '/../models/MarketStallModel.php';
require_once __DIR__ . '/../models/SectorModel.php';
require_once __DIR__ . '/../models/ZoneModel.php';

class MarketStallController {
    private $stallModel;
    private $sectorModel;
    private $zoneModel;

    public function __construct() {
        $this->stallModel = new MarketStallModel();
        $this->sectorModel = new SectorModel();
        $this->zoneModel = new ZoneModel();
    }

    public function index() {
        $filters = [
            'stall_number' => $_GET['stall_number'] ?? '',
            'sector_id' => $_GET['sector_id'] ?? '',
            'zone_id' => $_GET['zone_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        $stalls = $this->stallModel->getAll($filters);
        $zones = $this->zoneModel->getAll();
        $sectors = $this->sectorModel->getAll();

        return [
            'page_title' => 'Gestión de Locales',
            'stalls' => $stalls,
            'zones' => $zones,
            'sectors' => $sectors,
            'filters' => $filters
        ];
    }

    public function create() {
        $zones = $this->zoneModel->getAll();
        $sectors = $this->sectorModel->getAll();
        return [
            'page_title' => 'Registrar Nuevo Local',
            'zones' => $zones,
            'sectors' => $sectors
        ];
    }

    public function store($data) {
        if (empty($data['stall_number']) || empty($data['sector_id'])) {
            return ['success' => false, 'message' => 'Número de local y sector son requeridos'];
        }

        // Check status default
        if (empty($data['status'])) {
            $data['status'] = 'vacant';
        }

        $id = $this->stallModel->create($data);
        if ($id) {
            return ['success' => true, 'message' => 'Local registrado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al registrar el local'];
    }

    public function edit($id) {
        $stall = $this->stallModel->getById($id);
        if (!$stall) return null;

        $zones = $this->zoneModel->getAll();
        $sectors = $this->sectorModel->getAll();
        return [
            'page_title' => 'Editar Local',
            'stall' => $stall,
            'zones' => $zones,
            'sectors' => $sectors
        ];
    }

    public function update($id, $data) {
        if (empty($data['stall_number']) || empty($data['sector_id'])) {
            return ['success' => false, 'message' => 'Número de local y sector son requeridos'];
        }

        if ($this->stallModel->update($id, $data)) {
            return ['success' => true, 'message' => 'Local actualizado correctamente'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el local'];
    }

    public function delete($id) {
         if ($this->stallModel->deleteStall($id)) {
            return ['success' => true, 'message' => 'Local eliminado'];
         }
         // Usually fails if linked to contracts, model handles constraints log/logic? 
         // Model `deleteStall` does check specific constraints or DB throws error.
         return ['success' => false, 'message' => 'No se puede eliminar el local (posiblemente asignado)'];
    }
    
    public function getSectorsByZone($zoneId) {
        return $this->sectorModel->getByZone($zoneId);
    }
}
