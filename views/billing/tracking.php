<?php
require_once __DIR__ . '/../../controllers/BillingController.php';

$controller = new BillingController();
$debtors = $controller->getDebtors();
$page_title = 'Seguimiento de Cobranza (Morosidad)';

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
                         <?php if (empty($debtors)): ?>
                            <div class="alert alert-success">No hay adjudicatarios con deudas vencidas registratas.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Adjudicatario</th>
                                            <th>Identificación</th>
                                            <th>Teléfono</th>
                                            <th>Facturas Vencidas</th>
                                            <th>Deuda Estimada (Bs)</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($debtors as $debtor): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($debtor['first_name'] . ' ' . $debtor['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($debtor['id_number']); ?></td>
                                                <td><?php echo htmlspecialchars($debtor['phone']); ?></td>
                                                <td class="text-danger font-weight-bold"><?php echo $debtor['overdue_count']; ?></td>
                                                <td><?php echo number_format($debtor['estimated_debt'], 2); ?></td>
                                                <td>
                                                    <a href="receivable.php?id_number=<?php echo $debtor['id_number']; ?>" class="btn btn-sm btn-primary">
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
