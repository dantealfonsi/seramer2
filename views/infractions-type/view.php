<?php
// ... Tu código PHP de lógica (session_start, includes, filters) se mantiene igual ...

session_start();

// 1. Incluir el controlador para Tipos de Infracción
require_once __DIR__ . '/../../controllers/InfractionTypeController.php';

$infractionTypeController = new InfractionTypeController();

// --- LÓGICA DE ELIMINACIÓN (Manejo de POST para DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE') {
    $deleteId = $_POST['id'] ?? null; 
    if ($deleteId) {
        // Llama al método de eliminación del controlador de Tipos de Infracción
        $deleteResult = $infractionTypeController->delete($deleteId); 

        $_SESSION['flash_message'] = [
            'type' => $deleteResult['success'] ? 'success' : 'danger',
            'message' => $deleteResult['message']
        ];
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Error: ID de Tipo de Infracción no proporcionado para la eliminación.'
        ];
    }
    
    header("Location: index.php"); 
    exit;
}
// ----------------------------------------------------------

// 2. Preparar parámetros de filtrado (Simplificado: solo búsqueda general)
$filters = [
    'search' => $_GET['search'] ?? '',
    // Se podrían agregar más filtros específicos si la tabla tiene más campos (ej: 'category')
];

$activeFilters = array_filter($filters, fn($value) => $value !== null && $value !== '');

$params = [
    'filters' => $activeFilters,
];

// 3. Obtener la lista de Tipos de Infracción
$result = $infractionTypeController->index($params);
$infractionTypes = $result['infraction_types'] ?? []; 
$page_title = $result['page_title'] ?? 'Listado de Tipos de Infracción';
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title dani-title">
                            <i class="ri-alert-line me-1 dani-icon"></i>
                            <?php echo htmlspecialchars("Gestión de Tipos de Infracción") ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Tipo
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form action="index.php" method="GET" class="card p-3 mb-4 shadow-sm">
                            <h6 class="card-title mb-3"><i class="ri-filter-2-line me-1 "></i> Opciones de Filtrado</h6>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-9">
                                    <label for="search" class="form-label small">Búsqueda General por Nombre o Descripción</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Nombre, Descripción, Penalidad..." 
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3 d-flex justify-content-end">
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
                                Resultados filtrados del servidor: (<?php echo count($infractionTypes); ?> registro<?php echo count($infractionTypes) != 1 ? 's' : ''; ?>). Use la caja de búsqueda para filtrar localmente.
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
                        <?php if (empty($infractionTypes)): ?>
                            <div class="text-center py-4">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No se encontraron Tipos de Infracción</h5>
                                <p class="text-muted">Ajusta los filtros o crea un nuevo tipo.</p>
                                <a href="create.php" class="btn btn-primary"><i class="ri-add-line"></i> Crear Nuevo Tipo</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="infractionTypesTable" class="table table-striped table-hover w-100">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre del Tipo</th>
                                            <th>Descripción</th>
                                            <th>Penalidad (días/monto)</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($infractionTypes as $type): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($type['infraction_type_id']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($type['name']); ?></span></td>
                                            <td><?php echo htmlspecialchars(substr($type['description'] ?? '', 0, 80)) . (strlen($type['description'] ?? '') > 80 ? '...' : ''); ?></td>
                                            <td><?php echo htmlspecialchars($type['penalty'] ?? 'N/A'); ?></td>
                                            <td class="text-center">
                                                <a href="view.php?id=<?php echo $type['infraction_type_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                <a href="edit.php?id=<?php echo $type['infraction_type_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                <button type="button" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Eliminar"
                                                                onclick="confirmDelete(<?php echo $type['infraction_type_id']; ?>)">
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Tipo de Infracción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el Tipo de Infracción con ID: <strong id="infractionTypeId"></strong>?</p>
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
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

## Script JavaScript para DataTables y Eliminación

Este script va después de la inclusión del `footer.php` y los archivos de DataTables.

```javascript
<script>
let deleteInfractionTypeId = null;

function confirmDelete(id) {
    deleteInfractionTypeId = id; 
    document.getElementById('infractionTypeId').textContent = id;
    
    // Asumimos que Bootstrap JS está cargado
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteInfractionTypeId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php'; // Apunta al mismo script para el manejo POST/DELETE
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteInfractionTypeId;
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
    
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Tipos de Infracción</h2>
        </div>
    `;
    
    // Columnas a exportar (excluyendo la Columna 4: Acciones)
    // Columnas: ID (0), Nombre (1), Descripción (2), Penalidad (3)
    const exportColumns = [0, 1, 2, 3]; 
    
    if ($.fn.DataTable) {
        $('#infractionTypesTable').DataTable({ 
            // Habilita la extensión Responsive
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
                    // Personalización del PDF
                    customize: function (doc) {
                        doc.content.splice(0, 0, {
                            text: 'Servicio Autonómo de Mercados de Bermúdez', 
                            alignment: 'center', 
                            style: 'header1'
                        }, {
                            text: 'Listado de Tipos de Infracción', 
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
                    title: 'Tipos_Infraccion_Seramer'
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
            order: [[0, 'asc']], 
            // Deshabilitar el ordenamiento en la columna de Acciones
            "columnDefs": [
                { "orderable": false, "targets": 4 } 
            ]
        });
    } else {
        console.error("DataTables no está cargado. Asegúrese de incluir los archivos JS y CSS en los layouts.");
    }
    
    // 2. Mover el valor del filtro 'search' (si existe) a la caja de búsqueda de DataTables
    const initialSearchValue = '<?php echo addslashes($_GET['search'] ?? ''); ?>';
    if (initialSearchValue) {
        $('#infractionTypesTable').DataTable().search(initialSearchValue).draw();
    }
});
</script>