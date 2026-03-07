<?php
/**
 * Vista: Ganancias Totales por Rango de Fecha (Gráfico) - Reporte Individual de Cobranza
 */
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../models/CollectionReportModel.php';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$model = new CollectionReportModel();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');
$filtered  = isset($_GET['start_date']);

$chartData = $filtered ? $model->getRevenueByDateRange($startDate, $endDate) : ['labels' => [], 'data' => []];
$labels    = json_encode($chartData['labels']);
$data      = json_encode($chartData['data']);
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
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e8fadf !important;">
                            <i class="ri-line-chart-line" style="color: #71dd37; font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold" style="color: #43495b;">Ganancias por Rango de Fecha</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Cobranza</a></li>
                                    <li class="breadcrumb-item active">Recaudación por Fecha</li>
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
                <h6 class="mb-3"><i class="ri-filter-line me-1 text-primary"></i> Rango de Fechas</h6>
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Desde</label>
                        <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Hasta</label>
                        <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-search-line me-1"></i> Generar Gráfico
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Gráfico / Resultado -->
        <?php if ($filtered): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h5 class="text-primary">Ingresos Totales desde <?= date('d/m/Y', strtotime($startDate)) ?> hasta <?= date('d/m/Y', strtotime($endDate)) ?></h5>
                    <h3 class="text-success fw-bold">
                        Bs. <?= number_format(array_sum($chartData['data']), 2, ',', '.') ?>
                    </h3>
                </div>
                <?php if (empty($chartData['data'])): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="ri-bar-chart-2-line" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Sin datos para el período seleccionado</h5>
                    </div>
                <?php else: ?>
                    <div style="position: relative; height: 400px; width: 100%;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5 text-muted">
                <i class="ri-bar-chart-line" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Seleccione un rango de fechas para generar el gráfico</h5>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script src="<?php echo vendor('libs/chartjs/chartjs.js'); ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $labels ?>,
                datasets: [{
                    label: 'Recaudación Diaria (Bs.)',
                    data: <?= $data ?>,
                    backgroundColor: 'rgba(113, 221, 55, 0.6)',
                    borderColor: 'rgba(113, 221, 55, 1)',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Bs. ' + value.toLocaleString('es-VE');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VES' }).format(context.parsed.y);
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
