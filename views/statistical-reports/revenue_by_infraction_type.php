<?php
// views/statistical-reports/revenue_by_infraction_type.php
require_once __DIR__ . '/../../models/StatisticalReportModel.php';

$statsModel = new StatisticalReportModel();
$chartData = $statsModel->getRevenueByInfractionType();

$labels = json_encode($chartData['labels']);
$data = json_encode($chartData['data']);
$backgroundColor = json_encode($chartData['backgroundColor']);
$borderColor = json_encode($chartData['borderColor']);

$report_title = "Ingresos Estimados por Tipo de Infracción";
?>

<div class="card-body">
    <h5 class="card-title text-primary"><i class="ri-money-dollar-circle-line me-1"></i> <?php echo $report_title; ?></h5>
    <div class="py-3">
        <div style="position: relative; height: 400px; width: 100%;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'pie', // Pie chart is good for slices of revenue
                data: {
                    labels: <?php echo $labels; ?>,
                    datasets: [{
                        label: 'Ingresos (Bs.)',
                        data: <?php echo $data; ?>,
                        backgroundColor: <?php echo $backgroundColor; ?>,
                        borderColor: <?php echo $borderColor; ?>,
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
                                    if (label) {
                                        label += ': ';
                                    }
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
