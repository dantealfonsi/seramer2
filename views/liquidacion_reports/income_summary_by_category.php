<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/LiquidacionReportController.php';

$controller = new LiquidacionReportController();
$dataResults = $controller->incomesummarybycategory();
extract($dataResults);

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
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e8fadf !important;">
                            <i class="ri-stack-line" style="color: #71dd37; font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #43495b;">Resumen de Ingresos por Rubro</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Reportes</a></li>
                                    <li class="breadcrumb-item active">Resumen por Rubro</li>
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
                                <div class="col-md-2">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Mes</label>
                                    <select class="form-select" name="month">
                                        <option value="">Todos</option>
                                        <?php foreach ($monthNames as $mNum => $mName): ?>
                                            <option value="<?= $mNum ?>" <?= $month == $mNum ? 'selected' : '' ?>><?= $mName ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Año</label>
                                    <input type="number" class="form-control" name="year" value="<?= $year ?>" placeholder="Todos">
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
                                <div class="col-md-3">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Tipo Contrato</label>
                                    <select class="form-select" name="contract_type">
                                        <option value="">Todos</option>
                                        <option value="simultaneous" <?= $contractType == 'simultaneous' ? 'selected' : '' ?>>Simultánea</option>
                                        <option value="advance" <?= $contractType == 'advance' ? 'selected' : '' ?>>Anticipada</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="ri-search-line me-1"></i> Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="reportTable" class="table table-hover table-bordered align-middle">
                        <thead>
                            <tr>
                                <th class="py-3">RUBRO</th>
                                <th class="text-center py-3">CANT. RECIBOS</th>
                                <th class="text-end py-3">MONTO RECAUDADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($row['rubro']) ?></td>
                                <td class="text-center fw-bold"><?= $row['cantidad_recibos'] ?: '-' ?></td>
                                <td class="text-end fw-bold text-dark">Bs. <?= number_format($row['monto_recaudado'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr class="fw-bold">
                                <td>TOTAL GENERAL</td>
                                <td class="text-center"><?= $total['total_recibos'] ?></td>
                                <td class="text-end">Bs. <?= number_format($total['total_recaudado'], 2) ?></td>
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
            { extend: 'excelHtml5', text: '<i class="ri-file-excel-line me-1"></i> Excel', className: 'btn btn-success btn-sm me-1', title: 'Resumen Ingresos por Rubro' },
            { extend: 'pdfHtml5', text: '<i class="ri-file-pdf-line me-1"></i> PDF', className: 'btn btn-danger btn-sm me-1', title: 'Resumen Ingresos por Rubro' }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        order: [[2, 'desc']]
    });
});
</script>
