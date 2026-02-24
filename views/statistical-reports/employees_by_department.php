<?php
// views/statistical-reports/employees_by_department.php
require_once __DIR__ . '/../../models/StatisticalReportModel.php';

$statsModel = new StatisticalReportModel();
$chartData = $statsModel->getEmployeesByDepartment();

$labels = json_encode($chartData['labels']);
$data = json_encode($chartData['data']);
$backgroundColor = json_encode($chartData['backgroundColor']);
$borderColor = json_encode($chartData['borderColor']);

$report_title = "Empleados por Departamento";
?>

<div class="card-body">
    <h5 class="card-title text-primary"><i class="ri-pie-chart-2-fill me-1"></i> <?php echo $report_title; ?></h5>
    <div class="py-3">
        <div style="position: relative; height: 400px; width: 100%;">
            <canvas id="employeesDeptChart"></canvas>
        </div>
    </div>
</div>

<script src="<?php echo vendor('libs/chartjs/chartjs.js'); ?>"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('employeesDeptChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $labels; ?>,
                    datasets: [{
                        label: 'Empleados',
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
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
</script>
