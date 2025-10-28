<?php
session_start();
// Asegúrate de que tu ComplaintsController->index() ahora acepta el array de filtros
require_once __DIR__ . '/../../controllers/ComplaintsController.php';
require_once __DIR__ . '/../../controllers/RolesController.php'; 

$complaintsController = new ComplaintsController();
$rol = new RolesController(); // Si se usa para permisos

// Opciones en español para los select de estado y prioridad (Necesario para el formulario)
$allowed_priority = [
    'Low' => 'Baja',
    'Medium' => 'Media',
    'High' => 'Alta',
    'Urgent' => 'Urgente'
];
$allowed_status = [
    'Received' => 'Recibido',
    'In Process' => 'En Proceso',
    'Resolved' => 'Resuelto',
    'Closed' => 'Cerrado'
];
$allowed_tipo = [
    'Suggestion' => 'Sugerencia',
    'Claim' => 'Reclamo',
    'Question' => 'Pregunta'     
];


// --- Lógica del Controlador para DataTables y Filtros ---

// 1. Preparar parámetros de filtrado
$filters = [
    // El filtro 'search' se mantiene para la búsqueda global si es necesaria antes de DataTables
    'search' => $_GET['search'] ?? '', 
    'complaint_type' => $_GET['complaint_type'] ?? null,
    'complaint_priority' => $_GET['complaint_priority'] ?? null,
    'complaint_status' => $_GET['complaint_status'] ?? null,
];

// Solo incluir filtros que tengan un valor distinto a null o cadena vacía para optimizar la consulta
$activeFilters = array_filter($filters, fn($value) => $value !== null && $value !== '');

$params = [
    'filters' => $activeFilters,
];

// Asume que index() ahora acepta el array $params y lo usa para filtrar en la consulta SQL.
$result = $complaintsController->index($params);

// 2. Extracción segura de resultados
if (isset($result['success']) && $result['success']) {
    $complaints = $result['complaints'] ?? [];
    $page_title = $result['page_title'] ?? 'Listado de Quejas';
    $has_filters = !empty($activeFilters); // Verifica si se aplicó algún filtro
} else {
    // Manejo de error si la carga falla
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Error al cargar las quejas: ' . ($result['message'] ?? 'Error desconocido.')
    ];
    $complaints = [];
    $page_title = 'Error de Carga';
    $has_filters = false;
}

