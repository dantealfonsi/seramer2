<?php
// Vista de listado de infracciones
session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InfractionsController.php';

$infractionsController = new InfractionsController();

// 1. Obtener la tasa actual (Asume que el controlador tiene un método para esto)
$economicIndicators = $infractionsController->getLatestEconomicIndicators();

// 2. Verificar si no existen indicadores (o si la función devuelve null/false)
if (is_null($economicIndicators) || empty($economicIndicators)) {
    // Almacenar el mensaje de error para mostrarlo en esta misma página
    if (basename($_SERVER['PHP_SELF']) == 'index.php') {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => '🚨 **ATENCIÓN:** No existen indicadores económicos (UT/Euro) registrados. **No puede crear nuevas infracciones** hasta que se establezcan las tasas vigentes. Por favor, regístrelas primero.'
        ];
        
        $can_create_infraction = false;
        
    } else {
        $can_create_infraction = false; 
    }
} else {
    $can_create_infraction = true; // Todo bien, puede crear infracciones
}

// 🚨 CAMBIO CLAVE: Eliminar la paginación y búsqueda por servidor.
// Ahora solo se usan los filtros de columna/estado y se obtiene TODO el listado.
$filters = [
    // El filtro 'search' se elimina de aquí, lo manejará DataTables.
    'infraction_date' => $_GET['infraction_date'] ?? null,
    'infraction_status' => $_GET['infraction_status'] ?? null,
    'infraction_type_id' => $_GET['infraction_type_id'] ?? null,
    'stall_id' => $_GET['stall_id'] ?? null,
    'awardee_id' => $_GET['awardee_id'] ?? null,
];

// Limpiar el arreglo eliminando valores nulos o vacíos
$activeFilters = array_filter($filters);

$params = [
    // La paginación y el límite se eliminan.
    'filters' => $activeFilters,
    // El 'search' aquí es solo para pre-llenar el input, no para filtrar el resultado de la DB.
    'search' => $_GET['search'] ?? '' 
];

// Usar el controlador para obtener TODOS los datos (sin paginación de servidor)
// NOTA: Para conjuntos de datos muy grandes, se debe implementar Server-Side Processing
// con DataTables, pero para un listado simple, obtener todos es el camino más fácil.
$result = $infractionsController->index($params); 

// Extraer variables para la vista
$infractions = $result['infractions'];
// 🚨 CAMBIO: Se eliminan $current_page, $total_pages, $total_records. DataTables los maneja.
$search = $result['search']; // Se mantiene para pre-llenar el input
$page_title = $result['page_title'];
// 🚨 CAMBIO: Se ajusta $has_search para la lógica de la vista (opcional, DataTables tiene su propia info)
$has_search = !empty($activeFilters) || !empty($search); 
$stalls = $infractionsController->getStallsList();
$infraction_types = $infractionsController->getInfractionTypesList();

