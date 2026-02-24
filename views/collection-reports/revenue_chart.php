<?php
// views/collection-reports/revenue_chart.php
require_once __DIR__ . '/../../models/CollectionReportModel.php';

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$model = new CollectionReportModel();
$chartData = $model->getRevenueByDateRange($startDate, $endDate);

$labels = json_encode($chartData['labels']);
$data = json_encode($chartData['data']);
?>

<div class="card-body">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h5 class="text-primary">Ingresos Totales desde <?php echo date('d/m/Y', strtotime($startDate)); ?> hasta <?php echo date('d/m/Y', strtotime($endDate)); ?></h5>
            <h3 class="text-success fw-bold">
                <?php echo 'Bs. ' . number_format(array_sum($chartData['data']), 2, ',', '.'); ?>
            </h3>
        </div>
    </div>

    <div class="py-3">
        <div style="position: relative; height: 400px; width: 100%;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<script src="<?php echo vendor('libs/chartjs/chartjs.js'); ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    label: 'Recaudación Diaria (Bs.)',
                    data: <?php echo $data; ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
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
                                if (label) {
                                    label += ': ';
                                }
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
