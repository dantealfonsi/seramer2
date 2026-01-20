<?php
require_once __DIR__ . '/../../controllers/SectorController.php';

$controller = new SectorController();
$data = $controller->index();
$sectors = $data['sectors'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($page_title); ?></h5>
                        <a href="create.php" class="btn btn-primary btn-sm">
                            <i class="ri-add-line"></i> Nuevo Sector
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sectors)): ?>
                            <div class="alert alert-info">No hay sectores registrados.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Zona</th>
                                            <th>Descripción</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sectors as $sector): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sector['name']); ?></td>
                                                <td><?php echo htmlspecialchars($sector['zone_name'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($sector['description'] ?? ''); ?></td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $sector['id']; ?>" class="btn btn-sm btn-info" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
