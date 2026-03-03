<?php
// views/collection-reports/index.php
session_start();

$page_title = "Centro de Reportes de Cobranza";
$selected_report = $_GET['report_type'] ?? null;
$selected_mode_param = $_GET['report_mode'] ?? null; 

require_once __DIR__ . '/../layouts/header.php'; 
include __DIR__ . '/../layouts/navigation.php'; 
include __DIR__ . '/../layouts/navigation-top.php'; 
?>

<style>
.print-header {
    display: none;
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
}

@media print {
    body * {
        visibility: hidden;
    }
    #reportContainer, #reportContainer * {
        visibility: visible;
    }
    #reportContainer {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        border: none;
        box-shadow: none;
    }
    .print-header {
        display: block; 
        color: #343a40; 
        border-bottom: 2px solid #343a40; 
        padding-top: 20px;
    }
    #reportContainer .btn {
        display: none !important;
    }
}
</style>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                <i class="ri-money-dollar-circle-line" style="color: #696cff; font-size: 1.5rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form id="reportForm" action="" method="GET" class="card p-3 mb-4 shadow-sm">
                            <h6 class="card-title mb-3"><i class="ri-settings-4-line me-1"></i> Configuración de Reporte</h6>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="report_type" class="form-label small">1. Seleccione el Tipo de Reporte</label>
                                    <select class="form-select" id="report_type" name="report_type">
                                        <option value="">-- Elija un Reporte --</option>
                                        <option value="activity_history" <?php echo ($selected_report == 'activity_history') ? 'selected' : ''; ?>>Historial de Actividad (Tabla)</option>
                                        <option value="revenue_by_date" <?php echo ($selected_report == 'revenue_by_date') ? 'selected' : ''; ?>>Ganancias Totales por Rango de Fecha (Gráfico)</option>
                                        <option value="top_payers" <?php echo ($selected_report == 'top_payers') ? 'selected' : ''; ?>>Adjudicatarios y Locales Mayores Pagadores</option>
                                        <option value="revenue_by_zone" <?php echo ($selected_report == 'revenue_by_zone') ? 'selected' : ''; ?>>Dinero Recaudado por Zona (Gráfico)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="report_mode" class="form-label small">2. Seleccione el Modo o Alcance</label>
                                    <select class="form-select" id="report_mode" name="report_mode" disabled>
                                        <option value="">-- Seleccione primero un Reporte --</option>
                                    </select>
                                </div>
                            </div>
                            
                            <?php 
                            // Fetch inspectors list for JS injection if needed (reusing logic just in case)
                            require_once __DIR__ . '/../../models/StatisticalReportModel.php';
                            require_once __DIR__ . '/../../models/UserModel.php'; 
                            // We might want regular users for filtering activity history
                            $userModel = new UserModel();
                            $usersList = $userModel->getAllForSelect();
                            ?>
                            
                            <div id="dynamicFiltersArea" class="row g-3 mt-3 p-3 border rounded" style="display: none; background-color: #f8f9fa;">
                                <div class="col-12">
                                    <h6 class="mb-3 text-primary"><i class="ri-filter-line me-1"></i> Filtros Específicos:</h6>
                                    <div class="row g-3" id="filtersContent">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary" id="generateReportBtn" disabled>
                                        <i class="ri-arrow-right-circle-line"></i> Generar Reporte
                                    </button>
                                    
                                    <?php if ($selected_report): ?>
                                    <button type="button" class="btn btn-secondary ms-2" onclick="window.print()">
                                        <i class="ri-printer-line"></i> Imprimir/Exportar PDF
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <?php if ($selected_report): ?>
                        <div class="card-footer p-4" id="reportContainer">
                            
                            <div class="print-header">
                                <h1 style="margin: 0; color: #000080; font-size: 24pt;">SERAMER</h1>
                                <p style="margin: 5px 0 0; font-size: 12pt;">Reporte de Cobranza Generado</p>
                            </div>
                            
                            <div class="card-header border-bottom">
                                <h5 class="card-title text-primary d-flex align-items-center" style="font-size: 1.2rem;"><i class="ri-file-chart-line me-1" style="color: #696cff;"></i> Resultados del Reporte</h5>
                            </div>
                            <?php
                            $report_content_map = [
                                'activity_history' => 'activityHistory.php', 
                                'revenue_by_date' => 'revenue_chart.php',
                                'top_payers' => 'top_payers.php',
                                'revenue_by_zone' => 'revenue_by_zone.php',
                            ];

                            if (isset($report_content_map[$selected_report]) && file_exists(__DIR__ . '/' . $report_content_map[$selected_report])) {
                                include __DIR__ . '/' . $report_content_map[$selected_report];
                            } else {
                                echo '<div class="card-body"><p class="text-danger">Error: Archivo de reporte no encontrado.</p></div>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="card-body">
                            <h5 class="mb-3 text-muted"><i class="ri-information-line me-2"></i> Instrucciones</h5>
                            <p class="text-muted">Seleccione las opciones y pulse "Generar Reporte".</p>
                        </div>
                    <?php endif; ?>
                    </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>
<script>
$(document).ready(function() {
    const reportTypeSelect = $('#report_type');
    const reportModeSelect = $('#report_mode');
    const dynamicFiltersArea = $('#dynamicFiltersArea');
    const filtersContent = $('#filtersContent');
    const generateReportBtn = $('#generateReportBtn');
    
    const usersData = <?php echo json_encode($usersList); ?>;
    let userOptions = '<option value="">-- Seleccione un Usuario --</option>';
    if (usersData) {
        usersData.forEach(function(u) {
            userOptions += `<option value="${u.id}">${u.username} (${u.staff_first_name} ${u.staff_last_name})</option>`;
        });
    }

    const reportModes = {
        activity_history: { 
            'date_range': 'Por Rango de Fechas',
            'user': 'Por Usuario Específico',
            'all': 'Ver todos los registros'
        },
        revenue_by_date: {
            'date_range': 'Intervalo Personalizado'
        },
        top_payers: {
            'all_time': 'Histórico Completo',
            'date_range': 'Por Rango de Fechas'
        },
        revenue_by_zone: {
            'all_time': 'Histórico Completo',
            'date_range': 'Por Rango de Fechas'
        }
    };
    
    // Templates
    const filterTemplates = {
        date_range: `
            <div class="col-md-6">
                <label for="start_date" class="form-label small">Fecha de Inicio</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label small">Fecha de Fin</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required>
            </div>
        `,
        user: `
            <div class="col-md-12">
                <label for="filter_user" class="form-label small">Seleccione Usuario</label>
                <select class="form-select" id="filter_user" name="filter_user">
                    ${userOptions}
                </select>
            </div>
        `,
        none: `<div class="col-12"><p class="text-muted mb-0">No se requieren filtros adicionales.</p></div>`
    };

    reportTypeSelect.on('change', function() {
        const selectedReport = $(this).val();
        reportModeSelect.empty().append('<option value="">-- Seleccione el Modo --</option>').prop('disabled', true);
        dynamicFiltersArea.slideUp();
        filtersContent.empty();
        generateReportBtn.prop('disabled', true);
        
        if (selectedReport && reportModes[selectedReport]) {
            $.each(reportModes[selectedReport], function(modeKey, modeValue) {
                reportModeSelect.append(`<option value="${modeKey}">${modeValue}</option>`);
            });
            reportModeSelect.prop('disabled', false);
        }
    });

    reportModeSelect.on('change', function() {
        const selectedMode = $(this).val();
        const selectedReport = reportTypeSelect.val();

        filtersContent.empty();
        generateReportBtn.prop('disabled', true);
        
        if (selectedMode) {
            generateReportBtn.prop('disabled', false);
            let filterHtml = filterTemplates.none; 
            
            if (selectedMode === 'date_range') {
                filterHtml = filterTemplates.date_range;
            } else if (selectedMode === 'user') {
                filterHtml = filterTemplates.user;
            }

            filtersContent.html(filterHtml);
            
            const needsFilters = (filterHtml !== filterTemplates.none);
            if (needsFilters) {
                dynamicFiltersArea.slideDown();
            } else {
                dynamicFiltersArea.slideUp();
            }
        } else {
            dynamicFiltersArea.slideUp();
        }
    });
    
    // Preload Logic
    function preloadFilters() {
        const params = new URLSearchParams(window.location.search);
        const selectedReport = params.get('report_type');
        const selectedMode = params.get('report_mode');
        
        if (selectedReport && reportModes[selectedReport]) {
            $.each(reportModes[selectedReport], function(modeKey, modeValue) {
                reportModeSelect.append(`<option value="${modeKey}">${modeValue}</option>`);
            });
            reportModeSelect.prop('disabled', false);
            
            if (selectedMode) {
                reportModeSelect.val(selectedMode);
                generateReportBtn.prop('disabled', false);
                
                let filterHtml = filterTemplates.none;
                if (selectedMode === 'date_range') {
                    filterHtml = filterTemplates.date_range;
                } else if (selectedMode === 'user') {
                    filterHtml = filterTemplates.user;
                }
                
                filtersContent.html(filterHtml);

                params.forEach((value, key) => {
                    const element = $(`#${key}`);
                    if (element.length) element.val(value);
                });
                
                if (filterHtml !== filterTemplates.none) {
                    dynamicFiltersArea.slideDown();
                }
            }
        }
    }
    preloadFilters();
});
</script>
