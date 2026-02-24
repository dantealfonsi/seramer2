<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/LiquidacionReportController.php';

$controller = new LiquidacionReportController();
$dataResults = $controller->incomebycategory();
extract($dataResults);

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .main-container { padding: 1.5rem; background-color: #f5f5f9; width: 100%; }
    .table thead th { background-color: #000000 !important; color: white !important; text-transform: uppercase; font-weight: 600; padding: 1rem !important; }
    .rubro-header { background-color: #f8f9fa; border-left: 5px solid #696cff; }
</style>

<div class="main-content main-container">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #f0f0f0 !important;">
                            <i class="ri-folder-chart-line" style="color: #4b4b4b; font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #43495b;">Ingresos por Rubro</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Reportes</a></li>
                                    <li class="breadcrumb-item active">Ingresos por Rubro</li>
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
                                        <?php foreach ($monthNames as $mNum => $mName): ?>
                                            <option value="<?= $mNum ?>" <?= $month == $mNum ? 'selected' : '' ?>><?= $mName ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Año</label>
                                    <input type="number" class="form-control" name="year" value="<?= $year ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-uppercase fw-bold text-muted">Zona</label>
                                    <select class="form-select" name="zone_id">
                                        <option value="">Todas</option>
                                        <?php foreach ($zones as $zone): ?>
                                            <option value="<?= $zone['id'] ?>" <?= $zoneId == $zone['id'] ? 'selected' : '' ?>><?= htmlspecialchars($zone['name']) ?></option>
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

                <div class="row mb-5 g-3">
                    <div class="col-md-6">
                        <div class="card shadow-none bg-label-secondary border-0 text-center">
                            <div class="card-body py-3">
                                <h6 class="mb-1 text-muted">Total de Facturas</h6>
                                <h3 class="mb-0 fw-bold"><?= number_format($totals['total_facturas']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-none bg-label-primary border-0 text-center">
                            <div class="card-body py-3">
                                <h6 class="mb-1 text-primary">Total de Ingresos</h6>
                                <h3 class="mb-0 fw-bold text-primary">Bs. <?= number_format($totals['total_ingresos'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <?php foreach ($dataByCategory as $rubro => $rows): ?>
                <div class="mb-5">
                    <div class="rubro-header p-3 mb-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary"><?= strtoupper(htmlspecialchars($rubro)) ?></h5>
                        <div class="text-muted d-flex gap-4">
                            <span>Facturas: <strong><?= count($rows) ?></strong></span>
                            <span>Total: <strong class="text-dark">Bs. <?= number_format(array_sum(array_column($rows, 'monto_total')), 2) ?></strong></span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover border align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Sector</th>
                                    <th>N° SERIAL</th>
                                    <th>Rubro</th>
                                    <th class="text-end">Pago Total</th>
                                    <th>N° FACTURA</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; foreach ($rows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['sector']) ?></td>
                                    <td><?= $counter++ ?></td>
                                    <td><?= htmlspecialchars($rubro) ?></td>
                                    <td class="text-end fw-bold text-dark"><?= number_format($row['monto_total'], 2) ?></td>
                                    <td><span class="badge bg-label-dark"><?= $row['factura_id'] ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($row['fecha_factura'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
