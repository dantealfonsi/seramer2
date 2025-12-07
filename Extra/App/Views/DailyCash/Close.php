<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Cerrar Caja: <?= htmlspecialchars($dailyCash['cash_register_name']) ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Monto Inicial</h6>
                                <h4 class="mb-0">Bs. <?= number_format($dailyCash['initial_amount'], 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Abonos</h6>
                                <h4 class="mb-0">Bs. <?= number_format($totalInstallments, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Monto Calculado</h6>
                                <h4 class="mb-0">Bs. <?= number_format($calculatedFinal, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-secondary">
                    <i class="ri ri-alert-line me-2"></i>
                    <strong>Fecha de Apertura:</strong> <?= date('d/m/Y H:i', strtotime($dailyCash['open_date'] . ' ' . $dailyCash['open_time'])) ?>
                </div>
                
                <!-- Movimientos Realizados -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri ri-money-dollar-circle-line me-2"></i>
                            Movimientos Realizados
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($installments) && count($installments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Adjudicatario</th>
                                            <th>Cédula</th>
                                            <th>Factura</th>
                                            <th>Método de Pago</th>
                                            <th>Concepto</th>
                                            <th class="text-end">Monto (Bs.)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($installments as $inst): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($inst['date'])) ?></td>
                                            <td><?= htmlspecialchars($inst['awardee_name']) ?></td>
                                            <td><?= htmlspecialchars($inst['awardee_id_number']) ?></td>
                                            <td>
                                                <a href="<?= $app['url'] ?>/cobro/verFactura/<?= $inst['payment_id'] ?>" 
                                                   target="_blank"
                                                   class="text-primary">
                                                    <?= htmlspecialchars($inst['payment_reference'] ?? '#' . $inst['payment_id']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($inst['payment_method_name']) ?></td>
                                            <td><?= htmlspecialchars($inst['concept'] ?? '-') ?></td>
                                            <td class="text-end fw-semibold">Bs. <?= number_format($inst['amount'], 2, ',', '.') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <td colspan="6" class="text-end fw-bold">Total Cobrado:</td>
                                            <td class="text-end fw-bold ">Bs. <?= number_format($totalInstallments, 2, ',', '.') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="ri ri-information-line me-2"></i>
                                No se han registrado movimientos en esta caja.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <form action="<?= $app['url'] ?>/dailycash/storeclose" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    <input type="hidden" name="daily_cash_id" value="<?= $dailyCash['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" 
                                       class="form-control" 
                                       id="final_amount" 
                                       name="final_amount" 
                                       step="0.01" 
                                       min="0" 
                                       value="<?= number_format($calculatedFinal, 2, '.', '') ?>"
                                       required />
                                <label for="final_amount">Monto Final (Bs.) *</label>
                            </div>
                            <small class="text-muted">
                                <i class="ri ri-information-line"></i>
                                El monto calculado es: Bs. <?= number_format($calculatedFinal, 2) ?>. Ajuste si es necesario.
                            </small>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger me-2">
                                <i class="ri ri-lock-line me-1"></i>
                                Cerrar Caja
                            </button>
                            <a href="<?= $app['url'] ?>/dailycash/index" class="btn btn-outline-secondary">
                                <i class="ri ri-arrow-left-line me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

