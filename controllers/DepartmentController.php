<?php
require_once __DIR__ . '/../models/DepartmentModel.php';

class DepartmentController {
    private $departmentModel;

    public function __construct() {
        $this->departmentModel = new DepartmentModel();
    }

    public function index() {
        $departments = $this->departmentModel->getAllWithManager();
        require __DIR__ . '/../views/departments/index.php';
    }

    public function create() {
        $managers = $this->departmentModel->getPotentialManagers();
        require __DIR__ . '/../views/departments/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'manager_id' => !empty($_POST['manager_id']) ? $_POST['manager_id'] : null,
                'shift_type' => $_POST['shift_type'] ?? 'Day',
                'description' => $_POST['description'] ?? ''
            ];

            if ($this->departmentModel->create($data)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Departamento creado exitosamente.'];
                header('Location: ' . url('views/departments/index.php'));
                exit;
            } else {
                $error = "Error al crear el departamento.";
                $managers = $this->departmentModel->getPotentialManagers();
                require __DIR__ . '/../views/departments/create.php';
            }
        }
    }

    public function edit($id) {
        $department = $this->departmentModel->findById($id);
        if (!$department) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Departamento no encontrado.'];
            header('Location: ' . url('views/departments/index.php'));
            exit;
        }
        $managers = $this->departmentModel->getPotentialManagers();
        require __DIR__ . '/../views/departments/edit.php';
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'manager_id' => !empty($_POST['manager_id']) ? $_POST['manager_id'] : null,
                'shift_type' => $_POST['shift_type'] ?? 'Day',
                'description' => $_POST['description'] ?? ''
            ];

            if ($this->departmentModel->update($id, $data)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Departamento actualizado exitosamente.'];
                header('Location: ' . url('views/departments/index.php'));
                exit;
            } else {
                $error = "Error al actualizar el departamento.";
                $department = $this->departmentModel->findById($id);
                $managers = $this->departmentModel->getPotentialManagers();
                require __DIR__ . '/../views/departments/edit.php';
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->departmentModel->delete($id)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Departamento eliminado exitosamente.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Error al eliminar el departamento.'];
            }
            header('Location: ' . url('views/departments/index.php'));
            exit;
        }
    }
}
