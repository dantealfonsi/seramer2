<?php
session_start();
// Asegúrate de que tu ComplaintsController->index() ahora acepta el array de filtros
require_once __DIR__ . '/../../controllers/ComplaintsController.php';
require_once __DIR__ . '/../../controllers/RolesController.php'; 

$complaintsController = new ComplaintsController();
$rol = new RolesController(); // Si se usa para permisos

require_once __DIR__ . '/../../models/StatisticalReportModel.php';
$statsModel = new StatisticalReportModel();
$dashboardStats = $statsModel->getDashboardStats();
$complaintsThisMonth = $dashboardStats['complaints_this_month'] ?? 0;

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

// 3. Lógica de eliminación (Manejo de POST para DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $deleteId = $_POST['id'];
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
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: '<?php echo $_SESSION['flash_message']['type'] === 'success' || $_SESSION['flash_message']['type'] === 'primary' ? 'success' : 'error'; ?>',
                            title: '<?php echo addslashes($_SESSION['flash_message']['message']); ?>',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true,
                            width: '450px'
                        });
                    });
                    </script>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-chat-voice-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <?php if ($_SESSION['selected_department'] === 'Recursos Humanos'): ?>
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-add-line"></i> Nueva Queja
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form action="index.php" method="GET">
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
                                        <div class="col-12 filter-card-actions">
                                            <a href="index.php" class="btn btn-filter-clear"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                            <button type="submit" class="btn btn-filter-apply"><i class="ri-search-line me-1"></i> Filtrar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tarjeta de Métricas (Ancho Completo) -->
                        <div class="row g-3 mt-4 mb-2">
                            <div class="col-12">
                                <div class="card card-status-primary" style="background-color: #ffffff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important; color: #696cff;">
                                            <i class="ri-chat-voice-line" style="font-size: 1.4rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #696cff;"><?php echo number_format($complaintsThisMonth); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Quejas / Sugerencias recibidas este mes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($has_filters): ?>
                        <div class="mt-2 text-end">
                            <small class="text-muted">
                                <i class="ri-information-line me-1"></i> Resultados filtrados (<?php echo count($complaints); ?> registros).
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
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo htmlspecialchars($allowed_tipo[$complaint['complaint_type']] ?? $complaint['complaint_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $priority_classes = ['Low' => 'success', 'Medium' => 'info', 'High' => 'warning', 'Urgent' => 'danger'];
                                                $p_class = $priority_classes[$complaint['complaint_priority']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $p_class; ?>">
                                                    <?php echo htmlspecialchars($allowed_priority[$complaint['complaint_priority']] ?? $complaint['complaint_priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_classes = ['Received' => 'secondary', 'In Process' => 'primary', 'Resolved' => 'success', 'Closed' => 'dark'];
                                                $s_class = $status_classes[$complaint['complaint_status']] ?? 'light';
                                                ?>
                                                <span class="badge bg-<?php echo $s_class; ?>">
                                                    <?php echo htmlspecialchars($allowed_status[$complaint['complaint_status']] ?? $complaint['complaint_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-calendar-line me-1 text-muted"></i>
                                                    <?php 
                                                    $date = new DateTime($complaint['complaint_datetime']);
                                                    echo $date->format('d/m/Y'); 
                                                    ?>
                                                </div>
                                                <small class="text-muted"><?php echo $date->format('h:i A'); ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <?php // if ($rol->hasPermission('COMPLAINTS', 'r')): ?>
                                                    <a href="view.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <?php // endif; ?>
                                                    <?php if ($_SESSION['selected_department'] === 'Recursos Humanos'): ?>
                                                    <a href="edit.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $complaint['complaint_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                    <?php endif; ?>
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

<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
// Función para eliminación con SweetAlert2
function confirmDelete(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "La queja con ID #" + id + " será eliminada permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#696cff',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-outline-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear un formulario temporal para enviar el DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(idInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

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
                        // 1. Remover título por defecto
                        doc.content.splice(0, 1);

                        // 2. Agregar Encabezado Institucional (Logo + Texto)
                        doc.content.unshift({
                            columns: [
                                {
                                    image: commonPdfLogo,
                                    width: 50
                                },
                                {
                                    text: [
                                        { text: 'REPÚBLICA BOLIVARIANA DE VENEZUELA\n', fontSize: 10, bold: true },
                                        { text: 'GOBIERNO BOLIVARIANA DE VENEZUELA\n', fontSize: 10, bold: true },
                                        { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\n', fontSize: 10, bold: true },
                                        { text: 'DIRECCIÓN DE ADMINISTRACIÓN "SERAMER"', fontSize: 10, bold: true }
                                    ],
                                    margin: [10, 0, 0, 0]
                                }
                            ],
                            margin: [0, 0, 0, 10]
                        });

                        // 3. Agregar Línea Horizontal
                        doc.content.splice(1, 0, {
                            canvas: [{ type: 'line', x1: 0, y1: 5, x2: 515, y2: 5, lineWidth: 1, lineColor: '#000000' }],
                            margin: [0, 0, 0, 20]
                        });

                        // 4. Agregar Título Centrado
                        doc.content.splice(2, 0, {
                            text: 'Listado de Quejas',
                            style: 'header',
                            alignment: 'center',
                            margin: [0, 0, 0, 15]
                        });

                        // 5. Estilo de la Tabla
                        const table = doc.content.find(content => content.table);
                        if (table) {
                            // Estilo de la cabecera
                            table.table.body[0].forEach(function(cell) {
                                cell.fillColor = '#2d4154';
                                cell.color = 'white';
                                cell.bold = true;
                                cell.alignment = 'center';
                            });

                            // Zebra striping
                            for (let i = 1; i < table.table.body.length; i++) {
                                if (i % 2 === 0) {
                                    table.table.body[i].forEach(function(cell) {
                                        cell.fillColor = '#f2f2f2';
                                    });
                                }
                            }
                            
                            // Ajustar anchos
                            table.table.widths = Array(table.table.body[0].length).fill('*');
                        }
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
                                '@media print { @page { size: letter; margin: 1cm; } } ' +
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

<!-- Modal para Agregar Seguimiento -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Agregar Seguimiento de Queja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="historyForm">
                <div class="modal-body">
                    <input type="hidden" id="history_complaint_id" name="complaint_id">
                    <input type="hidden" name="action" value="addHistory">
                    
                    <div class="mb-3">
                        <label for="action_type" class="form-label">Tipo de Acción</label>
                        <select class="form-select" id="action_type" name="action_type" required>
                            <option value="Inspection">Inspección</option>
                            <option value="Call">Llamada</option>
                            <option value="Meeting">Reunión</option>
                            <option value="Resolution">Resolución</option>
                            <option value="Other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="action_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="action_description" name="action_description" rows="3" required placeholder="Escriba qué acciones se tomaron..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="action_result" class="form-label">Resultado</label>
                        <textarea class="form-control" id="action_result" name="action_result" rows="2" placeholder="Resultado de estas acciones..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-info">Guardar Seguimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Funciones para Seguimiento
function openHistoryModal(complaintId) {
    document.getElementById('history_complaint_id').value = complaintId;
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    historyModal.show();
}

document.getElementById('historyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('api_complaints.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Seguimiento agregado correctamente.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'No se pudo agregar el seguimiento.'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al procesar la solicitud.'
        });
    });
});
</script>