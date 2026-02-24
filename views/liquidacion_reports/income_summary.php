<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/LiquidacionReportController.php';

$controller = new LiquidacionReportController();
$dataResults = $controller->incomesummary();
extract($dataResults);

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .main-container { padding: 1.5rem; background-color: #f5f5f9; width: 100%; }
    .table thead th { background-color: #000000 !important; color: white !important; text-transform: uppercase; font-weight: 600; padding: 1rem !important; }
    .zone-row { background-color: #f8f9fa !important; font-weight: 700; color: #696cff; }
    .total-zone-row { background-color: #e7e7ff !important; font-weight: 700; }
</style>

<div class="main-content main-container">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e0f2ff !important;">
                            <i class="ri-bar-chart-box-line" style="color: #03c3ec; font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #43495b;">Resumen de Ingresos</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Reportes</a></li>
                                    <li class="breadcrumb-item active">Resumen de Ingresos</li>
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
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th class="py-3">SECTOR</th>
                                <th class="text-center py-3">CANT. RECIBOS</th>
                                <th class="text-end py-3">MONTO RECAUDADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataByZone as $zId => $zoneData): ?>
                            <tr class="zone-row">
                                <td colspan="3" class="ps-4">ZONA: <?= strtoupper(htmlspecialchars($zoneData['zona'])) ?></td>
                            </tr>
                            <?php foreach ($zoneData['sectores'] as $sector): ?>
                            <tr>
                                <td class="ps-5"><?= htmlspecialchars($sector['sector']) ?></td>
                                <td class="text-center"><?= $sector['cantidad_recibos'] ?: '-' ?></td>
                                <td class="text-end fw-bold"><?= $sector['monto_recaudado'] > 0 ? 'Bs. ' . number_format($sector['monto_recaudado'], 2) : 'Bs. 0,00' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (isset($totalsByZoneMap[$zId])): $zt = $totalsByZoneMap[$zId]; ?>
                            <tr class="total-zone-row">
                                <td class="ps-4">TOTAL INGRESO <?= strtoupper(htmlspecialchars($zoneData['zona'])) ?></td>
                                <td class="text-center"><?= $zt['cantidad_recibos'] ?></td>
                                <td class="text-end">Bs. <?= number_format($zt['monto_recaudado'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr class="fw-bold">
                                <td class="ps-4">TOTAL GENERAL RECAUDADO</td>
                                <td class="text-center"><?= $totals['total_general']['total_recibos'] ?></td>
                                <td class="text-end">Bs. <?= number_format($totals['total_general']['total_recaudado'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
