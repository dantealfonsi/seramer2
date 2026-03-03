<?php
// Vista de creación de departamentos
session_start();

require_once __DIR__ . '/../../controllers/DepartmentController.php';
require_once __DIR__ . '/../../models/DepartmentModel.php';

$controller = new DepartmentController();
$departmentModel = new DepartmentModel();

$managers = $departmentModel->getPotentialManagers();
$page_title = 'Registrar Nuevo Departamento';

require_once __DIR__ . '/../../views/layouts/header.php';
include __DIR__ . '/../../views/layouts/navigation.php';
include __DIR__ . '/../../views/layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-1" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-building-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Departamentos</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al listado
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="store.php" method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nombre del Departamento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Ej: Recursos Humanos" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="manager_id" class="form-label">Gerente / Jefe (Manager)</label>
                                        <select class="form-select select2" id="manager_id" name="manager_id">
                                            <option value="">-- Seleccionar Manager --</option>
                                            <?php foreach ($managers as $manager): ?>
                                                <option value="<?php echo $manager['id']; ?>">
                                                    <?php echo htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name'] . ' (' . $manager['id_number'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="shift_type" class="form-label">Tipo de Turno</label>
                                        <select class="form-select" id="shift_type" name="shift_type">
                                            <option value="Day" selected>Diurno</option>
                                            <option value="Night">Nocturno</option>
                                            <option value="Mixed">Mixto</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Descripción o Notas</label>
                                        <input type="text" class="form-control" id="description" name="description" placeholder="Breve detalle..." />
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line"></i> Guardar Departamento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

