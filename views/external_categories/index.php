<?php
require_once __DIR__ . '/../../controllers/ExternalCategoryController.php';

$controller = new ExternalCategoryController();
$data = $controller->index();
$categories = $data['categories'];
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
                            <i class="ri-add-line"></i> Nuevo Rubro Externo
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <div class="alert alert-info">No hay rubros externos registrados.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Tipo Instalación</th>
                                            <th>Pagos anuales</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                                <td><?php echo htmlspecialchars($cat['installation_type'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($cat['payment_count']); ?></td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-info" title="Editar">
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
