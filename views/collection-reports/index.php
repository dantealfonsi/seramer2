<?php
/**
 * Vista: Menú principal de Reportes de Cobranza (Individuales)
 */
require_once __DIR__ . '/../../config/app.php';

// Incluir header y layouts
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
        min-height: calc(100vh - 100px);
    }
    .report-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 12px;
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .avatar-bg-light {
        background-color: #f3f4f6;
    }
</style>

<div class="main-content main-container">
    <div class="container-fluid">
        <!-- Header con Icono y Breadcrumb -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex align-items-center">
                    <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e7e7ff !important;">
                        <i class="ri-money-dollar-circle-line" style="color: #696cff; font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="mb-0 fw-bold" style="color: #43495b;">Reportes de Cobranza</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Cobranza</a></li>
                                <li class="breadcrumb-item active">Reportes</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Historial de Actividad -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="ri-time-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Historial de Actividad</h5>
                        <p class="card-text text-muted small px-2">Tabla con la bitácora de acciones realizadas por los usuarios en el sistema.</p>
                        <a href="activity_history_page.php" class="btn btn-outline-info w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ganancias por Rango de Fecha -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="ri-line-chart-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Ganancias Totales</h5>
                        <p class="card-text text-muted small px-2">Gráfico de recaudación agrupado por fechas para el análisis de ingresos.</p>
                        <a href="revenue_by_date_page.php" class="btn btn-outline-success w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top Pagadores -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="ri-medal-fill ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Mayores Pagadores</h5>
                        <p class="card-text text-muted small px-2">Ranking de los adjudicatarios y locales con mayor cantidad de pagos.</p>
                        <a href="top_payers_page.php" class="btn btn-outline-danger w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dinero Recaudado por Zona -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="ri-pie-chart-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Recaudación por Zona</h5>
                        <p class="card-text text-muted small px-2">Muestra la distribución geográfica de los ingresos totales percibidos.</p>
                        <a href="revenue_by_zone_page.php" class="btn btn-outline-warning w-100 mt-2 text-dark">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
