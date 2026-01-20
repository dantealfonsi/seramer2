<?php
require_once __DIR__ . '/../models/SectorModel.php';
require_once __DIR__ . '/../models/ZoneModel.php';

class SectorController {
    private $sectorModel;
    private $zoneModel;

    public function __construct() {
        $this->sectorModel = new SectorModel();
        $this->zoneModel = new ZoneModel();
    }

    public function index() {
        $sectors = $this->sectorModel->getAll();
        return [
            'page_title' => 'Gestión de Sectores',
            'sectors' => $sectors
        ];
    }

    public function create() {
        $zones = $this->zoneModel->getAll();
        return [
            'page_title' => 'Registrar Nuevo Sector',
            'zones' => $zones
        ];
    }

    public function store($data) {
        if (empty($data['name']) || empty($data['zone_id'])) {
             return ['success' => false, 'message' => 'Nombre y zona son requeridos'];
        }

        $id = $this->sectorModel->create($data);
        if ($id) {
            return ['success' => true, 'message' => 'Sector creado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al crear el sector'];
    }

    public function edit($id) {
        $sector = $this->sectorModel->getById($id);
        if (!$sector) return null;

        $zones = $this->zoneModel->getAll();
        return [
            'page_title' => 'Editar Sector',
            'sector' => $sector,
            'zones' => $zones
        ];
    }

    public function update($id, $data) {
         if (empty($data['name']) || empty($data['zone_id'])) {
             return ['success' => false, 'message' => 'Nombre y zona son requeridos'];
        }

        if ($this->sectorModel->update($id, $data)) {
            return ['success' => true, 'message' => 'Sector actualizado'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el sector'];
    }

    public function delete($id) {
        if ($this->sectorModel->deleteSector($id)) {
            return ['success' => true, 'message' => 'Sector eliminado'];
        }
        return ['success' => false, 'message' => 'No se puede eliminar el sector (tiene locales asociados)'];
    }
}
