<?php
// views/collection-reports/revenue_by_zone.php
require_once __DIR__ . '/../../models/CollectionReportModel.php';

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$model = new CollectionReportModel();
$chartData = $model->getRevenueByZone($startDate, $endDate);

$labels = json_encode($chartData['labels']);
$data = json_encode($chartData['data']);
$backgroundColor = json_encode($chartData['backgroundColor']);
$borderColor = json_encode($chartData['borderColor']);
?>

<div class="card-body">
    <h5 class="card-title text-primary"><i class="ri-map-pin-range-line me-1"></i> Distribución de Recaudación por Zona</h5>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="py-3">
                <div style="position: relative; height: 400px; width: 100%;">
                    <canvas id="zoneChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
             <div class="table-responsive mt-3">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Zona</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = array_sum($chartData['data']);
                        for($i=0; $i<count($chartData['labels']); $i++): 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($chartData['labels'][$i]); ?></td>
                            <td class="text-end"><?php echo 'Bs. ' . number_format($chartData['data'][$i], 2, ',', '.'); ?></td>
                        </tr>
                        <?php endfor; ?>
                        <tr class="table-active fw-bold">
                            <td>TOTAL</td>
                            <td class="text-end"><?php echo 'Bs. ' . number_format($total, 2, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('zoneChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'pie', // Or 'doughnut'
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    label: 'Recaudación (Bs.)',
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
                    legend: {
                        position: 'right',
                    },
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
