<?php
require_once __DIR__ . '/../../controllers/BillingController.php';

$controller = new BillingController();
$payments = $controller->getPaymentHistory(100);
$page_title = 'Pagos Recibidos';

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
                         <?php if (empty($payments)): ?>
                            <div class="alert alert-info">No hay pagos registrados recientemente.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID Tx</th>
                                            <th>Fecha</th>
                                            <th>Adjudicatario</th>
                                            <th>Referencia Factura</th>
                                            <th>Método</th>
                                            <th>Monto (Bs)</th>
                                            <th>Concepto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?php echo $payment['id']; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($payment['date'])); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($payment['id_number']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($payment['payment_reference']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['payment_method_name']); ?></td>
                                                <td><?php echo number_format($payment['amount'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($payment['concept']); ?></td>
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
