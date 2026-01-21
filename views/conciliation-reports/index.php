<?php
// Vista de listado de Informes de Conciliación

session_start();
require_once __DIR__ . '/../../controllers/ConciliationReportsController.php';

$reportsController = new ConciliationReportsController();

// Lógica de eliminación (se mantiene tu método actual con GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $reportsController->delete($deleteId);

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    header("Location: index.php");
    exit;
}

// --- 1. LÓGICA DE FILTRADO (Captura de GET) ---
$filters = [
    'citation_id' => $_GET['citation_id'] ?? null,
    'result' => $_GET['result'] ?? null,
    'start_date' => $_GET['start_date'] ?? null, // Para filtrar desde esta fecha
    'end_date' => $_GET['end_date'] ?? null,     // Para filtrar hasta esta fecha
];

// Solo incluir filtros que tengan un valor distinto a null o cadena vacía
$activeFilters = array_filter($filters, fn($value) => $value !== null && $value !== '');

$params = [
    'filters' => $activeFilters,
];
// ------------------------------------------------

// Lógica del Controlador para DataTables: Se pasa $params con los filtros activos.
$result = $reportsController->index($params);

// Extrae los resultados.
$reports = $result['reports'] ?? [];
$page_title = $result['page_title'] ?? 'Listado de Informes de Conciliación';
$has_filters = !empty($activeFilters); // Indicador para mostrar el mensaje de filtros