// Mapa para traducir los estados
$status_text_map = [
    'Reported' => 'Reportada',
    'In Process' => 'En Proceso',
    'Resolved' => 'Resuelta',
    'Cancelled' => 'Cancelada'
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
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-alert-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <?php if ($can_create_infraction): ?>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Infracción
                        </a>
                        <?php else: ?>
                        <a href="#" class="btn btn-secondary disabled" 
                        title="Debe registrar tasas económicas antes de crear una infracción">
                            <i class="ri-alert-line"></i> Nueva Infracción
                        </a>
                        <?php endif; ?>        
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form action="index.php" method="GET" class="card p-3 mb-4 shadow-sm">
                            <h6 class="card-title mb-3"><i class="ri-filter-2-line me-1"></i> Opciones de Filtrado de Columnas (No DataTables)</h6>
                            <div class="row g-3">
                                
                                <div class="col-md-3">
                                    <label for="search" class="form-label small">Búsqueda General (Inicial)</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                        placeholder="Nombre, Puesto, Tipo..." 
                                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="infraction_status" class="form-label small">Estado</label>
                                    <select class="form-select" id="infraction_status" name="infraction_status">
                                        <option value="">-- Todos los Estados --</option>
                                        <?php 
                                        // Usamos el mismo mapa de traducción para llenar las opciones
                                        $current_status = $_GET['infraction_status'] ?? '';
                                        foreach ($status_text_map as $key => $value): ?>
                                            <option value="<?php echo $key; ?>" 
                                                            <?php echo ($current_status === $key) ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="infraction_type_id" class="form-label small">Tipo de Infracción</label>
                                    <select class="form-select" id="infraction_type_id" name="infraction_type_id">
                                        <option value="">-- Todos los Tipos --</option>
                                        <?php 
                                        $current_type = $_GET['infraction_type_id'] ?? '';
                                        if (isset($infraction_types) && is_array($infraction_types)) {
                                            foreach ($infraction_types as $type) {
                                                $id = $type['infraction_type_id'];
                                                $name = $type['infraction_type_name'];
                                                echo "<option value=\"$id\" " . (($current_type == $id) ? 'selected' : '') . ">$name</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="infraction_date" class="form-label small">Fecha Específica</label>
                                    <input type="date" class="form-control" id="infraction_date" name="infraction_date" 
                                        value="<?php echo htmlspecialchars($_GET['infraction_date'] ?? ''); ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="stall_id" class="form-label small">Puesto (ID/Nro)</label>
                                    <input type="number" class="form-control" id="stall_id" name="stall_id" 
                                        placeholder="Ej: 15" 
                                        value="<?php echo htmlspecialchars($_GET['stall_id'] ?? ''); ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="awardee_id" class="form-label small">Adjudicatario (ID)</label>
                                    <input type="number" class="form-control" id="awardee_id" name="awardee_id" 
                                        placeholder="Ej: 42" 
                                        value="<?php echo htmlspecialchars($_GET['awardee_id'] ?? ''); ?>">
                                </div>

                                <div class="col-12 d-flex justify-content-end align-items-end">
                                    <a href="index.php" class="btn btn-outline-secondary me-2">Limpiar Filtros</a>
                                    <button type="submit" class="btn btn-info">
                                        <i class="ri-search-line"></i> Aplicar Filtros (Lado Servidor)
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if ($has_search && empty($activeFilters)): // Ahora $has_search solo indica si se usó el input de búsqueda global o filtros de columna ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Mostrando todos los registros (DataTables se encargará de la paginación).
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mx-3 mt-3" role="alert">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <?php if (empty($infractions)): ?>
                            <div class="text-center py-4">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron infracciones con ese criterio' : 'No hay infracciones registradas'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                <p class="text-muted">Comienza creando la primera infracción.</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Crear Primera Infracción
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle" id="infractionsTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th> <th>Adjudicatario</th>
                                            <th>Puesto</th>
                                            <th>Tipo</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($infractions as $infraction): ?>
                                        <tr>
                                            <td class="d-none d-print-table-row"><?php echo htmlspecialchars($infraction['infraction_id']); ?></td>
                                            <td>
                                                <strong>
                                                    <?php echo htmlspecialchars($infraction['adjudicatory_name']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($infraction['stall_number'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($infraction['infraction_type_name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                // Convertir la fecha para un formato legible en la tabla, pero usar el formato ISO para ordenar
                                                $infraction_date = new DateTime($infraction['infraction_datetime']);
                                                // Se añade data-order para que DataTables ordene correctamente por fecha
                                                echo '<span data-order="' . $infraction_date->format('Y-m-d H:i:s') . '">';
                                                echo $infraction_date->format('d/m/Y'); 
                                                echo '</span>';
                                                ?>
                                            </td>
                                            <td>
                                            <?php
                                            $status_colors = [
                                                'Reported' => 'warning',
                                                'In Process' => 'primary',
                                                'Resolved' => 'success',
                                                'Cancelled' => 'danger'
                                            ];
                                            $raw_status = $infraction['infraction_status'];
                                            
                                            $color = $status_colors[$raw_status] ?? 'secondary';
                                            $translated_status = $status_text_map[$raw_status] ?? 'Desconocido';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo htmlspecialchars($translated_status); // Muestra el estado traducido ?>
                                            </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="view.php?id=<?php echo $infraction['infraction_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $infraction['infraction_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Eliminar"
                                                            onclick="confirmDelete(<?php echo $infraction['infraction_id']; ?>)">
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Infracción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar la infracción con ID: <strong id="infractionId"></strong>?</p>
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

<script>
let deleteInfractionId = null;

function confirmDelete(id) {
    deleteInfractionId = id;
    document.getElementById('infractionId').textContent = id;
    
    // Asumiendo Bootstrap 5
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteInfractionId) {
        // Crear formulario para enviar la solicitud de eliminación
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteInfractionId;
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
    
    // Contenido del encabezado personalizado para la vista de Impresión
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Infracciones</h2>
        </div>
    `;
    
    // Columnas a exportar
    // Columnas: ID (0, oculta), Adjudicatario (1), Puesto (2), Tipo (3), Fecha (4), Estado (5)
    // Se excluye la Columna 6 (Acciones)
    const exportColumns = [1, 2, 3, 4, 5]; 
    
    if ($.fn.DataTable) {
        // DataTables se inicializa ahora con todas las funcionalidades (dom: 'Bfrtip')
        const infractionsTable = $('#infractionsTable').DataTable({ 
            // Habilita la extensión Responsive
            responsive: true,
            
            // Configuración de los botones de exportación (B), Filtro (f), Tabla (t), Información (i), Paginación (p)
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'portrait', // Vertical
                    pageSize: 'LETTER', 
                    exportOptions: {
                        columns: exportColumns // Exportar solo las columnas visibles de datos
                    },
                    // Personalización del PDF
                    customize: function (doc) {
                        doc.content.splice(0, 0, {
                            text: 'Servicio Autonómo de Mercados de Bermúdez', 
                            alignment: 'center', 
                            style: 'header1'
                        }, {
                            text: 'Listado de Infracciones', 
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
                    title: 'Listado_Infracciones_Seramer' 
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
                'colvis' // Botón para visibilidad de columnas
            ],
            // Configuración de idioma a español
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' 
            },
            // Orden por defecto: Fecha (Columna 4, índice 4), descendente
            order: [[4, 'desc']], 
             // Deshabilitar el ordenamiento en las columnas de ID y Acciones
            "columnDefs": [
                { "visible": false, "targets": 0 }, // Ocultar columna ID
                { "orderable": false, "targets": 6 } // Columna Acciones
            ]
        });

        // 🚨 CAMBIO: Mover el valor del filtro 'search' (si existe) a la caja de búsqueda de DataTables
        const initialSearchValue = '<?php echo addslashes($_GET['search'] ?? ''); ?>';
        if (initialSearchValue) {
            // Aplica el filtro global del DataTables con el valor de la búsqueda general del GET
            infractionsTable.search(initialSearchValue).draw();
        }
    } else {
        console.error("DataTables no está cargado.");
    }
});
</script>