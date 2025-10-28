<?php
session_start();
require_once __DIR__ . '/../../controllers/SanctionTypesController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$sanctionTypesController = new SanctionTypesController();
$rol = new RolesController();

// --- Lógica del Controlador para DataTables ---
// DataTables no necesita 'page' ni 'limit' si no usas Server-Side Processing.
// Solo necesitamos obtener TODOS los datos (o la colección completa filtrada) al inicio.

// Si usabas paginación, el método index() del controlador debe ser modificado 
// para devolver todos los resultados sin paginar por defecto.
$params = [
    'search' => $_GET['search'] ?? '' // Mantener el filtro de búsqueda si lo usas en el controlador/modelo
];
$result = $sanctionTypesController->index($params);

// Verifica y extrae el resultado de forma segura
if (isset($result['success']) && $result['success']) {
    $sanction_types = $result['sanction_types'] ?? [];
    $page_title = $result['page_title'] ?? 'Listado de Tipos de Sanción';
    $search = $params['search']; // Para el input search si lo mantienes
    $has_search = !empty($search);
} else {
    // Manejo de error si la carga falla
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Error al cargar los tipos de sanción: ' . ($result['message'] ?? 'Error desconocido.')
    ];
    $sanction_types = [];
    $page_title = 'Error de Carga';
    $search = '';
    $has_search = false;
}

// Lógica de eliminación (manteniendo tu método actual con GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $sanctionTypesController->delete($deleteId);

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
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title dani-title"  style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-alert-line me-1 dani-icon-lg dani-bg-purple" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Tipo de Sanción
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($sanction_types)): ?>
                            <div class="text-center py-4">
                                <i class="ri-file-search-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    No hay tipos de sanción registrados.
                                </h5>
                                <a href="create.php" class="btn btn-primary mt-2">
                                    <i class="ri-add-line"></i> Registrar Primer Tipo de Sanción
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="sanctionTypesTable" class="table table-striped table-hover w-100">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th> <th>Tipo de Sanción</th>
                                            <th>Descripción</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                        <tbody>
                                            <?php foreach ($sanction_types as $sanctionType): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($sanctionType['sanction_type_id']); ?>
                                                </td>
                                                
                                                <td>
                                                    <strong><?php echo htmlspecialchars($sanctionType['severity_name']); ?></strong>
                                                </td>
                                                
                                                <td><?php echo htmlspecialchars($sanctionType['description']); ?></td>
                                                
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <?php if ($rol->hasPermission('INFRACTIONS', 'r')): ?>
                                                        <a href="view.php?id=<?php echo $sanctionType['sanction_type_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                        <?php endif; ?>
                                                        <?php if ($rol->hasPermission('INFRACTIONS', 'w')): ?>
                                                        <a href="edit.php?id=<?php echo $sanctionType['sanction_type_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $sanctionType['sanction_type_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
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
                <p>¿Está seguro que desea eliminar el tipo de sanción con ID: <strong id="sanctionTypeId"></strong>?</p>
                <p class="text-danger"><small>Esta acción es permanente y puede afectar a otros registros que hagan referencia a este tipo de sanción.</small></p>
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
let deleteSanctionTypeId = null;

function confirmDelete(id) {
    deleteSanctionTypeId = id;
    document.getElementById('sanctionTypeId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteSanctionTypeId) {
        // Mantenemos la eliminación por GET/redirección para simplicidad, como ya estaba
        window.location.href = 'index.php?delete_id=' + deleteSanctionTypeId; 
    }
});

// Inicialización de DataTables 🚀
$(document).ready(function() {
    
    // Contenido del encabezado personalizado para la vista de Impresión
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Tipos de Sanción</h2>
        </div>
    `;
    
    // Columnas a exportar: ID (0), Tipo (1), Descripción (2). Se excluye Acciones (3).
    const exportColumns = [0, 1, 2]; 
    
    if ($.fn.DataTable) {
        $('#sanctionTypesTable').DataTable({ 
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
                            text: 'Listado de Tipos de Sanción', 
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
                    title: 'Listado_TiposSancion_Seramer' 
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
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' 
            },
            // Orden por defecto
            order: [[0, 'desc']], 
            // Deshabilitar el ordenamiento en la columna de Acciones
            "columnDefs": [
                { "orderable": false, "targets": 3 },
                // Ocultar la columna ID por defecto, pero permitir exportarla
                { "visible": false, "targets": 0 }
            ]
        });
    } else {
        console.error("DataTables no está cargado.");
    }
});
</script>