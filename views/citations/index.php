<?php
session_start();
// Asegúrate de que esta ruta es correcta
require_once __DIR__ . '/../../controllers/CitationsController.php';
require_once __DIR__ . '/../../controllers/ConciliationReportsController.php';

$citationsController = new CitationsController();
$conciliationReportsController = new ConciliationReportsController();

// 1. OBTENER PARÁMETROS DE FILTRADO DEL GET
// Ahora obtenemos los parámetros de filtro del URL (método GET), que se establecen al hacer clic en "Aplicar Filtros"
$filter_params = [
    'status'   => $_GET['filterStatus'] ?? '',
    'location' => $_GET['filterLocation'] ?? '',
    'dateStart'=> $_GET['filterDateStart'] ?? '',
    'dateEnd'  => $_GET['filterDateEnd'] ?? '',
    // Mantenemos la lógica de búsqueda original si la tienes, aunque DataTables tiene su propio campo global.
    'search'   => $_GET['search'] ?? ''
];

$params = [
    'search' => $filter_params['search']
];

// Llama al controlador para obtener TODOS los registros (asumiendo que el modelo fue ajustado)
$result = $citationsController->index($params);

// Opciones en español para el select de estado de citación
$allowed_status = [
    ''            => 'Todos los Estados',
    'Scheduled'   => 'Programada',
    'Rescheduled' => 'Reprogramada',
    'Completed'   => 'Completada',
    'Canceled'    => 'Cancelada'
];
$status_colors = ['Scheduled' => 'primary', 'Rescheduled' => 'info', 'Completed' => 'success', 'Canceled' => 'dark'];


// Verifica y extrae el resultado de forma segura
if (isset($result['success']) && $result['success']) {
    extract($result);
    $citations = $citations ?? []; 
    $page_title = $page_title ?? 'Gestión de Citaciones';
} else {
    $citations = [];
    $page_title = 'Error de Carga';
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => $result['message'] ?? 'Hubo un error al cargar las citaciones.'
    ];
}


// Lógica de eliminación (Procesada en la misma página)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $citationsController->delete($deleteId);

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    // Redirigir para evitar reenvío del formulario
    header("Location: index.php");
    exit;
}

