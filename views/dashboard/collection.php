<?php
/**
 * Collection Dashboard - Panel de Cobranza
 */

// Include Config & Auth
require_once __DIR__ . '/../../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $loginUrl = url('views/auth/login.php');
    header("Location: $loginUrl");
    exit();
}

$user = [
    'id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['user_name'] ?? 'Usuario',
];
$current_user = ['full_name' => $user['name']];

// Sincronizar automáticamente la tasa del Euro
require_once __DIR__ . '/../../controllers/InfractionsController.php';
$infractionsCtrl = new InfractionsController();
$infractionsCtrl->syncEuroWithSystemRates();

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

// Fetch Data
require_once __DIR__ . '/../../models/CollectionReportModel.php';
$model = new CollectionReportModel();
$stats = $model->getDashboardStats();
$monthlyRevenue = $model->getMonthlyRevenue(6);
$zoneRevenue = $model->getRevenueByZone(date('Y-m-01'), date('Y-m-t')); // This month logic for pie chart
$recentPayments = $model->getRecentPayments(6);

// Prepare JSON for JS
$monthlyRevLabels = json_encode($monthlyRevenue['labels']);
$monthlyRevData = json_encode($monthlyRevenue['data']);

$zoneLabels = json_encode($zoneRevenue['labels']);
$zoneData = json_encode($zoneRevenue['data']);
$zoneBg = json_encode($zoneRevenue['backgroundColor']);
$zoneBorder = json_encode($zoneRevenue['borderColor']);
?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6">
        <!-- Welcome Card -->
        <div class="col-md-12 col-xxl-8" style="width: 100%;">
            <div class="card mb-4">
                <div class="d-flex align-items-end row">
                    <div class="col-md-8">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Panel de Control de <span class="fw-bold text-primary">Cobranza</span> 💰</h4>
                            <p class="mb-0">Bienvenido, <?php echo htmlspecialchars($current_user['full_name']); ?>. Aquí tienes el resumen financiero de hoy.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="col-12">
            <div class="row g-4">
                <!-- Card 1: Revenue Today -->
                <div class="col-lg-3 col-sm-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">Recaudado Hoy</h6>
                                    <h4 class="mb-0 text-success"><?php echo 'Bs. ' . number_format($stats['today_revenue'], 2, ',', '.'); ?></h4>
                                </div>
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded-3 bg-label-success"><i class="ri-money-dollar-circle-line ri-2x"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Revenue This Month -->
                <div class="col-lg-3 col-sm-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">Recaudado este Mes</h6>
                                    <h4 class="mb-0 text-primary"><?php echo 'Bs. ' . number_format($stats['month_revenue'], 2, ',', '.'); ?></h4>
                                </div>
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded-3 bg-label-primary"><i class="ri-calendar-check-line ri-2x"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Active Payers -->
                <div class="col-lg-3 col-sm-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">Pagadores Únicos (Mes)</h6>
                                    <h4 class="mb-0 text-info"><?php echo number_format($stats['active_payers']); ?></h4>
                                </div>
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded-3 bg-label-info"><i class="ri-user-follow-line ri-2x"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Recent Transactions -->
                <div class="col-lg-3 col-sm-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">Transacciones (30d)</h6>
                                    <h4 class="mb-0 text-warning"><?php echo number_format($stats['recent_transactions']); ?></h4>
                                </div>
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded-3 bg-label-warning"><i class="ri-file-list-3-line ri-2x"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="col-12 mt-4">
            <div class="row g-4">
                <!-- Bar Chart: Monthly Revenue -->
                <div class="col-lg-8 col-md-12">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Tendencia de Recaudación (6 Meses)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyRevenueChart" style="min-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart: Revenue by Zone -->
                <div class="col-lg-4 col-md-12">
                     <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Por Zona (Este Mes)</h5>
                        </div>
                        <div class="card-body">
                             <canvas id="zoneRevenueChart" style="min-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments Table -->
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Pagos Recientes</h5>
                    <a href="<?php echo url('views/collection-reports/index.php?report_type=activity_history'); ?>" class="btn btn-sm btn-outline-primary">Ver Historial Completo</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Pagador</th>
                                <th>Tipo</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <?php foreach ($recentPayments as $payment): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($payment['awardee_name']); ?></strong></td>
                                <td>
                                    <?php if ($payment['type'] == 'Canon'): ?>
                                        <span class="badge bg-label-primary me-1">Canon</span>
                                    <?php else: ?>
                                        <span class="badge bg-label-danger me-1">Multa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    <?php echo 'Bs. ' . number_format($payment['amount'], 2, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recentPayments)): ?>
                                <tr><td colspan="4" class="text-center text-muted">No hay pagos recientes.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // Bar Chart
    const barCtx = document.getElementById('monthlyRevenueChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $monthlyRevLabels; ?>,
                datasets: [{
                    label: 'Recaudación (Bs.)',
                    data: <?php echo $monthlyRevData; ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Standard Bootstrap Info/Primary
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
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
                            callback: function(value) { return 'Bs. ' + value.toLocaleString('es-VE'); }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                         callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VES' }).format(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
    }

    // Pie Chart
    const pieCtx = document.getElementById('zoneRevenueChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $zoneLabels; ?>,
                datasets: [{
                    data: <?php echo $zoneData; ?>,
                    backgroundColor: <?php echo $zoneBg; ?>,
                    borderColor: <?php echo $zoneBorder; ?>,
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
