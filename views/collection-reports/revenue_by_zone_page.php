<?php
/**
 * Vista: Dinero Recaudado por Zona (Gráfico) - Reporte Individual de Cobranza
 */
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../models/CollectionReportModel.php';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$model = new CollectionReportModel();

$startDate = $_GET['start_date'] ?? null;
$endDate   = $_GET['end_date'] ?? null;
$filtered  = isset($_GET['start_date']);

$chartData = $filtered ? $model->getRevenueByZone($startDate, $endDate) : ['labels' => [], 'data' => []];
$labels = json_encode($chartData['labels'] ?? []);
$data = json_encode($chartData['data'] ?? []);
$backgroundColor = json_encode($chartData['backgroundColor'] ?? []);
$borderColor = json_encode($chartData['borderColor'] ?? []);
?>

<style>
    .main-container { padding: 1.5rem; background-color: #f5f5f9; min-height: calc(100vh - 100px); }
</style>

<div class="main-content main-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #fff2d6 !important;">
                            <i class="ri-pie-chart-line" style="color: #ffab00; font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold" style="color: #43495b;">Dinero Recaudado por Zona</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Cobranza</a></li>
                                    <li class="breadcrumb-item active">Recaudación por Zona</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Volver al Menú
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="ri-filter-line me-1 text-primary"></i> Filtro de Fechas</h6>
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Desde</label>
                        <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Hasta</label>
                        <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="ri-search-line me-1"></i> Filtrar Gráfico
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <?php if (!$filtered): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ri-pie-chart-2-line" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Seleccione un rango de fechas para ver el reporte</h5>
                    </div>
                <?php elseif (empty($chartData['data'])): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ri-inbox-line" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No hay recaudación registrada en este período</h5>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-lg-8 border-end">
                            <div style="position: relative; height: 400px; width: 100%;">
                                <canvas id="zoneChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Zona</th>
                                            <th class="text-end">Monto (Bs.)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total = array_sum($chartData['data']);
                                        for($i=0; $i<count($chartData['labels']); $i++): 
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($chartData['labels'][$i]) ?></td>
                                            <td class="text-end fw-semibold"><?= number_format($chartData['data'][$i], 2, ',', '.') ?></td>
                                        </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                    <tfoot class="table-dark">
                                        <tr class="fw-bold fs-6">
                                            <td>TOTAL</td>
                                            <td class="text-end text-success">Bs. <?= number_format($total, 2, ',', '.') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script src="<?= vendor('libs/chartjs/chartjs.js') ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('zoneChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= $labels ?>,
                datasets: [{
                    label: 'Recaudación (Bs.)',
                    data: <?= $data ?>,
                    backgroundColor: <?= $backgroundColor ?>,
                    borderColor: <?= $borderColor ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VES' }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
