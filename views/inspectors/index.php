<?php
// Vista de listado de inspectores

session_start();

// Incluir el controlador necesario
require_once __DIR__ . '/../../controllers/InspectorsController.php';

$inspectorsController = new InspectorsController();

require_once __DIR__ . '/../../models/StatisticalReportModel.php';
$statsModel = new StatisticalReportModel();
$dashboardStats = $statsModel->getDashboardStats();
$totalInspectors = $dashboardStats['total_inspectors'] ?? 0;

// --- LÓGICA DE ELIMINACIÓN (Manejo de POST para DELETE) ---
// La lógica de delete ya estaba en el código original, se mantiene.
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $deleteId = $_POST['id'];
    $deleteResult = $inspectorsController->delete($deleteId);

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    header("Location: index.php");
    exit;    
}
// ----------------------------------------------------------

// Mapa de traducción para el estado (Activo/Inactivo)
$status_translations = [
    '1' => 'Activo',
    '0' => 'Inactivo',
];

// Preparar parámetros de filtrado
// Los filtros deben coincidir con los campos de la tabla o la lógica del Model
$filters = [
    'search' => $_GET['search'] ?? '',
    'inspector_code' => $_GET['inspector_code'] ?? null,
    'full_name' => $_GET['full_name'] ?? null,
    'email' => $_GET['email'] ?? null,
    'is_active' => $_GET['is_active'] ?? null,
];

// Solo incluir filtros que tengan un valor distinto a null o cadena vacía
$activeFilters = array_filter($filters, fn($value) => $value !== null && $value !== '');

$params = [
    'filters' => $activeFilters,
];

$result = $inspectorsController->index($params);

// Si no se pudo obtener la lista, manejar el error
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'No se pudo cargar la lista de inspectores. ' . ($result['message'] ?? '')
    ];
}

$inspectors = $result['inspectors'] ?? [];
$page_title = $result['page_title'] ?? 'Listado de Inspectores';
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                             <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-user-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Crear Nuevo Inspector
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form action="index.php" method="GET">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="inspector_code" class="form-label small">Código Inspector</label>
                                            <input type="text" class="form-control" id="inspector_code" name="inspector_code" 
                                                placeholder="Ej: I-001" 
                                                value="<?php echo htmlspecialchars($_GET['inspector_code'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="email" class="form-label small">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                placeholder="ejemplo@correo.com" 
                                                value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="is_active" class="form-label small">Estado</label>
                                            <select class="form-select" id="is_active" name="is_active">
                                                <option value="">-- Todos los Estados --</option>
                                                <?php 
                                                $current_status = $_GET['is_active'] ?? '';
                                                foreach ($status_translations as $key => $value): 
                                                ?>
                                                    <option value="<?php echo $key; ?>" <?php echo (string)$current_status === $key ? 'selected' : ''; ?>>
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
                                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e0faff !important; color: #03c3ec;">
                                            <i class="ri-user-star-line" style="font-size: 1.4rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #03c3ec;"><?php echo number_format($totalInspectors); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Total de Inspectores Registrados</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($has_filters): ?>
                        <div class="mt-2 text-end">
                            <small class="text-muted">
                                <i class="ri-information-line me-1"></i> Resultados filtrados (<?php echo count($inspectors); ?> registros).
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>

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

                    <div class="card-body">
                        <?php if (empty($inspectors)): ?>
                            <div class="text-center py-4">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No se encontraron inspectores</h5>
                                <p class="text-muted">Ajusta los filtros o crea uno nuevo.</p>
                                <a href="create.php" class="btn btn-primary"><i class="ri-add-line"></i> Crear Nuevo Inspector</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="inspectorsTable" class="table table-striped table-hover align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Nombre Completo</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inspectors as $inspector): ?>
                                            <tr>
                                                <td><span class="badge bg-light text-primary border border-primary"><?php echo htmlspecialchars($inspector['inspector_code']); ?></span></td>
                                                <td><span class="fw-semibold"><?php echo htmlspecialchars($inspector['full_name']); ?></span></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-mail-line me-1 text-muted"></i>
                                                        <?php echo htmlspecialchars($inspector['email'] ?? 'N/A'); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-phone-line me-1 text-muted"></i>
                                                        <?php echo htmlspecialchars($inspector['phone_number'] ?? 'N/A'); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $is_active = $inspector['is_active'] == 1;
                                                    $status_class = $is_active ? 'success' : 'danger';
                                                    $status_text = $is_active ? 'Activo' : 'Inactivo';
                                                    ?>
                                                    <span class="badge bg-label-<?php echo $status_class; ?> bg-<?php echo $status_class; ?>">
                                                        <?php echo htmlspecialchars($status_text); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <a href="view.php?id=<?php echo htmlspecialchars($inspector['inspector_id']); ?>" class="btn btn-sm btn-outline-primary" title="Ver">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        <a href="edit.php?id=<?php echo htmlspecialchars($inspector['inspector_id']); ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                            <i class="ri-edit-line"></i>
                                                        </a>
                                                         <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDelete(<?php echo htmlspecialchars($inspector['inspector_id']); ?>, '<?php echo addslashes($inspector['full_name']); ?>', '<?php echo addslashes($inspector['inspector_code']); ?>')">
                                                             <i class="ri-delete-bin-line"></i>
                                                         </button>
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

<!-- El modal de Bootstrap se ha eliminado ya que usamos SweetAlert2 -->

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
// Función para desactivación con SweetAlert2
function confirmDelete(id, name, code) {
    Swal.fire({
        title: '¿Está seguro?',
        text: `El inspector "${name}" (Código: ${code}) será desactivado y no podrá ser asignado a nuevas tareas.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff3e1d',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
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
    
    // Contenido del encabezado personalizado para la vista de Impresión (se inyecta en messageTop)
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Inspectores</h2>
        </div>
    `;
    
    // Columnas a exportar
    // Columnas: Código (0), Nombre Completo (1), Email (2), Teléfono (3), Estado (4)
    // Se excluye la Columna 5 (Acciones)
    const exportColumns = [0, 1, 2, 3, 4]; 
    
    if ($.fn.DataTable) {
        $('#inspectorsTable').DataTable({ 
            // Habilita la extensión Responsive
            responsive: true,
            // Configuración de los botones de exportación (Bf para botones y buscador en la misma linea)
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3"Bf>rtip',
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
                        doc.content.splice(0, 0, {
                            text: 'Servicio Autonómo de Mercados de Bermúdez', 
                            alignment: 'center', 
                            style: 'header1'
                        }, {
                            text: 'Listado de Inspectores', 
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
                    title: 'Listado_Inspectores_Seramer' 
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
                                '   background-color: #343a40 !important; ' + 
                                '   color: white !important; ' + 
                                '   -webkit-print-color-adjust: exact; ' + 
                                '   text-align: left !important;' + 
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
                { "orderable": false, "targets": 5 } 
            ]
        });
    } else {
        console.error("DataTables no está cargado.");
    }
    
    // Mover el valor del filtro 'search' (si existe) a la caja de búsqueda de DataTables
    const initialSearchValue = '<?php echo addslashes($_GET['search'] ?? ''); ?>';
    if (initialSearchValue) {
        // Aplica el filtro global del DataTables con el valor de la búsqueda general del GET
        $('#inspectorsTable').DataTable().search(initialSearchValue).draw();
    }
});
</script>