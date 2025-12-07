<?php
require_once __DIR__ . '/../../controllers/CashRegisterController.php';

$controller = new CashRegisterController();
$data = $controller->index();
$cashRegisters = $data['cashRegisters'];
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
                        <h5 class="card-title"><?php echo htmlspecialchars($page_title); ?></h5>
                        <div class="card-tools">
                            <a href="create.php" class="btn btn-primary btn-sm">Nueva Caja</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Usuario Asignado</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cashRegisters as $cr): ?>
                                    <tr>
                                        <td><?php echo $cr['id']; ?></td>
                                        <td><?php echo htmlspecialchars($cr['name']); ?></td>
                                        <td><?php echo htmlspecialchars($cr['assigned_user_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($cr['email'] ?? '-'); ?></td>
                                        <td><?php echo ucfirst($cr['status']); ?></td>
                                        <td>
                                            <a href="edit.php?id=<?php echo $cr['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
