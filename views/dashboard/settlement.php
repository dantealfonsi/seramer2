<?php
/**
 * Dashboard de Liquidación - Basado en seramer-local
 */

// Incluir configuración
require_once __DIR__ . '/../../config/app.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
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

// Incluir header y layouts
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

// Modelos necesarios (esto se cargará desde el controlador en el futuro, pero para que funcione directo como los de seramer2)
require_once __DIR__ . '/../../models/SettlementDashboardModel.php';
$dashboardModel = new SettlementDashboardModel();

// Datos para la vista
$awardeeMetrics = $dashboardModel->getAwardeeMetrics();
$contractMetrics = $dashboardModel->getContractMetrics();
$monthlyStats = $dashboardModel->getMonthlyStatistics();

$startDate = date('Y-m-01');
$endDate = date('Y-m-t');
$totalRevenue = $dashboardModel->getTotalRevenue($startDate, $endDate);

$chartData = $dashboardModel->getAwardeesPerZone();
$chartLabels = array_column($chartData, 'label');
$chartValues = array_column($chartData, 'value');

$currentYear = date('Y');
$monthlyIncome = $dashboardModel->getMonthlyIncome((int)$currentYear);
$incomeValues = array_values($monthlyIncome);
$incomeLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <!-- Estadísticas Cards -->
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Adjudicatarios</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2"><?= number_format($awardeeMetrics['total']) ?></h4>
                            </div>
                            <small>Total activos</small>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="ri-group-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Contratos</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2"><?= number_format($contractMetrics['active']) ?></h4>
                            </div>
                            <small>Vigentes</small>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ri-file-text-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Pagos Pendientes</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2"><?= number_format($monthlyStats['pending_payments']) ?></h4>
                            </div>
                            <small>En este mes</small>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="ri-time-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Recaudación</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">Bs. <?= number_format($totalRevenue, 2, ',', '.') ?></h4>
                            </div>
                            <small>En este mes</small>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="ri-money-dollar-circle-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Ingresos Mensuales (Bar Chart) -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header d-flex align-items-center justify-content-between border-bottom-0 pb-0">
                    <h5 class="card-title mb-0">Ingresos Mensuales (<?= date('Y') ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div id="incomeChart"></div>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de Adjudicatarios por Zona -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header d-flex align-items-center justify-content-between border-bottom-0 pb-0">
                    <h5 class="card-title mb-0">Adjudicatarios por Zona</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div id="awardeesChart" class="w-100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Sistema y desarrolladores -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom-0">
                    <h5 class="card-title mb-0">Panel de Control - Liquidación</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Bienvenido al módulo de liquidación. Aquí puede visualizar el estado de los contratos, pagos y recaudación en tiempo real.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- ApexCharts scripts adapted from seramer-local -->
<script src="<?= asset('vendor/libs/apex-charts/apexcharts.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        /**
         * Gráfico de Adjudicatarios por Zona (Pie Chart)
         */
        const chartValues = <?= json_encode($chartValues) ?>;
        const chartLabels = <?= json_encode($chartLabels) ?>;
        
        if (chartValues.length > 0) {
            const options = {
                series: chartValues.map(v => parseInt(v)),
                chart: {
                    type: 'pie',
                    height: 350,
                    fontFamily: 'Inter, sans-serif'
                },
                labels: chartLabels,
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                colors: ['#1e6091', '#2d7a4f', '#b8860b', '#c0392b', '#2980b9', '#5d6778', '#D0D2D6', '#4B4B4B'],
                legend: {
                    position: 'bottom',
                    labels: {
                        colors: '#6f6b7d',
                        useSeriesColors: false
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return opts.w.config.series[opts.seriesIndex]
                    },
                    style: {
                        fontSize: '14px',
                        fontFamily: 'Inter, sans-serif',
                        fontWeight: 500
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " adjudicatarios"
                        }
                    },
                    style: {
                        fontSize: '14px',
                        fontFamily: 'Inter, sans-serif'
                    }
                },
                stroke: {
                    show: false
                }
            };

            const chart = new ApexCharts(document.querySelector("#awardeesChart"), options);
            chart.render();
        } else {
            document.querySelector("#awardeesChart").innerHTML = '<div class="text-center p-4 text-muted">No hay datos suficientes para mostrar el gráfico</div>';
        }
        
        /**
         * Gráfico de Ingresos Mensuales (Bar Chart)
         */
        const incomeValues = <?= json_encode($incomeValues) ?>;
        const incomeLabels = <?= json_encode($incomeLabels) ?>;
        
        const incomeOptions = {
            series: [{
                name: 'Ingresos',
                data: incomeValues
            }],
            chart: {
                type: 'bar',
                height: 350,
                fontFamily: 'Inter, sans-serif',
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded',
                    borderRadius: 4
                },
            },
            colors: ['#1e6091'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: incomeLabels,
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#6f6b7d',
                        fontSize: '13px',
                        fontFamily: 'Inter, sans-serif'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#6f6b7d',
                        fontSize: '13px',
                        fontFamily: 'Inter, sans-serif'
                    },
                    formatter: function(val) {
                        return new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VEF', currencyDisplay: 'code' }).format(val).replace('VEF', 'Bs');
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: 'vertical',
                    shadeIntensity: 0.5,
                    gradientToColors: ['#1e6091'], // var(--metro-primary)
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 100]
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VEF', currencyDisplay: 'code' }).format(val).replace('VEF', 'Bs');
                    }
                },
                style: {
                    fontSize: '14px',
                    fontFamily: 'Inter, sans-serif'
                }
            },
            grid: {
                borderColor: '#f1f1f1',
                padding: {
                    top: 0,
                    bottom: 0,
                    left: 10,
                    right: 0
                }
            }
        };

        const incomeChart = new ApexCharts(document.querySelector("#incomeChart"), incomeOptions);
        incomeChart.render();
    });
</script>
