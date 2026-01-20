<?php
/**
 * Vista: Detalle de Factura con Abonos
 * Muestra la información completa de una factura y su historial de pagos
 */
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Cobros /</span> Detalle de Factura
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= $app['url'] ?>">Inicio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= $app['url'] ?>/cobro/index">Cobros</a>
                    </li>
                    <li class="breadcrumb-item active">Factura #<?= $payment['id'] ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= $app['url'] ?>/cobro/buscar?id_number=<?= urlencode($awardee['id_number']) ?>" class="btn btn-secondary">
                <i class="ri-arrow-left-line me-1"></i>
                Volver
            </a>
            <a href="<?= $app['url'] ?>/cobro/imprimirFactura/<?= $payment['id'] ?>" 
               class="btn btn-primary" 
               target="_blank">
                <i class="ri-printer-line me-1"></i>
                Imprimir PDF
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información de la Factura -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-file-text-line me-2"></i>
                        Información de la Factura
                    </h5>
                    <?php
                        $isPaid = ($payment['remaining_balance'] <= 0.01);
                        $badgeClass = $isPaid ? 'bg-label-success' : 'bg-label-warning';
                        $badgeText = $isPaid ? 'Pagada' : 'Pendiente';
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">N° Factura</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($payment['payment_reference'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">N° Interno</label>
                                <p class="form-control-plaintext">#<?= $payment['id'] ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">Contrato</label>
                                <p class="form-control-plaintext">
                                    <a href="<?= $app['url'] ?>/contract/detail/<?= $payment['contract_id'] ?>">
                                        #<?= $payment['contract_id'] ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">Año Fiscal</label>
                                <p class="form-control-plaintext"><?= $contract['fiscal_year'] ?? 'N/A' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">Fecha de Pago</label>
                                <p class="form-control-plaintext"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">Mes Facturado</label>
                                <p class="form-control-plaintext">
                                    <?php 
                                        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        $mes = date('n', strtotime($payment['payment_date'])) - 1;
                                        echo $meses[$mes] . ' ' . date('Y', strtotime($payment['payment_date']));
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Tasa del Euro -->
                    <h6 class="fw-semibold mb-3">Tasa del Euro</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">Tasa</label>
                                <p class="form-control-plaintext">Bs. <?= number_format($payment['euro_rate_value'] ?? 0, 2, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">Fecha de la Tasa</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars(ucfirst($payment['euro_rate_date'] ?? '')) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Abonos Realizados -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-money-dollar-circle-line me-2"></i>
                        Historial de Abonos
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($installments) && count($installments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Método de Pago</th>
                                        <th>Concepto</th>
                                        <th class="text-end">Monto (Bs.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($installments as $inst): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($inst['date'])) ?></td>
                                        <td><?= htmlspecialchars($inst['payment_method_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($inst['concept'] ?? '') ?></td>
                                        <td class="text-end fw-semibold">Bs. <?= number_format($inst['amount'], 2, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-end fw-bold">Total Pagado:</td>
                                        <td class="text-end fw-bold text-success">Bs. <?= number_format($payment['total_paid'], 2, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="ri-information-line me-2"></i>
                            No se han registrado abonos para esta factura.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Resumen de Montos y Cliente -->
        <div class="col-md-4">
            <!-- Información del Cliente -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-user-line me-2"></i>
                        Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Nombre</label>
                        <p class="form-control-plaintext"><?= htmlspecialchars($awardee_name) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Cédula</label>
                        <p class="form-control-plaintext"><?= htmlspecialchars($awardee['id_number']) ?></p>
                    </div>
                    <?php if (!empty($awardee['phone'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Teléfono</label>
                        <p class="form-control-plaintext"><?= htmlspecialchars($awardee['phone']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($awardee['email'])): ?>
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted">Email</label>
                        <p class="form-control-plaintext"><?= htmlspecialchars($awardee['email']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Resumen de Montos -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-money-dollar-box-line me-2"></i>
                        Resumen de Montos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Monto (EUR):</span>
                        <span class="fw-semibold">€ <?= number_format($payment['amount_eur'] ?? 0, 2, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Monto Total (Bs.):</span>
                        <span class="fw-semibold">Bs. <?= number_format($payment['amount_bs'] ?? 0, 2, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Total Pagado:</span>
                        <span class="fw-semibold text-success">Bs. <?= number_format($payment['total_paid'] ?? 0, 2, ',', '.') ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Saldo Pendiente:</span>
                        <span class="fw-bold fs-5 <?= $payment['remaining_balance'] > 0 ? 'text-warning' : 'text-success' ?>">
                            Bs. <?= number_format($payment['remaining_balance'] ?? 0, 2, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pagos Vencidos (si los hay) -->
            <?php if (!empty($overduePayments) && count($overduePayments) > 0): ?>
            <div class="card border-warning">
                <div class="card-header bg-label-warning">
                    <h6 class="mb-0">
                        <i class="ri-alert-line me-2"></i>
                        Facturas Vencidas
                    </h6>
                </div>
                <div class="card-body">
                    <small class="text-muted d-block mb-2">Este cliente tiene facturas vencidas:</small>
                    <?php 
                    $vencidoCount = 0;
                    foreach ($overduePayments as $overdue): 
                        if ($overdue['id'] == $payment['id']) continue;
                        $vencidoCount++;
                    ?>
                        <div class="mb-2">
                            <small>
                                Factura #<?= $overdue['id'] ?><br>
                                Vencimiento: <?= date('d/m/Y', strtotime($overdue['payment_date'])) ?><br>
                                Saldo: <strong>Bs. <?= number_format($overdue['amount_bs'] - $overdue['total_paid'], 2, ',', '.') ?></strong>
                            </small>
                        </div>
                        <?php if ($vencidoCount < count($overduePayments) - 1): ?>
                        <hr class="my-2">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

