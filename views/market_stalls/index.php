<?php
require_once __DIR__ . '/../../controllers/MarketStallController.php';

$controller = new MarketStallController();
$data = $controller->index();
$stalls = $data['stalls'];
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
                            <i class="ri-add-line"></i> Nuevo Local
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($stalls)): ?>
                            <div class="alert alert-info">No hay locales registrados.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Sector</th>
                                            <th>Zona</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stalls as $stall): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($stall['stall_number']); ?></td>
                                                <td><?php echo htmlspecialchars($stall['sector_name'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($stall['zone_name'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo ($stall['status'] == 'occupied') ? 'bg-danger' : 'bg-success'; ?>">
                                                        <?php echo $stall['status'] == 'occupied' ? 'Ocupado' : 'Vacante'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $stall['id']; ?>" class="btn btn-sm btn-info" title="Editar">
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
