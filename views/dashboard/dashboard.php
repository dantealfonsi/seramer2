<?php
/**
 * Dashboard principal - Requiere autenticación
 */

// Incluir configuración
require_once __DIR__ . '/../../config/app.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // No autenticado - redirigir al login
    $loginUrl = url('views/auth/login.php');
    header("Location: $loginUrl");
    exit();
}

// Redireccionar si es el departamento de Cobranza
if (isset($_SESSION['selected_department']) && $_SESSION['selected_department'] === 'Cobranza') {
    $collectionDashboardUrl = url('views/dashboard/collection.php');
    header("Location: $collectionDashboardUrl");
    exit();
}

// Redireccionar si es el departamento de Liquidacion
if (isset($_SESSION['selected_department']) && $_SESSION['selected_department'] === 'Liquidacion') {
    $settlementDashboardUrl = url('views/dashboard/settlement.php');
    header("Location: $settlementDashboardUrl");
    exit();
}

// Verificar si la sesión es válida (no expirada)
$session_timeout = 1800; // 30 minutos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    // Sesión expirada
    session_unset();
    session_destroy();
    $loginUrl = url('views/auth/login.php');
    header("Location: $loginUrl");
    exit();
}

// Actualizar timestamp de actividad
$_SESSION['last_activity'] = time();

// Sincronizar automáticamente la tasa del Euro
require_once __DIR__ . '/../../controllers/InfractionsController.php';
$infractionsCtrl = new InfractionsController();
$infractionsCtrl->syncEuroWithSystemRates();

// Obtener datos del usuario
$user = [
    'id' => $_SESSION['user_id'] ?? null,
    'email' => $_SESSION['user_email'] ?? null,
    'name' => $_SESSION['user_name'] ?? 'Usuario',
    'role' => $_SESSION['user_role'] ?? 'user'
];

$current_user = ['full_name' => $user['name']]; // Usar el nombre de usuario de la sesión

// Incluir header y layouts
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

// Fetch Dashboard Stats
require_once __DIR__ . '/../../models/StatisticalReportModel.php';
$statsModel = new StatisticalReportModel();
$dashboardStats = $statsModel->getDashboardStats();
?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6">
        <div class="col-md-12 col-xxl-8" style="width: 100%;">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-md-6 order-2 order-md-1" >
                        <div class="card-body">
                            <h4 class="card-title mb-4">Bienvenido <span class="fw-bold"><?php echo htmlspecialchars($current_user['full_name'] ?? 'Usuario'); ?></span> 🎉</h4>
                            <p class="mb-0">Aquí puedes encontrar un resumen de la actividad reciente del sistema.</p>
                        </div>
                    </div>
                    <div class="col-md-6 text-center text-md-end order-1 order-md-2">
                        <div class="card-body pb-0 px-0 pt-2">
                            <!-- Nota: Reemplazado con una imagen de placeholder para que el código sea autocontenido -->
                            <img src="<?php echo img('illustrations/rocket.png'); ?>" height="186" class="scaleX-n1-rtl" alt="View Profile" style="visibility: visible;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Métricas -->
        <div class="col-12 mt-4">
            <div class="row g-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">Infracciones Activas</h6>
                                    <div class="d-flex align-items-center">
                                        <h4 class="mb-0 me-2"><?php echo $dashboardStats['active_infractions']; ?></h4>
                                        <small class="text-success fw-semibold"><i class="ri-arrow-up-s-line align-middle"></i>+5%</small>
                                    </div>
                                </div>
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded-3 bg-label-warning"><i class="ri-alert-line ri-2x"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">Infracciones Resueltas</h6>
                                    <div class="d-flex align-items-center">
                                        <h4 class="mb-0 me-2"><?php echo $dashboardStats['resolved_infractions']; ?></h4>
                                        <small class="text-success fw-semibold"><i class="ri-arrow-up-s-line align-middle"></i>+12%</small>
                                    </div>
                                </div>
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded-3 bg-label-success"><i class="ri-check-line ri-2x"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">Adjudicatarios</h6>
                                    <h4 class="mb-0"><?php echo number_format($dashboardStats['awardees']); ?></h4>
                                </div>
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded-3 bg-label-info"><i class="ri-group-line ri-2x"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal">Puestos de Mercado</h6>
                                    <h4 class="mb-0"><?php echo number_format($dashboardStats['stalls']); ?></h4>
                                </div>
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded-3 bg-label-primary"><i class="ri-store-2-line ri-2x"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="col-12 mt-4">
            <div class="row g-4">
                <!-- Gráfico de Infracciones por Mes -->
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Infracciones por Mes</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyInfractionsChart" class="w-100" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Empleados por Departamento -->
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Empleados por Departamento</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="employeesByDepartmentChart" class="w-100" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de Línea de Productividad -->
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Productividad de la Inspección (Últimos 12 meses)</h5>
                </div>
                <div class="card-body">
                    <canvas id="inspectionProductivityChart" class="w-100" height="400"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- / Content -->

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<?php
// --- Obtención de Datos Reales para Gráficos ---
// (StatisticalReportModel ya inicializado arriba)

// 1. Infracciones por Mes (Últimos 6 meses)
$infractionsData = $statsModel->getInfractionsByMonth(6);
$infractionsLabels = json_encode($infractionsData['labels']);
$infractionsCounts = json_encode($infractionsData['data']);

// 2. Empleados por Departamento
$employeesData = $statsModel->getEmployeesByDepartment();
$employeesLabels = json_encode($employeesData['labels']);
$employeesCounts = json_encode($employeesData['data']);
$employeesBgColors = json_encode($employeesData['backgroundColor']);
$employeesBorderColors = json_encode($employeesData['borderColor']);

// 3. Productividad de Inspección (Últimos 12 meses)
$productivityData = $statsModel->getInspectionProductivity(12);
$productivityLabels = json_encode($productivityData['labels']);
$productivityCounts = json_encode($productivityData['data']);
?>

<script src="<?php echo vendor('libs/chartjs/chartjs.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // Gráfico de Infracciones por Mes (Barra)
    const monthlyInfractionsConfig = {
        type: 'bar',
        data: {
            labels: <?php echo $infractionsLabels; ?>,
            datasets: [{
                label: 'Número de Infracciones',
                data: <?php echo $infractionsCounts; ?>,
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
                    ticks: { precision: 0 }
                }
            }
        }
    };
    new Chart(document.getElementById('monthlyInfractionsChart'), monthlyInfractionsConfig);

    // Gráfico de Empleados por Departamento (Dona)
    const employeesByDepartmentConfig = {
        type: 'doughnut',
        data: {
            labels: <?php echo $employeesLabels; ?>,
            datasets: [{
                label: 'Empleados',
                data: <?php echo $employeesCounts; ?>,
                backgroundColor: <?php echo $employeesBgColors; ?>,
                borderColor: <?php echo $employeesBorderColors; ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    };
    new Chart(document.getElementById('employeesByDepartmentChart'), employeesByDepartmentConfig);

    // Gráfico de Línea de Productividad de la Inspección
    const inspectionProductivityConfig = {
        type: 'line',
        data: {
            labels: <?php echo $productivityLabels; ?>,
            datasets: [{
                label: 'Inspecciones Realizadas',
                data: <?php echo $productivityCounts; ?>,
                borderColor: '#424242',
                backgroundColor: 'rgba(66, 66, 66, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    };
    new Chart(document.getElementById('inspectionProductivityChart'), inspectionProductivityConfig);
});
</script>