<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Reporte de Contratos Morosos</h5>
        <div>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="ri ri-printer-line me-1"></i>
                Imprimir
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="ri ri-alert-line me-2"></i>
            <strong>Total de contratos morosos:</strong> <?= count($contracts) ?>
        </div>
        
        <?php if (empty($contracts)): ?>
        <div class="alert alert-info">
            <i class="ri ri-information-line me-2"></i>
            No se encontraron contratos morosos
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table id="delinquentContractsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Contrato #</th>
                        <th>Adjudicatario</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Facturas Vencidas</th>
                        <th>Monto Adeudado</th>
                        <th>Monto Pagado</th>
                        <th>Saldo Pendiente</th>
                        <th>Días de Mora</th>
                        <th>Primera Fecha Vencida</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $contract): ?>
                    <tr>
                        <td>#<?= $contract['contract_id'] ?></td>
                        <td><?= htmlspecialchars($contract['awardee_name']) ?></td>
                        <td><?= htmlspecialchars($contract['awardee_id_number']) ?></td>
                        <td><?= htmlspecialchars($contract['awardee_phone'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-label-danger"><?= $contract['overdue_payments_count'] ?></span>
                        </td>
                        <td>
                            <strong class="text-danger">Bs. <?= number_format($contract['total_amount_due'] ?? 0, 2) ?></strong>
                        </td>
                        <td>
                            Bs. <?= number_format($contract['total_paid'] ?? 0, 2) ?>
                        </td>
                        <td>
                            <strong>Bs. <?= number_format(($contract['total_amount_due'] ?? 0) - ($contract['total_paid'] ?? 0), 2) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-label-warning"><?= $contract['days_overdue'] ?> días</span>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($contract['first_overdue_date'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">TOTALES:</td>
                        <td><?= array_sum(array_column($contracts, 'overdue_payments_count')) ?></td>
                        <td class="text-danger">Bs. <?= number_format(array_sum(array_column($contracts, 'total_amount_due')), 2) ?></td>
                        <td>Bs. <?= number_format(array_sum(array_column($contracts, 'total_paid')), 2) ?></td>
                        <td class="text-danger">Bs. <?= number_format(array_sum(array_column($contracts, 'total_amount_due')) - array_sum(array_column($contracts, 'total_paid')), 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .card-header .btn,
    .alert {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    table {
        font-size: 10px;
    }
}
</style>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    if ($.fn.DataTable) {
        $('#delinquentContractsTable').DataTable({
            order: [[8, 'desc']], // Ordenar por días de mora descendente
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="ri ri-file-excel-line me-1"></i> Exportar a Excel',
                    className: 'btn btn-success'
                },
                {
                    extend: 'pdf',
                    text: '<i class="ri ri-file-pdf-line me-1"></i> Exportar a PDF',
                    className: 'btn btn-danger'
                }
            ]
        });
    }
    
    // Configurar título de la tarjeta
    $('.head-label').html('<h5 class="card-title mb-0">Reporte de Contratos Morosos</h5>');
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