// 3. Lógica de eliminación (manteniendo tu método actual con GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $complaintsController->delete($deleteId);

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    header("Location: index.php");
    exit;
}

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
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-chat-voice-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Queja
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form action="index.php" method="GET" class="card p-3 mb-4 shadow-sm">
                            <h6 class="card-title mb-3"><i class="ri-filter-2-line me-1 "></i> Opciones de Filtrado Avanzado</h6>
                            <div class="row g-3">
                                
                                <div class="col-md-4">
                                    <label for="complaint_type" class="form-label small">Tipo de Queja</label>
                                    <select class="form-select" id="complaint_type" name="complaint_type">
                                        <option value="">-- Todos los Tipos --</option>
                                        <?php 
                                        $current_type = $activeFilters['complaint_type'] ?? '';
                                        foreach ($allowed_tipo as $key => $value): 
                                        ?>
                                            <option value="<?php echo $key; ?>" <?php echo $current_type === $key ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="complaint_priority" class="form-label small">Prioridad</label>
                                    <select class="form-select" id="complaint_priority" name="complaint_priority">
                                        <option value="">-- Todas las Prioridades --</option>
                                        <?php 
                                        $current_priority = $activeFilters['complaint_priority'] ?? '';
                                        foreach ($allowed_priority as $key => $value): 
                                        ?>
                                            <option value="<?php echo $key; ?>" <?php echo $current_priority === $key ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="complaint_status" class="form-label small">Estado</label>
                                    <select class="form-select" id="complaint_status" name="complaint_status">
                                        <option value="">-- Todos los Estados --</option>
                                        <?php 
                                        $current_status = $activeFilters['complaint_status'] ?? '';
                                        foreach ($allowed_status as $key => $value): 
                                        ?>
                                            <option value="<?php echo $key; ?>" <?php echo $current_status === $key ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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
                                Resultados filtrados del servidor: (<?php echo count($complaints); ?> registro<?php echo count($complaints) != 1 ? 's' : ''; ?>). Use la caja de búsqueda para filtrar localmente.
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>


                    <div class="card-body">
                        <?php if (empty($complaints)): ?>
                            <div class="text-center py-4">
                                <i class="ri-chat-off-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_filters ? 'No se encontraron quejas con los filtros aplicados' : 'No hay quejas registradas'; ?>
                                </h5>
                                <?php if (!$has_filters): ?>
                                    <a href="create.php" class="btn btn-primary mt-2">
                                        <i class="ri-add-line"></i> Registrar Primera Queja
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="complaintsTable" class="table table-striped table-hover w-100">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="d-none">ID</th> 
                                            <th>Cliente</th>
                                            <th>Tipo</th>
                                            <th>Prioridad</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($complaints as $complaint): ?>
                                        <tr>
                                            <td class="d-none"><?php echo htmlspecialchars($complaint['complaint_id']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($complaint['client_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($complaint['client_email']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($allowed_tipo[$complaint['complaint_type']]); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $priority_colors = ['Low' => 'secondary', 'Medium' => 'info', 'High' => 'warning', 'Urgent' => 'danger'];
                                                $p_color = $priority_colors[$complaint['complaint_priority']] ?? 'light';
                                                ?>
                                                <span class="badge bg-<?php echo $p_color; ?>"><?php echo htmlspecialchars($allowed_priority[$complaint['complaint_priority']]); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = ['Received' => 'primary', 'In Process' => 'warning', 'Resolved' => 'success', 'Closed' => 'dark'];
                                                $s_color = $status_colors[$complaint['complaint_status']] ?? 'light';
                                                ?>
                                                <span class="badge bg-<?php echo $s_color; ?>"><?php echo htmlspecialchars($allowed_status[$complaint['complaint_status']]); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $date = new DateTime($complaint['complaint_datetime']);
                                                echo $date->format('d/m/Y H:i'); 
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <?php // if ($rol->hasPermission('COMPLAINTS', 'r')): ?>
                                                    <a href="view.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <?php // endif; ?>
                                                    <?php // if ($rol->hasPermission('COMPLAINTS', 'w')): ?>
                                                    <a href="edit.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $complaint['complaint_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                    <?php // endif; ?>
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
                <p>¿Está seguro que desea eliminar la queja con ID: <strong id="complaintId"></strong>?</p>
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
let deleteComplaintId = null;

function confirmDelete(id) {
    deleteComplaintId = id;
    document.getElementById('complaintId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal')); 
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteComplaintId) {
        // Mantiene la lógica original de eliminación por GET
        window.location.href = 'index.php?delete_id=' + deleteComplaintId; 
    }
});

// Inicialización de DataTables 🚀
$(document).ready(function() {
    
    // Contenido del encabezado personalizado para la vista de Impresión
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Quejas</h2>
        </div>
    `;
    
    // Columnas a exportar: ID (0, oculta), Cliente (1), Tipo (2), Prioridad (3), Estado (4), Fecha (5). Se excluye Acciones (6).
    const exportColumns = [1, 2, 3, 4, 5]; 
    
    if ($.fn.DataTable) {
        const complaintsTable = $('#complaintsTable').DataTable({ 
            responsive: true,
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
                        doc.content.splice(0, 0, {
                            text: 'Servicio Autonómo de Mercados de Bermúdez', 
                            alignment: 'center', 
                            style: 'header1'
                        }, {
                            text: 'Listado de Quejas', 
                            alignment: 'center', 
                            style: 'header2'
                        }, {
                            text: '', 
                            margin: [0, 0, 0, 10]
                        });
                        doc.styles.header1 = { fontSize: 14, bold: true, margin: [0, 10, 0, 0] };
                        doc.styles.header2 = { fontSize: 12, bold: true, margin: [0, 0, 0, 5] };
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
                    title: 'Listado_Quejas_Seramer' 
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
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' 
            },
            order: [[0, 'desc']], 
            "columnDefs": [
                { "orderable": false, "targets": 6 }, // Acciones
                { "visible": false, "targets": 0 } // ID (oculta)
            ]
        });
        
        // Mover el valor del filtro 'search' (si existe) a la caja de búsqueda de DataTables
        const initialSearchValue = '<?php echo addslashes($activeFilters['search'] ?? ''); ?>';
        if (initialSearchValue) {
            // Aplica el filtro global del DataTables con el valor de la búsqueda general del GET
            complaintsTable.search(initialSearchValue).draw();
        }

    } else {
        console.error("DataTables no está cargado.");
    }
});
</script>