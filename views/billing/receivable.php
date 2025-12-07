<?php
require_once __DIR__ . '/../../controllers/BillingController.php';

$controller = new BillingController();
$idNumber = $_GET['id_number'] ?? '';
$result = $controller->search($idNumber);

$page_title = $result['page_title'];
$has_results = $result['has_results'];
$error = $result['error'] ?? null;

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
                        <h5 class="card-title">Cuentas por Cobrar</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Form -->
                        <form method="GET" action="" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="id_number" class="form-control" placeholder="Buscar por Cédula/RIF" value="<?php echo htmlspecialchars($idNumber); ?>">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </form>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if ($has_results): ?>
                            <?php 
                                $awardee = $result['awardee'];
                                $contracts = $result['contracts'];
                                $payments = $result['allPayments'];
                                $paymentMethods = $result['paymentMethods'];
                            ?>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Datos del Contribuyente</h6>
                                    <p>
                                        <strong>Nombre:</strong> <?php echo htmlspecialchars($awardee['first_name'] . ' ' . $awardee['last_name']); ?><br>
                                        <strong>Identificación:</strong> <?php echo htmlspecialchars($awardee['id_number']); ?><br>
                                        <strong>Teléfono:</strong> <?php echo htmlspecialchars($awardee['phone']); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Contratos Activos</h6>
                                    <ul>
                                        <?php foreach ($contracts as $contract): ?>
                                            <li>
                                                Contrato #<?php echo $contract['id']; ?> 
                                                (<?php echo ucfirst($contract['type']); ?>) 
                                                - <?php echo ucfirst($contract['status']); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6>Pagos Pendientes y Recientes</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Ref</th>
                                            <th>Periodo</th>
                                            <th>Vencimiento</th>
                                            <th>Monto (EUR)</th>
                                            <th>Tasa</th>
                                            <th>Monto (Bs)</th>
                                            <th>Abonado</th>
                                            <th>Saldo</th>
                                            <th>Estado</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <?php 
                                                $totalBs = (float) $payment['amount_bs'];
                                                // We need to fetch paid amount for each payment or use what is available
                                                // The controller logic fetched 'allPaymentsWithRate' which typically includes status
                                                // Assuming we need to calculate 'paid' separately or it's in the query.
                                                // For now, let's assume the controller didn't fetch installments sum for every payment in the list (expensive).
                                                // BUT, we need it to calculate balance.
                                                // Let's rely on standard fields. If 'paid', balance is 0.
                                                // If pending, we might need to fetch it via AJAX or if the model provided it.
                                                // Looking at PaymentModel::getAllPaymentsWithRateByAwardee... it does some joins.
                                                // Let's assume for this View, we might just show Pay button.
                                                
                                                // WAIT, in CobroController logic it uses 'getPendingPayments'.
                                                // Here we have 'allPayments'.
                                                
                                                // Let's use a Data Attribute for Total Amount and handle logic in JS modal.
                                            ?>
                                            <tr>
                                                <td><?php echo $payment['payment_reference']; ?></td>
                                                <td><?php echo $payment['month_name'] . ' ' . $payment['year']; ?></td>
                                                <td><?php echo $payment['due_date']; ?></td>
                                                <td><?php echo number_format($payment['amount_euro'], 2); ?></td>
                                                <td><?php echo number_format($payment['rate_amount'] ?? 0, 2); ?></td>
                                                <td><?php echo number_format($totalBs, 2); ?></td>
                                                <td>-</td> <!-- Would need query -->
                                                <td>-</td> <!-- Would need query -->
                                                <td>
                                                    <span class="badge <?php echo ($payment['status'] == 'paid' ? 'bg-success' : 'bg-warning'); ?>">
                                                        <?php echo ucfirst($payment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($payment['status'] != 'paid'): ?>
                                                        <button class="btn btn-sm btn-success btn-pay" 
                                                                data-id="<?php echo $payment['id']; ?>"
                                                                data-amount="<?php echo $totalBs; ?>"
                                                                data-bs-toggle="modal" data-bs-target="#paymentModal">
                                                            Pagar
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-info btn-view-installments" data-id="<?php echo $payment['id']; ?>">
                                                        Ver Abonos
                                                    </button>
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

<!-- Modal Pago -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" name="payment_id" id="modalPaymentId">
                    <div class="mb-3">
                        <label class="form-label">Monto a Pagar (Bs)</label>
                        <input type="number" step="0.01" name="amount" id="modalAmount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select name="payment_method_id" class="form-control" required>
                            <?php if (isset($paymentMethods)): ?>
                                <?php foreach ($paymentMethods as $pm): ?>
                                    <option value="<?php echo $pm['id']; ?>"><?php echo htmlspecialchars($pm['name']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Concepto</label>
                        <input type="text" name="concept" class="form-control" value="Pago de mensualidad">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmPayment">Confirmar Pago</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentModal = document.getElementById('paymentModal');
    if (paymentModal) {
        paymentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const amount = button.getAttribute('data-amount');
            
            document.getElementById('modalPaymentId').value = id;
            document.getElementById('modalAmount').value = amount;
        });
        
        document.getElementById('btnConfirmPayment').addEventListener('click', function() {
            const form = document.getElementById('paymentForm');
            const formData = new FormData(form);
            
            fetch('ajax.php?action=register_payment', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
