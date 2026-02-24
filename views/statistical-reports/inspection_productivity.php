<?php
// views/statistical-reports/inspection_productivity.php
require_once __DIR__ . '/../../models/StatisticalReportModel.php';

$statsModel = new StatisticalReportModel();
// Default 12 months
$months = 12;
$chartData = $statsModel->getInspectionProductivity($months);

$labels = json_encode($chartData['labels']);
$counts = json_encode($chartData['data']);
$report_title = "Productividad de la Inspección (Últimos 12 meses)";
?>

<div class="card-body">
    <h5 class="card-title text-primary"><i class="ri-line-chart-fill me-1"></i> <?php echo $report_title; ?></h5>
    <div class="py-3">
        <div style="position: relative; height: 400px; width: 100%;">
            <canvas id="inspectionProdChart"></canvas>
        </div>
    </div>
</div>

<script src="<?php echo vendor('libs/chartjs/chartjs.js'); ?>"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('inspectionProdChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo $labels; ?>,
                    datasets: [{
                        label: 'Inspecciones Realizadas',
                        data: <?php echo $counts; ?>,
                        backgroundColor: 'rgba(66, 66, 66, 0.2)',
                        borderColor: '#424242',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    });
</script>
