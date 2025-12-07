<?php
require_once __DIR__ . '/../../controllers/ReportController.php';

$controller = new ReportController();
$data = $controller->billingReport();
$contracts = $data['contracts'];
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
                    <div class="card-header">
                        <h5 class="card-title"><?php echo htmlspecialchars($page_title); ?></h5>
                    </div>
                    <div class="card-body">
                         <?php if (empty($contracts)): ?>
                            <div class="alert alert-success">No hay contratos morosos registrados.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Adjudicatario</th>
                                            <th>Contrato ID</th>
                                            <th>Facturas Vencidas</th>
                                            <th>Días Vencido</th>
                                            <th>Total Deuda (Bs)</th>
                                            <th>Total Pagado (Bs)</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contracts as $contract): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($contract['awardee_name']); ?><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($contract['awardee_id_number']); ?></small>
                                                </td>
                                                <td><?php echo $contract['contract_id']; ?></td>
                                                <td class="text-danger font-weight-bold text-center"><?php echo $contract['overdue_payments_count']; ?></td>
                                                <td class="text-danger font-weight-bold text-center"><?php echo $contract['days_overdue']; ?></td>
                                                <td><?php echo number_format($contract['total_amount_due'], 2); ?></td>
                                                <td><?php echo number_format($contract['total_paid'], 2); ?></td>
                                                <td>
                                                     <a href="../billing/receivable.php?id_number=<?php echo urlencode($contract['awardee_id_number']); ?>" class="btn btn-sm btn-primary">
                                                        Gestionar Cobro
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
