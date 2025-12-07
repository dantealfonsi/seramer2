<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Reporte de Total Acumulado por Zona</h5>
        <div>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="ri ri-printer-line me-1"></i>
                Imprimir
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" action="<?= $app['url'] ?>/report/zoneaccumulated" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" name="start_date" value="<?= $startDate ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" name="end_date" value="<?= $endDate ?>" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri ri-search-line me-1"></i>
                        Filtrar
                    </button>
                </div>
            </div>
        </form>
        
        <?php if (empty($zones)): ?>
        <div class="alert alert-info">
            <i class="ri ri-information-line me-2"></i>
            No se encontraron datos para el rango de fechas seleccionado
        </div>
        <?php else: ?>
        <div class="alert alert-success">
            <i class="ri ri-information-line me-2"></i>
            <strong>Período:</strong> <?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?>
            | <strong>Total General:</strong> Bs. <?= number_format(array_sum(array_column($zones, 'total_accumulated')), 2) ?>
        </div>
        
        <div class="table-responsive">
            <table id="zoneAccumulatedTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Zona</th>
                        <th>N° Contratos</th>
                        <th>N° Pagos</th>
                        <th>Total Acumulado (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($zones as $zone): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($zone['zone_name']) ?></strong>
                        </td>
                        <td><?= $zone['contracts_count'] ?></td>
                        <td><?= $zone['payments_count'] ?></td>
                        <td>
                            <strong class="text-success">Bs. <?= number_format($zone['total_accumulated'], 2) ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td class="text-end">TOTALES:</td>
                        <td><?= array_sum(array_column($zones, 'contracts_count')) ?></td>
                        <td><?= array_sum(array_column($zones, 'payments_count')) ?></td>
                        <td class="text-success">Bs. <?= number_format(array_sum(array_column($zones, 'total_accumulated')), 2) ?></td>
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
    form,
    .alert {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    table {
        font-size: 12px;
    }
    @page {
        margin: 1cm;
    }
}
</style>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    if ($.fn.DataTable) {
        $('#zoneAccumulatedTable').DataTable({
            order: [[3, 'desc']], // Ordenar por total acumulado descendente
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
    $('.head-label').html('<h5 class="card-title mb-0">Reporte de Total Acumulado por Zona</h5>');
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

