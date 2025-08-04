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
                                        <h4 class="mb-0 me-2">45</h4>
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
                                        <h4 class="mb-0 me-2">120</h4>
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
                                    <h4 class="mb-0">850</h4>
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
                                    <h4 class="mb-0">1,500</h4>
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

<!-- Scripts para Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Datos simulados para los gráficos ---
    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    // Gráfico de Infracciones por Mes (Barra)
    const monthlyInfractionsData = {
        labels: months.slice(6), // Últimos 6 meses
        datasets: [{
            label: 'Número de Infracciones',
            data: [12, 19, 3, 5, 2, 3].sort(() => Math.random() - 0.5), // Datos simulados
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };

    const monthlyInfractionsConfig = {
        type: 'bar',
        data: monthlyInfractionsData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };
    new Chart(document.getElementById('monthlyInfractionsChart'), monthlyInfractionsConfig);

    // Gráfico de Empleados por Departamento (Dona)
    const employeesByDepartmentData = {
        labels: ['Vigilancia', 'Administración', 'Inspección', 'Mantenimiento'],
        datasets: [{
            label: 'Empleados',
            data: [30, 15, 25, 10], // Datos simulados
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    };

    const employeesByDepartmentConfig = {
        type: 'doughnut',
        data: employeesByDepartmentData,
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
    const inspectionProductivityData = {
        labels: months,
        datasets: [{
            label: 'Inspecciones Realizadas',
            data: [35, 42, 50, 48, 55, 60, 65, 70, 68, 75, 72, 80],
            borderColor: '#424242',
            backgroundColor: 'rgba(66, 66, 66, 0.2)',
            fill: true,
            tension: 0.4
        }]
    };

    const inspectionProductivityConfig = {
        type: 'line',
        data: inspectionProductivityData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
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