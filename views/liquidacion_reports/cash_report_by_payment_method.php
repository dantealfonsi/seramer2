<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/LiquidacionReportController.php';

$controller = new LiquidacionReportController();
$dataResults = $controller->cashreportbypaymentmethod();
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
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #fff3e0 !important;">
                            <i class="ri-bank-card-line" style="color: #ffb822; font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #43495b;">Caja por Métodos de Pago</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Reportes</a></li>
                                    <li class="breadcrumb-item active">Métodos de Pago</li>
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
                                    <label class="form-label small text-uppercase fw-bold text-muted">Desde</label>
                                    <input type="date" class="form-control" name="start_date" value="<?= $startDate ?>">
                                </div>
                                <div class="col-md-2">
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
                                <div class="col-md-3">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Zonas</label>
                                    <select class="form-select select2" name="zone_ids[]" multiple style="min-height: 38px;">
                                        <?php foreach ($zones as $zone): ?>
                                            <option value="<?= $zone['id'] ?>" <?= $zoneIds && in_array($zone['id'], $zoneIds) ? 'selected' : '' ?>><?= htmlspecialchars($zone['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="ri-search-line me-1"></i> Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Resumen por Método -->
                <?php if (!empty($totalsByPaymentMethod)): ?>
                <div class="row mb-4 g-3">
                    <?php foreach ($totalsByPaymentMethod as $tm): ?>
                    <div class="col-md-3">
                        <div class="card shadow-none bg-label-warning border-0">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <i class="ri-bank-card-2-line text-warning"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h6 class="mb-0 text-warning fw-bold text-truncate"><?= htmlspecialchars($tm['metodo_pago']) ?></h6>
                                        <p class="mb-0 fw-bold text-dark">Bs. <?= number_format($tm['total'], 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="reportTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th>Adjudicatario</th>
                                <th>Rubro</th>
                                <th>Zona</th>
                                <th>Local</th>
                                <th>Mes</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($row['metodo_pago']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['adjudicatario']) ?></td>
                                <td><?= htmlspecialchars($row['rubro'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-label-info"><?= htmlspecialchars($row['zona']) ?></span></td>
                                <td><span class="badge bg-label-secondary"><?= htmlspecialchars($row['local']) ?></span></td>
                                <td><?= htmlspecialchars($row['mes_pagado']) ?></td>
                                <td class="fw-bold fs-6">Bs. <?= number_format($row['monto'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr class="fw-bold">
                                <td colspan="6" class="text-end">TOTAL GENERAL:</td>
                                <td>Bs. <?= number_format(array_sum(array_column($data, 'monto')), 2) ?></td>
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
            { extend: 'excelHtml5', text: '<i class="ri-file-excel-line me-1"></i> Excel', className: 'btn btn-success btn-sm me-1', title: 'Caja por Método de Pago' },
            { extend: 'pdfHtml5', text: '<i class="ri-file-pdf-line me-1"></i> PDF', className: 'btn btn-danger btn-sm me-1', title: 'Caja por Método de Pago' }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
    });
});
</script>
