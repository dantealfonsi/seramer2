<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/LiquidacionReportController.php';

$controller = new LiquidacionReportController();
$data = $controller->zoneaccumulated();
extract($data);

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .main-container { padding: 1.5rem; background-color: #f5f5f9; width: 100%; }
    .table thead th { background-color: #000000 !important; color: white !important; text-transform: uppercase; font-weight: 600; padding: 1rem !important; }
</style>

<div class="main-content main-container">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e0f2ff !important;">
                            <i class="ri-map-pin-line" style="color: #03c3ec; font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #43495b;">Total Acumulado por Zona</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Reportes</a></li>
                                    <li class="breadcrumb-item active">Total por Zona</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="ri-arrow-left-line me-1"></i> Volver</a>
                </div>

                <!-- Filtros -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <form method="GET">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Desde</label>
                                    <input type="date" class="form-control" name="start_date" value="<?= $startDate ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Hasta</label>
                                    <input type="date" class="form-control" name="end_date" value="<?= $endDate ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Año Fiscal</label>
                                    <select class="form-select" name="fiscal_year_id">
                                        <option value="">Todos</option>
                                        <?php foreach ($fiscalYears as $fy): ?>
                                            <option value="<?= $fy['id'] ?>" <?= $fiscalYearId == $fy['id'] ? 'selected' : '' ?>><?= $fy['year'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="ri-search-line me-1"></i> Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="reportTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Zona</th>
                                <th>N° Contratos</th>
                                <th>N° Pagos</th>
                                <th>Total Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($zones as $zone): ?>
                            <tr>
                                <td class="fw-bold fs-5"><?= htmlspecialchars($zone['zone_name']) ?></td>
                                <td><span class="badge bg-label-info"><?= $zone['contracts_count'] ?></span></td>
                                <td><span class="badge bg-label-secondary"><?= $zone['payments_count'] ?></span></td>
                                <td class="fw-bold text-dark">Bs. <?= number_format($zone['total_accumulated'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>TOTALES</td>
                                <td><?= array_sum(array_column($zones, 'contracts_count')) ?></td>
                                <td><?= array_sum(array_column($zones, 'payments_count')) ?></td>
                                <td>Bs. <?= number_format(array_sum(array_column($zones, 'total_accumulated')), 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    $('#reportTable').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="ri-file-excel-line me-1"></i> Excel', className: 'btn btn-success btn-sm me-1', title: 'Total por Zona' },
            { extend: 'pdfHtml5', text: '<i class="ri-file-pdf-line me-1"></i> PDF', className: 'btn btn-danger btn-sm me-1', title: 'Total por Zona' }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        order: [[3, 'desc']]
    });
});
</script>
