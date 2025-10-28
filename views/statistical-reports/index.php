<?php
// views/reports/statistical_reports.php - COMPLETO con Inclusión de Reporte Interno
session_start();

$page_title = "Centro de Reportes y Estadísticas";
$selected_report = $_GET['report_type'] ?? null; // Variable para detectar si hay un reporte que cargar

// Incluir layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title dani-title">
                            <i class="ri-bar-chart-line me-1 dani-icon"></i>
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
                                        <option value="inspector_performance" <?php echo ($selected_report == 'inspector_performance') ? 'selected' : ''; ?>>Desempeño de Inspectores (Estadístico)</option>
                                        <option value="detailed_inspections" <?php echo ($selected_report == 'detailed_inspecciones') ? 'selected' : ''; ?>>Detalle de Inspecciones (Tabla)</option>
                                        <option value="payment_status" <?php echo ($selected_report == 'payment_status') ? 'selected' : ''; ?>>Estatus de Pagos (Gráfico)</option>
                                        <option value="market_summary" <?php echo ($selected_report == 'market_summary') ? 'selected' : ''; ?>>Resumen por Mercado (Tabla)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="report_mode" class="form-label small">2. Seleccione el Modo o Alcance</label>
                                    <select class="form-select" id="report_mode" name="report_mode" disabled>
                                        <option value="">-- Seleccione primero un Reporte --</option>
                                        </select>
                                </div>
                            </div>
                            
                            <div id="dynamicFiltersArea" class="row g-3 mt-3 p-3 border rounded" style="display: none; background-color: #f8f9fa;">
                                <div class="col-12">
                                    <h6 class="mb-3 text-primary"><i class="ri-filter-line me-1"></i> Filtros Específicos para el Reporte Seleccionado:</h6>
                                    
                                    <div class="row g-3" id="filtersContent">
                                        </div>
                                    
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary" id="generateReportBtn" disabled>
                                        <i class="ri-arrow-right-circle-line"></i> Generar Reporte
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="mb-3 text-muted"><i class="ri-information-line me-2"></i> Instrucciones</h5>
                        <p class="text-muted">Seleccione las opciones y pulse "Generar Reporte". El resultado aparecerá a continuación.</p>
                    </div>

                    <?php if ($selected_report): ?>
                        <div class="card-footer" id="reportContainer">
                            <div class="card-header border-bottom">
                                <h5 class="card-title dani-title text-primary"><i class="ri-file-chart-line me-1"></i> Resultados del Reporte Seleccionado</h5>
                            </div>
                            <?php
                            // Mapeo del tipo de reporte a su archivo de contenido (SIN LAYOUTS).
                            $report_content_map = [
                                'activity_history' => 'activityHistory.php', // ** ESTE ES EL ARCHIVO QUE DEBES CREAR **
                                'inspector_performance' => 'performance_content.php',
                            ];

                            if (isset($report_content_map[$selected_report]) && file_exists(__DIR__ . '/' . $report_content_map[$selected_report])) {
                                // INCLUYE el archivo de contenido aquí, cargando la tabla
                                include __DIR__ . '/' . $report_content_map[$selected_report];
                            } else {
                                echo '<div class="card-body"><p class="text-danger">Error: Tipo de reporte no válido o el archivo de contenido (' . htmlspecialchars($report_content_map[$selected_report] ?? 'N/A') . ') no se encontró.</p></div>';
                            }
                            ?>
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
    
    // 1. Opciones de Modos (Filtro Principal) para cada Reporte
    const reportModes = {
        activity_history: { 
            'date_range': 'Por Rango de Fechas',
            'user': 'Por Usuario Específico',
            'all': 'Ver todos los registros'
        },
        inspector_performance: {
            'period': 'Por Período (Año/Mes)',
            'inspector': 'Por Inspector Específico',
        },
        detailed_inspections: {
            'all': 'Ver todos los registros (Sin modo)',
            'period': 'Filtrar por Período (Año/Mes)',
        },
        payment_status: {
            'year': 'Resumen Anual',
            'quarter': 'Resumen Trimestral',
        },
        market_summary: {
            'market': 'Por Mercado Específico',
            'period': 'Por Período (Año/Mes)',
        }
    };
    
    // 2. Definición de Plantillas de Filtros Secundarios
    const filterTemplates = {
        date_range: `
            <div class="col-md-6">
                <label for="start_date" class="form-label small">Fecha de Inicio</label>
                <input type="date" class="form-control" id="start_date" name="start_date">
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label small">Fecha de Fin</label>
                <input type="date" class="form-control" id="end_date" name="end_date">
            </div>
        `,
        user: `
            <div class="col-md-12">
                <label for="filter_user" class="form-label small">Seleccione Usuario</label>
                <select class="form-select" id="filter_user" name="filter_user">
                    <option value="">-- Seleccione un Usuario --</option>
                    <option value="1">admin</option>
                    <option value="2">operador_01</option>
                    <option value="3">inspector_99</option>
                </select>
            </div>
        `,
        period: `
            <div class="col-md-6">
                <label for="filter_year" class="form-label small">Año</label>
                <select class="form-select" id="filter_year" name="filter_year">
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="filter_month" class="form-label small">Mes</label>
                <select class="form-select" id="filter_month" name="filter_month">
                    <option value="">Todos los Meses</option>
                    <option value="1">Enero</option>
                    <option value="2">Febrero</option>
                </select>
            </div>
        `,
        none: `<div class="col-12"><p class="text-muted mb-0">No se requieren filtros adicionales para este modo.</p></div>`
    };

    // FUNCIÓN PRINCIPAL: MANEJA EL CAMBIO EN EL TIPO DE REPORTE
    reportTypeSelect.on('change', function() {
        const selectedReport = $(this).val();
        
        reportModeSelect.empty();
        reportModeSelect.append('<option value="">-- Seleccione el Modo --</option>');
        reportModeSelect.prop('disabled', true);
        
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

    // FUNCIÓN SECUNDARIA: MANEJA EL CAMBIO EN EL MODO (FILTRO PRINCIPAL)
    reportModeSelect.on('change', function() {
        const selectedMode = $(this).val();
        const selectedReport = reportTypeSelect.val();

        filtersContent.empty();
        generateReportBtn.prop('disabled', true);
        
        if (selectedMode) {
            generateReportBtn.prop('disabled', false);

            let filterHtml = filterTemplates.none; 
            
            // Lógica de aparición de filtros
            if (selectedReport === 'activity_history') {
                if (selectedMode === 'date_range') {
                    filterHtml = filterTemplates.date_range;
                } else if (selectedMode === 'user') {
                    filterHtml = filterTemplates.user;
                }
            } else if (selectedMode === 'period') {
                filterHtml = filterTemplates.period;
            } else if (selectedMode === 'inspector') {
                filterHtml = filterTemplates.user; 
            }
            
            filtersContent.html(filterHtml);
            
            if (selectedMode !== 'all' && selectedMode !== 'none') {
                 dynamicFiltersArea.slideDown();
            } else {
                 dynamicFiltersArea.slideUp();
            }
        } else {
            dynamicFiltersArea.slideUp();
        }
    });
    
    // *** LÓGICA DE PRECARGA Y ESTADO INICIAL (CRUCIAL PARA LA REDIRECCIÓN GET) ***
    
    function preloadFilters() {
        const params = new URLSearchParams(window.location.search);
        const selectedReport = params.get('report_type');
        const selectedMode = params.get('report_mode');
        
        if (selectedReport) {
            if (reportModes[selectedReport]) {
                // 1. Habilitar y poblar el selector de Modo
                $.each(reportModes[selectedReport], function(modeKey, modeValue) {
                    reportModeSelect.append(`<option value="${modeKey}">${modeValue}</option>`);
                });
                reportModeSelect.prop('disabled', false);
                
                // 2. Seleccionar el modo que viene en la URL
                if (selectedMode) {
                    reportModeSelect.val(selectedMode);
                    generateReportBtn.prop('disabled', false); // Habilitar el botón
                    
                    // 3. Mostrar y precargar filtros dinámicos
                    let filterHtml = filterTemplates.none; 
                    if (selectedReport === 'activity_history') {
                        if (selectedMode === 'date_range') {
                            filterHtml = filterTemplates.date_range;
                        } else if (selectedMode === 'user') {
                            filterHtml = filterTemplates.user;
                        }
                    } else if (selectedMode === 'period') {
                        filterHtml = filterTemplates.period;
                    } else if (selectedMode === 'inspector') {
                        filterHtml = filterTemplates.user; 
                    }
                    
                    filtersContent.html(filterHtml);

                    // Precargar los valores específicos de los filtros
                    params.forEach((value, key) => {
                        const element = $(`#${key}`);
                        if (element.length) {
                            element.val(value);
                        }
                    });
                    
                    // Mostrar área de filtros si no es modo 'all' o 'none'
                    if (selectedMode !== 'all' && selectedMode !== 'none') {
                         dynamicFiltersArea.slideDown();
                    } else {
                         dynamicFiltersArea.hide();
                    }
                }
            }
        }
    }

    // Llamar a la función al cargar la página
    preloadFilters();
});
</script>