<?php
// Vista de listado de sanciones

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/SanctionsController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$sanctionsController = new SanctionsController();
$rol = new RolesController();

require_once __DIR__ . '/../../models/StatisticalReportModel.php';
$statsModel = new StatisticalReportModel();
$dashboardStats = $statsModel->getDashboardStats();

$sanctionsImposed = $dashboardStats['sanctions_imposed_month'] ?? 0;
$sanctionsPaid = $dashboardStats['sanctions_paid_month'] ?? 0;
$sanctionsPending = $dashboardStats['sanctions_pending_month'] ?? 0;

// --- MAPAS DE TRADUCCIÓN Y ESTILOS ---
$allowed_sanction_status = [
    'Imposed' => 'Impuesta',
    'Paid' => 'Pagada',
    'Pending' => 'Pendiente',
    'Canceled' => 'Cancelada'
];

$status_colors = [
    'Imposed' => 'warning',
    'Paid' => 'success',
    'Pending' => 'secondary',
    'Canceled' => 'danger'
];

// --- LÓGICA DE ELIMINACIÓN (Manejo de POST para DELETE) ---
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $deleteId = $_POST['id'];
    $deleteResult = $sanctionsController->delete($deleteId); 

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    header("Location: index.php");
    exit;    
}

// Preparar parámetros de filtrado
$filters = [
    'search' => $_GET['search'] ?? '', // Filtro general para DataTables
    'sanction_status' => $_GET['sanction_status'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null,
    'awardee_name' => $_GET['awardee_name'] ?? null,
    'awardee_cedula' => isset($_GET['awardee_cedula_number']) && $_GET['awardee_cedula_number'] !== '' 
                        ? ($_GET['awardee_cedula_prefix'] ?? 'V') . '%' . $_GET['awardee_cedula_number'] 
                        : null,
];

$activeFilters = array_filter($filters, fn($value, $key) => $value !== null && $value !== '' && $key !== 'search', ARRAY_FILTER_USE_BOTH);

$params = [
    'filters' => $activeFilters,
];

$result = $sanctionsController->index($params);

if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'No se pudo cargar la lista de sanciones. ' . ($result['message'] ?? 'Error desconocido.')
    ];
    $sanctions = []; 
} else {
    $sanctions = $result['sanctions'] ?? [];
}

$awardees = $result['awardees'] ?? [];
$has_filters = !empty($activeFilters); 

