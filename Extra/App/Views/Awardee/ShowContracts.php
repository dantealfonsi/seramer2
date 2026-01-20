<!-- Botón Volver -->
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-end">
        <a href="<?= $app['url'] ?>/awardee/index" class="btn btn-outline-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>
            Volver al Listado
        </a>
    </div>
</div>

<!-- Información del Adjudicatario -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Información del Adjudicatario</h5>
                <span class="badge bg-label-primary"><?= count($contracts) ?> Contrato(s)</span>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 ">
                        <div class="avatar avatar-xl mb-2 mx-auto">
                            <span class="avatar-initial rounded-circle bg-label-primary text-center">
                                <?= strtoupper(substr($awardee['first_name'], 0, 1) . substr($awardee['last_name'], 0, 1)) ?>
                            </span>
                        </div>
                        <h5 class="mb-0"><?= htmlspecialchars(\App\Models\AwardeeModel::getFullName($awardee)) ?></h5>
                        <small class="text-muted">C.I.: <?= htmlspecialchars($awardee['id_number']) ?></small>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-start mb-2">
                            <i class="ri ri-phone-line me-2 text-primary ri-20px"></i>
                            <div>
                                <small class="text-muted d-block">Teléfono</small>
                                <span class="fw-medium"><?= htmlspecialchars($awardee['phone'] ?? 'No registrado') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-start mb-2">
                            <i class="ri ri-mail-line me-2 text-info ri-20px"></i>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <span class="fw-medium"><?= htmlspecialchars($awardee['email'] ?? 'No registrado') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-start mb-2">
                            <i class="ri ri-map-pin-line me-2 text-success ri-20px"></i>
                            <div>
                                <small class="text-muted d-block">Dirección</small>
                                <span class="fw-medium"><?= htmlspecialchars($awardee['address'] ?? 'No registrada') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Contratos -->
