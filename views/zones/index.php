<?php
require_once __DIR__ . '/../../controllers/ZoneController.php';

$controller = new ZoneController();
$data = $controller->index();
$zones = $data['zones'];
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
                            <i class="ri-add-line"></i> Nueva Zona
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($zones)): ?>
                            <div class="alert alert-info">No hay zonas registradas.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($zones as $zone): ?>
                                            <tr>
                                                <td><?php echo $zone['id']; ?></td>
                                                <td><?php echo htmlspecialchars($zone['name']); ?></td>
                                                <td><?php echo htmlspecialchars($zone['description'] ?? ''); ?></td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $zone['id']; ?>" class="btn btn-sm btn-info" title="Editar">
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
