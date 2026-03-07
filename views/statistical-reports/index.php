<?php
/**
 * Vista: Menú principal de Centro de Estadísticas (Tarjetas)
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
                        <i class="ri-bar-chart-box-line" style="color: #696cff; font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="mb-0 fw-bold" style="color: #43495b;">Reportes de Fiscalización</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Fiscalización</a></li>
                                <li class="breadcrumb-item active">Reportes de Fiscalización</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Infracciones por Mes -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="ri-line-chart-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Infracciones por Mes</h5>
                        <p class="card-text text-muted small px-2">Análisis gráfico del historial de infracciones durante un período.</p>
                        <a href="view_report.php?report_type=infractions_by_month&report_mode=last_6_months" class="btn btn-outline-danger w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Productividad de la Inspección -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="ri-pie-chart-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Productividad</h5>
                        <p class="card-text text-muted small px-2">Métricas de desempeño y volumen de tareas y procesos inspeccionados.</p>
                        <a href="view_report.php?report_type=inspection_productivity&report_mode=last_12" class="btn btn-outline-success w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ingresos por Tipo de Infracción -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="ri-money-dollar-circle-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Ingresos/Tipos Multa</h5>
                        <p class="card-text text-muted small px-2">Desglose de la recaudación dividida por cada tipo de sanción.</p>
                        <a href="view_report.php?report_type=revenue_by_type&report_mode=all" class="btn btn-outline-warning w-100 mt-2 text-dark">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Empleados por Departamento -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="ri-group-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Personal Operativo</h5>
                        <p class="card-text text-muted small px-2">Distribución visual del grupo de trabajo por departamento.</p>
                        <a href="view_report.php?report_type=employees_by_department&report_mode=all" class="btn btn-outline-primary w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Historial de Actividad (Auditoria solo para roles) -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="ri-history-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Historial Actividad</h5>
                        <p class="card-text text-muted small px-2">Listado exhaustivo de todas las operaciones de usuarios en el sistema.</p>
                        <a href="view_report.php?report_type=activity_history&report_mode=all" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Desempeño de Inspectores -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="ri-user-star-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Desempeño Inspector</h5>
                        <p class="card-text text-muted small px-2">Evaluación individual de tareas o general para los inspectores durante períodos de evaluación.</p>
                        <a href="view_report.php?report_type=inspector_performance&report_mode=period" class="btn btn-outline-info w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
