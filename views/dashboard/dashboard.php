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
                        <div class="card-body pb-0 px-0 pt-2" style="background: linear-gradient(270deg, #203565, transparent); height: 186px; border-radius: 0.5em;">
                            <!-- Banner area without image -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Métricas -->
        <div class="col-12 mt-4">
            <div class="row g-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>Infracciones Activas</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2"><?php echo $dashboardStats['active_infractions']; ?></h4>
                                        <small class="text-success fw-semibold"><i class="ri-arrow-up-s-line align-middle"></i>+5%</small>
                                    </div>
                                    <small>Total activas</small>
                                </div>
                                <span class="badge bg-label-warning rounded p-2">
                                    <i class="ri-alert-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>Infracciones Resueltas</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2"><?php echo $dashboardStats['resolved_infractions']; ?></h4>
                                        <small class="text-success fw-semibold"><i class="ri-arrow-up-s-line align-middle"></i>+12%</small>
                                    </div>
                                    <small>Total resueltas</small>
                                </div>
                                <span class="badge bg-label-success rounded p-2">
                                    <i class="ri-check-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>Adjudicatarios</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2"><?php echo number_format($dashboardStats['awardees']); ?></h4>
                                    </div>
                                    <small>Usuarios registrados</small>
                                </div>
                                <span class="badge bg-label-info rounded p-2">
                                    <i class="ri-group-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>Puestos de Mercado</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2"><?php echo number_format($dashboardStats['stalls']); ?></h4>
                                    </div>
                                    <small>Total puestos</small>
                                </div>
                                <span class="badge bg-label-primary rounded p-2">
                                    <i class="ri-store-2-line ri-24px"></i>
                                </span>
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
    const ctxMonthly = document.getElementById('monthlyInfractionsChart').getContext('2d');
    const gradientMonthly = ctxMonthly.createLinearGradient(0, 0, 0, 400);
    gradientMonthly.addColorStop(0, 'rgba(30, 96, 145, 0.9)'); // var(--metro-primary)
    gradientMonthly.addColorStop(1, 'rgba(30, 96, 145, 0.2)');

    const monthlyInfractionsConfig = {
        type: 'bar',
        data: {
            labels: <?php echo $infractionsLabels; ?>,
            datasets: [{
                label: 'Número de Infracciones',
                data: <?php echo $infractionsCounts; ?>,
                backgroundColor: gradientMonthly,
                borderColor: 'rgba(30, 96, 145, 1)',
                borderWidth: 1,
                borderRadius: 6
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
                // Using the specific metro palette defined in PHP, or overriding if we want but PHP already provides colors.
                // We'll keep the PHP-provided colors but increase borderWidth for contrast
                backgroundColor: <?php echo $employeesBgColors; ?>,
                borderColor: '#ffffff',
                borderWidth: 2,
                hoverOffset: 4
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
    const ctxProd = document.getElementById('inspectionProductivityChart').getContext('2d');
    const gradientProd = ctxProd.createLinearGradient(0, 0, 0, 400);
    gradientProd.addColorStop(0, 'rgba(45, 122, 79, 0.6)'); // var(--metro-success)
    gradientProd.addColorStop(1, 'rgba(45, 122, 79, 0.05)');

    const inspectionProductivityConfig = {
        type: 'line',
        data: {
            labels: <?php echo $productivityLabels; ?>,
            datasets: [{
                label: 'Inspecciones Realizadas',
                data: <?php echo $productivityCounts; ?>,
                borderColor: 'rgba(45, 122, 79, 1)',
                backgroundColor: gradientProd,
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: 'rgba(45, 122, 79, 1)',
                pointBorderWidth: 2,
                pointRadius: 4
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