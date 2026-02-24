<?php
/**
 * Vista: Menú principal de Reportes de Liquidación
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
                        <i class="ri-file-chart-line" style="color: #696cff; font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="mb-0 fw-bold" style="color: #43495b;">Reportes de Liquidación</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Liquidación</a></li>
                                <li class="breadcrumb-item active">Reportes</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Contratos Morosos -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="ri-alert-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Contratos Morosos</h5>
                        <p class="card-text text-muted small px-2">Reporte de contratos con facturas vencidas y días de mora.</p>
                        <a href="delinquent_contracts.php" class="btn btn-outline-danger w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total por Zona -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="ri-map-pin-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Total por Zona</h5>
                        <p class="card-text text-muted small px-2">Acumulado monetario total agrupado por zonas.</p>
                        <a href="zone_accumulated.php" class="btn btn-outline-info w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reporte de Caja -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="ri-money-dollar-circle-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Reporte de Caja</h5>
                        <p class="card-text text-muted small px-2">Detalle de movimientos de caja diarios y por período.</p>
                        <a href="cash_report.php" class="btn btn-outline-success w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Caja por Métodos de Pago -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="ri-bank-card-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Caja por Pagos</h5>
                        <p class="card-text text-muted small px-2">Ingresos clasificados por método de pago y zona.</p>
                        <a href="cash_report_by_payment_method.php" class="btn btn-outline-warning w-100 mt-2 text-dark">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ingresos por Zona -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="ri-file-chart-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Ingresos por Zona</h5>
                        <p class="card-text text-muted small px-2">Reporte de ingresos mensual filtrado por zona.</p>
                        <a href="income_by_zone.php" class="btn btn-outline-primary w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ingresos por Rubro -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="ri-folder-chart-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Ingresos por Rubro</h5>
                        <p class="card-text text-muted small px-2">Desglose de ingresos según categoría o rubro comercial.</p>
                        <a href="income_by_category.php" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Resumen de Ingresos -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="ri-bar-chart-box-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Resumen Global</h5>
                        <p class="card-text text-muted small px-2">Consolidado general de recaudación por zona/sector.</p>
                        <a href="income_summary.php" class="btn btn-outline-info w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Resumen de Ingresos por Rubro -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 report-card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="ri-stack-line ri-32px"></i>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">Resumen Rubros</h5>
                        <p class="card-text text-muted small px-2">Total recaudado agrupado únicamente por rubros.</p>
                        <a href="income_summary_by_category.php" class="btn btn-outline-success w-100 mt-2">
                            <i class="ri-eye-line me-1"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
