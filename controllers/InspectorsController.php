<?php
require_once __DIR__ . '/../models/InspectorsModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class InspectorsController {
    private $inspectorsModel;
    private $usersModel;

    public function __construct() {
        $this->inspectorsModel = new InspectorsModel();
        $this->usersModel = new UserModel();
    }

    // Muestra la lista de todos los inspectores
    public function index() {
        $inspectors = $this->inspectorsModel->getAll();
        $page_title = "Listado de Inspectores";
        return [
            'success' => true,
            'inspectors' => $inspectors,
            'page_title' => $page_title
        ];
    }

    // Muestra el formulario para crear un nuevo inspector
    public function create() {
        $users = $this->usersModel->getAll();
        $page_title = "Crear Nuevo Inspector";
        return [
            'success' => true,
            'page_title' => $page_title,
            'users' => $users
        ];
    }

    // Procesa el formulario de creación y guarda el nuevo inspector
    public function store($data) {
        // Validación de datos
        $errors = [];
        if (empty(trim($data['inspector_code']))) {
            $errors[] = 'El código de inspector es obligatorio.';
        }
        if (empty(trim($data['full_name']))) {
            $errors[] = 'El nombre completo es obligatorio.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $errors
            ];
        }

        if ($this->inspectorsModel->create($data)) {
            return [
                'success' => true,
                'message' => 'Inspector creado con éxito.',
                'redirect' => 'index.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ocurrió un error al crear el inspector.'
            ];
        }
    }

    // Muestra los detalles de un inspector específico
    public function view($id) {
        $inspector = $this->inspectorsModel->getById($id);
        if (!$inspector) {
            return [
                'success' => false,
                'message' => 'Inspector no encontrado.'
            ];
        }

        $page_title = "Detalles del Inspector: " . htmlspecialchars($inspector['full_name']);
        return [
            'success' => true,
            'inspector' => $inspector,
            'page_title' => $page_title
        ];
    }

    // Muestra el formulario para editar un inspector existente
    public function edit($id) {
        $inspector = $this->inspectorsModel->getById($id);
        if (!$inspector) {
            return [
                'success' => false,
                'message' => 'Inspector no encontrado.'
            ];
        }
        
        $page_title = "Editar Inspector: " . htmlspecialchars($inspector['full_name']);
        return [
            'success' => true,
            'inspector' => $inspector,
            'page_title' => $page_title
        ];
    }

    // Procesa el formulario de edición y actualiza un inspector
    public function update($id, $data) {
        // Validación de datos
        $errors = [];
        if (empty(trim($data['inspector_code']))) {
            $errors[] = 'El código de inspector es obligatorio.';
        }
        if (empty(trim($data['full_name']))) {
            $errors[] = 'El nombre completo es obligatorio.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $errors
            ];
        }

        if ($this->inspectorsModel->update($id, $data)) {
            return [
                'success' => true,
                'message' => 'Inspector actualizado con éxito.',
                'redirect' => 'index.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el inspector.'
            ];
        }
    }

    // Elimina (desactiva) un inspector
    public function delete($id) {
        if ($this->inspectorsModel->delete($id)) {
            return [
                'success' => true,
                'message' => 'Inspector eliminado con éxito.',
                'redirect' => 'index.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el inspector.'
            ];
        }
    }
}