// Incluir header y layouts
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
                        icon: '<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'error'; ?>',
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
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-forbid-2-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                Listado de Sanciones
                            </h5>
                        </div>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form action="index.php" method="GET">
                                    <div class="row g-3">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                        <div class="col-md-3">
                                            <label for="awardee_name" class="form-label small">Adjudicatario</label>
                                            <input class="form-control" list="awardee_names" id="awardee_name" name="awardee_name" 
                                                   placeholder="Buscar por Nombre..." value="<?php echo htmlspecialchars($_GET['awardee_name'] ?? ''); ?>">
                                            <datalist id="awardee_names">
                                                <?php foreach ($awardees as $aw): ?>
                                                    <option value="<?php echo htmlspecialchars($aw['first_name'] . ' ' . $aw['last_name']); ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="awardee_cedula_number" class="form-label small">Cédula / RIF</label>
                                            <div class="input-group">
                                                <select class="form-select" name="awardee_cedula_prefix" style="max-width: 70px;">
                                                    <option value="V" <?php echo ($_GET['awardee_cedula_prefix'] ?? '') === 'V' ? 'selected' : ''; ?>>V</option>
                                                    <option value="E" <?php echo ($_GET['awardee_cedula_prefix'] ?? '') === 'E' ? 'selected' : ''; ?>>E</option>
                                                    <option value="J" <?php echo ($_GET['awardee_cedula_prefix'] ?? '') === 'J' ? 'selected' : ''; ?>>J</option>
                                                    <option value="G" <?php echo ($_GET['awardee_cedula_prefix'] ?? '') === 'G' ? 'selected' : ''; ?>>G</option>
                                                    <option value="P" <?php echo ($_GET['awardee_cedula_prefix'] ?? '') === 'P' ? 'selected' : ''; ?>>P</option>
                                                </select>
                                                <input type="number" class="form-control" id="awardee_cedula_number" name="awardee_cedula_number" 
                                                       placeholder="Solo números" value="<?php echo htmlspecialchars($_GET['awardee_cedula_number'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="sanction_status" class="form-label small">Estado</label>
                                            <select class="form-select" id="sanction_status" name="sanction_status">
                                                <option value="">-- Todos --</option>
                                                <?php 
                                                $current_status = $_GET['sanction_status'] ?? '';
                                                foreach ($allowed_sanction_status as $key => $value): 
                                                ?>
                                                    <option value="<?php echo htmlspecialchars($key); ?>" 
                                                        <?php echo $current_status === $key ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($value); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="date_from" class="form-label small">Fecha Desde</label>
                                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                                value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="date_to" class="form-label small">Fecha Hasta</label>
                                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                                value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                                        </div>
                                        <div class="col-12 filter-card-actions">
                                            <a href="index.php" class="btn btn-filter-clear"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                            <button type="submit" class="btn btn-filter-apply"><i class="ri-search-line me-1"></i> Filtrar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tarjetas de Métricas (Sanciones del Mes) -->
                        <div class="row g-3 mt-4 mb-2">
                            <!-- Impuestas -->
                            <div class="col-md-4">
                                <div class="card card-status-warning" style="background-color: #fff4e1; border: none; border-radius: 12px;">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="page-icon me-3" style="width:48px;height:48px;font-size:1.4rem; background-color: #ffab00; color: white; display: flex; align-items: center; justify-content: center; border-radius: 10px;">
                                            <i class="ri-file-warning-line"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #ffab00;"><?php echo number_format($sanctionsImposed); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Impuestas este mes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Pagadas -->
                            <div class="col-md-4">
                                <div class="card card-status-success" style="background-color: #e8fadf; border: none; border-radius: 12px;">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="page-icon me-3" style="width:48px;height:48px;font-size:1.4rem; background-color: #71dd37; color: white; display: flex; align-items: center; justify-content: center; border-radius: 10px;">
                                            <i class="ri-checkbox-circle-line"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #71dd37;"><?php echo number_format($sanctionsPaid); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Pagadas este mes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Pendientes -->
                            <div class="col-md-4">
                                <div class="card card-status-secondary" style="background-color: #ebeef1; border: none; border-radius: 12px;">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="page-icon me-3" style="width:48px;height:48px;font-size:1.4rem; background-color: #8592a3; color: white; display: flex; align-items: center; justify-content: center; border-radius: 10px;">
                                            <i class="ri-time-line"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #8592a3;"><?php echo number_format($sanctionsPending); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Pendientes este mes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($has_filters): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Resultados filtrados del servidor: (<?php echo count($sanctions); ?> registro<?php echo count($sanctions) != 1 ? 's' : ''; ?>). Use la caja de búsqueda (de la tabla) para filtrar localmente.
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <?php if (empty($sanctions)) : ?>
                            <div class="text-center py-4">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No se encontraron sanciones</h5>
                                <p class="text-muted">Ajusta los filtros.</p>
                            </div>
                        <?php else : ?>
                            <div class="table-responsive">
                                <table id="sanctionsTable" class="table table-striped table-hover align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>Puesto</th>
                                            <th>Adjudicatario</th>
                                            <th>Infracción</th>
                                            <th>Tipo de Sanción</th>
                                            <th>Monto de Multa</th>
                                            <th>Fecha Imposición</th>
                                            <th>Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sanctions as $sanction) : ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sanction['stall_number'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($sanction['first_name'] . ' ' . $sanction['last_name']); ?> 
                                                    <small class="text-muted">(<?php echo htmlspecialchars($sanction['id_number']); ?>)</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo ucfirst(htmlspecialchars($sanction['infraction_type_name'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo ucfirst(htmlspecialchars($sanction['severity_name'])); ?></td>
                                                <td><?php echo htmlspecialchars($sanction['fine_amount'] ?? 'N/A') . ' ' . htmlspecialchars($sanction['fine_currency'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($sanction['imposition_date']))); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $status_colors[$sanction['sanction_status']] ?? 'info'; ?>">
                                                        <?php echo htmlspecialchars($allowed_sanction_status[$sanction['sanction_status']] ?? 'Desconocido'); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($rol->hasPermission('INFRACTIONS', 'r')): ?>
                                                    <a href="view.php?id=<?php echo $sanction['sanction_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver Detalles">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php if ($rol->hasPermission('INFRACTIONS', 'w') && $sanction['sanction_status'] !== 'Paid'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar/Cancelar" 
                                                            onclick="confirmDelete(<?php echo $sanction['sanction_id']; ?>, '<?php echo htmlspecialchars($sanction['stall_number']); ?>')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
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
function confirmDelete(id, stall) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Vas a eliminar o cancelar la sanción del puesto ' + stall + '. Esta acción no se puede deshacer.',
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

// Inicialización de DataTables 🚀
$(document).ready(function() {
    
    // Contenido del encabezado personalizado para la vista de Impresión
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Sanciones</h2>
        </div>
    `;
    
    // Columnas a exportar
    const exportColumns = [0, 1, 2, 3, 4, 5, 6]; 
    
    if ($.fn.DataTable) {
        $('#sanctionsTable').DataTable({ 
            responsive: true,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3"Bf>rtip',
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
                        doc.content.splice(0, 1);
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
                        doc.content.splice(1, 0, {
                            canvas: [{ type: 'line', x1: 0, y1: 5, x2: 515, y2: 5, lineWidth: 1, lineColor: '#000000' }],
                            margin: [0, 0, 0, 20]
                        });
                        doc.content.splice(2, 0, {
                            text: 'Listado de Sanciones',
                            style: 'header',
                            alignment: 'center',
                            margin: [0, 0, 0, 15]
                        });
                        const table = doc.content.find(content => content.table);
                        if (table) {
                            table.table.body[0].forEach(function(cell) {
                                cell.fillColor = '#2d4154';
                                cell.color = 'white';
                                cell.bold = true;
                                cell.alignment = 'center';
                            });
                            for (let i = 1; i < table.table.body.length; i++) {
                                if (i % 2 === 0) {
                                    table.table.body[i].forEach(function(cell) {
                                        cell.fillColor = '#f2f2f2';
                                    });
                                }
                            }
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
                    title: 'Listado_Sanciones_Seramer' 
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
                { "orderable": false, "targets": 7 } 
            ]
        });
    } else {
        console.error("DataTables no está cargado.");
    }
    
    const initialSearchValue = '<?php echo addslashes($_GET['search'] ?? ''); ?>';
    if (initialSearchValue) {
        $('#sanctionsTable').DataTable().search(initialSearchValue).draw();
    }
});
</script>