// Opciones para el select de resultados de conciliación
$allowed_results = [
    'Agreement Reached' => 'Acuerdo alcanzado',
    'No Agreement' => 'Sin acuerdo',
    'Case Postponed' => 'Caso pospuesto',
    'Absent Party' => 'Parte ausente'
];

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
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-file-text-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Informe
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form action="index.php" method="GET" class="card p-3 mb-4 shadow-sm">
                            <h6 class="card-title mb-3"><i class="ri-filter-2-line me-1"></i> Opciones de Filtrado Avanzado</h6>
                            <div class="row g-3">
                                
                                <div class="col-md-3">
                                    <label for="citation_id" class="form-label small">ID de Citación</label>
                                    <input type="number" class="form-control" id="citation_id" name="citation_id" 
                                        placeholder="Ej: 154" 
                                        value="<?php echo htmlspecialchars($_GET['citation_id'] ?? ''); ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="result" class="form-label small">Resultado</label>
                                    <select class="form-select" id="result" name="result">
                                        <option value="">-- Todos los Resultados --</option>
                                        <?php 
                                        $current_result = $_GET['result'] ?? '';
                                        foreach ($allowed_results as $key => $value): 
                                        ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>" 
                                                <?php echo (string)$current_result === $key ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($value); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label small">Fecha de Informe (Desde)</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                        value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="end_date" class="form-label small">Fecha de Informe (Hasta)</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                        value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-12 d-flex justify-content-end align-items-end">
                                    <a href="index.php" class="btn btn-outline-secondary me-2">Limpiar Filtros</a>
                                    <button type="submit" class="btn btn-info">
                                        <i class="ri-search-line"></i> Aplicar Filtros
                                    </button>
                                </div>
                            </div>
                        </form>

                        <?php if ($has_filters): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Resultados filtrados del servidor: (**<?php echo count($reports); ?>** registro<?php echo count($reports) != 1 ? 's' : ''; ?>).
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show mt-2 mx-3" role="alert">
                            <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <?php if (empty($reports)): ?>
                            <div class="text-center py-4">
                                <i class="ri-file-forbid-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    No hay informes de conciliación registrados
                                </h5>
                                <a href="create.php" class="btn btn-primary mt-2">
                                    <i class="ri-add-line"></i> Registrar Primer Informe
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="reportsTable" class="table table-striped table-hover w-100">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Citación</th>
                                            <th>Asistencia</th>
                                            <th>Resultado</th>
                                            <th>Fecha del Informe</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td>
                                                <a href="view.php?id=<?php echo $report['citation_id']; ?>">#<?php echo htmlspecialchars($report['citation_id']); ?></a>
                                            </td>
                                            <td>
                                                <?php if ($report['awardee_attendance'] == 1): ?>
                                                    <span class="badge bg-success">Presente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Ausente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($allowed_results[$report['result']] ?? $report['result']); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                // Asumiendo que report_date es un string de fecha/hora válido
                                                try {
                                                    $date = new DateTime($report['report_date']);
                                                    echo $date->format('d/m/Y H:i'); 
                                                } catch (Exception $e) {
                                                    echo 'Fecha inválida'; 
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $report['report_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <a href="edit.php?id=<?php echo $report['report_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $report['report_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el informe con ID: <strong id="reportId"></strong>?</p>
                <p class="text-danger"><small>Esta acción es permanente.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>


<script>
let deleteReportId = null;

function confirmDelete(id) {
    deleteReportId = id;
    document.getElementById('reportId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteReportId) {
        window.location.href = 'index.php?delete_id=' + deleteReportId; 
    }
});


// 4. Inicialización de DataTables 🚀
$(document).ready(function() {
    
    // Contenido del encabezado personalizado para la vista de Impresión
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Informes de Conciliación</h2>
        </div>
    `;
    
    // Columnas a exportar: ID Citación (0), Asistencia (1), Resultado (2), Fecha (3). Se excluye Acciones (4).
    const exportColumns = [0, 1, 2, 3]; 
    
    if ($.fn.DataTable) {
        $('#reportsTable').DataTable({ 
            responsive: true,
            
            // Configuración de los botones de exportación
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'portrait', 
                    pageSize: 'LETTER', 
                    exportOptions: {
                        columns: exportColumns 
                    },
                    customize: function (doc) {
                        // Agregar logo y encabezados personalizados
                        doc.content.splice(0, 0, {
                            image: commonPdfLogo,
                            width: 150,
                            alignment: 'center',
                            margin: [0, 0, 0, 10]
                        });
                        doc.content.splice(1, 0, { 
                            text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ', 
                            alignment: 'center', 
                            style: 'header1',
                            margin: [0, 0, 0, 5]
                        });
                        doc.content.splice(2, 0, { 
                            text: 'Listado de Informes de Conciliación', 
                            alignment: 'center', 
                            style: 'header2',
                            margin: [0, 0, 0, 15]
                        });

                        doc.styles.header1 = { fontSize: 14, bold: true };
                        doc.styles.header2 = { fontSize: 12, bold: true };

                        const table = doc.content.find(content => content.table);
                        if (table && table.table.body.length > 0) {
                            const headerRow = table.table.body[0];
                            headerRow.forEach(cell => {
                                cell.fillColor = '#343a40'; 
                                cell.color = '#ffffff';
                                cell.bold = true;
                                cell.alignment = 'left'; 
                            });
                        }
                        
                        table.table.widths = Array(table.table.body[0].length).fill('*');
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: {
                        columns: exportColumns 
                    },
                    title: 'Listado_InformesConciliacion_Seramer' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: exportColumns 
                    },
                    messageTop: customHeader, 
                    customize: function (win) {
                        $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                        
                        // Aplicar estilos para que el encabezado se imprima correctamente
                        $(win.document.body).find('head').append(
                            '<style>' +
                                'table thead th { ' + 
                                '   background-color: #343a40 !important; ' + 
                                '   color: white !important; ' + 
                                '   -webkit-print-color-adjust: exact; ' + 
                                '   text-align: left !important;' + 
                                '}' +
                            '</style>'
                        );
                    }
                },
                'colvis' 
            ],
            // Configuración de idioma a español
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
            // Orden por defecto
            order: [[3, 'desc']], // Ordenar por 'Fecha del Informe' (columna 3) de forma descendente
            // Deshabilitar el ordenamiento en la columna de Acciones
            "columnDefs": [
                { "orderable": false, "targets": 4 } // Columna 'Acciones' es la número 4 (0-indexado)
            ]
        });
    } else {
        console.error("DataTables no está cargado.");
    }
});
</script>