<?php
require_once __DIR__ . '/../../controllers/AwardeeController.php';

$controller = new AwardeeController();
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = (int)$_GET['id'];
$data_view = $controller->showContracts($id);
if (!$data_view) {
    header('Location: index.php');
    exit;
}

$awardee = $data_view['awardee'];
$contracts = $data_view['contracts'];
$page_title = $data_view['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Información del Adjudicatario</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($awardee['first_name'] . ' ' . $awardee['last_name']); ?></p>
                                <p><strong>Cédula:</strong> <?php echo htmlspecialchars($awardee['id_number']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($awardee['email'] ?? 'No registrado'); ?></p>
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($awardee['phone'] ?? 'No registrado'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Contratos Asociados</h5>
                         <!-- Assuming links to Contracts Module for creating new contracts -->
                         <a href="../contracts/create.php?awardee_id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                            <i class="ri-add-line"></i> Nuevo Contrato
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($contracts)): ?>
                            <div class="alert alert-info">No hay contratos asociados.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID Contrato</th>
                                            <th>Fecha Incio</th>
                                            <th>Fecha Fin</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contracts as $contract): ?>
                                            <tr>
                                                <td><?php echo $contract['id']; ?></td>
                                                <td><?php echo $contract['start_date']; ?></td>
                                                <td><?php echo $contract['end_date']; ?></td>
                                                <td>
                                                     <span class="badge <?php echo ($contract['status'] == 'active') ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo ucfirst($contract['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../contracts/detail.php?id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-info" title="Ver Detalle">
                                                        <i class="ri-eye-line"></i>
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