// Incluye los layouts base (Asegúrate de que estas rutas son correctas)
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show mt-2" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title dani-title">
                            <i class="ri-calendar-event-line me-1 dani-icon-lg dani-bg-purple" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Citación
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form action="index.php" method="GET" class="card p-3 mb-4 shadow-sm" id="filterForm">
                            <h6 class="card-title mb-3"><i class="ri-filter-2-line me-1"></i> Opciones de Filtrado</h6>
                            <div class="row g-3 align-items-end">
                                
                                <!-- Filtro por Fecha -->
                                <div class="col-md-2">
                                    <label for="filterDate" class="form-label small">Fecha:</label>
                                    <input type="date" id="filterDate" name="filterDate" class="form-control" 
                                           value="<?php echo htmlspecialchars($_GET['filterDate'] ?? ''); ?>">
                                </div>
                                
                                <!-- Filtro por Estado -->
                                <div class="col-md-2">
                                    <label for="filterStatus" class="form-label small">Estado:</label>
                                    <select id="filterStatus" name="filterStatus" class="form-select">
                                        <?php foreach ($allowed_status as $key => $value): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>" 
                                                <?php echo ($filter_params['status'] === $key) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($value); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Filtro por Puesto -->
                                <div class="col-md-2">
                                    <label for="filterStall" class="form-label small">Puesto:</label>
                                    <input type="text" id="filterStall" name="filterStall" class="form-control" 
                                           placeholder="Ej: L-001" value="<?php echo htmlspecialchars($_GET['filterStall'] ?? ''); ?>">
                                </div>

                                <!-- Filtro por Adjudicatario -->
                                <div class="col-md-2">
                                    <label for="filterAwardee" class="form-label small">Adjudicatario:</label>
                                    <input type="text" id="filterAwardee" name="filterAwardee" class="form-control" 
                                           placeholder="Nombre" value="<?php echo htmlspecialchars($_GET['filterAwardee'] ?? ''); ?>">
                                </div>

                                <!-- Filtro por Cédula/RIF -->
                                <div class="col-md-3">
                                    <label for="filterIdNumber" class="form-label small">Cédula/RIF:</label>
                                    <div class="input-group">
                                        <select id="filterIdPrefix" name="filterIdPrefix" class="form-select" style="max-width: 70px;">
                                            <option value="">-</option>
                                            <?php 
                                            $prefixes = ['V', 'E', 'J', 'G', 'P'];
                                            $selectedPrefix = $_GET['filterIdPrefix'] ?? '';
                                            foreach ($prefixes as $prefix): ?>
                                                <option value="<?php echo $prefix; ?>" <?php echo ($selectedPrefix === $prefix) ? 'selected' : ''; ?>>
                                                    <?php echo $prefix; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" id="filterIdNumber" name="filterIdNumber" class="form-control" 
                                               placeholder="Número" pattern="[0-9]*" value="<?php echo htmlspecialchars($_GET['filterIdNumber'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                                <div class="col-md-2 d-flex justify-content-end" style="align-self: end;width: auto;margin-top: 1em;">
                                    <a href="index.php" class="btn btn-outline-secondary me-2">Limpiar Filtros</a>
                                    <button type="submit" class="btn btn-info">
                                        <i class="ri-search-line"></i> Aplicar Filtros
                                    </button>
                                </div>
                        </form>
                        </div>

                    <div class="card-body">
                        <?php if (empty($citations)): ?>
                            <div class="text-center py-4">
                                <i class="ri-calendar-close-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    No hay citaciones programadas.
                                </h5>
                                <a href="create.php" class="btn btn-primary mt-2">
                                    <i class="ri-add-line"></i> Programar Primera Citación
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="citationsTable" class="table table-striped table-hover w-100">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Citación</th> 
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Puesto</th>
                                            <th>Adjudicatario</th>
                                            <th>Infracción</th>
                                            <th>Mediador</th>
                                            <th>Estado</th> 
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citations as $citation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($citation['citation_id']); ?></td>
                                            <td>
                                                <?php 
                                                $date = new DateTime($citation['citation_datetime']);
                                                echo $date->format('d/m/Y'); 
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                echo $date->format('h:i A'); 
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo htmlspecialchars($citation['stall_number'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($citation['awardee_full_name'] ?? 'N/A'); ?>
                                                <?php if (!empty($citation['awardee_id_number'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($citation['awardee_id_number']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <i class="ri-eye-line text-primary" style="cursor: pointer; font-size: 1.2rem;" 
                                                   data-bs-toggle="popover" 
                                                   data-bs-trigger="hover focus" 
                                                   title="Detalles de la Infracción" 
                                                   data-bs-html="true"
                                                   data-bs-content="<strong>Desc:</strong> <?php echo htmlspecialchars($citation['infraction_description'] ?? 'Sin descripción'); ?><br><strong>Ubicación:</strong> <?php echo htmlspecialchars($citation['location']); ?>">
                                                </i>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($citation['mediator_full_name'] ?? 'N/A'); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_key = $citation['citation_status']; 
                                                $badge_class = '';
                                                switch($status_key) {
                                                    case 'Scheduled': $badge_class = 'primary'; break;
                                                    case 'In Process': $badge_class = 'warning'; break; // Nuevo estado
                                                    case 'Rescheduled': $badge_class = 'info'; break;
                                                    case 'Completed': $badge_class = 'success'; break;
                                                    case 'Canceled': $badge_class = 'danger'; break; // Cambiado a danger o dark según preferencia
                                                    default: $badge_class = 'secondary';
                                                }
                                                // Traducción manual si no está en el array allowed_status por alguna razón
                                                $status_label = $allowed_status[$status_key] ?? $status_key;
                                                if ($status_key === 'In Process') $status_label = 'En Proceso';
                                                ?>
                                                <span class="badge bg-<?php echo $badge_class; ?>" data-status-key="<?php echo htmlspecialchars(strtolower($status_key)); ?>">
                                                    <?php echo htmlspecialchars($status_label); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                // Check if this citation has a conciliation report
                                                $existingReport = $conciliationReportsController->getByCitationId($citation['citation_id']);
                                                ?>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $citation['citation_id']; ?>" class="btn btn-sm btn-outline-info" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <a href="edit.php?id=<?php echo $citation['citation_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <?php if ($existingReport): ?>
                                                        <a href="../conciliation-reports/view.php?id=<?php echo $existingReport['report_id']; ?>" class="btn btn-sm btn-outline-success" title="Ver Informe"><i class="ri-file-text-line"></i></a>
                                                    <?php else: ?>
                                                        <a href="../conciliation-reports/create.php?citation_id=<?php echo $citation['citation_id']; ?>" class="btn btn-sm btn-outline-primary" title="Registrar Informe"><i class="ri-file-add-line"></i></a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $citation['citation_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
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
                <p>¿Está seguro que desea eliminar la citación con ID: <strong id="citationId"></strong>?</p>
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

<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
let deleteCitationId = null;

function confirmDelete(id) {
    deleteCitationId = id;
    document.getElementById('citationId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteCitationId) {
        window.location.href = 'index.php?delete_id=' + deleteCitationId; 
    }
});

$(document).ready(function() {
    // 1. Obtener los valores de filtro de la URL (PHP los cargó en el HTML)
    // No necesitamos obtener los valores de la URL aquí, ya que el PHP los imprime directamente en los campos del formulario.
    // Al cargar la página, los campos ya tienen los valores del filtro aplicado.
    
    // 2. Inicializar DataTables
    const table = $('#citationsTable').DataTable({ 
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            // Configuración de botones (Mantenida)
             {
                extend: 'pdfHtml5',
                text: '<i class="ri-file-pdf-line"></i> PDF',
                className: 'btn btn-danger btn-sm me-1',
                orientation: 'landscape',
                pageSize: 'LETTER', 
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                customize: function (doc) {
                    doc.content.splice(0, 0, { text: 'Servicio Autonómo de Mercados de Bermúdez', alignment: 'center', style: 'header1' }, { text: 'Listado de Citaciones Programadas', alignment: 'center', style: 'header2' }, { text: '', margin: [0, 0, 0, 10] });
                    doc.styles.header1 = { fontSize: 14, bold: true, margin: [0, 10, 0, 0] };
                    doc.styles.header2 = { fontSize: 12, bold: true, margin: [0, 0, 0, 5] };
                    const table = doc.content.find(content => content.table);
                    if (table && table.table.body.length > 0) {
                        const headerRow = table.table.body[0];
                        headerRow.forEach(cell => { cell.fillColor = '#343a40'; cell.color = '#ffffff'; cell.bold = true; cell.alignment = 'left'; });
                    }
                    table.table.widths = Array(table.table.body[0].length).fill('*');
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="ri-file-excel-line"></i> Excel',
                className: 'btn btn-success btn-sm me-1',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                title: 'Listado_Citaciones_Seramer' 
            },
            {
                extend: 'print',
                text: '<i class="ri-printer-line"></i> Imprimir',
                className: 'btn btn-info btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                messageTop: `
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
                        <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Citaciones Programadas</h2>
                    </div>`,
                customize: function (win) {
                    $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                    $(win.document.body).find('head').append(
                        '<style>table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important;}</style>'
                    );
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
        order: [[1, 'desc'], [2, 'desc']], 
        "columnDefs": [
            { "orderable": false, "targets": [5, 8] },
            { "visible": false, "targets": 0 } 
        ],
        drawCallback: function() {
            // Reinicializar popovers después de cada renderizado de la tabla
            $('[data-bs-toggle="popover"]').popover({
                html: true,
                trigger: 'hover'
            });
        }
    });

    // =========================================================
    // 3. FUNCIÓN DE FILTRADO GLOBAL PERSONALIZADO
    // =========================================================

    // Sobreescribe la función de búsqueda de DataTables
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            // Obtener los valores de filtro de los campos de entrada
            const statusFilter = $('#filterStatus').val();
            // const locationFilter = $('#filterLocation').val().toLowerCase(); // Eliminado
            const dateFilter = $('#filterDate').val();

            // Columnas de la tabla (índices basados en 0):
            // 0: ID (Oculto)
            // 1: Fecha (DD/MM/YYYY)
            // 2: Hora (hh:mm A)
            // 3: Puesto
            // 4: Adjudicatario
            // 5: Infracción (Icono)
            // 6: Mediador
            // 7: Estado (HTML del badge)
            // 8: Acciones

            // Obtener datos de la fila actual
            const statusMatch = data[7].match(/data-status-key="([^"]+)"/);
            const rowStatus = statusMatch ? statusMatch[1] : ''; 

            const rowDateStr = data[1]; // Formato: DD/MM/YYYY

            let passStatus = true;
            let passDate = true;

            // 1. Filtrar por Estado
            if (statusFilter !== '' && statusFilter.toLowerCase() !== rowStatus) {
                passStatus = false;
            }

            // 2. Filtrar por Fecha exacta
            if (dateFilter) {
                // Convertir DD/MM/YYYY a YYYY-MM-DD para comparar con el input date (YYYY-MM-DD)
                const parts = rowDateStr.split('/');
                if (parts.length === 3) {
                    // parts[0] = DD, parts[1] = MM, parts[2] = YYYY
                    // Input date format: YYYY-MM-DD
                    const rowDateISO = `${parts[2]}-${parts[1]}-${parts[0]}`;
                    if (rowDateISO !== dateFilter) {
                        passDate = false;
                    }
                }
            }

            // La fila solo pasa si cumple con los criterios
            return passStatus && passDate;
        }
    );
    
    // **APLICACIÓN INICIAL DEL FILTRO:**
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('filterStatus') || urlParams.get('filterDate')) {
        table.draw();
    }
});
</script>