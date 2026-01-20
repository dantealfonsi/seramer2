<?php
// ... Tu código PHP de lógica (session_start, includes, filters) se mantiene igual ...

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InspectionController.php';

$inspectionsController = new InspectionController();

// --- LÓGICA DE ELIMINACIÓN (Manejo de POST para DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE') {
    $deleteId = $_POST['id'] ?? null; 
    if ($deleteId) {
        $deleteResult = $inspectionsController->delete($deleteId); 

        $_SESSION['flash_message'] = [
            'type' => $deleteResult['success'] ? 'success' : 'danger',
            'message' => $deleteResult['message']
        ];
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Error: ID de reporte de inspección no proporcionado para la eliminación.'
        ];
    }
    
    header("Location: index.php"); 
    exit;
}
// ----------------------------------------------------------

// Mapa de traducción para los estados
$status_translations = [
    'Pending' => 'Pendiente', 
    'In Progress' => 'En Curso', 
    'Completed' => 'Completado', 
    'Cancelled' => 'Cancelado'
];

// Preparar parámetros de filtrado
$filters = [
    'search' => $_GET['search'] ?? '',
    'inspection_date' => $_GET['inspection_date'] ?? null,
    'inspection_status' => $_GET['inspection_status'] ?? null,
    'inspection_type_id' => $_GET['inspection_type_id'] ?? null,
    'stall_id' => $_GET['stall_id'] ?? null,
    'inspector_id' => $_GET['inspector_id'] ?? null,
];

$activeFilters = array_filter($filters, fn($value) => $value !== null && $value !== '');

$params = [
    'filters' => $activeFilters,
];

$result = $inspectionsController->index($params);
$inspections = $result['inspections'] ?? []; 
$page_title = $result['page_title'] ?? 'Listado de Reportes de Inspección';
$has_filters = !empty($activeFilters);
$inspection_types = $inspectionsController->getInspectionTypesList(); 
$stalls_list = $result['stalls'] ?? [];
$inspectors_list = $result['inspectors'] ?? [];

// Mapa de traducción para tipos de inspección
$type_translations = [
    'Rutine' => 'Rutina',
    'New Stall' => 'Nuevo Puesto',
    'Complain' => 'Queja/Denuncia'
];

