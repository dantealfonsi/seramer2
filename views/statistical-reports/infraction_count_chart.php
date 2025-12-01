<?php
// views/reports/infraction_count_chart.php - Contenido del Reporte Gráfico de Conteo de Infracciones

require_once __DIR__ . '/../../models/InfractionsModel.php'; 

// --- 1. Lógica de Filtros y Modo ---
$reportMode = $_GET['report_mode'] ?? 'date_range';
$infractionModel = new InfractionsModel();
$chartData = [];
$error_message = null;

// --- 2. Procesamiento basado en el Modo de Reporte ---
switch ($reportMode) {
    case 'last_6_months':
        // Filtro por defecto: Últimos 6 meses, agrupado por mes
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-6 months'));
        // El modelo debe saber que debe agrupar por mes en este caso
        $chartData = $infractionModel->countInfractionsByMode($startDate, $endDate, 'month');
        $report_title = "Conteo Mensual de Infracciones (Últimos 6 Meses)";
        break;

    case 'annual':
        $year = $_GET['filter_year'] ?? date('Y');
        $startDate = $year . '-01-01';
        $endDate = $year . '-12-31';
        // El modelo debe agrupar por mes
        $chartData = $infractionModel->countInfractionsByMode($startDate, $endDate, 'month');
        $report_title = "Conteo Mensual de Infracciones para el Año " . htmlspecialchars($year);
        break;

    case 'weekly':
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        if (!$startDate || !$endDate) {
             $error_message = "Seleccione un rango de fechas para el análisis Semanal.";
        } else {
            // El modelo debe agrupar por semana
            $chartData = $infractionModel->countInfractionsByMode($startDate, $endDate, 'week');
            $report_title = "Conteo Semanal de Infracciones";
        }
        break;

    case 'date_range':
    default:
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        if (!$startDate || !$endDate) {
             $error_message = "Seleccione un rango de fechas para el análisis Diario.";
        } else {
            // Agrupación por defecto (diario)
            $chartData = $infractionModel->countInfractionsByMode($startDate, $endDate, 'day');
            $report_title = "Conteo Diario de Infracciones";
        }
        break;
}

// 3. Conversión de Datos para JavaScript
$labels = json_encode(array_column($chartData, 'label')); // label puede ser Fecha, Mes-Año, o Semana
$counts = json_encode(array_column($chartData, 'count')); 

// Subtítulo
$subtitle = $error_message ?: "Período analizado: " . date('d/m/Y', strtotime($startDate)) . " al " . date('d/m/Y', strtotime($endDate));
?>

<div class="card-body">
    <h5 class="card-title text-primary"><i class="ri-line-chart-line me-1"></i> <?php echo $report_title; ?></h5>
    <p class="text-muted mb-4 small"><?php echo htmlspecialchars($subtitle); ?></p>
    
    <?php if ($error_message): ?>
        <div class="text-center py-5 alert alert-warning">
            <i class="ri-calendar-2-line" style="font-size: 3rem;"></i>
            <h5 class="mt-2"><?php echo $error_message; ?></h5>
        </div>
    <?php elseif (empty($chartData)): ?>
        <div class="text-center py-5">
            <i class="ri-bar-chart-2-line text-muted" style="font-size: 3rem;"></i>
            <h5 class="text-muted mt-2">No se encontraron infracciones en el rango seleccionado.</h5>
        </div>
    <?php else: ?>
        <div class="py-3">
            <div style="position: relative; height: 400px; width: 100%;">
                <canvas id="infractionChart"></canvas>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Solo inicializar si hay datos
    if (<?php echo json_encode(!empty($chartData)); ?>) {
        
        const chartData = {
            labels: <?php echo $labels; ?>,
            datasets: [{
                label: 'Total de Infracciones Reportadas',
                data: <?php echo $counts; ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.5)', 
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 2,
                fill: true, 
                tension: 0.3
            }]
        };

        const config = {
            type: 'bar', // Manteniendo 'bar' para mejor visualización
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false, 
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Infracciones'
                        },
                        ticks: {
                            precision: 0 
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: '<?php echo ($reportMode === 'annual' || $reportMode === 'last_6_months') ? 'Mes/Año' : (($reportMode === 'weekly') ? 'Semana del Año' : 'Fecha'); ?>'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                }
            }
        };

        const ctx = document.getElementById('infractionChart');
        if (ctx) {
            if (window.infractionChartInstance) {
                window.infractionChartInstance.destroy();
            }
            window.infractionChartInstance = new Chart(ctx, config);
        }
    }
});
</script>