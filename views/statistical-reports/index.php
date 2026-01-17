<?php
// views/reports/statistical_reports.php - CÓDIGO FINAL CON TODOS LOS CAMBIOS Y CORRECCIONES DE LÓGICA
session_start();

$page_title = "Centro de Reportes y Estadísticas";
// Variable para detectar si hay un reporte que cargar (report_type) y su modo (report_mode)
$selected_report = $_GET['report_type'] ?? null;
$selected_mode_param = $_GET['report_mode'] ?? null; 

// Incluir layouts (Asegúrate que estas rutas sean correctas)
require_once __DIR__ . '/../layouts/header.php'; 
include __DIR__ . '/../layouts/navigation.php'; // Navbar lateral
include __DIR__ . '/../layouts/navigation-top.php'; // Navbar superior
?>

<style>
/* Por defecto, el encabezado SERAMER está oculto en la pantalla */
.print-header {
    display: none;
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
}

@media print {
    /* 1. Ocultar todos los elementos de la interfaz (navbars, formulario, botones) */
    body * {
        visibility: hidden;
    }

    /* 2. Mostrar solo el contenedor del reporte (#reportContainer) y su contenido */
    #reportContainer, #reportContainer * {
        visibility: visible;
    }

    /* 3. Posicionar el reporte en la esquina superior de la página impresa */
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
    
    /* 4. Mostrar y estilizar el encabezado de impresión */
    .print-header {
        display: block; 
        color: #343a40; /* Color oscuro para impresión */
        border-bottom: 2px solid #343a40; 
        padding-top: 20px;
    }

    /* 5. Ocultar los botones de "Imprimir" y "Generar Reporte" dentro del contenedor de resultados */
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
                                        <option value="infraction_count" <?php echo ($selected_report == 'infraction_count') ? 'selected' : ''; ?>>Conteo de Infracciones por Tiempo (Gráfico) *</option>
                                        <option value="infractions_by_month" <?php echo ($selected_report == 'infractions_by_month') ? 'selected' : ''; ?>>Infracciones por Mes (Gráfico)</option>
                                        <option value="employees_by_department" <?php echo ($selected_report == 'employees_by_department') ? 'selected' : ''; ?>>Empleados por Departamento (Gráfico)</option>
                                        <option value="inspection_productivity" <?php echo ($selected_report == 'inspection_productivity') ? 'selected' : ''; ?>>Productividad de la Inspección (Gráfico)</option>
                                        <option value="inspector_performance" <?php echo ($selected_report == 'inspector_performance') ? 'selected' : ''; ?>>Desempeño de Inspectores (Estadístico)</option>
                                        <option value="revenue_by_type" <?php echo ($selected_report == 'revenue_by_type') ? 'selected' : ''; ?>>Ingresos por Tipo de Infracción (Gráfico)</option>
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
                            // Fetch inspectors list for JS injection
                            require_once __DIR__ . '/../../models/StatisticalReportModel.php';
                            $statsModelForFilter = new StatisticalReportModel();
                            $inspectorsList = $statsModelForFilter->getInspectorsList();
                            ?>
                            
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
                                <p style="margin: 5px 0 0; font-size: 12pt;">Reporte Estadístico Generado</p>
                            </div>
                            
                            <div class="card-header border-bottom">
                                <h5 class="card-title dani-title text-primary"><i class="ri-file-chart-line me-1"></i> Resultados del Reporte Seleccionado</h5>
                            </div>
                            <?php
                            $report_content_map = [
                                'activity_history' => 'activityHistory.php', 
                                'infraction_count' => 'infraction_count_chart.php',
                                'infractions_by_month' => 'infractions_by_month.php',
                                'employees_by_department' => 'employees_by_department.php',
                                'inspection_productivity' => 'inspection_productivity.php',
                                'inspector_performance' => 'performance_content.php',
                                'revenue_by_type' => 'revenue_by_infraction_type.php',
                                'detailed_inspections' => 'detailed_inspections_content.php',
                                'payment_status' => 'payment_status_content.php',
                                'market_summary' => 'market_summary_content.php',
                            ];

                            if (isset($report_content_map[$selected_report]) && file_exists(__DIR__ . '/' . $report_content_map[$selected_report])) {
                                include __DIR__ . '/' . $report_content_map[$selected_report];
                            } else {
                                echo '<div class="card-body"><p class="text-danger">Error: Tipo de reporte no válido o el archivo de contenido (' . htmlspecialchars($report_content_map[$selected_report] ?? 'N/A') . ') no se encontró.</p></div>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="card-body">
                            <h5 class="mb-3 text-muted"><i class="ri-information-line me-2"></i> Instrucciones</h5>
                            <p class="text-muted">Seleccione las opciones y pulse "Generar Reporte". El resultado aparecerá a continuación.</p>
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

## 💡 Lógica JavaScript (Corregida)

```javascript
<script>
$(document).ready(function() {
    const reportTypeSelect = $('#report_type');
    const reportModeSelect = $('#report_mode');
    const dynamicFiltersArea = $('#dynamicFiltersArea');
    const filtersContent = $('#filtersContent');
    const generateReportBtn = $('#generateReportBtn');
    
    // Inject Inspectors Data from PHP
    const inspectorsData = <?php echo json_encode($inspectorsList); ?>;
    let inspectorOptions = '<option value="">-- Seleccione un Inspector --</option>';
    if (inspectorsData) {
        inspectorsData.forEach(function(inspector) {
            inspectorOptions += `<option value="${inspector.inspector_id}">${inspector.full_name}</option>`;
        });
    }

    // 1. Opciones de Modos (Filtro Principal)
    const reportModes = {
        activity_history: { 
            'date_range': 'Por Rango de Fechas',
            'user': 'Por Usuario Específico', // Keep generic user filter for audit
            'all': 'Ver todos los registros'
        },
        infraction_count: { 
            'date_range': 'Por Rango de Fechas (Diario)',
            'weekly': 'Por Rango de Fechas (Semanal)',
            'last_6_months': 'Últimos 6 Meses (Por Mes)',
            'annual': 'Por Año Específico',
        },
        infractions_by_month: {
            'last_6_months': 'Últimos 6 Meses',
            'annual': 'Últimos 12 Meses (Anual)',
        },
        employees_by_department: {
            'all': 'Ver Distribución Actual',
        },
        inspection_productivity: {
            'last_12': 'Últimos 12 Meses',
        },
        revenue_by_type: {
             'all': 'Ver Distribución Global',
        },
        inspector_performance: {
            'period': 'Por Período (Año/Mes)',
            'inspector': 'Por Inspector Específico',
        },
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
                <label for="filter_inspector" class="form-label small">Seleccione Inspector</label>
                <select class="form-select" id="filter_inspector" name="filter_inspector">
                    ${inspectorOptions}
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
        year: `
            <div class="col-md-4">
                <label for="filter_year" class="form-label small">Seleccione el Año</label>
                <select class="form-select" id="filter_year" name="filter_year">
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                </select>
            </div>
        `,
        none: `<div class="col-12"><p class="text-muted mb-0">No se requieren filtros adicionales para este modo.</p></div>`
    };

    // FUNCIÓN PRINCIPAL: MANEJA EL CAMBIO EN EL TIPO DE REPORTE
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

    // FUNCIÓN SECUNDARIA: MANEJA EL CAMBIO EN EL MODO (FILTRO PRINCIPAL)
    reportModeSelect.on('change', function() {
        const selectedMode = $(this).val();
        const selectedReport = reportTypeSelect.val();

        filtersContent.empty();
        generateReportBtn.prop('disabled', true);
        
        if (selectedMode) {
            generateReportBtn.prop('disabled', false);

            let filterHtml = filterTemplates.none; 
            
            // Lógica de aparición de filtros (USANDO SWITCH)
            switch (selectedReport) {
                case 'activity_history':
                    if (selectedMode === 'date_range') {
                        filterHtml = filterTemplates.date_range;
                    } else if (selectedMode === 'user') {
                        filterHtml = filterTemplates.user;
                    }
                    break;
                case 'infraction_count': 
                    if (selectedMode === 'date_range' || selectedMode === 'weekly') {
                        filterHtml = filterTemplates.date_range;
                    } else if (selectedMode === 'annual') {
                        filterHtml = filterTemplates.year;
                    }
                    // 'last_6_months' usa filterTemplates.none
                    break;
                case 'infractions_by_month':
                case 'employees_by_department':
                case 'inspection_productivity':
                case 'revenue_by_type':
                    // Estos reportes no usan filtros adicionales por ahora (o usan valores por defecto)
                    filterHtml = filterTemplates.none; 
                    break;
                case 'inspector_performance':
                    if (selectedMode === 'period') {
                        filterHtml = filterTemplates.period;
                    } else if (selectedMode === 'inspector') {
                        filterHtml = filterTemplates.user; 
                    }
                    break;
                case 'detailed_inspections':
                    if (selectedMode === 'period') {
                        filterHtml = filterTemplates.period;
                    }
                    // 'all' usa filterTemplates.none
                    break;
                case 'payment_status':
                    if (selectedMode === 'year') {
                        filterHtml = filterTemplates.year; 
                    } else if (selectedMode === 'quarter') {
                        filterHtml = filterTemplates.year; // O period, dependiendo de lo que requiera tu lógica de backend.
                    }
                    break;
                case 'market_summary':
                    if (selectedMode === 'period') {
                        filterHtml = filterTemplates.period;
                    }
                    // 'market' usa filterTemplates.none
                    break;
            }
            
            filtersContent.html(filterHtml);
            
            // Mostrar u ocultar el área de filtros dinámicos
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
                    
                    // 3. Mostrar y precargar filtros dinámicos (Lógica de precarga completa)
                    let filterHtml = filterTemplates.none; 
                    
                    switch (selectedReport) {
                        case 'activity_history':
                            if (selectedMode === 'date_range') {
                                filterHtml = filterTemplates.date_range;
                            } else if (selectedMode === 'user') {
                                filterHtml = filterTemplates.user;
                            }
                            break;
                        case 'infraction_count': 
                            if (selectedMode === 'date_range' || selectedMode === 'weekly') {
                                filterHtml = filterTemplates.date_range;
                            } else if (selectedMode === 'annual') {
                                filterHtml = filterTemplates.year;
                            }
                            break;
                        case 'inspector_performance':
                            if (selectedMode === 'period') {
                                filterHtml = filterTemplates.period;
                            } else if (selectedMode === 'inspector') {
                                filterHtml = filterTemplates.user; 
                            }
                            break;
                        case 'detailed_inspections':
                            if (selectedMode === 'period') {
                                filterHtml = filterTemplates.period;
                            }
                            break;
                        case 'payment_status':
                            if (selectedMode === 'year') {
                                filterHtml = filterTemplates.year; 
                            } else if (selectedMode === 'quarter') {
                                filterHtml = filterTemplates.year; 
                            }
                            break;
                        case 'market_summary':
                            if (selectedMode === 'period') {
                                filterHtml = filterTemplates.period;
                            }
                            break;
                    }
                    
                    filtersContent.html(filterHtml);

                    // Precargar los valores específicos de los filtros
                    params.forEach((value, key) => {
                        const element = $(`#${key}`);
                        if (element.length) {
                            element.val(value);
                        }
                    });
                    
                    // Mostrar área de filtros si es necesario
                    const needsFilters = (filterHtml !== filterTemplates.none);
                    if (needsFilters) {
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