// Incluir header y layouts
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
                            <i class="ri-search-eye-line me-1 dani-icon"></i>
                            <?php echo htmlspecialchars("Gestion de Inspecciones") ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Reporte
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form action="index.php" method="GET" class="card p-3 mb-4 shadow-sm">
                            <h6 class="card-title mb-3"><i class="ri-filter-2-line me-1 "></i> Opciones de Filtrado Avanzado</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="search" class="form-label small">Búsqueda General</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                        placeholder="Inspector, Puesto, Estado..." 
                                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="inspection_status" class="form-label small">Estado</label>
                                    <select class="form-select" id="inspection_status" name="inspection_status">
                                        <option value="">-- Todos los Estados --</option>
                                        <?php 
                                        // Usamos el mapa de traducción para llenar las opciones del filtro
                                        $allowed_status = $status_translations; 
                                        $current_status = $_GET['inspection_status'] ?? '';
                                        foreach ($allowed_status as $key => $value): // $key es 'Pending', $value es 'Pendiente'
                                        ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($current_status === $key) ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="inspection_type_id" class="form-label small">Tipo de Inspección</label>
                                    <select class="form-select" id="inspection_type_id" name="inspection_type_id">
                                        <option value="">-- Todos los Tipos --</option>
                                        <?php 
                                        $current_type = $_GET['inspection_type_id'] ?? '';
                                        if (isset($inspection_types) && is_array($inspection_types)) {
                                            foreach ($inspection_types as $type) {
                                                // Nota: Asumo que el valor del filtro debe ser el ID/nombre que entiende el backend.
                                                // Aquí se deja como estaba, usando el nombre o el ID.
                                                $value = htmlspecialchars($type['name'] ?? $type['inspection_type_id']);
                                                $display = htmlspecialchars($type['name'] ?? 'N/A'); 
                                                echo "<option value=\"$value\" " . (($current_type == $value) ? 'selected' : '') . ">$display</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="inspection_date" class="form-label small">Fecha Específica</label>
                                    <input type="date" class="form-control" id="inspection_date" name="inspection_date" 
                                        value="<?php echo htmlspecialchars($_GET['inspection_date'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="stall_id" class="form-label small">Puesto</label>
                                    <select class="form-select" id="stall_id" name="stall_id">
                                        <option value="">-- Todos los Puestos --</option>
                                        <?php 
                                        $current_stall = $_GET['stall_id'] ?? '';
                                        foreach ($stalls_list as $stall): 
                                        ?>
                                            <option value="<?php echo $stall['id']; ?>" <?php echo ($current_stall == $stall['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($stall['stall_number']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="inspector_id" class="form-label small">Inspector</label>
                                    <select class="form-select" id="inspector_id" name="inspector_id">
                                        <option value="">-- Todos los Inspectores --</option>
                                        <?php 
                                        $current_inspector = $_GET['inspector_id'] ?? '';
                                        foreach ($inspectors_list as $inspector): 
                                        ?>
                                            <option value="<?php echo $inspector['inspector_id']; ?>" <?php echo ($current_inspector == $inspector['inspector_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($inspector['full_name']); ?>
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
                                Resultados filtrados del servidor: (<?php echo count($inspections); ?> registro<?php echo count($inspections) != 1 ? 's' : ''; ?>). Use la caja de búsqueda para filtrar localmente.
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mx-3 mt-3" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <?php if (empty($inspections)): ?>
                            <div class="text-center py-4">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No se encontraron reportes de inspección</h5>
                                <p class="text-muted">Ajusta los filtros o crea un nuevo reporte.</p>
                                <a href="create.php" class="btn btn-primary"><i class="ri-add-line"></i> Crear Nuevo Reporte</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="inspectionsTable" class="table table-striped table-hover w-100">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Puesto</th>
                                            <th>Tipo</th>
                                            <th>Fecha Programada</th>
                                            <th>Inspector</th>
                                            <th>Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inspections as $inspection): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($inspection['stall_number'] ?? 'N/A'); ?></span></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php 
                                                    $type_key = $inspection['inspection_type_name'];
                                                    echo htmlspecialchars($type_translations[$type_key] ?? $type_key); 
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $inspection_date = new DateTime($inspection['scheduled_datetime']);
                                                echo $inspection_date->format('d/m/Y'); 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($inspection['inspector_name'] ?? 'N/A'); ?></td>
                                            <td>
                                            <?php
                                            $status_colors = ['Pending' => 'warning', 'In Progress' => 'primary', 'Completed' => 'success', 'Cancelled' => 'danger'];
                                            $status_key = $inspection['inspection_status']; // Clave en inglés
                                            $status_display = $status_translations[$status_key] ?? $status_key; // Obtener la traducción o usar la clave si no existe
                                            $color = $status_colors[$status_key] ?? 'secondary'; // Color basado en la clave en inglés
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>"><?php echo htmlspecialchars($status_display); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="view.php?id=<?php echo $inspection['report_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                <a href="edit.php?id=<?php echo $inspection['report_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Eliminar"
                                                            onclick="confirmDelete(<?php echo $inspection['report_id']; ?>)">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el reporte con ID: <strong id="inspectionId"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer y eliminará el registro de forma permanente.</small></p>
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
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>
<script>
let deleteInspectionId = null;

function confirmDelete(id) {
    deleteInspectionId = id; 
    document.getElementById('inspectionId').textContent = id;
    
    // Asumimos que Bootstrap JS está cargado
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteInspectionId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php'; 
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteInspectionId;
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

// Inicialización de DataTables 🚀
$(document).ready(function() {
    
    // Contenido del encabezado personalizado para la vista de Impresión (se inyecta en messageTop)
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Reportes de Inspección</h2>
        </div>
    `;
    
    // Columnas a exportar (excluyendo la Columna 5: Acciones)
    // Columnas: Puesto (0), Tipo (1), Fecha Programada (2), Inspector (3), Estado (4)
    const exportColumns = [0, 1, 2, 3, 4]; 
    
    if ($.fn.DataTable) {
        $('#inspectionsTable').DataTable({ 
            // Habilita la extensión Responsive
            responsive: true,
            
            // Configuración de los botones de exportación
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'portrait', // Vertical
                    pageSize: 'LETTER', 
                    exportOptions: {
                        columns: exportColumns 
                    },
                    // Personalización del PDF
                    customize: function (doc) {
                        // 1. Añadir encabezados personalizados
                        doc.content.splice(0, 0, {
                            text: 'Servicio Autonómo de Mercados de Bermúdez', 
                            alignment: 'center', 
                            style: 'header1'
                        }, {
                            text: 'Reportes de Inspección', 
                            alignment: 'center', 
                            style: 'header2'
                        }, {
                            text: '', // Espaciador
                            margin: [0, 0, 0, 10]
                        });

                        // 2. Definir estilos para los encabezados personalizados
                        doc.styles.header1 = { fontSize: 14, bold: true, margin: [0, 10, 0, 0] };
                        doc.styles.header2 = { fontSize: 12, bold: true, margin: [0, 0, 0, 5] };

                        // 3. Estilo para el encabezado de la tabla (thead)
                        const table = doc.content.find(content => content.table);
                        if (table && table.table.body.length > 0) {
                            const headerRow = table.table.body[0];
                            headerRow.forEach(cell => {
                                cell.fillColor = '#343a40'; 
                                cell.color = '#ffffff';      
                                cell.bold = true;
                                cell.alignment = 'left'; // Alineación a la IZQUIERDA
                            });
                        }
                        
                        // 4. Ajustar el ancho de la tabla para ocupar el 100% de la página
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
                    title: 'Reportes_Inspeccion_Seramer' // Nombre del archivo
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: exportColumns 
                    },
                    // Añadir encabezados personalizados al inicio de la vista de impresión
                    messageTop: customHeader, 
                    customize: function (win) {
                        // 1. Asegurar el ancho de la tabla
                        $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                        
                        // 2. Estilo del thead para impresión
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
                // Botón para restablecer la vista (ocultar/mostrar columnas)
                'colvis' 
            ],
            // Configuración de idioma a español
            language: {
                // Versión para DataTables 1.13.x
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
                { "orderable": false, "targets": 5 } 
            ]
        });
    } else {
        console.error("DataTables no está cargado. Asegúrese de incluir los archivos JS y CSS en los layouts.");
    }
    
    // 2. Mover el valor del filtro 'search' (si existe) a la caja de búsqueda de DataTables
    const initialSearchValue = '<?php echo addslashes($_GET['search'] ?? ''); ?>';
    if (initialSearchValue) {
        // Solo aplicar si hay una búsqueda inicial, si no, se deja la búsqueda del DataTables en blanco
        $('#inspectionsTable').DataTable().search(initialSearchValue).draw();
    }
});
</script>