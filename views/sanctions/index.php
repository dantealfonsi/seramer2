<?php
// Vista de listado de sanciones

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/SanctionsController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$sanctionsController = new SanctionsController();
$rol = new RolesController();

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
// El formulario JS envía POST a 'delete.php', por lo que esta lógica
// debe replicarse en 'delete.php' o redirigir aquí si se desea centralizar.
// Si deseas centralizar la lógica de delete aquí:
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    // Si la acción DELETE viene centralizada a index.php (más común en frameworks)
    $deleteId = $_POST['id'];
    // Llama al método de eliminación (o desactivación, según la lógica de tu controlador)
    $deleteResult = $sanctionsController->delete($deleteId); 

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    header("Location: index.php");
    exit;    
}
// ----------------------------------------------------------

// Preparar parámetros de filtrado
$filters = [
    'search' => $_GET['search'] ?? '', // Filtro general para DataTables
    // ID removed
    'sanction_status' => $_GET['sanction_status'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null,
    'awardee_name' => $_GET['awardee_name'] ?? null,
    'awardee_cedula' => isset($_GET['awardee_cedula_number']) && $_GET['awardee_cedula_number'] !== '' 
                        ? ($_GET['awardee_cedula_prefix'] ?? 'V') . '%' . $_GET['awardee_cedula_number'] 
                        : null,
];

// Solo incluir filtros de servidor que tengan un valor distinto a null o cadena vacía
// Excluimos 'search' del filtrado del servidor si solo queremos usarlo para DataTables localmente
$activeFilters = array_filter($filters, fn($value, $key) => $value !== null && $value !== '' && $key !== 'search', ARRAY_FILTER_USE_BOTH);

$params = [
    'filters' => $activeFilters,
];

// Usar el controlador para obtener los datos
$result = $sanctionsController->index($params);

// Manejar el resultado de la carga
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'No se pudo cargar la lista de sanciones. ' . ($result['message'] ?? 'Error desconocido.')
    ];
    $sanctions = []; // Asegurar que $sanctions esté definida
} else {
    $sanctions = $result['sanctions'] ?? [];
}

// Recuperar lista de adjudicatarios para los filtros
$awardees = $result['awardees'] ?? [];

$has_filters = !empty($activeFilters); // Para el mensaje de resultados filtrados

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                
                <?php if (isset($_SESSION['flash_message'])) : ?>
                    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_message']['type']); ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title dani-title">
                            <i class="ri-forbid-2-line me-1 dani-icon-lg dani-bg-purple" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            Listado de Sanciones
                        </h5>
                        <!-- Botón de crear eliminado por solicitud -->
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form action="index.php" method="GET" class="card p-3 mb-4 shadow-sm">
                            <h6 class="card-title mb-3"><i class="ri-filter-2-line me-1 "></i> Opciones de Filtrado Avanzado</h6>
                            <div class="row g-3">
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                
                                <!-- Filtro por Adjudicatario -->
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

                                <!-- Filtro por Cédula -->
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
                                <table id="sanctionsTable" class="table table-striped table-hover w-100">
                                    <thead class="table-dark">
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
                                                    <a href="view.php?id=<?php echo $sanction['sanction_id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
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

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación/Cancelación de Sanción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea **eliminar o cancelar** la sanción del puesto <strong id="sanctionId"></strong>?</p>
                <p class="text-danger"><small>Esta acción marcará la sanción como CANCELADA o la eliminará permanentemente (dependiendo de la lógica del servidor).</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Continuar</button>
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
let deleteSanctionId = null;

function confirmDelete(id) {
    deleteSanctionId = id;
    document.getElementById('sanctionId').textContent = id;
    
    // Inicializar y mostrar el modal de Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteSanctionId) {
        // Crear un formulario para enviar la solicitud de eliminación/cancelación
        const form = document.createElement('form');
        form.method = 'POST';
        // NOTA: Si centralizaste la lógica en index.php, usa 'index.php'. 
        // Si usas un archivo dedicado 'delete.php' para el POST, usa 'delete.php'.
        form.action = 'index.php'; 
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteSanctionId;
        form.appendChild(idInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE'; // Usar DELETE para la acción
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
});

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
    // Puesto(0), Adjudicatario(1), Infracción(2), Tipo(3), Monto(4), Fecha(5), Estado(6)
    // Se excluye la Columna 7 (Acciones)
    const exportColumns = [0, 1, 2, 3, 4, 5, 6]; 
    
    if ($.fn.DataTable) {
        $('#sanctionsTable').DataTable({ 
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
                        doc.content.splice(0, 0, {
                            text: 'Servicio Autonómo de Mercados de Bermúdez', 
                            alignment: 'center', 
                            style: 'header1'
                        }, {
                            text: 'Listado de Sanciones', 
                            alignment: 'center', 
                            style: 'header2'
                        }, {
                            text: '', // Espaciador
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
            order: [[0, 'desc']], 
            // Deshabilitar el ordenamiento en la columna de Acciones
            "columnDefs": [
                { "orderable": false, "targets": 7 } 
            ]
        });
    } else {
        console.error("DataTables no está cargado.");
    }
    
    // Mover el valor del filtro 'search' (si existe) a la caja de búsqueda de DataTables
    const initialSearchValue = '<?php echo addslashes($_GET['search'] ?? ''); ?>';
    if (initialSearchValue) {
        // Aplica el filtro global del DataTables con el valor de la búsqueda general del GET
        $('#sanctionsTable').DataTable().search(initialSearchValue).draw();
    }
});
</script>