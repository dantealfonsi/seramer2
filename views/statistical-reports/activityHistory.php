<?php
// views/reports/activity_history_content.php - SOLO CONTENIDO DE REPORTE Y LÓGICA
// Este archivo será INCLUIDO dentro de statistical_reports.php

// 1. CORRECCIÓN DE RUTA: Subir dos niveles (../../) para ir de views/reports/ a seramer2/models/
require_once __DIR__ . '/../../models/UserRecordsModel.php'; 

$userRecordsModel = new UserRecordsModel();

// --- 1. LÓGICA DE FILTROS ---
// Obtener los filtros del URL (vienen de statistical_reports.php a través de $_GET)
$filters = [
    // El filtro 'all' en report_mode hace que todos estos sean NULL, por lo que array_filter los eliminará
    'user_id'    => $_GET['filter_user'] ?? null,
    'start_date' => $_GET['start_date'] ?? null,
    'end_date'   => $_GET['end_date'] ?? null,
];

// 2. Obtener los datos del Modelo, solo enviando filtros que NO son NULL
$records = $userRecordsModel->getRecords(array_filter($filters));
$report_title = "Reporte Tabular: Historial de Actividad de Usuarios";
?>

<div class="card-body">
    <?php if (empty($records)): ?>
        <div class="text-center py-4">
            <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
            <h5 class="text-muted mt-2">No se encontraron registros de actividad</h5>
            <p class="text-muted">Ajuste los filtros de búsqueda e intente nuevamente.</p>
        </div>
    <?php else: ?>
        <p class="text-muted mb-3">Detalle de registros: **<?php echo count($records); ?>** acciones encontradas.</p>
        <div class="table-responsive">
            <table id="activityHistoryTable" class="table table-striped table-hover w-100">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Departamento</th>
                        <th>Acción Realizada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['id']); ?></td>
                            <td data-order="<?php echo strtotime($record['created_at']); ?>">
                                <?php echo date('d/m/Y H:i:s', strtotime($record['created_at'])); ?>
                            </td>
                            <td><?php echo htmlspecialchars($record['username']); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($record['department_name'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($record['action']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Contenido del encabezado personalizado para la vista de Impresión
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Reporte de Historial de Actividad</h2>
        </div>
    `;
    
    const tableId = '#activityHistoryTable';
    const exportColumns = [0, 1, 2, 3, 4]; 
    
    // Si DataTables ya está inicializado, destrúyelo primero.
    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }
    
    if ($.fn.DataTable) {
        $(tableId).DataTable({ 
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'landscape',
                    pageSize: 'LETTER', 
                    exportOptions: { columns: exportColumns },
                    customize: function (doc) {
                        doc.content.splice(0, 0, { text: 'Servicio Autonómo de Mercados de Bermúdez', alignment: 'center', style: 'header1' }, 
                                                { text: 'Reporte de Historial de Actividad', alignment: 'center', style: 'header2' }, 
                                                { text: '', margin: [0, 0, 0, 10] });
                        
                        doc.styles.header1 = { fontSize: 14, bold: true, margin: [0, 10, 0, 0] };
                        doc.styles.header2 = { fontSize: 12, bold: true, margin: [0, 0, 0, 5] };

                        const table = doc.content.find(content => content.table);
                        if (table) {
                            table.table.body[0].forEach(cell => { cell.fillColor = '#343a40'; cell.color = '#ffffff'; cell.bold = true; });
                            table.table.widths = Array(table.table.body[0].length).fill('*');
                        }
                    }
                },
                { extend: 'excelHtml5', text: '<i class="ri-file-excel-line"></i> Excel', className: 'btn btn-success btn-sm me-1', exportOptions: { columns: exportColumns }, title: 'Historial_Actividad_Seramer' },
                { extend: 'print', text: '<i class="ri-printer-line"></i> Imprimir', className: 'btn btn-info btn-sm', exportOptions: { columns: exportColumns }, messageTop: customHeader, 
                    customize: function (win) {
                        $(win.document.body).find('table thead th').css({'background-color': '#343a40', 'color': 'white', '-webkit-print-color-adjust': 'exact'});
                    }
                },
                'colvis'
            ],
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar la columna ascendente",
                    "sortDescending": ": activar para ordenar la columna descendente"
                } 
            },
            order: [[1, 'desc']], 
            "columnDefs": [] 
        });
    }
});
</script>