<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

$controller = new ContractController();
$result = $controller->index();

$contracts = $result['contracts'];
$metrics = $result['metrics'];
$page_title = $result['page_title'];

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
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                             <i class="ri-file-list-3-line mr-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                             <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="card-tools">
                            <a href="create.php" class="btn btn-primary btn-sm">
                                <i class="ri-add-line mr-1"></i>
                                Nuevo Contrato
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Metrics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-file-contract"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total</span>
                                        <span class="info-box-number"><?php echo $metrics['total']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Activos</span>
                                        <span class="info-box-number"><?php echo $metrics['active']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Por Vencer</span>
                                        <span class="info-box-number"><?php echo $metrics['expiring_soon']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-danger">
                                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Vencidos</span>
                                        <span class="info-box-number"><?php echo $metrics['expired']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contracts Table -->
                        <?php if (empty($contracts)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay contratos registrados</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Adjudicatario</th>
                                            <th>Tipo</th>
                                            <th>Modo</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contracts as $contract): ?>
                                            <tr>
                                                <td><?php echo $contract['id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($contract['awardee_name']); ?><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($contract['awardee_id_number']); ?></small>
                                                </td>
                                                <td><?php echo ucfirst($contract['type']); ?></td>
                                                <td><?php echo ucfirst($contract['contract_mode']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($contract['start_date'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($contract['end_date'])); ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        echo match($contract['status']) {
                                                            'active' => 'bg-success',
                                                            'expired' => 'bg-danger',
                                                            'canceled' => 'bg-secondary',
                                                            'renewed' => 'bg-info',
                                                            default => 'bg-light'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($contract['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="detail.php?id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="ri-edit-line"></i>
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
