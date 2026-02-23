<?php
require_once __DIR__ . '/../models/ZoneModel.php';

class ZoneController {
    private $zoneModel;

    public function __construct() {
        $this->zoneModel = new ZoneModel();
    }

    public function index() {
        $filters = [
            'name' => $_GET['name'] ?? ''
        ];

        $zones = $this->zoneModel->getAll($filters);
        return [
            'page_title' => 'Gestión de Zonas',
            'zones' => $zones,
            'filters' => $filters
        ];
    }

    public function create() {
        return ['page_title' => 'Registrar Nueva Zona'];
    }

    public function store($data) {
        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'El nombre de la zona es requerido'];
        }

        $id = $this->zoneModel->create($data);
        if ($id) {
             return ['success' => true, 'message' => 'Zona creada exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al crear la zona'];
    }

    public function edit($id) {
        $zone = $this->zoneModel->getById($id);
        if (!$zone) return null;
        
        return [
            'page_title' => 'Editar Zona',
            'zone' => $zone
        ];
    }

    public function update($id, $data) {
        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'El nombre de la zona es requerido'];
        }

        if ($this->zoneModel->update($id, $data)) {
            return ['success' => true, 'message' => 'Zona actualizada'];
        }
        return ['success' => false, 'message' => 'Error al actualizar la zona'];
    }

    public function delete($id) {
        if ($this->zoneModel->deleteZone($id)) {
             return ['success' => true, 'message' => 'Zona eliminada'];
        }
        return ['success' => false, 'message' => 'No se puede eliminar la zona (tiene sectores asociados)'];
    }
}