<div class="row">
    <?php if (empty($contracts)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ri ri-file-list-line ri-64px text-muted mb-3"></i>
                    <p class="text-muted">Este adjudicatario no tiene contratos registrados</p>
                    <a href="<?= $app['url'] ?>/contract/create?awardee_id=<?= $awardee['id'] ?>" class="btn btn-primary">
                        <i class="ri ri-add-line me-1"></i>
                        Crear Primer Contrato
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($contracts as $index => $contract): ?>
        <div class="col-12 mb-4">
            <div class="card border">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                Contrato #<?= $contract['id'] ?>
                            </span>
                            <div>
                                <h5 class="mb-0">Año Fiscal <?= $contract['fiscal_year'] ?? 'N/A' ?></h5>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($contract['start_date'])) ?> - 
                                    <?= date('d/m/Y', strtotime($contract['end_date'])) ?>
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <?php
                            $statusConfig = [
                                'active' => ['label' => 'Activo', 'color' => 'success'],
                                'renewed' => ['label' => 'Renovado', 'color' => 'info'],
                                'canceled' => ['label' => 'Cancelado', 'color' => 'danger']
                            ];
                            $status = $contract['status'] ?? 'active';
                            $statusInfo = $statusConfig[$status] ?? ['label' => ucfirst($status), 'color' => 'secondary'];
                            ?>
                            <span class="badge bg-<?= $statusInfo['color'] ?>"><?= $statusInfo['label'] ?></span>
                            <?php
                            $paymentStatusConfig = [
                                'up to date' => ['label' => 'Al día', 'color' => 'success'],
                                'delinquent' => ['label' => 'Moroso', 'color' => 'warning'],
                                'unable to pay' => ['label' => 'Insolvente', 'color' => 'danger']
                            ];
                            $paymentStatus = $contract['status_payment'] ?? 'up to date';
                            $paymentStatusInfo = $paymentStatusConfig[$paymentStatus] ?? ['label' => ucfirst($paymentStatus), 'color' => 'secondary'];
                            ?>
                            <span class="badge bg-<?= $paymentStatusInfo['color'] ?>"><?= $paymentStatusInfo['label'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <!-- Información General -->
                        <div class="col-md-3 mb-4">
                            <h6 class="mb-3"><i class="ri ri-information-line me-2"></i>Información General</h6>
                            <ul class="list-unstyled">
                                <li class="d-flex align-items-center mb-3">
                                    <i class="ri ri-file-list-3-line me-2 text-primary"></i>
                                    <div>
                                        <small class="text-muted d-block">Tipo</small>
                                        <?php if ($contract['type'] === 'simultaneous'): ?>
                                            <span class="badge bg-label-info">Simultáneo</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-warning">Anticipado</span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="ri ri-repeat-line me-2 text-info"></i>
                                    <div>
                                        <small class="text-muted d-block">Modalidad</small>
                                        <span class="fw-medium"><?= $contract['contract_mode'] === 'monthly' ? 'Mensual' : ($contract['contract_mode'] === 'annual' ? 'Anual' : 'Semanal') ?></span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center">
                                    <i class="ri ri-calendar-line me-2 text-success"></i>
                                    <div>
                                        <small class="text-muted d-block">Periodo Año Fiscal</small>
                                        <span class="fw-medium">
                                            <?= $contract['fiscal_start_date'] ? date('d/m/Y', strtotime($contract['fiscal_start_date'])) : '-' ?> - 
                                            <?= $contract['fiscal_end_date'] ? date('d/m/Y', strtotime($contract['fiscal_end_date'])) : '-' ?>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Locales -->
                        <div class="col-md-5 mb-4">
                            <h6 class="mb-3"><i class="ri ri-map-pin-line me-2"></i>Locales Asignados</h6>
                            <?php if (!empty($contract['locations'])): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($contract['locations'] as $location): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="ri ri-store-3-line me-1 text-primary"></i>
                                            <span class="badge bg-label-secondary">
                                                Local <?= htmlspecialchars($location['stall_number']) ?>
                                                <small class="ms-1">(<?= htmlspecialchars($location['zone_name']) ?> - <?= htmlspecialchars($location['sector_name']) ?>)</small>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted"><i class="ri ri-information-line me-1"></i>No hay locales asignados</span>
                            <?php endif; ?>
                        </div>
                        <!-- Categorías -->
                        <div class="col-md-4 mb-4">
                            <h6 class="mb-3"><i class="ri ri-price-tag-3-line me-2"></i>Categorías de Negocio</h6>
                            <?php if (!empty($contract['categories'])): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($contract['categories'] as $category): ?>
                                        <?php if (!empty($category['internal_category_name'])): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-store-2-line me-1 text-success"></i>
                                                <span class="badge bg-label-primary"><?= htmlspecialchars($category['internal_category_name']) ?></span>
                                            </div>
                                        <?php elseif (!empty($category['external_category_name'])): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="ri ri-building-line me-1 text-info"></i>
                                                <span class="badge bg-label-info"><?= htmlspecialchars($category['external_category_name']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted"><i class="ri ri-information-line me-1"></i>No hay categorías asignadas</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pagos del Contrato -->
                    <div class="row mt-4 border-top pt-4">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="ri ri-money-dollar-circle-line me-2"></i>Pagos del Contrato (<?= !empty($contract['payments']) ? count($contract['payments']) : '0' ?>)</h6>
                            <?php if (!empty($contract['payments'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Referencia</th>
                                                <th>Fecha</th>
                                                <th>Frecuencia</th>
                                                <th>Tasa Euro</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contract['payments'] as $payment): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($payment['payment_reference'] ?? '-') ?></strong></td>
                                                <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                                <td>
                                                    <span class="badge bg-label-secondary">
                                                        <?= number_format($payment['total_payment_count'] ?? 0, 2, '.', '') ?> veces
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (isset($payment['euro_rate_id']) && isset($payment['euro_rate_value'])): ?>
                                                        <span class="badge bg-primary">
                                                            Bs. <?= number_format($payment['euro_rate_value'], 2, '.', ',') ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">No asignada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($payment['calculated_amount']) && $payment['calculated_amount'] > 0): ?>
                                                        <span class="fw-bold text-dark">
                                                            Bs. <?= number_format($payment['calculated_amount'], 2, '.', ',') ?>
                                                        </span>
                                                    <?php else: ?>
                                                        Bs. 0.00
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'paid' => 'success',
                                                        'cancelled' => 'danger',
                                                        'refunded' => 'info'
                                                    ];
                                                    $statusLabels = [
                                                        'pending' => 'Pendiente',
                                                        'paid' => 'Pagado',
                                                        'cancelled' => 'Cancelado',
                                                        'refunded' => 'Reembolsado'
                                                    ];
                                                    $color = $statusColors[$payment['status']] ?? 'secondary';
                                                    $label = $statusLabels[$payment['status']] ?? $payment['status'];
                                                    ?>
                                                    <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    <i class="ri ri-information-line me-2"></i>
                                    No hay pagos registrados para este contrato
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    $('.head-label').html('<h5 class="card-title mb-0">Contratos de Adjudicatario - Vista Completa</h5>');
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

