<?php
// views/statistical-reports/performance_content.php
require_once __DIR__ . '/../../models/StatisticalReportModel.php';

$statsModel = new StatisticalReportModel();
$period = $_GET['report_mode'] ?? 'annual';
$inspectorId = $_GET['filter_inspector'] ?? null; // Corrección: Coincidir con el ID del select en el JS

// Mapeo del parámetro de periodo del JS ('period') a lo que espera el modelo
if ($period === 'period') {
     // Si el modo es 'period', comprobamos si hay un filtro de año/mes o lo tratamos como anual por defecto
     $period = 'annual'; 
} elseif ($period === 'inspector') {
    // Si el modo es inspector, por defecto mostramos el histórico (anual) de ese inspector
    $period = 'annual';
}

$chartData = $statsModel->getInspectorPerformance($period, $inspectorId);

$labels = json_encode($chartData['labels']);
$counts = json_encode($chartData['data']);
$report_title = "Desempeño de Inspectores - Inspecciones Realizadas";
?>

<div class="card-body">
    <h5 class="card-title text-primary"><i class="ri-medal-line me-1"></i> <?php echo $report_title; ?></h5>
    
    <?php if (empty($chartData['data'])): ?>
        <div class="text-center py-5">
            <i class="ri-bar-chart-horizontal-line text-muted" style="font-size: 3rem;"></i>
            <h5 class="text-muted mt-2">No se encontraron datos de inspecciones para el filtro seleccionado.</h5>
        </div>
    <?php else: ?>
        <div class="py-3">
            <div style="position: relative; height: 400px; width: 100%;">
                <canvas id="inspectorPerfChart"></canvas>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('inspectorPerfChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo $labels; ?>,
                    datasets: [{
                        label: 'Inspecciones Completadas',
                        data: <?php echo $counts; ?>,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Horizontal bar chart for leaderboard style
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } }
                    },
                    plugins: {
                        legend: { display: false },
                        title: { display: true, text: 'Top Inspectores por Volumen de Inspecciones' }
                    }
                }
            });
        }
    });
</script>
