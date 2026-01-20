<div class="alert alert-info mb-4" role="alert">
    <i class="ri ri-information-line me-2"></i>
    Las tasas de euro se establecen mensualmente. Al actualizar una tasa, el sistema actualizará automáticamente todas las facturas pendientes del mes correspondiente.
</div>

<div class="card">
    <div class="card-datatable table-responsive">
        <table id="ratesTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th></th>
                    <th>Mes/Año</th>
                    <th>Valor en Bs.</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Mapeo de números de mes a nombres en español
                $monthsMap = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                    // Soporte legacy para meses en texto (por si aún no se migró)
                    'enero' => 'Enero', 'febrero' => 'Febrero', 'marzo' => 'Marzo',
                    'abril' => 'Abril', 'mayo' => 'Mayo', 'junio' => 'Junio',
                    'julio' => 'Julio', 'agosto' => 'Agosto', 'septiembre' => 'Septiembre',
                    'octubre' => 'Octubre', 'noviembre' => 'Noviembre', 'diciembre' => 'Diciembre'
                ];
                
                foreach ($rates as $rate): 
                    $monthKey = is_numeric($rate['month']) ? (int)$rate['month'] : strtolower($rate['month']);
                    $monthName = $monthsMap[$monthKey] ?? ucfirst($rate['month']);
                ?>
                <tr data-id="<?= $rate['id'] ?>">
                    <td></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri ri-exchange-dollar-line"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= $monthName ?> <?= $rate['year'] ?></h6>
                                <small class="text-muted">Tasa de cambio</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-label-success">Bs. <?= number_format($rate['bs_value'], 2, ',', '.') ?></span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= $app['url'] ?>/fiscalyear/editrate/<?= $rate['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect"
                               data-bs-toggle="tooltip"
                               title="Editar">
                                <i class="ri ri-pencil-line ri-20px"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="deleteRecord(<?= $rate['id'] ?>, '<?= $app['url'] ?>/fiscalyear/deleterate/:id', 'ratesTable')" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect"
                               data-bs-toggle="tooltip"
                               title="Eliminar">
                                <i class="ri ri-delete-bin-7-line ri-20px"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable con checkbox
    initDataTableWithCheckbox('ratesTable', {
        createUrl: '<?= $app['url'] ?>/fiscalyear/createrate',
        bulkDeleteUrl: '<?= $app['url'] ?>/fiscalyear/bulkdeleterate',
        order: [[1, 'desc']], // Ordenar por Mes/Año descendente
        pageLength: 25
    });
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
