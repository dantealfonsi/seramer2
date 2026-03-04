<?php
session_start();
// Asegúrate de que esta ruta es correcta
require_once __DIR__ . '/../../controllers/CitationsController.php';
require_once __DIR__ . '/../../controllers/ConciliationReportsController.php';

$citationsController = new CitationsController();
$conciliationReportsController = new ConciliationReportsController();

require_once __DIR__ . '/../../models/StatisticalReportModel.php';
$statsModel = new StatisticalReportModel();
$dashboardStats = $statsModel->getDashboardStats();
$citationsThisMonth = $dashboardStats['citations_this_month'] ?? 0;
$citationsScheduledMonth = $dashboardStats['citations_scheduled_month'] ?? 0;
$citationsCompletedMonth = $dashboardStats['citations_completed_month'] ?? 0;

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $deleteId = $_POST['id'];
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
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-0" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-calendar-event-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                        </div>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Citación
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form action="index.php" method="GET" id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label for="filterDate" class="form-label small">Fecha:</label>
                                            <input type="date" id="filterDate" name="filterDate" class="form-control" 
                                                   value="<?php echo htmlspecialchars($_GET['filterDate'] ?? ''); ?>">
                                        </div>
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
                                        <div class="col-md-2">
                                            <label for="filterStall" class="form-label small">Puesto:</label>
                                            <input type="text" id="filterStall" name="filterStall" class="form-control" 
                                                   placeholder="Ej: L-001" value="<?php echo htmlspecialchars($_GET['filterStall'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="filterAwardee" class="form-label small">Adjudicatario:</label>
                                            <input type="text" id="filterAwardee" name="filterAwardee" class="form-control" 
                                                   placeholder="Nombre" value="<?php echo htmlspecialchars($_GET['filterAwardee'] ?? ''); ?>">
                                        </div>
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
                                        <div class="col-12 filter-card-actions">
                                            <a href="index.php" class="btn btn-filter-clear"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                            <button type="submit" class="btn btn-filter-apply"><i class="ri-search-line me-1"></i> Filtrar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tarjetas de Métricas -->
                        <div class="row g-3 mt-4 mb-2">
                             <div class="col-md-4">
                                <div class="card card-status-primary" style="background-color: #ffffff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important; color: #696cff;">
                                            <i class="ri-calendar-event-line" style="font-size: 1.4rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #696cff;"><?php echo number_format($citationsThisMonth); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Citaciones este mes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tarjeta: Programadas -->
                             <div class="col-md-4">
                                <div class="card card-status-info" style="background-color: #ffffff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e1f5fe !important; color: #03a9f4;">
                                            <i class="ri-time-line" style="font-size: 1.4rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #03a9f4;"><?php echo number_format($citationsScheduledMonth); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Programadas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjeta: Resueltas (Completadas) -->
                             <div class="col-md-4">
                                <div class="card card-status-success" style="background-color: #ffffff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e8f5e9 !important; color: #4caf50;">
                                            <i class="ri-checkbox-circle-line" style="font-size: 1.4rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #4caf50;"><?php echo number_format($citationsCompletedMonth); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Resueltas este mes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
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



<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Vas a eliminar la citación #' + id + '. Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff3e1d',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php'; 
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

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
                exportOptions: { columns: [1, 2, 3, 4, 6, 7] },
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
                        canvas: [{ type: 'line', x1: 0, y1: 5, x2: 750, y2: 5, lineWidth: 1, lineColor: '#000000' }], // Adjusted x2 for landscape
                        margin: [0, 0, 0, 20]
                    });

                    // 4. Agregar Título Centrado
                    doc.content.splice(2, 0, {
                        text: 'Listado de Citaciones Programadas',
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
                        '<style>@media print { @page { size: letter; margin: 1cm; } } table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important;}</style>'
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
            const dateFilter = $('#filterDate').val();
            const stallFilter = $('#filterStall').val().toLowerCase().trim();
            const awardeeFilter = $('#filterAwardee').val().toLowerCase().trim();
            const idPrefixFilter = $('#filterIdPrefix').val();
            const idNumberFilter = $('#filterIdNumber').val().trim();

            // Columnas de la tabla (índices basados en 0):
            // 0: ID Citación
            // 1: Fecha (DD/MM/YYYY)
            // 2: Hora (hh:mm A)
            // 3: Puesto (HTML badge)
            // 4: Adjudicatario (Nombre + ID small)
            // 5: Infracción (Icono popover)
            // 6: Mediador
            // 7: Estado (HTML del badge)
            // 8: Acciones

            // 1. Obtener Estado
            const statusMatch = data[7].match(/data-status-key="([^"]+)"/);
            const rowStatus = statusMatch ? statusMatch[1] : ''; 

            // 2. Obtener Fecha
            const rowDateStr = data[1]; // Formato: DD/MM/YYYY

            // 3. Obtener Puesto (limpiar del badge)
            const rowStall = data[3].replace(/<[^>]*>?/gm, '').toLowerCase().trim();

            // 4. Obtener Adjudicatario
            const rowAwardee = data[4].toLowerCase().trim();

            let passStatus = true;
            let passDate = true;
            let passStall = true;
            let passAwardee = true;
            let passId = true;

            // 1. Filtrar por Estado
            if (statusFilter !== '' && statusFilter.toLowerCase() !== rowStatus) {
                passStatus = false;
            }

            // 2. Filtrar por Fecha exacta
            if (dateFilter) {
                const parts = rowDateStr.split('/');
                if (parts.length === 3) {
                    const rowDateISO = `${parts[2]}-${parts[1]}-${parts[0]}`;
                    if (rowDateISO !== dateFilter) {
                        passDate = false;
                    }
                }
            }

            // 3. Filtrar por Puesto (parcial)
            if (stallFilter && !rowStall.includes(stallFilter)) {
                passStall = false;
            }

            // 4. Filtrar por Adjudicatario (parcial)
            if (awardeeFilter && !rowAwardee.includes(awardeeFilter)) {
                passAwardee = false;
            }

            // 5. Filtrar por Cédula (Prefijo y/o Número)
            if (idPrefixFilter || idNumberFilter) {
                const fullIdSearch = (idPrefixFilter + idNumberFilter).toLowerCase();
                if (!rowAwardee.includes(fullIdSearch)) {
                    passId = false;
                }
            }

            // La fila solo pasa si cumple con TODOS los criterios
            return passStatus && passDate && passStall && passAwardee && passId;
        }
    );
    
    // **APLICACIÓN INICIAL DEL FILTRO:**
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('filterStatus') || urlParams.get('filterDate') || urlParams.get('filterStall') || urlParams.get('filterAwardee') || urlParams.get('filterIdNumber')) {
        table.draw();
    }
});
</script>