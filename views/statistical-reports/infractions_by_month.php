<?php
// views/statistical-reports/infractions_by_month.php
require_once __DIR__ . '/../../models/StatisticalReportModel.php';

$statsModel = new StatisticalReportModel();
$reportMode = $_GET['report_mode'] ?? 'last_6_months'; // Fix: Get from GET if not in scope
// Mode dictates lookback period: 'last_6_months' or 'annual' (defaults to 12 months for annual)
$months = ($reportMode === 'last_6_months') ? 6 : 12; 
if ($reportMode === 'annual') {
    // If specific year logic is needed, Model would need update. 
    // For now, mapping 'annual' to last 12 months as per request context
    $months = 12;
}

$chartData = $statsModel->getInfractionsByMonth($months);

$labels = json_encode($chartData['labels']);
$counts = json_encode($chartData['data']);
$report_title = "Infracciones por Mes (" . ($reportMode === 'last_6_months' ? 'Últimos 6 Meses' : 'Anual') . ")";
?>

<div class="card-body">
    <h5 class="card-title text-primary"><i class="ri-bar-chart-fill me-1"></i> <?php echo $report_title; ?></h5>
    <div class="py-3">
        <div style="position: relative; height: 400px; width: 100%;">
            <canvas id="infractionsMonthChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('infractionsMonthChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo $labels; ?>,
                    datasets: [{
                        label: 'Número de Infracciones',
                        data: <?php echo $counts; ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' }
                    }
                }
            });
        }
    });
</script>
